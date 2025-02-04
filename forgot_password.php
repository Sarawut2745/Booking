<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $activity = "ขอรหัสผ่านใหม่!";  // กำหนดกิจกรรมใหม่

    // ตรวจสอบอีเมลในฐานข้อมูล
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // สร้าง token และบันทึกลงฐานข้อมูล
        $token = bin2hex(random_bytes(50));  // สร้าง token สำหรับการรีเซ็ตรหัสผ่าน
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));  // กำหนดเวลาหมดอายุของ token

        // อัปเดต token และเวลาหมดอายุในฐานข้อมูล
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);

        // บันทึกกิจกรรมการขอรหัสผ่านใหม่
        $stmt = $pdo->prepare("INSERT INTO activities (activity) VALUES (?)");
        $stmt->execute([$activity]);

        // สร้างลิงก์รีเซ็ตรหัสผ่าน
        $resetLink = "http://mike-server.tailed9121.ts.net//booking/reset_password.php?token=$token";

        $subject = "ลิงก์รีเซ็ตรหัสผ่าน";
        $message = "
        <html>
        <head><title>รีเซ็ตรหัสผ่าน</title></head>
        <body>
            <p>คลิกที่ลิงก์นี้เพื่อรีเซ็ตรหัสผ่าน:</p>
            <a href='$resetLink'>รีเซ็ตรหัสผ่าน</a>
        </body>
        </html>
        ";

        // ตั้งค่า headers สำหรับการส่งอีเมล
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Your Website <your_email@example.com>\r\n";

        // ส่งอีเมล
        if (mail($email, $subject, $message, $headers)) {
            $notificationType = 'success';
            $notificationMessage = 'ลิงก์รีเซ็ตรหัสผ่านถูกส่งไปยังอีเมลของคุณแล้ว!';
        } else {
            $notificationType = 'error';
            $notificationMessage = 'เกิดข้อผิดพลาด ไม่สามารถส่งอีเมลได้ในขณะนี้!';
        }
    } else {
        $notificationType = 'error';
        $notificationMessage = 'ไม่พบอีเมลนี้ในระบบ!';
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
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

        #notification {
            animation: fadeIn 0.5s ease-in-out, fadeOut 0.5s ease-in-out 4.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
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
        <h1 class="text-2xl font-bold text-center mb-4">ลืมรหัสผ่าน</h1>

        <form id="forgotPass" action="forgot_password.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>

            <button type="submit"
                class="bg-blue-500 text-white py-2 px-4 rounded w-full">ส่งลิงก์รีเซ็ตรหัสผ่าน</button>
        </form>

        <p class="mt-4 text-center"><a href="login.php" class="text-blue-500">กลับสู่หน้าล็อกอิน</a></p>
    </div>

    <div id="notification"
        class="hidden fixed top-5 right-5 bg-white shadow-lg rounded-md p-4 flex items-center space-x-4">
        <div id="notificationIcon" class="w-6 h-6"></div>
        <p id="notificationMessage" class="text-sm font-medium"></p>
    </div>
    <script>
        document.getElementById('forgotPass').addEventListener('submit', function (e) {
            e.preventDefault();

            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';

            // Submit the form after a small delay to show the animation
            setTimeout(() => {
                this.submit();
            }, 500);
        });

        function showNotification(type, message) {
            const notification = document.getElementById('notification');
            const notificationIcon = document.getElementById('notificationIcon');
            const notificationMessage = document.getElementById('notificationMessage');

            notificationMessage.textContent = message;

            if (type === 'success') {
                notification.classList.add('bg-green-500', 'text-white');
                notificationIcon.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>`;
            } else if (type === 'error') {
                notification.classList.add('bg-red-500', 'text-white');
                notificationIcon.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>`;
            }

            notification.classList.remove('hidden');
            notification.classList.add('flex');

            setTimeout(() => {
                notification.classList.add('hidden');
                notification.classList.remove('flex', 'bg-green-500', 'bg-red-500', 'text-white');
            }, 5000);
        }

        <?php if (!empty($notificationType) && !empty($notificationMessage)): ?>
            showNotification('<?php echo $notificationType; ?>', '<?php echo $notificationMessage; ?>');
        <?php endif; ?>

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
    </script>
</body>

</html>