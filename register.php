<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['name']);
    $username = trim($_POST['user_id']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบพื้นฐาน
    if ($password !== $confirm_password) {
        die("รหัสผ่านไม่ตรงกัน!");
    }

    // Hash รหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (full_name, username, phone, email, password) 
                VALUES (:full_name, :username, :phone, :email, :hashed_password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $full_name,
            ':username' => $username,
            ':phone' => $phone,
            ':email' => $email,
            ':hashed_password' => $hashed_password
        ]);

        echo "<h2>สมัครสมาชิกสำเร็จ!</h2>";
        echo "<a href='login.html'>ไปที่หน้าเข้าสู่ระบบ</a>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "ข้อผิดพลาด: ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว!";
        } else {
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>