<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $activity = "สร้างผู้ใช้งานใหม่!";  // กำหนดกิจกรรมใหม่

    // Error handling initialization
    $error = '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง!";
    }

    // Validate password length
    if (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีความยาวไม่น้อยกว่า 6 ตัวอักษร!";
    }

    // If no error, proceed with checking duplicate username or email
    if (empty($error)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if username or email is already taken
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error checking existing users: ' . $e->getMessage());
        }

        if ($existingUser) {
            // If username or email is already in use
            $error = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว!";
        } else {
            // If valid, insert new user into database
            try {
                // Insert user data into 'users' table
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, fullname) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $fullname]);

                // Insert activity into 'activities' table
                $stmt = $pdo->prepare("INSERT INTO activities (activity) VALUES (?)");
                $stmt->execute([$activity]);
            } catch (PDOException $e) {
                die('Error inserting user into database: ' . $e->getMessage());
            }

            // Redirect to login page after successful registration
            header("Location: login.php");
            exit();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .sky-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            transition: all 3s ease;
        }

        .clouds-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .cloud {
            position: absolute;
            width: 200px;
            height: 60px;
            background: #fff;
            border-radius: 200px;
            animation: float linear infinite;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .cloud::before,
        .cloud::after {
            content: '';
            position: absolute;
            background: #fff;
            border-radius: 50%;
        }

        .cloud::before {
            width: 80px;
            height: 80px;
            top: -30px;
            left: 25px;
        }

        .cloud::after {
            width: 100px;
            height: 100px;
            top: -40px;
            right: 25px;
        }

        .cloud.anime-style {
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.7));
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .cloud.anime-style::before,
        .cloud.anime-style::after {
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.7));
        }

        @keyframes float {
            0% {
                transform: translateX(-250px) translateY(0);
                opacity: 0;
            }

            10% {
                opacity: var(--cloud-opacity);
            }

            90% {
                opacity: var(--cloud-opacity);
            }

            100% {
                transform: translateX(calc(100vw + 250px)) translateY(var(--float-y));
                opacity: 0;
            }
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        /* Loading overlay styles */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .loading-container {
            text-align: center;
            color: white;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
            animation: rotate 2s linear infinite;
        }

        .loading-spinner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 4px solid transparent;
            border-top-color: #66A6FF;
            border-radius: 50%;
            animation: spin 1s ease-in-out infinite;
        }

        .loading-spinner::after {
            content: "";
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 4px solid transparent;
            border-top-color: #FA709A;
            border-radius: 50%;
            animation: spin 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            font-size: 1.2rem;
            margin-top: 1rem;
            opacity: 0;
            animation: fadeInOut 1.5s ease-in-out infinite;
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="h-screen flex items-center justify-center">

    <div id="skyBackground" class="sky-background"></div>
    <div id="cloudsContainer" class="clouds-container"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-container">
            <div class="loading-spinner"></div>
            <div class="loading-text">กำลังเข้าสู่ระบบ...</div>
        </div>
    </div>

    <div class="login-container p-6 shadow-lg rounded-lg w-full max-w-sm relative z-10">
        <h1 class="text-2xl font-bold text-center mb-4">สมัครสมาชิก</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-4 mb-4 rounded"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="RegisterForm" action="register.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="fullname" class="block text-sm font-medium text-gray-700">ชื่อเต็ม</label>
                <input type="text" id="fullname" name="fullname" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>


            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <input type="hidden" value="สร้างผู้ใช้งานใหม่" name="activity">

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded w-full">สมัครสมาชิก</button>
        </form>

        <p class="mt-4 text-center">มีบัญชีอยู่แล้ว? <a href="login.php" class="text-blue-500">เข้าสู่ระบบ</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // เรียกใช้ฟังก์ชันอัปเดตพื้นหลังทันทีที่หน้าโหลด
            updateSkyBackground();

            // อัปเดตพื้นหลังทุกๆ 60 วินาที
            setInterval(updateSkyBackground, 60000);

            // สร้างเมฆทุกๆ 8 วินาที
            setInterval(createCloud, 8000);

            // สร้างเมฆเริ่มต้น 8 เมฆ
            for (let i = 0; i < 8; i++) {
                setTimeout(() => {
                    createCloud();
                }, i * 1000);
            }

            // ฟังก์ชันอัปเดตพื้นหลังท้องฟ้าตามเวลา
            function updateSkyBackground() {
                const now = new Date();
                const hours = now.getHours();
                const minutes = now.getMinutes();
                const time = hours + minutes / 60;

                const skyBackground = document.getElementById('skyBackground');

                let backgroundColor;
                if (time >= 5 && time < 7) { // Dawn
                    backgroundColor = 'linear-gradient(to bottom, #FF9A9E, #FAD0C4)';
                } else if (time >= 7 && time < 12) { // Morning
                    backgroundColor = 'linear-gradient(to bottom, #89F7FE, #66A6FF)';
                } else if (time >= 12 && time < 16) { // Afternoon
                    backgroundColor = 'linear-gradient(to bottom, #4FACFE, #00F2FE)';
                } else if (time >= 16 && time < 19) { // Sunset
                    backgroundColor = 'linear-gradient(to bottom, #FA709A, #FEE140)';
                } else { // Night
                    backgroundColor = 'linear-gradient(to bottom, #0C2B4B, #1D4350)';
                }

                // อัปเดตสีพื้นหลัง
                skyBackground.style.background = backgroundColor;
            }

            // ฟังก์ชันสร้างเมฆ
            function createCloud() {
                const cloud = document.createElement('div');
                cloud.className = 'cloud anime-style';

                // กำหนดค่าเมฆแบบสุ่ม
                const scale = Math.random() * 1.5 + 0.5;
                const top = Math.random() * window.innerHeight * 0.7;
                const duration = Math.random() * 30 + 60; // ความเร็วการเคลื่อนที่
                const floatY = Math.random() * 50 - 25; // การเคลื่อนที่ในแนวตั้ง
                const opacity = Math.random() * 0.3 + 0.4; // ความทึบของเมฆ

                cloud.style.transform = `scale(${scale})`;
                cloud.style.top = `${top}px`;
                cloud.style.animationDuration = `${duration}s`;
                cloud.style.setProperty('--float-y', `${floatY}px`);
                cloud.style.setProperty('--cloud-opacity', opacity);

                document.getElementById('cloudsContainer').appendChild(cloud);

                // ลบเมฆหลังจากแอนิเมชันเสร็จสิ้น
                setTimeout(() => {
                    cloud.remove();
                }, duration * 1000);
            }
        });

             // Handle form submission and loading animation
             document.getElementById('RegisterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Submit the form after a small delay to show the animation
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    </script>
</body>

</html>