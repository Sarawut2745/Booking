<?php
require('fpdf/fpdf.php');
require('db.php');
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Explicitly add font path
define('FPDF_FONTPATH', 'fpdf/font/');

class PDF extends FPDF {
    function __construct() {
        parent::__construct();
        // Add Garuda font variants
        $this->AddFont('Garuda', '', 'garuda.php');
        $this->AddFont('Garuda', 'B', 'garudab.php');
        $this->AddFont('Garuda', 'I', 'garudai.php');
        $this->AddFont('Garuda', 'BI', 'garudaz.php');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Garuda', '', 8);
        $this->Cell(0, 10, 'หน้า ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    function Header() {
        $this->SetFont('Garuda', 'B', 15);
        $this->Cell(0, 10, 'รายงานทางการเงิน', 0, 1, 'C');
        $this->Ln(5);
    }
}

// Create PDF instance
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Basic settings
$pdf->SetFont('Garuda', '', 12);

// Get filter parameters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build query
$query = 'SELECT * FROM entries WHERE user_id = :user_id';
$params = ['user_id' => $_SESSION['user_id']];

if ($type_filter) {
    $query .= ' AND type = :type';
    $params['type'] = $type_filter;
}

if ($start_date) {
    $query .= ' AND DATE(date) >= :start_date';
    $params['start_date'] = $start_date;
}

if ($end_date) {
    $query .= ' AND DATE(date) <= :end_date';
    $params['end_date'] = $end_date;
}

$query .= ' ORDER BY date DESC';

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_income = 0;
$total_expense = 0;
foreach ($entries as $entry) {
    if ($entry['type'] == 'income') {
        $total_income += $entry['amount'];
    } else {
        $total_expense += $entry['amount'];
    }
}

// Filter information
$pdf->SetFont('Garuda', '', 10);
$pdf->Cell(0, 7, 'วันที่ออกรายงาน: ' . date('Y-m-d'), 0, 1);
if ($start_date && $end_date) {
    $pdf->Cell(0, 7, "ช่วงวันที่: $start_date ถึง $end_date", 0, 1);
}
$pdf->Cell(0, 7, "ประเภท: " . ($type_filter ?: 'ทั้งหมด'), 0, 1);
$pdf->Ln(5);

// Table headers
$pdf->SetFillColor(200, 220, 255);
$pdf->SetFont('Garuda', 'B', 10);
$pdf->Cell(40, 7, 'วันที่', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'รายละเอียด', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'จำนวนเงิน', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'ประเภท', 1, 1, 'C', true);

// Table content
$pdf->SetFont('Garuda', '', 10);
foreach ($entries as $entry) {
    // Add page if needed
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        // Repeat headers
        $pdf->SetFont('Garuda', 'B', 10);
        $pdf->Cell(40, 7, 'วันที่', 1, 0, 'C', true);
        $pdf->Cell(70, 7, 'รายละเอียด', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'จำนวนเงิน', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'ประเภท', 1, 1, 'C', true);
        $pdf->SetFont('Garuda', '', 10);
    }
    
    $pdf->Cell(40, 7, $entry['date'], 1, 0, 'C');
    $pdf->Cell(70, 7, $entry['description'], 1, 0, 'L');
    $pdf->Cell(40, 7, number_format($entry['amount'], 2), 1, 0, 'R');
    $pdf->Cell(40, 7, ucfirst($entry['type']), 1, 1, 'C');
}

$pdf->Ln(10);

// Summary
$pdf->SetFont('Garuda', 'B', 10);
$pdf->Cell(0, 7, 'รายรับรวม: ' . number_format($total_income, 2), 0, 1);
$pdf->Cell(0, 7, 'รายจ่ายรวม: ' . number_format($total_expense, 2), 0, 1);
$pdf->Cell(0, 7, 'ยอดคงเหลือ: ' . number_format($total_income - $total_expense, 2), 0, 1);

// Output PDF
$pdf->Output('D', 'รายงานทางการเงิน_' . date('Y-m-d') . '.pdf');
?>