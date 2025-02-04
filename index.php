<?php
session_start();

// ตรวจสอบว่าเซสชันผู้ใช้มีการตั้งค่าชื่อผู้ใช้แล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include 'navbar.php';

// ดึงประเภทที่เลือกจากฟอร์มตัวกรอง (ถ้ามี)
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// กำหนดค่าช่วงเวลาเริ่มต้นและสิ้นสุด
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// สร้างคำสั่ง SQL ตามตัวกรองประเภทและช่วงเวลา
$query = 'SELECT * FROM entries WHERE user_id = :user_id';
$params = ['user_id' => $_SESSION['user_id']];

if ($type_filter != '') {
    $query .= ' AND type = :type';
    $params['type'] = $type_filter;
}

if ($start_date != '') {
    // เปลี่ยนให้ตัดเวลาจากวันที่
    $query .= ' AND DATE(date) >= :start_date';
    $params['start_date'] = $start_date;
}

if ($end_date != '') {
    // เปลี่ยนให้ตัดเวลาจากวันที่
    $query .= ' AND DATE(date) <= :end_date';
    $params['end_date'] = $end_date;
}

$query .= ' ORDER BY date DESC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมุดรายรับรายจ่าย</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Animation styles */
        .fade-in {
            animation: fadeIn 0.2s ease-in-out forwards;
        }

        .fade-out {
            animation: fadeOut 0.2s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(0.9);
            }
        }

        /* Theme transitions */
        * {
            transition: background-color 0.3s, border-color 0.3s, color 0.2s;
        }

        /* Dark mode styles */
        html.dark body {
            background-color: #111827;
            color: #e5e7eb;
        }

        html.dark .bg-white {
            background-color: #1f2937;
        }

        html.dark .text-gray-800 {
            color: #f3f4f6;
        }

        html.dark .text-gray-700 {
            color: #d1d5db;
        }

        html.dark .text-gray-600 {
            color: #9ca3af;
        }

        html.dark .border-gray-200 {
            border-color: #374151;
        }

        html.dark .bg-gray-50 {
            background-color: #374151;
        }

        html.dark input,
        html.dark select {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }

        html.dark .hover\:bg-gray-50:hover {
            background-color: #2d3748;
        }

        .theme-toggle {
            position: fixed;
            bottom: 1rem;
            /* จากเดิม top: 1rem; */
            right: 1rem;
            padding: 0.75rem;
            border-radius: 9999px;
            transition: all 0.3s;
            z-index: 50;
        }

        html.light .theme-toggle {
            background-color: #f3f4f6;
            color: #4b5563;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        html.dark .theme-toggle {
            background-color: #374151;
            color: #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Base styles */
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background-color: #f0f4f8;
        }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle theme">
        <i class="fas fa-moon dark:hidden"></i>
        <i class="fas fa-sun hidden dark:inline"></i>
    </button>


    <div class="container mx-auto my-8 p-6">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">สมุดรายรับรายจ่าย</h1>

            <form method="GET" action="index.php"
                class="mb-8 flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-6">
                <div class="flex items-center space-x-4">
                    <label for="type" class="text-sm font-semibold text-gray-600">ประเภท:</label>
                    <select name="type" id="type"
                        class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50">
                        <option value="">ทั้งหมด</option>
                        <option value="income" <?php echo $type_filter == 'income' ? 'selected' : ''; ?>>รายรับ</option>
                        <option value="expense" <?php echo $type_filter == 'expense' ? 'selected' : ''; ?>>รายจ่าย
                        </option>
                    </select>
                </div>

                <div class="flex items-center space-x-4">
                    <label for="start_date" class="text-sm font-semibold text-gray-600">เริ่มต้น:</label>
                    <input type="date" id="start_date" name="start_date"
                        value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>"
                        class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50">
                </div>

                <div class="flex items-center space-x-4">
                    <label for="end_date" class="text-sm font-semibold text-gray-600">สิ้นสุด:</label>
                    <input type="date" id="end_date" name="end_date"
                        value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>"
                        class="border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50">
                </div>

                <button type="submit"
                    class="bg-indigo-600 text-white py-3 px-6 rounded-lg transform transition duration-200 hover:bg-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    กรอง
                </button>

                <!-- ปุ่มดาวน์โหลด Excel -->
                <div class="flex justify-end mb-6">
                    <a href="export_excel.php"
                        class="bg-blue-500 text-white py-3 px-6 rounded-lg transform transition duration-200 hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <i class="fas fa-file-excel mr-2"></i> ดาวน์โหลด Excel
                    </a>
                    <a href="export_pdf.php?type=" + encodeURIComponent(document.getElementById('type').value)
                        + '&start_date=' + encodeURIComponent(document.getElementById('start_date').value)
                        + '&end_date=' + encodeURIComponent(document.getElementById('end_date').value);"
                        class="bg-red-500 text-white py-3 px-6 rounded-lg transform transition duration-200 hover:bg-red-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-400">
                        <i class="fas fa-file-pdf mr-2"></i> ดาวน์โหลด PDF
                    </a>
                </div>

            </form>

            <div class="flex justify-end mb-6">
                <button
                    class="bg-green-500 text-white py-3 px-6 rounded-lg transform transition duration-200 hover:bg-green-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-400"><a
                        href="add_entry.php">
                        <i class="fas fa-plus mr-2"></i>เพิ่มรายการ
                    </a>
                </button>
            </div>

            <div class="overflow-hidden rounded-xl shadow-sm border border-gray-200">
                <table class="min-w-full bg-white">
                    <thead class="bg-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">วันที่</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">รายละเอียด</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">จำนวน</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">ประเภท</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($entries as $entry): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo $entry['date']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo $entry['description']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo number_format($entry['amount'], 2); ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium <?php echo $entry['type'] == 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; ?>">
                                        <?php echo ucfirst($entry['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm flex gap-2">
                                    <a href="edit_entry.php?id=<?php echo $entry['id']; ?>"
                                        class="bg-yellow-500 text-white py-2 px-4 rounded-lg flex items-center hover:bg-yellow-600 hover:shadow-md transition duration-200">
                                        <i class="fas fa-edit mr-2"></i> แก้ไข
                                    </a>
                                    <button onclick="showDeleteModal(<?php echo $entry['id']; ?>)"
                                        class="bg-red-500 text-white py-2 px-4 rounded-lg flex items-center hover:bg-red-600 hover:shadow-md transition duration-200">
                                        <i class="fas fa-trash-alt mr-2"></i> ลบ
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
        <div id="modal-content"
            class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-xl w-full max-w-md transform scale-90 opacity-0">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900 mb-6">
                    <svg class="w-8 h-8 text-red-500 dark:text-red-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">ยืนยันการลบ</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?</p>
                <div class="flex justify-center space-x-4">
                    <button id="confirm-delete"
                        class="bg-red-500 text-white py-2 px-6 rounded-lg hover:bg-red-600 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-400">ยืนยัน</button>
                    <button id="cancel-delete"
                        class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 py-2 px-6 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const addButton = document.querySelector('.add-entry-button'); // Add entry button
            const elementsToUpdate = document.querySelectorAll('.theme-toggleable'); // Any element with the class "theme-toggleable"

            if (html.classList.contains('dark')) {
                // Remove dark mode classes
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');

                // Update all elements
                elementsToUpdate.forEach(element => {
                    if (element.classList.contains('bg-emerald-500')) {
                        element.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                        element.classList.add('bg-emerald-300', 'hover:bg-emerald-400'); // Light mode colors for bg
                    }
                    if (element.classList.contains('text-white')) {
                        element.classList.remove('text-white');
                        element.classList.add('text-gray-800'); // Text color for light mode
                    }
                });
            } else {
                // Add dark mode classes
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');

                // Update all elements
                elementsToUpdate.forEach(element => {
                    if (element.classList.contains('bg-emerald-300')) {
                        element.classList.remove('bg-emerald-300', 'hover:bg-emerald-400');
                        element.classList.add('bg-emerald-500', 'hover:bg-emerald-600'); // Dark mode colors for bg
                    }
                    if (element.classList.contains('text-gray-800')) {
                        element.classList.remove('text-gray-800');
                        element.classList.add('text-white'); // Text color for dark mode
                    }
                });
            }
        }

        // Load saved theme preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Modal functionality
        let deleteId = null;

        function showDeleteModal(id) {
            deleteId = id;
            const modal = document.getElementById('delete-modal');
            modal.classList.remove('hidden');
            document.getElementById('modal-content').classList.add('fade-in');
        }

        document.getElementById('cancel-delete').onclick = function () {
            const modal = document.getElementById('delete-modal');
            modal.classList.add('hidden');
        };

        document.getElementById('confirm-delete').onclick = function () {
            if (deleteId !== null) {
                window.location.href = `delete_entry.php?id=${deleteId}`;
            }
        };
    </script>
</body>

</html>