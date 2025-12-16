<?php
session_start();
require 'db_connect.php';

// ตรวจสอบว่าเป็น admin (เพิ่ม logic จริงตามระบบคุณ เช่น จากตาราง admins)

// ประมวลผลเปิดตู้ manual
if (isset($_GET['force_open'])) {
    $locker_id = (int)$_GET['force_open'];
    try {
        $pdo->beginTransaction();

        // ตั้งตู้เป็นว่าง
        $sql = "UPDATE lockers 
                SET status = 0, phone_owner = NULL, user_id = NULL, deposit_time = NULL 
                WHERE locker_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $locker_id]);

        // บันทึก transaction
        $sql = "INSERT INTO transactions (locker_id, action, detail) 
                VALUES (:locker_id, 'admin_force_open', 'เปิดตู้ด้วยมือโดย admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':locker_id' => $locker_id]);

        $pdo->commit();
        $message = "เปิดตู้ #$locker_id สำเร็จ (Manual)!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงข้อมูลตู้
$sql = "SELECT l.locker_id, l.status, l.phone_owner, l.deposit_time, u.fullname, u.room_number
        FROM lockers l
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY l.locker_id ASC";
$stmt = $pdo->query($sql);
$lockers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการตู้ฝากของ (Admin) - Safe Locker</title>
    <link rel="stylesheet" href="styles_admin_lockers.css">
</head>
<body>
    <div class="container">
        <h2>จัดการตู้ฝากของทั้งหมด (Admin)</h2>
        <p class="subtitle">ตู้สีเขียว = ว่าง | ตู้สีแดง = ใช้งาน | คลิกปุ่มเพื่อจัดการ</p>

        <?php if (isset($message)): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="lockers-grid">
            <?php foreach ($lockers as $locker): ?>
                <div class="locker-card <?php echo $locker['status'] ? 'occupied' : 'available'; ?>">
                    <div class="locker-number">#<?php echo sprintf('%02d', $locker['locker_id']); ?></div>
                    <div class="locker-status">
                        <?php if ($locker['status']): ?>
                            <strong>ใช้งานอยู่</strong><br>
                            เบอร์: <?php echo htmlspecialchars($locker['phone_owner']); ?><br>
                            <?php if ($locker['fullname']): ?>
                                ชื่อ: <?php echo htmlspecialchars($locker['fullname']); ?><br>
                                ห้อง: <?php echo htmlspecialchars($locker['room_number']); ?><br>
                            <?php endif; ?>
                            ฝากเมื่อ: <?php echo date('d/m/Y H:i', strtotime($locker['deposit_time'])); ?>
                        <?php else: ?>
                            <strong>ว่าง</strong><br>
                            พร้อมใช้งาน
                        <?php endif; ?>
                    </div>
                    <div class="actions">
                        <a href="action_locker.php?id=<?php echo $locker['locker_id']; ?>" class="btn-manage">
                            จัดการปกติ
                        </a>
                        <a href="?force_open=<?php echo $locker['locker_id']; ?>" class="btn-force" 
                           onclick="return confirm('ยืนยันเปิดตู้ #<?php echo $locker['locker_id']; ?> ด้วยมือหรือไม่? (จะล้างข้อมูลเจ้าของทันที)');">
                            เปิดตู้ Manual
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="back-link">
            <a href="dashboard.php">← กลับหน้าหลัก Admin</a>
        </div>
    </div>
</body>
</html>