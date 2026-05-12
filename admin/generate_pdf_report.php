<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../hospital/fpdf/fpdf.php';
require_once '../classes/AdminReport.php';

$report = new AdminReport();
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch Stats
$total_donors = $report->getUsersByRole('donor');
$total_hospitals = $report->getUsersByRole('hospital');
$total_organizers = $report->getUsersByRole('organizer');

$completed_appointments = $report->getAppointmentStats($year, 'Completed');
$total_events = $report->getEventsCount($year);
$monthly_data = $report->getMonthlyAppointments($year);

// Fetch Operational Insights
$top_locations = $report->getTopLocations($year, 5);

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Blood Donation Management System', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Annual Admin Report', 0, 1, 'C');
        $this->Line(10, 30, 200, 30);
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SectionTitle($label) {
        $this->SetFillColor(53, 55, 85); // Dark blue header
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, '  ' . $label, 1, 1, 'L', true);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 12);
        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Report for Year: ' . $year, 0, 1, 'L');
$pdf->Ln(5);

// 1. Executive Summary
$pdf->SectionTitle('Activity Summary (' . $year . ')');
$pdf->Cell(95, 10, 'Completed Donations:', 1);
$pdf->Cell(95, 10, $completed_appointments, 1, 1);
$pdf->Cell(95, 10, 'Events Organized:', 1);
$pdf->Cell(95, 10, $total_events, 1, 1);
$pdf->Ln(10);

// 2. User Stats
$pdf->SectionTitle('Registered Users (All Time)');
$pdf->Cell(63, 10, 'Donors', 1, 0, 'C');
$pdf->Cell(63, 10, 'Hospitals', 1, 0, 'C');
$pdf->Cell(64, 10, 'Organizers', 1, 1, 'C');

$pdf->Cell(63, 10, $total_donors, 1, 0, 'C');
$pdf->Cell(63, 10, $total_hospitals, 1, 0, 'C');
$pdf->Cell(64, 10, $total_organizers, 1, 1, 'C');
$pdf->Ln(10);

// 3. Monthly Activity
$pdf->SectionTitle('Monthly Donations Breakdown');
$pdf->SetFont('Arial', '', 10);
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
foreach($months as $m) $pdf->Cell(16, 8, $m, 1, 0, 'C');
$pdf->Ln();
foreach($monthly_data as $count) $pdf->Cell(16, 8, $count, 1, 0, 'C');
$pdf->Ln(10);

// 4. Top Locations Table
$pdf->SetFont('Arial', '', 12);
$pdf->SectionTitle('Top 5 Event Venues');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 8, 'Venue', 1);
$pdf->Cell(50, 8, 'Events Held', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);

if (count($top_locations) > 0) {
    foreach ($top_locations as $loc) {
        $pdf->Cell(140, 8, $loc['location'], 1);
        $pdf->Cell(50, 8, $loc['count'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 8, 'No venue data available.', 1, 1, 'C');
}

$pdf->Ln(10);

// Date Generated
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Report generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');

$pdf->Output('I', 'Annual_Report_'.$year.'.pdf');
?>
