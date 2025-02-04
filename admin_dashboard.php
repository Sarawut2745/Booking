<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// คำนวณ Total Users
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_users = $result['total_users'];


// คำนวณ Total List
$stmt = $pdo->prepare("SELECT COUNT(*) as total_entries FROM entries");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_entries = $result['total_entries'];

// ดึงข้อมูลผลรวมของ amount จากตาราง entries
$stmt = $pdo->prepare("SELECT SUM(amount) as total_amount FROM entries");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_amount = $result['total_amount'] ?: 0; // ถ้าไม่มีค่า ให้แสดง 0

// ดึงข้อมูลกิจกรรมล่าสุดจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT activity, timestamp FROM activities ORDER BY timestamp DESC LIMIT 5");
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);


// PHP timeAgo function (Place this in the appropriate PHP file, not in JavaScript)
function timeAgo($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    $minutes = round($seconds / 60);           // value 60 is seconds
    $hours = round($seconds / 3600);           // value 3600 is 60 minutes * 60 sec
    $days = round($seconds / 86400);           // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks = round($seconds / 604800);         // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months = round($seconds / 2629440);       // value 2629440 is ((365+365+365+365+365)/5/12/30) 
    $years = round($seconds / 31553280);       // value 31553280 is (365+365+365+365+365)/5

    if ($seconds <= 60) {
        return "เมื่อสักครู่นี้";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "หนึ่งนาทีที่แล้ว" : "$minutes นาทีที่แล้ว";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "หนึ่งชั่วโมงที่แล้ว" : "$hours ชั่วโมงที่แล้ว";
    } else if ($days <= 7) {
        return ($days == 1) ? "เมื่อวานนี้" : "$days วันก่อน";
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        return ($weeks == 1) ? "หนึ่งสัปดาห์ที่แล้ว" : "$weeks สัปดาห์ที่แล้ว";
    } else if ($months <= 12) {
        return ($months == 1) ? "หนึ่งเดือนที่แล้ว" : "$months เดือนที่แล้ว";
    } else {
        return ($years == 1) ? "หนึ่งปีที่แล้ว" : "$years ปีที่แล้ว";
    }    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="bg-gradient-to-b from-blue-800 to-blue-900 text-white w-82 min-h-screen shadow-xl">
            <div class="p-6">
                <h1 class="text-3xl font-bold flex items-center">
                    <i class="fas fa-shield-alt mr-3"></i>
                    แผงผู้ดูแลระบบ
                </h1>
                <div class="mt-4 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="font-medium">ยินดีตอนรับ,</p>
                        <p class="text-sm opacity-80"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                </div>
            </div>

            <nav class="mt-8">
                <div class="px-4 py-2 text-xs uppercase text-gray-300">เมนูหลัก</div>
                <a href="admin_dashboard.php"
                    class="flex items-center py-3 px-6 text-gray-100 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-home w-5"></i>
                    <span class="ml-3">แดชบอร์ด</span>
                </a>
                <a href="manage_users.php"
                    class="flex items-center py-3 px-6 text-gray-100 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">การจัดการ ผู้ใช้</span>
                </a>
                <!-- <a href="manage_accounts.php"
                    class="flex items-center py-3 px-6 text-gray-100 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-wallet w-5"></i>
                    <span class="ml-3">Manage Accounts</span>
                </a>
                <a href="reports.php"
                    class="flex items-center py-3 px-6 text-gray-100 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="ml-3">Reports</span>
                </a> -->

                <div class="px-4 py-2 mt-8 text-xs uppercase text-gray-300">ระบบ</div>
                <a href="settings.php"
                    class="flex items-center py-3 px-6 text-gray-100 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">การตั้งค่า</span>
                </a>
            </nav>

            <div class="mt-auto p-6">
                <a href="logout.php"
                    class="flex items-center justify-center bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    ออกจากระบบ
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden">
            <!-- Top Navigation -->
            <div class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-8 py-4">
                    <div class="flex items-center">
                        <button class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="ml-6">
                            <h2 class="text-2xl font-semibold text-gray-800">ภาพรวม แดชบอร์ด</h2>
                            <p class="text-sm text-gray-600">ยินดีตอนรับสู่ หน้าแผนควบคุม</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="p-2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bell"></i>
                        </button>
                        <button class="p-2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-envelope"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="ml-4">
                                    <h3 class="text-sm text-gray-500">จำนวนผู้ใช้</h3>
                                    <p class="text-2xl font-semibold"><?php echo htmlspecialchars($total_users); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                                <i class="fas fa-list text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm text-gray-500">จำนวนรายการ</h3>
                                <p class="text-2xl font-semibold"><?php echo htmlspecialchars($total_entries); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-dollar-sign text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm text-gray-500">จำนวนเงินทั้งหมด</h3>
                                <p class="text-2xl font-semibold"><?php echo htmlspecialchars($total_amount); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="mt-8 bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold">กิจกรรมล่าสุด</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($activities as $activity): ?>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                    <p class="ml-3 text-sm text-gray-600">
                                        <?php echo htmlspecialchars($activity['activity']); ?>
                                        <span class="text-gray-400">- <?php echo timeAgo($activity['timestamp']); ?></span>
                                    </p>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>


<script>
    // Add any JavaScript functionality here
    document.addEventListener('DOMContentLoaded', function () {
        // Example: Toggle sidebar
        const menuButton = document.querySelector('.fa-bars').parentElement;
        const sidebar = document.querySelector('aside');

        menuButton.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });
    });
</script>
</body>

</html>