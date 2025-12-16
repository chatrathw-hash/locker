<?php
require 'vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$host = '163.44.196.172';
$port = 1883;
$username = 'lockermqtt';
$password = 'lockermqtt@DonAusDev01';
$clientId = 'test_client_' . rand();

try {
    $connectionSettings = (new ConnectionSettings)
        ->setUsername($username)
        ->setPassword($password);

    $mqtt = new MqttClient($host, $port, $clientId);
    $mqtt->connect($connectionSettings, true);
    $mqtt->publish('safe_locker/open/test', 'OPEN_FROM_TEST', 1);
    $mqtt->disconnect();

    echo "ส่งข้อความทดสอบสำเร็จ! ไปดูใน MQTT Explorer ที่ topic safe_locker/open/test";
} catch (Exception $e) {
    echo "ผิดพลาด: " . $e->getMessage();
}
?>