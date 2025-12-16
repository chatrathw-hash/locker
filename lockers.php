<?php
session_start();
require 'db_connect.php';

// ดึงข้อมูลตู้ทั้งหมด
$sql = "SELECT l.locker_id, l.status, l.phone_owner, l.deposit_time, u.fullname, u.room_number
        FROM lockers l
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY l.locker_id ASC";
$stmt = $pdo->query($sql);
$lockers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะตู้ฝากของ - Safe Locker</title>
    <link rel="stylesheet" href="styles_lockers.css">
</head>
<body>
    <div class="container">
        <h2>สถานะตู้ฝากของทั้งหมด</h2>
        <p class="subtitle">ตู้สีเขียว = ว่าง | ตู้สีแดง = มีคนใช้งาน</p>

        <div class="lockers-grid">
            <?php foreach ($lockers as $locker): ?>
                <div class="locker-card <?php echo $locker['status'] ? 'occupied' : 'available'; ?>">
                    <div class="locker-number">#<?php echo sprintf('%02d', $locker['locker_id']); ?></div>
                    <div class="locker-status">
                        <?php if ($locker['status']): ?>
                            <strong>ใช้งานอยู่</strong><br>
                            เบอร์: <?php echo htmlspecialchars($locker['phone_owner']); ?><br>
                            <?php if ($locker['fullname']): ?>
                                ชื่อ: <?php echo htmlspecialchars($locker['fullname']); ?><br>
                                ห้อง: <?php echo htmlspecialchars($locker['room_number']); ?><br>
                            <?php endif; ?>
                            ฝากเมื่อ: <?php echo date('d/m/Y H:i', strtotime($locker['deposit_time'])); ?>
                        <?php else: ?>
                            <strong>ว่าง</strong><br>
                            พร้อมใช้งาน
                        <?php endif; ?>
                    </div>
                    <a href="action_locker.php?id=<?php echo $locker['locker_id']; ?>" class="btn-action">
                        <?php echo $locker['status'] ? 'จัดการตู้' : 'ฝากของ'; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="back-link">
            <a href="dashboard.php">← กลับหน้าหลัก</a>
        </div>
    </div>
</body>
</html>