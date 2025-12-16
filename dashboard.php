<!--<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
-->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="stylesheet" href="styles_dashboard.css">
</head>
<body>
    <div class="container">
        <h2>Safe Locker</h2>
        <p>คุณเข้าสู่ระบบสำเร็จแล้ว</p>
        <a href="admin_users.php">แก้ไขข้อมูลส่วนตัว</a>
        <a href="lockers.php">จัดการตู้ฝากของ</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>
</body>
</html>