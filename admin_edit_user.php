<?php
session_start();
require 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: admin_users.php");
    exit;
}

$user_id = $_GET['id'];

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM users WHERE user_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ไม่พบผู้ใช้นี้");
}

// บันทึกการแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $phone = trim($_POST['phone']);
    $fullname = trim($_POST['fullname']);
    $note = trim($_POST['note']);
    $active = isset($_POST['active']) ? 1 : 0;
    $new_passcode = $_POST['new_passcode'];
    $confirm_passcode = $_POST['confirm_passcode'];

    // ตรวจสอบเบอร์โทรซ้ำ (ยกเว้นตัวเอง)
    $check_phone = $pdo->prepare("SELECT user_id FROM users WHERE phone = :phone AND user_id != :id");
    $check_phone->execute([':phone' => $phone, ':id' => $user_id]);
    if ($check_phone->rowCount() > 0) {
        $error = "เบอร์โทรศัพท์นี้มีอยู่ในระบบแล้ว";
    }

    // รหัสผ่านใหม่
    if (!empty($new_passcode)) {
        if ($new_passcode !== $confirm_passcode) {
            $error = "รหัสผ่านใหม่ไม่ตรงกัน";
        } elseif (strlen($new_passcode) !== 4 || !ctype_digit($new_passcode)) {
            $error = "รหัสผ่านต้องเป็นตัวเลข 4 หลัก";
        } else {
            $passcode = $new_passcode;
        }
    } else {
        $passcode = $user['passcode'];
    }

    if (!isset($error)) {
        $sql = "UPDATE users SET 
                room_number = :room_number,
                phone = :phone,
                fullname = :fullname,
                note = :note,
                passcode = :passcode,
                active = :active
                WHERE user_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':room_number' => $room_number,
            ':phone' => $phone,
            ':fullname' => $fullname,
            ':note' => $note,
            ':passcode' => $passcode,
            ':active' => $active,
            ':id' => $user_id
        ]);

        $success = "บันทึกข้อมูลสำเร็จ!";
        $user = array_merge($user, $_POST); // อัปเดตแสดงผล
        $user['active'] = $active;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขผู้ใช้ - Safe Locker Admin</title>
    <link rel="stylesheet" href="styles_admin_edit.css">
</head>
<body>
    <div class="container">
        <h2>แก้ไขข้อมูลผู้ใช้</h2>

        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <label>เลขห้อง</label>
            <input type="text" name="room_number" value="<?php echo htmlspecialchars($user['room_number']); ?>" required>

            <label>เบอร์โทรศัพท์</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" maxlength="10" required>

            <label>ชื่อ-นามสกุล</label>
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>">

            <label>หมายเหตุ</label>
            <textarea name="note" rows="3"><?php echo htmlspecialchars($user['note']); ?></textarea>

            <label>สถานะการใช้งาน</label>
            <label class="checkbox">
                <input type="checkbox" name="active" <?php echo $user['active'] ? 'checked' : ''; ?>>
                เปิดใช้งาน (ถ้าไม่ติ๊ก = ปิดการใช้งาน)
            </label>

            <hr>

            <label>เปลี่ยนรหัสผ่านใหม่ (4 หลัก)</label>
            <input type="password" name="new_passcode" maxlength="4" inputmode="numeric" placeholder="password">

            <label>ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="confirm_passcode" maxlength="4" inputmode="numeric" placeholder ="confirm password">

            <div class="buttons">
                <button type="submit">บันทึก</button>
                <a href="admin_users.php" class="btn-back">กลับไปรายชื่อ</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('input[inputmode="numeric"]').forEach(el => {
            el.addEventListener('input', e => e.target.value = e.target.value.replace(/[^0-9]/g, ''));
        });
    </script>
</body>
</html>