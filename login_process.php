<?php
session_start();
require 'db_connect.php'; // ไฟล์เชื่อมต่อฐานข้อมูล (ดูด้านล่าง)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $passcode = $_POST['passcode'];

    if (empty($phone) || empty($passcode)) {
        die("กรุณากรอกข้อมูลให้ครบ");
    }

    try {
        $sql = "SELECT user_id, fullname, room_number, passcode FROM users WHERE phone = :phone AND active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $passcode === $user['passcode']) { // ในระบบจริงควรใช้ password_verify ถ้า hash
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['room_number'] = $user['room_number'];
            $_SESSION['phone'] = $phone;

            header("Location: dashboard.php"); // ไปหน้าหลักหลังล็อคอิน
            exit;
        } else {
            echo "<h3 style='text-align:center; color:red; margin-top:50px;'>เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง</h3>";
            echo "<p style='text-align:center;'><a href='login.html'>กลับไปล็อคอินใหม่</a></p>";
        }
    } catch (Exception $e) {
        die("เกิดข้อผิดพลาด: " . $e->getMessage());
    }
}
?>