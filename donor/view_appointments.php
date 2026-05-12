<?php
session_start();
include '../classes/Database.php';
include_once '../classes/Appointment.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get donor id
$donor_id = $_SESSION['user_id'];

$appointmentService = new Appointment($con);
try {
    // Get appointments details
    $appointments = $appointmentService->getAppointmentsDetails($donor_id);
} catch (PDOException $e) {
    die("Error fetching appointments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Blood Bank</title>
    <link rel="stylesheet" href="css/donor.css?v=7">
</head>
<body>

    <div class="back-container">
        <a href="view_events.php" class="btn-back">← Back to Events</a>
    </div>

    <h1>My Registered Appointments</h1>

    <div class="event-list">
        <!-- If got appointments -->
        <?php if (count($appointments) > 0): ?>
            <!-- Loop For every each appointment -->
            <?php foreach ($appointments as $row): ?>
                <div class="event-card">
                    <div style="float: right; background: #27ae60; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8em; font-weight: bold;">
                        Confirmed
                    </div>
                    
                    <h3>Booking #<?= $row['appointment_id'] ?></h3>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                    
                    <p><strong>Event:</strong> <?= htmlspecialchars($row['event_name']) ?></p>
                    <p><strong>Venue:</strong> <?= htmlspecialchars($row['venue']) ?></p>
                    <p><strong>Date:</strong> <?= date('d M Y', strtotime($row['appointment_date'])) ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($row['time_slot']) ?></p>
                    <p><strong>Blood Type:</strong> <span style="color: #ff6b6b; font-weight: bold;"><?= htmlspecialchars($row['blood_type']) ?></span></p>
                    
                    <!-- Cancel Button -->
                    <!-- Only show button if status is not Cancelled or Completed -->
                    <?php if ($row['status'] !== 'Cancelled' && $row['status'] !== 'Completed'): ?>
                        <a href="delete_appointments.php?id=<?= $row['appointment_id'] ?>" 
                           class="btn" 
                           style="background-color: #7f8c8d; margin-top: 20px;" 
                           onclick="return confirm('Are you sure you want to cancel this appointment?')">
                           Cancel Appointment
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; background: #7f8c8d; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p style="color: #666; font-size: 1.1em;">You haven't registered for any donation events yet.</p>
                <a href="view_events.php" class="btn" style="display: inline-block; width: auto; padding: 10px 30px; margin-top: 15px;">Find an Event</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>