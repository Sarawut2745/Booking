<!-- navbar.php -->
<nav class="bg-yellow-600 p-4">
    <div class="flex items-center justify-between">
        <a href="index.php" class="text-white text-2xl font-bold">สมุดรายรับรายจ่าย</a>

        <!-- แสดงวันที่และเวลาปัจจุบันที่ตรงกลาง -->
        <div class="flex-grow text-center text-white font-semibold">
            <span id="current-date-time"></span>
        </div>

        <!-- เมนู Dropdown สำหรับโปรไฟล์และการออกจากระบบ -->
        <div class="relative">
            <button onclick="toggleDropdown()" class="text-white font-semibold px-4 py-2 rounded-md">
                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'ผู้ใช้'; ?>
            </button>
            <div id="dropdown-menu" class="absolute right-0 hidden bg-white shadow-lg rounded-lg w-48 mt-2">
                <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">ดูโปรไฟล์</a>
                <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdown-menu');
        dropdownMenu.classList.toggle('hidden');
    }

    function updateDateTime() {
        const now = new Date();

        // วันที่ (วัน เดือน ปี)
        const daysOfWeek = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"];
        const monthsOfYear = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        
        const dayOfWeek = daysOfWeek[now.getDay()];
        const day = now.getDate();
        const month = monthsOfYear[now.getMonth()];
        const year = now.getFullYear();

        // เวลา (ชั่วโมง นาที วินาที)
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const currentDateTime = `${dayOfWeek}, ${day} ${month} ${year} เวลา ${hours}:${minutes}:${seconds}`;
        document.getElementById('current-date-time').textContent = currentDateTime;
    }

    // อัพเดตเวลาเป็นทุกๆ วินาที
    setInterval(updateDateTime, 1000);
    updateDateTime(); // เรียกครั้งแรกเพื่อแสดงเวลาโดยทันที
</script>
