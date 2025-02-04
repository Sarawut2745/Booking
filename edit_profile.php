<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'navbar.php';

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้");
}

// เมื่อส่งข้อมูลการแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $activity = "แก้ไขหน้าโปรไฟล์!";

    // อัปเดตข้อมูลในฐานข้อมูล
    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, bio = ? WHERE id = ?");
    $stmt->execute([$fullname, $bio, $user_id]);

     // Insert activity into 'activities' table
     $stmt = $pdo->prepare("INSERT INTO activities (activity) VALUES (?)");
     $stmt->execute([$activity]);

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">


    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto my-8 p-6 bg-white shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold mb-4">แก้ไขโปรไฟล์</h1>
        <form method="POST">
            <div class="mb-4">
                <label for="fullname" class="block text-sm font-medium text-gray-700">ชื่อเต็ม</label>
                <input type="text" id="fullname" name="fullname"
                    value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="bio" class="block text-sm font-medium text-gray-700">คำอธิบายตัวเอง</label>
                <textarea id="bio" name="bio"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded  text-lg hover:bg-green-700 transition duration-200">บันทึก</button>
             <!-- ปุ่มย้อนกลับ -->
             <a href="profile.php"
                    class="bg-green-500 text-white py-2 px-4 rounded-lg text-lg hover:bg-green-700 transition duration-200">ย้อนกลับ</a>
        </a>
        </form>
    </div>
</body>

</html>