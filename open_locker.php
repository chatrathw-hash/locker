<?php
session_start();
// ถ้าต้องการตรวจสอบสิทธิ์แอดมิน ให้เพิ่มโค้ดตรวจสอบ SESSION ที่นี่
// เช่น if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }

require 'db_connect.php';

// ---------- ตั้งค่า MQTT Broker ของคุณ ----------
define('MQTT_HOST', '163.44.196.172');
define('MQTT_PORT', 1883);
define('MQTT_USERNAME', 'lockermqtt');
define('MQTT_PASSWORD', 'lockermqtt@DonAusDev01');
define('MQTT_CLIENT_ID', 'safe_locker_admin_' . uniqid());
// ------------------------------------------------

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ไม่พบ ID ผู้ใช้');
}

$user_id = (int)$_GET['id'];

// ดึงข้อมูลห้องจาก database
$sql = "SELECT room_number, fullname FROM users WHERE user_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('ไม่พบข้อมูลผู้ใช้');
}

$room_number = $user['room_number'];
$fullname    = $user['fullname'];

// Topic ที่อุปกรณ์ Locker จะ subscribe อยู่
// แนะนำรูปแบบนี้เพื่อให้ง่ายต่อการจัดการ
$topic = "safe_locker/open/{$room_number}";

// Payload คำสั่งเปิด (สามารถเปลี่ยนเป็น JSON ได้ตามที่อุปกรณ์ต้องการ)
$payload = "OPENYES|BYADMIN| ". $fullname;  
// หรือถ้าอุปกรณ์ต้องการ JSON:
// $payload = json_encode(['command' => 'open', 'by' => 'admin', 'name' => $fullname, 'timestamp' => time()]);

// ใช้ php-mqtt/client (ต้องติดตั้งผ่าน Composer ก่อน)
require 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

try {
    $connectionSettings = (new ConnectionSettings)
        ->setUsername(MQTT_USERNAME)
        ->setPassword(MQTT_PASSWORD)
        ->setConnectTimeout(10)
        ->setKeepAliveInterval(60);

    $mqtt = new MqttClient(MQTT_HOST, MQTT_PORT, MQTT_CLIENT_ID);
    $mqtt->connect($connectionSettings, true);

    // Publish คำสั่งเปิด (QoS 1 เพื่อให้แน่ใจว่าส่งถึงอย่างน้อย 1 ครั้ง)
    $mqtt->publish($topic, $payload, 1);

    $mqtt->disconnect();

    // แจ้งเตือนสำเร็จแล้วกลับไปหน้าจัดการผู้ใช้
    echo "<script>
            alert('ส่งคำสั่งเปิด Locker ห้อง {$room_number} สำเร็จแล้ว!');
            window.location='admin_users.php';
          </script>";
} catch (Exception $e) {
    // ถ้ามีข้อผิดพลาด (เช่น broker ไม่ตอบสนอง, username/password ผิด ฯลฯ)
    echo "<script>
            alert('เกิดข้อผิดพลาดในการส่งคำสั่ง MQTT:\\n" . addslashes($e->getMessage()) . "');
            window.history.back();
          </script>";
}
?>