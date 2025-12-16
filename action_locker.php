<?php
session_start();
require 'db_connect.php';

// ตรวจสอบล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$locker_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($locker_id < 1) {
    die("ไม่พบตู้ที่เลือก");
}

// ดึงข้อมูลตู้
$sql = "SELECT l.*, u.user_id AS owner_id, u.phone AS owner_phone, u.fullname
        FROM lockers l
        LEFT JOIN users u ON l.user_id = u.user_id
        WHERE l.locker_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $locker_id]);
$locker = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$locker) {
    die("ไม่พบข้อมูลตู้นี้");
}

$user_id = $_SESSION['user_id'];
$user_phone = $_SESSION['phone'] ?? '';

// ประมวลผลเมื่อกดยืนยัน (ไม่ต้องกรอกรหัสผ่านแล้ว)
$message = '';
$type = ''; // success หรือ error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if ($locker['status'] == 0) { // ตู้ว่าง → ฝากของ
            $sql = "UPDATE lockers 
                    SET status = 1, 
                        phone_owner = :phone, 
                        user_id = :user_id, 
                        deposit_time = NOW() 
                    WHERE locker_id = :locker_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':phone' => $user_phone,
                ':user_id' => $user_id,
                ':locker_id' => $locker_id
            ]);

            $action = 'deposit';
            $detail = 'ฝากของโดยผู้ใช้';

        } else { // ตู้ถูกใช้งาน → ถอนของ (ตรวจสอบว่าเป็นเจ้าของ)
            if ($locker['owner_id'] != $user_id) {
                $message = "คุณไม่มีสิทธิ์ถอนของจากตู้นี้!";
                $type = 'error';
                $pdo->rollBack();
            } else {
                $sql = "UPDATE lockers 
                        SET status = 0, 
                            phone_owner = NULL, 
                            user_id = NULL, 
                            deposit_time = NULL 
                        WHERE locker_id = :locker_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':locker_id' => $locker_id]);

                $action = 'withdraw';
                $detail = 'ถอนของโดยเจ้าของ';
            }
        }

        if (!isset($message)) {
            // บันทึก transaction
            $sql = "INSERT INTO transactions 
                    (locker_id, user_id, phone, action, detail) 
                    VALUES (:locker_id, :user_id, :phone, :action, :detail)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':locker_id' => $locker_id,
                ':user_id' => $user_id,
                ':phone' => $user_phone,
                ':action' => $action,
                ':detail' => $detail
            ]);

            $pdo->commit();
            $message = $action === 'deposit' ? "ฝากของสำเร็จ! ตู้ #$locker_id พร้อมใช้งาน" : "ถอนของสำเร็จ! ตู้ #$locker_id ว่างแล้ว";
            $type = 'success';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตู้ #<?php echo $locker_id; ?> - Safe Locker</title>
    <link rel="stylesheet" href="styles_action_locker.css">
</head>
<body>
    <div class="container">
        <h2>ตู้ฝากของ #<?php echo sprintf('%02d', $locker_id); ?></h2>

        <div class="locker-info <?php echo $locker['status'] ? 'occupied' : 'available'; ?>">
            <p><strong>สถานะ:</strong> <?php echo $locker['status'] ? 'ถูกใช้งาน' : 'ว่าง'; ?></p>
            <?php if ($locker['status']): ?>
                <p><strong>เจ้าของปัจจุบัน:</strong> <?php echo htmlspecialchars($locker['phone_owner']); ?></p>
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($locker['fullname'] ?: '-'); ?></p>
                <p><strong>ฝากเมื่อ:</strong> <?php echo date('d/m/Y H:i', strtotime($locker['deposit_time'])); ?></p>
            <?php endif; ?>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo $type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($locker['status'] == 0 || ($locker['status'] == 1 && $locker['owner_id'] == $user_id)): ?>
            <form action="" method="post">
                <p class="instruction warning">
                    <?php echo $locker['status'] ? 'คุณกำลังจะถอนของออกจากตู้' : 'คุณกำลังจะฝากของลงในตู้'; ?><br>
                    <strong>กดยืนยันเพื่อดำเนินการทันที</strong>
                </p>

                <div class="buttons">
                    <button type="submit">
                        <?php echo $locker['status'] ? 'ยืนยันถอนของ' : 'ยืนยันฝากของ'; ?>
                    </button>
                    <a href="lockers.php" class="btn-back">ยกเลิก</a>
                </div>
            </form>
        <?php else: ?>
            <p class="warning">ตู้นี้ถูกใช้งานโดยผู้อื่น คุณไม่สามารถดำเนินการได้</p>
            <a href="lockers.php" class="btn-back single">กลับไปหน้าตู้ทั้งหมด</a>
        <?php endif; ?>

        <div class="back-link">
            <a href="lockers.php">← กลับหน้าตู้ทั้งหมด</a>
        </div>
    </div>
</body>
</html>