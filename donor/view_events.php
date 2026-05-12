<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../classes/Database.php';
include_once '../classes/Event.php';

// Get ids
$donor_id = $_SESSION['user_id'];
$search = $_GET['venue'] ?? '';

$eventRepo = new Event($con);

try {
    $events = $eventRepo->getUpcomingEventsWithSlots($search);
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Events - Donor</title>
    <link rel="stylesheet" href="css/donor.css?v=6">
</head>
<body>

    <div class="back-container">
       <a href="view_appointments.php" class="btn-back"  style="display: inline-block; width: auto; padding: 10px 30px; background-color: #2c3e50;">View My Bookings</a>
       <a href="../logout.php" class="btn">Logout</a>
    </div>

    <h1>Available Blood Donation Events</h1>

    <form method="GET" style="text-align:center; margin-bottom:20px;">
        <input type="text" name="venue" placeholder="Search by venue..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Filter</button>
    </form>

    <div class="event-list">
        <!-- If got events -->
        <?php if (count($events) > 0): ?>
            <!-- Loop For every each appointment -->
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <h3><?= htmlspecialchars($event['event_name']) ?></h3>
                    <p><strong>Date:</strong> <?= $event['event_date'] ?></p>
                    <p><strong>Time:</strong> <?= $event['event_time'] ?></p>
                    <p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                    <p><strong>Available Slots:</strong> <?= $event['slots'] ?> / <?= $event['capacity'] ?></p>
                    
                    <?php if ($event['slots'] > 0): ?>
                        <a href="register_appointments.php?id=<?= $event['event_id'] ?>" class="btn">Register Now</a>
                    <?php else: ?>
                        <button class="btn btn-full" disabled>Event Full</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style='text-align:center; width:100%;'>No upcoming events found matching your criteria.</p>
        <?php endif; ?>
    </div>
</body>
</html>