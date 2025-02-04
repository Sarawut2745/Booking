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

// เชื่อมต่อ PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// สร้างไฟล์ Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// กำหนดชื่อหัวข้อ
$sheet->setCellValue('A1', 'วันที่');
$sheet->setCellValue('B1', 'รายละเอียด');
$sheet->setCellValue('C1', 'จำนวน');
$sheet->setCellValue('D1', 'ประเภท');

// กำหนดสไตล์สำหรับหัวข้อ
$styleArrayHeader = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['argb' => 'FFFFFFFF'], // ฟอนต์สีขาว
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // จัดกลาง
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF4CAF50'], // สีพื้นหลังเขียว
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'], // สีเส้นขอบดำ
        ],
    ],
];

// กำหนดสไตล์ให้กับหัวข้อ
$sheet->getStyle('A1:D1')->applyFromArray($styleArrayHeader);

// กรอกข้อมูลลงในแต่ละแถว
$row = 2; // เริ่มจากแถวที่ 2 หลังจากหัวข้อ
$income_total = 0;  // ตัวแปรสำหรับยอดรวมรายรับ
$expense_total = 0; // ตัวแปรสำหรับยอดรวมรายจ่าย

foreach ($entries as $entry) {
    $sheet->setCellValue("A$row", $entry['date']);
    $sheet->setCellValue("B$row", $entry['description']);
    $sheet->setCellValue("C$row", $entry['amount']);
    $sheet->setCellValue("D$row", ucfirst($entry['type']));
    
    // เพิ่มยอดรวมตามประเภท
    if ($entry['type'] == 'income') {
        $income_total += $entry['amount'];
    } elseif ($entry['type'] == 'expense') {
        $expense_total += $entry['amount'];
    }
    
    // กำหนดสไตล์ให้กับแถวข้อมูล
    $sheet->getStyle("A$row:D$row")->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ]);
    $row++;
}

// เพิ่มแถวสำหรับแสดงยอดรวมรายรับและรายจ่าย
$sheet->setCellValue("A$row", 'รวมรายรับ');
$sheet->setCellValue("C$row", $income_total);
$sheet->setCellValue("D$row", 'รายรับ');
$sheet->getStyle("A$row:D$row")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
]);

$row++;

$sheet->setCellValue("A$row", 'รวมรายจ่าย');
$sheet->setCellValue("C$row", $expense_total);
$sheet->setCellValue("D$row", 'รายจ่าย');
$sheet->getStyle("A$row:D$row")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
]);

$row++;

// กำหนดยอดรวมทั้งหมด
$sheet->setCellValue("A$row", 'ยอดรวมทั้งหมด');
$sheet->setCellValue("C$row", $income_total - $expense_total);
$sheet->setCellValue("D$row", 'ยอดสุทธิ');
$sheet->getStyle("A$row:D$row")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
]);

// ตั้งค่าขนาดคอลัมน์ให้เหมาะสม
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);
$sheet->getColumnDimension('C')->setAutoSize(true);
$sheet->getColumnDimension('D')->setAutoSize(true);

// ลบข้อมูลจาก buffer เพื่อให้แน่ใจว่าไฟล์จะถูกส่งไปยังเบราว์เซอร์อย่างถูกต้อง
ob_clean();
flush();

// เขียนไฟล์ Excel
$writer = new Xlsx($spreadsheet);
$fileName = 'รายรับรายจ่าย.xlsx';

// ตั้งค่าให้ดาวน์โหลดไฟล์ Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

// เขียนไฟล์ลงไปในบราวเซอร์
$writer->save('php://output');
exit();
?>
