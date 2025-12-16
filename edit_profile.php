<?php
session_start();
require 'db_connect.php';

// ตรวจสอบว่าล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้ปัจจุบัน
$sql = "SELECT room_number, phone, passcode, fullname, note FROM users WHERE user_id = :user_id AND active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit;
}

// ประมวลผลเมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $fullname    = trim($_POST['fullname']);
    $note        = trim($_POST['note']);
    $new_passcode = $_POST['new_passcode'];
    $confirm_passcode = $_POST['confirm_passcode'];

    // ตรวจสอบรหัสผ่านใหม่ (ถ้ากรอก)
    if (!empty($new_passcode)) {
        if ($new_passcode !== $confirm_passcode) {
            $error = "รหัสผ่านใหม่ไม่ตรงกัน";
        } elseif (strlen($new_passcode) !== 4 || !ctype_digit($new_passcode)) {
            $error = "รหัสผ่านต้องเป็นตัวเลข 4 หลักเท่านั้น";
        } else {
            $update_passcode = $new_passcode;
        }
    } else {
        $update_passcode = $user['passcode']; // คงเดิม
    }

    if (!isset($error)) {
        try {
            $sql = "UPDATE users SET 
                    room_number = :room_number,
                    fullname = :fullname,
                    note = :note,
                    passcode = :passcode
                    WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':room_number' => $room_number,
                ':fullname'    => $fullname,
                ':note'        => $note,
                ':passcode'    => $update_passcode,
                ':user_id'     => $user_id
            ]);

            $success = "บันทึกข้อมูลสำเร็จ!";
            // อัปเดต session
            $_SESSION['fullname'] = $fullname;
            $_SESSION['room_number'] = $room_number;

            // รีเฟรชข้อมูลใหม่
            $user['room_number'] = $room_number;
            $user['fullname']    = $fullname;
            $user['note']        = $note;
        } catch (Exception $e) {
            $error = "เกิดข้อผิดพลาดในการบันทึก";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูล - Safe Locker</title>
    <link rel="stylesheet" href="edit_profile.css">
</head>
<body>
    <div class="container">
        <h2>แก้ไขข้อมูลส่วนตัว</h2>
        <p class="welcome">ยินดีต้อนรับ <?php echo htmlspecialchars($user['fullname'] ?: 'ผู้ใช้'); ?> (ห้อง <?php echo htmlspecialchars($user['room_number']); ?>)</p>

        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="edit_profile.php" method="post">
            <label for="phone">เบอร์โทรศัพท์ (ไม่สามารถแก้ไขได้)</label>
            <input type="text" value="<?php echo htmlspecialchars($user['phone']); ?>" disabled>

            <label for="room_number">เลขห้อง <span class="required">*</span></label>
            <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($user['room_number']); ?>" required>

            <label for="fullname">ชื่อ-นามสกุล</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" placeholder="ไม่บังคับ">

            <label for="note">หมายเหตุ</label>
            <textarea id="note" name="note" rows="3" placeholder="เช่น ข้อมูลเพิ่มเติม..."><?php echo htmlspecialchars($user['note']); ?></textarea>

            <hr>

            <label for="new_passcode">รหัสผ่านใหม่ (4 หลัก) <small>(เว้นว่างหากไม่ต้องการเปลี่ยน)</small></label>
            <input type="password" id="new_passcode" name="new_passcode" maxlength="4" inputmode="numeric" pattern="[0-9]{4}">

            <label for="confirm_passcode">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" id="confirm_passcode" name="confirm_passcode" maxlength="4" inputmode="numeric" pattern="[0-9]{4}">

            <button type="submit">บันทึกการเปลี่ยนแปลง</button>
        </form>

        <div class="links">
            <a href="dashboard.php">กลับหน้าหลัก</a>
            <a href="logout.php" class="logout">ออกจากระบบ</a>
        </div>
    </div>

    <script>
        // พิมพ์ได้เฉพาะตัวเลขในช่องรหัสผ่าน
        document.querySelectorAll('input[inputmode="numeric"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
</body>
</html>