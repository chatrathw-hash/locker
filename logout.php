<?php
session_start();

// ล้าง session ทั้งหมด
session_unset();     // ลบตัวแปร session ทั้งหมด
session_destroy();   // ทำลาย session

// ส่งกลับไปหน้า login
// ถ้าเป็น admin (ตรวจจาก URL หรือ session ก่อนล้าง)
// แต่เนื่องจาก session ถูกล้างแล้ว เราสามารถใช้ parameter หรือแยกไฟล์ก็ได้

// แนะนำ: สร้าง 2 ไฟล์แยกเพื่อความชัดเจน

// เวอร์ชันรวม (ตรวจจาก referrer หรือ parameter)
$redirect = 'login.html'; // default สำหรับผู้ใช้ปกติ

if (isset($_GET['admin']) && $_GET['admin'] == 1) {
    $redirect = 'login_admin.php'; // สำหรับ admin
}

header("Location: $redirect");
exit;
?>