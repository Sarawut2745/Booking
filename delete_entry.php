<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// ตรวจสอบว่าได้ส่ง `id` มาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// ตรวจสอบว่า entry เป็นของผู้ใช้ที่ล็อกอินอยู่หรือไม่
$query = "SELECT * FROM entries WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id, $_SESSION['user_id']]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    // ถ้าไม่พบ entry หรือไม่ใช่ของผู้ใช้คนนี้
    header("Location: index.php");
    exit();
}

$activity = "ลบรายการ!";
// ลบรายการ
$deleteQuery = "DELETE FROM entries WHERE id = ?";
$stmt = $pdo->prepare($deleteQuery);
$stmt->execute([$id]);

// Insert activity into 'activities' table
$stmt = $pdo->prepare("INSERT INTO activities (activity) VALUES (?)");
$stmt->execute([$activity]);

header("Location: index.php");
exit();
?>