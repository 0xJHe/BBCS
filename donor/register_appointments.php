<?php
session_start();
include '../classes/Database.php';
include_once '../classes/Donor.php';
include_once '../classes/Appointment.php';
include_once '../classes/Event.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get ids
$donor_id = $_SESSION['user_id'];
$event_id = $_GET['id'] ?? null;

// exit if event id null
if (!$event_id) {
    die("Invalid Event ID.");
}

$donor = new Donor($con, $donor_id);
$appointmentService = new Appointment($con);
$eventRepo = new Event($con);

// Get event details
$event = $eventRepo->getEventById($event_id);
if (!$event) {
    die("Event not found.");
}

// Get Donor Details
$donorName = $donor->getName() ?? 'Donor';
$bloodType = $donor->getBloodType() ?? 'Unknown';

// Process Registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $registered = $appointmentService->registerEvent($donor_id, $event_id);
        // True The event is registered
        if ($registered) {
            $appt_date = $event['event_date'];
            echo "<script>
                    alert('Registration Successful! See you on " . $appt_date . "');
                    window.location.href='view_events.php';
                  </script>";
        // False If the event is registered                  
        } else {
            echo "<script>
                    alert('You have already registered for this event or the event is invalid.');
                    window.location.href='view_events.php';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Appointment</title>
    <link rel="stylesheet" href="css/donor.css?v=5">
</head>
<body>

    <div class="back-container">
        <a href="view_events.php" class="btn-back">← Back</a>
    </div>

    <div class="registration-wrapper">
        <div class="registration-card">
            <h2>Registration Form</h2>
            <p>You are booking for <strong>Event ID: #<?= htmlspecialchars($event_id) ?></strong></p>
            <hr>

            <div style="margin-bottom: 20px; color: #eaf0ff;">
                <p><strong>Event:</strong> <?= htmlspecialchars($event['event_name']) ?></p>
                <p><strong>Date:</strong> <?= $event['event_date'] ?> at <?= $event['event_time'] ?></p>
                <p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
            </div>
            <p>Confirm Your Information Below</p>

            <form method="POST">
                <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">
                
                <!-- Donor Info -->
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" value="<?= htmlspecialchars($donorName) ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Your Blood Type</label>
                    <input type="text" value="<?= htmlspecialchars($bloodType) ?>" disabled>
                </div>

                <div><button type="submit" class="btn-confirm">Complete Registration</button></div>
            </form>
        </div>
    </div>

</body>
</html>