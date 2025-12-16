<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    try {
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone, email = :email WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':id' => $user_id
        ]);

        // อัปเดต session ด้วยชื่อใหม่
        $_SESSION['full_name'] = $full_name;

        echo "<h2 style='text-align:center; color:green;'>อัปเดตข้อมูลสำเร็จ!</h2>";
        echo "<p style='text-align:center;'>กำลังกลับไปหน้าแก้ไข...</p>";
        echo "<meta http-equiv='refresh' content='2;url=edit_profile.php'>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<p style='text-align:center; color:red;'>ข้อผิดพลาด: อีเมลนี้มีผู้ใช้อื่นใช้แล้ว</p>";
        } else {
            echo "<p style='text-align:center; color:red;'>เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
        }
        echo "<a href='edit_profile.php' style='display:block; text-align:center; margin-top:10px;'>กลับไปแก้ไขใหม่</a>";
    }
}
?>