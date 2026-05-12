<?php
session_start();
include '../classes/Database.php';
include_once '../classes/Appointment.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the ids
$donor_id = $_SESSION['user_id'];
$appt_id = $_GET['id'] ?? null; // else null

// if appt id not null
if ($appt_id) {
    try {
        $appointmentService = new Appointment($con);
        // delete appt
        $deleted = $appointmentService->deleteAppt($appt_id, $donor_id);
        // alert
        if ($deleted) {
            echo "<script>
                    alert('Appointment has been deleted successfully.');
                    window.location.href = 'view_appointments.php';
                  </script>";
        } else {
            // Row count is 0 if ID doesn't exist OR if it belongs to a different user
            echo "<script>
                    alert('Error: Could not delete appointment. It may not exist or belongs to another user.');
                    window.location.href = 'view_appointments.php';
                  </script>";
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    // Redirect if no id provided
    header("Location: view_appointments.php");
    exit();
}
?>