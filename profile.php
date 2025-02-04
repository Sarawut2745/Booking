<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'navbar.php';

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background-color: #1D4ED8;
            /* Blue color */
        }

        body {
            font-family: 'Noto Sans Thai', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-4xl bg-white rounded-lg shadow-lg p-8">

            <!-- โปรไฟล์หัวข้อ -->
            <div class="profile-header p-6 rounded-lg text-white mb-6 shadow-md">
                <h1 class="text-3xl font-bold text-center">โปรไฟล์ของคุณ</h1>
            </div>

            <!-- ข้อมูลผู้ใช้ -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-lg">ชื่อผู้ใช้:</span>
                    <span class="text-gray-700"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-lg">อีเมล:</span>
                    <span class="text-gray-700"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-lg">ชื่อเต็ม:</span>
                    <span
                        class="text-gray-700"><?php echo htmlspecialchars($user['fullname'] ?? 'ยังไม่ได้ระบุ'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-lg">คำอธิบายตัวเอง:</span>
                    <span class="text-gray-700"><?php echo htmlspecialchars($user['bio'] ?? 'ยังไม่ได้ระบุ'); ?></span>
                </div>
            </div>

            <!-- ปุ่มแก้ไขโปรไฟล์ -->
            <div class="mt-8 text-center">
                <a href="edit_profile.php"
                    class="bg-blue-500 text-white py-2 px-6 rounded-lg text-lg font-semibold hover:bg-blue-600 transition duration-200">แก้ไขโปรไฟล์</a>

                <!-- ปุ่มย้อนกลับ -->
                <a href="index.php"
                    class="bg-green-500 text-white py-2 px-4 rounded-lg text-lg font-semibold hover:bg-green-700 transition duration-200">ย้อนกลับ</a>

            </div>
        </div>
    </div>

</body>

</html>