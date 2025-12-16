<?php
$host = 'localhost';
$dbname = 'db_safe_locker';
$username = 'root';     // แก้ตามของเครื่องคุณ
$password = '1234';         // แก้ตามของเครื่องคุณ

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่ได้: " . $e->getMessage());
}
?>