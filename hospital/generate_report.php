<?php
// ===============================
// CONFIGURATION & SETUP
// ===============================

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied: You must be logged in to generate a report.");
}

// FPDF installation path
$fpdfAbsPath = 'C:/laragon/www/hospital/fpdf/fpdf.php';
$fpdfRelPath = '../fpdf/fpdf.php';

if (file_exists($fpdfAbsPath)) {
    require_once $fpdfAbsPath;
} elseif (file_exists($fpdfRelPath)) {
    require_once $fpdfRelPath;
} else {
    // Try one more common location or die
    if(file_exists('fpdf/fpdf.php')) {
        require_once 'fpdf/fpdf.php';
    } else {
        die("❌ FPDF not found. Please ensure fpdf.php is in the correct directory.");
    }
}

// ===============================
// DATABASE CONNECTION
// ===============================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=blood_donation_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// ===============================
// FETCH INVENTORY DATA
// ===============================

// Define standard blood types
$standardTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$hospital_id = $_SESSION['user_id'];

// Fetch inventory specifically for this hospital
$sql = "SELECT blood_type, quantity, status, last_updated 
        FROM BloodInventory 
        WHERE hospital_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hospital_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map DB data by blood type
$inventory = [];
$totalUnits = 0;
$criticalCount = 0;

foreach ($rows as $row) {
    $bt = strtoupper($row['blood_type']);
    $inventory[$bt] = $row;
    
    $totalUnits += $row['quantity'];
    if (strtolower($row['status']) === 'critical') {
        $criticalCount++;
    }
}

// ===============================
// REPORT METADATA LOGGING
// ===============================
$report_type    = 'Inventory Status';
$generated_by   = $_SESSION['user_id']; // Must be INT (Foreign Key)
$generated_date = date('Y-m-d');        // Must be DATE format
$export_format  = 'PDF';

try {
    // Using table 'Report' (Singular, Capitalized) per sqldb.sql
    $stmt = $pdo->prepare("
      INSERT INTO Report (report_type, generated_by, generated_date, export_format)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$report_type, $generated_by, $generated_date, $export_format]);
    $report_id = $pdo->lastInsertId();
} catch (PDOException $e) {
    die("❌ Failed to log report: " . $e->getMessage());
}

// ===============================
// GENERATE PDF
// ===============================
class InventoryPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'Blood Bank Inventory Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Generated: ' . date('F j, Y, g:i a'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' - Internal Document', 0, 0, 'C');
    }
}

$pdf = new InventoryPDF();
$pdf->AddPage();

// --- SUMMARY SECTION ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Executive Summary', 0, 1);
$pdf->SetFont('Arial', '', 11);

// Summary Box
$pdf->SetFillColor(240, 245, 255);
$pdf->Rect($pdf->GetX(), $pdf->GetY(), 190, 25, 'F');
$pdf->SetXY($pdf->GetX() + 5, $pdf->GetY() + 5);

$pdf->Cell(60, 8, 'Total Units in Stock:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 8, $totalUnits . ' Units', 0, 0);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 8, 'Critical Alerts:', 0, 0);

if ($criticalCount > 0) {
    $pdf->SetTextColor(200, 0, 0); // Red
    $pdf->SetFont('Arial', 'B', 11);
} else {
    $pdf->SetTextColor(0, 128, 0); // Green
}
$pdf->Cell(30, 8, $criticalCount . ' Types', 0, 1);
$pdf->SetTextColor(0, 0, 0); // Reset

$pdf->SetX($pdf->GetX() + 5); 
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(60, 8, 'Report ID:', 0, 0);
$pdf->Cell(30, 8, '#' . $report_id, 0, 1);

$pdf->Ln(15);

// --- INVENTORY TABLE ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Detailed Stock Levels', 0, 1);

// Table Header
$pdf->SetFillColor(50, 50, 50);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);

$w = [40, 40, 40, 60]; // Column widths
$headers = ['Blood Type', 'Quantity (Units)', 'Status', 'Last Updated'];

foreach ($headers as $i => $h) {
    $pdf->Cell($w[$i], 10, $h, 0, 0, 'C', true);
}
$pdf->Ln();

// Table Body
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false; 

foreach ($standardTypes as $type) {
    if (isset($inventory[$type])) {
        $data = $inventory[$type];
        $qty = $data['quantity'];
        $status = ucfirst($data['status']);
        $updated = $data['last_updated'];
    } else {
        // Default values if no record exists for this blood type
        $data = null;
        $qty = 0;
        $status = 'Critical';
        $updated = 'N/A';
    }

    $pdf->SetFillColor(245, 245, 245);

    // Blood Type
    $pdf->Cell($w[0], 9, $type, 'LRB', 0, 'C', $fill);

    // Quantity
    $pdf->Cell($w[1], 9, $qty, 'LRB', 0, 'C', $fill);

    // Status
    $statusLower = strtolower($status);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    
    $pdf->Cell($w[2], 9, '', 'LRB', 0, 'C', $fill); // Background
    $pdf->SetXY($x, $y); // Restore position
    
    if ($statusLower === 'critical') {
        $pdf->SetTextColor(180, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
    } elseif ($statusLower === 'low') {
        $pdf->SetTextColor(200, 100, 0);
        $pdf->SetFont('Arial', 'B', 10);
    } else {
        $pdf->SetTextColor(0, 100, 0);
        $pdf->SetFont('Arial', '', 10);
    }
    
    $pdf->Cell($w[2], 9, $status, 0, 0, 'C');
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);

    // Last Updated
    $pdf->Cell($w[3], 9, $updated, 'LRB', 0, 'C', $fill);

    $pdf->Ln();
    $fill = !$fill;
}

// --- FOOTER NOTE ---
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->MultiCell(0, 6, "Note: 'Critical' status indicates stock is at 0 units. 'Low' status indicates stock is below the safety threshold. Please arrange for donation drives immediately for critical types.");

// OUTPUT
$pdf->Output('I', 'Inventory_Report_' . date('Ymd') . '.pdf');
exit;
?>