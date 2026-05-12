<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Organizer.php';

// Get ids
$organizerId = $_SESSION['user_id'];
$eventManager = new Organizer($con);

// Get organizer name
$organizer_name = $eventManager->getOrganizerName($organizerId) ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bind the input labels to the array
    $data = [
        'event_name'  => $_POST['event_name'] ?? '',
        'location'    => $_POST['location'] ?? '',
        'event_date'  => $_POST['event_date'] ?? '',
        'event_time'  => $_POST['event_time'] ?? '',
        'total_slot'  => $_POST['total_slot'] ?? 0,
        'description' => $_POST['description'] ?? '',
    ];

    // --- NEW VALIDATION: Check if date is in the past ---
    $inputDateTime = $data['event_date'] . ' ' . $data['event_time'];
    $currentDateTime = date('Y-m-d H:i:s');

    if (strtotime($inputDateTime) < strtotime($currentDateTime)) {
        echo "<script>alert('Error: You cannot create an event in the past. Please check the Date and Time.');</script>";
    } else {
        // Only proceed if validation passes
        try {
            if ($eventManager->createEvent($data, $organizerId)) {
                echo "<script>alert('Event created successfully!')</script>";
            } else {
                echo "<script>alert('Failed to create event.')</script>";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Create Event</title>
        <link rel="stylesheet" href="../style.css">
        <!-- Script -->
        <script>

        </script>
        <style>
            .form-layout {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;     
                margin-bottom: 20px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                width: calc(50% - 10px);
            }

            label {
                font-weight: 500;
                margin-bottom: 8px;
                color: #ffffff;
                font-size: 0.95rem;
            }

            input[type="text"],
            input[type="date"],
            input[type="time"],
            input[type="number"],
            textarea {
                padding: 12px 15px;
                border: 1px solid ; /* Light gray border */
                border-radius: 6px;
                font-size: 1rem;
                width: 100%; /* Ensure input takes full width of parent form-group */
            }
        </style>
    </head>

    <body>
        <div class="dashboard-container">

            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h3>BloodBank System</h3>
                    <div style="font-size:12px">Main Dashboard</div>
                </div>
                <nav>
                    <ul>
                        <li><a href="create_event.php" style="color: white; background: rgba(255,255,255,0.05);">Create Event</a></li>
                        <li><a href="manage_events.php">Manage Event</a></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <div class="main-content">
                <!-- Header -->
                <header class="top-header">
                    <h1>Create Event</h1>
                </header>

                <!-- Content -->
                <div class="content-body">

                    <form class="card" style="padding: 20px;" action="create_event.php" method="POST">
                                
                        <div class="form-layout">
                            <!-- Row 1 -->
                                <div class="form-group">
                                    <label>Event Name</label>
                                    <input type="text" name="event_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Organizer</label>
                                    <input type="text" name="coordinator"  value="<?php echo htmlspecialchars($organizer_name); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" required>
                                </div>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="event_date" required placeholder="dd/mm/yyyy">
                                </div>
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="time" name="event_time" required>
                                </div>
                                <div class="form-group">
                                    <label>Total Slot</label>
                                    <input type="number" name="total_slot" placeholder=>
                                </div>
                                <div class="form-group">
                                    <label style="resize: vertical; width: 100%;">Description</label>
                                    <textarea name="description"></textarea>
                                </div>
                        </div>

                        <button type="submit">Create Event</button>

                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
