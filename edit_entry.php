<?php
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    die("เกิดข้อผิดพลาด: ไม่พบรหัสผู้ใช้");
}

$user_id = $_SESSION['user_id']; // ดึง user_id จาก session
include 'db.php';
include 'navbar.php';

// ดึงรายการที่ต้องการแก้ไขจากฐานข้อมูล
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM entries WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$entry) {
        die("เกิดข้อผิดพลาด: ไม่พบรายการที่ต้องการแก้ไขหรือคุณไม่มีสิทธิ์แก้ไขรายการนี้");
    }
} else {
    die("เกิดข้อผิดพลาด: ไม่พบ ID ของรายการที่ต้องการแก้ไข");
}

// จัดการการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $type = $_POST['type'] ?? '';
    $activity = "แก้ไขรายการ!";

    // ตรวจสอบข้อมูลฟอร์ม
    if (empty($description) || empty($amount) || empty($type)) {
        die("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    try {
        // อัปเดตข้อมูลในฐานข้อมูล
        $query = "UPDATE entries SET description = ?, amount = ?, type = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$description, $amount, $type, $id, $user_id]);

         // Insert activity into 'activities' table
         $stmt = $pdo->prepare("INSERT INTO activities (activity) VALUES (?)");
         $stmt->execute([$activity]);

        // แสดง Modal สำเร็จ
        $modal_message = 'success';
    } catch (PDOException $e) {
        // แสดง Modal ไม่สำเร็จ
        $modal_message = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขรายการ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">

    <style>
        /* Animation สำหรับ Checkmark */
        .checkmark {
            width: 50px;
            height: 50px;
            stroke: #34D399;
            stroke-width: 4;
            fill: none;
            stroke-dasharray: 160; /* ความยาวทั้งหมดของเส้น */
            stroke-dashoffset: 160; /* ทำให้เส้นไม่แสดง */
            animation: checkmark 1s ease forwards; /* เริ่มต้นเมื่อเปิด */
        }

        /* Animation: วาด Checkmark */
        @keyframes checkmark {
            to {
                stroke-dashoffset: 0; /* เส้นทั้งหมดจะถูกวาด */
            }
        }

        /* กากบาท (Cross) SVG Animation */
        .svg-error {
            width: 50px;
            height: 50px;
            animation: errorAnimation 1s ease forwards;
        }

        /* Animation สำหรับการหมุนและการเปลี่ยนสี */
        @keyframes errorAnimation {
            0% {
                transform: rotate(0deg);
                stroke: #E53E3E; /* สีเริ่มต้นเป็นแดง */
            }
            50% {
                transform: rotate(90deg); /* หมุน 90 องศา */
                stroke: #F56565; /* เปลี่ยนสีเป็นแดงอ่อน */
            }
            100% {
                transform: rotate(180deg); /* หมุน 180 องศา */
                stroke: #E53E3E; /* กลับสีเป็นแดง */
            }
        }

        /* Modal Styles */
        #success-modal, #error-modal {
            display: none; /* ซ่อน Modal */
            justify-content: center;
            align-items: center;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5); /* Overlay สีเทา */
        }

        /* Modal show */
        #success-modal.show, #error-modal.show {
            display: flex; /* แสดง Modal */
        }

        #modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            transform: scale(0.8);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        #success-modal.show #modal-content, #error-modal.show #modal-content {
            transform: scale(1);
            opacity: 1;
        }

        body {
            font-family: 'Noto Sans Thai', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Modal สำเร็จ -->
    <div id="success-modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div id="modal-content" class="bg-white p-8 rounded-lg shadow-xl w-1/3">
            <div class="flex justify-center mb-4">
                <!-- Checkmark SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="checkmark" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-4 text-center text-green-600">อัพเดทสำเร็จ</h3>
            <p class="mb-4 text-center text-gray-700">ข้อมูลของคุณถูกอัพเดทเรียบร้อยแล้ว</p>
            <div class="flex justify-center">
                <button onclick="window.location.href = 'index.php';"
                    class="bg-green-500 text-white py-2 px-6 rounded-md hover:bg-green-600 focus:outline-none">
                    ปิด
                </button>
            </div>
        </div>
    </div>

    <!-- Modal ไม่สำเร็จ -->
    <div id="error-modal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div id="modal-content" class="bg-white p-8 rounded-lg shadow-xl w-1/3">
            <div class="flex justify-center mb-4">
                <!-- กากบาท (Cross) SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="svg-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold mb-4 text-center text-red-600">เกิดข้อผิดพลาด</h3>
            <p class="mb-4 text-center text-gray-700">การอัปเดตข้อมูลไม่สำเร็จ กรุณาลองใหม่อีกครั้ง</p>
            <div class="flex justify-center">
                <button onclick="window.location.href = 'edit_entry.php?id=<?php echo htmlspecialchars($id); ?>';"
                    class="bg-red-500 text-white py-2 px-6 rounded-md hover:bg-red-600 focus:outline-none">
                    ปิด
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto my-8 p-6 bg-white shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold text-center mb-4">แก้ไขรายการ</h1>

        <form action="edit_entry.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">รายละเอียด</label>
                <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($entry['description']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">จำนวนเงิน</label>
                <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($entry['amount']); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">ประเภท</label>
                <select id="type" name="type" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    <option value="income" <?php echo $entry['type'] == 'income' ? 'selected' : ''; ?>>รายรับ</option>
                    <option value="expense" <?php echo $entry['type'] == 'expense' ? 'selected' : ''; ?>>รายจ่าย</option>
                </select>
            </div>

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">อัพเดท</button>
            <a href="index.php">
                <button type="button" class="bg-green-500 text-white py-2 px-4 rounded">ย้อนกลับ</button>
            </a>
        </form>
    </div>

    <script>
        <?php if (isset($modal_message) && $modal_message == 'success'): ?>
            document.getElementById("success-modal").classList.add("show");
            setTimeout(function() {
                window.location.href = "index.php"; // ปิด modal และไปหน้า index หลังจาก 3 วินาที
            }, 3000); // 3 วินาที
        <?php elseif (isset($modal_message) && $modal_message == 'error'): ?>
            document.getElementById("error-modal").classList.add("show");
        <?php endif; ?>
    </script>
</body>
</html>
