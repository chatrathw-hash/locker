<?php
session_start();
// ตรวจสอบสิทธิ์แอดมินที่นี่ถ้าต้องการ

require 'db_connect.php';

if (!isset($_GET['id'])) {
    die('ไม่พบ ID ผู้ใช้');
}

$user_id = $_GET['id'];

try {
    // ดึงข้อมูลห้องเพื่อแสดงผล/บันทึก log
    $stmt = $pdo->prepare("SELECT room_number, fullname FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('ไม่พบผู้ใช้นี้');
    }

    // ==== ที่นี่ใส่ logic การเปิด locker จริง ====
    // เช่น ส่งคำสั่งไปยัง Arduino, Raspberry Pi, Relay, API ฯลฯ
    // ตัวอย่างจำลอง:
    // file_get_contents("http://192.168.1.100/open?room=" . $user['room_number']);
    // หรือใช้ MQTT, WebSocket ฯลฯ

    // บันทึก log การเปิดแบบ manual (แนะนำ)
    $log_stmt = $pdo->prepare("INSERT INTO locker_logs (user_id, room_number, action, opened_by, opened_at) 
                               VALUES (?, ?, 'manual_open', 'admin', NOW())");
    $log_stmt->execute([$user_id, $user['room_number']]);

    // แสดงผลสำเร็จ
    echo "<h2>เปิด Locker สำเร็จ!</h2>";
    echo "<p>ห้อง: " . htmlspecialchars($user['room_number']) . "<br>";
    echo "ชื่อ: " . htmlspecialchars($user['fullname'] ?: '-') . "</p>";
    echo '<a href="admin_users.php">กลับไปจัดการผู้ใช้</a>';

} catch (Exception $e) {
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>