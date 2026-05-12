<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Organizer.php';

$organizerId = $_SESSION['user_id'];
$eventManager = new Organizer($con);

// If request POST and action is not null
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Set event id for procees later
    $target_ids = isset($_POST['event_id']) ? $_POST['event_id'] : [];
    if (!is_array($target_ids)) {
        $target_ids = [$target_ids];
    }

    try {
        if ($action === 'update_event') {
            // Bind the input labels to the array
            $data = [
                'event_id'    => (int)($_POST['edit_event_id'] ?? 0),
                'event_name'  => $_POST['event_name'] ?? '',
                'event_date'  => $_POST['event_date'] ?? '',
                'event_time'  => $_POST['event_time'] ?? '',
                'location'    => $_POST['location'] ?? '',
                'capacity'    => $_POST['capacity'] ?? 0,
                'status'      => $_POST['status'] ?? 'Active',
                'description' => $_POST['description'] ?? '',
            ];

            // --- NEW VALIDATION: Check if date is in the past ---
            $inputDateTime = strtotime($data['event_date'] . ' ' . $data['event_time']);
            $currentDateTime = time(); // Current timestamp

            if ($inputDateTime < $currentDateTime) {
                echo "<script>alert('Error: You cannot update an event to a date/time that has already passed.');</script>";
            } else {
                // Only proceed if validation passes
                if ($eventManager->updateEvent($data, $organizerId)) {
                    $msg = 'Event details updated successfully.';
                
                    // NEW: Check if notification checkbox is selected
                    if (isset($_POST['notify_donors']) && $_POST['notify_donors'] == '1') {
                        $notifiedCount = $eventManager->notifyEventUpdate($data['event_id'], $data['event_name']);
                        if ($notifiedCount > 0) {
                            $msg .= "\\n$notifiedCount registered donor(s) have been notified.";
                        }
                    }
                    
                    echo "<script>alert('$msg');</script>";
                } else {
                echo "<script>alert('Failed to update event.');</script>";
                }
            }

        } else {
            // Cancel/Delete
            if ($action === 'cancel_event') {
                $count = $eventManager->cancelEvents($target_ids, $organizerId);
            } elseif ($action === 'delete_event') {
                $count = $eventManager->deleteEvents($target_ids, $organizerId);
            } else {
                $count = 0;
            }

            if (!empty($count)) {
                echo "<script>alert('Action processed for $count event(s).');</script>";
            }
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database Error');</script>";
    }
}

// Fetch Events
$events = $eventManager->getEventsForOrganizer($organizerId);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Manage Events</title>
        <link rel="stylesheet" href="../style.css">
        <!-- Script -->
        <script>
            // Update the button state if event is selected
            function updateButtonState() {
                const radios = document.querySelectorAll('.event-radio');
                let isChecked = false;
                radios.forEach(r => { if(r.checked) isChecked = true; });
                document.getElementById('btn-edit').disabled = !isChecked;
                document.getElementById('btn-delete').disabled = !isChecked;
            }

            // Open the dialog to edit the event details
            function openEditDialog() {
                const checkedRadio = document.querySelector('.event-radio:checked');
                if (!checkedRadio) return;

                // Get Data from Attributes
                const id = checkedRadio.value;
                const name = checkedRadio.getAttribute('data-name');
                const date = checkedRadio.getAttribute('data-date');
                const time = checkedRadio.getAttribute('data-time');
                const venue = checkedRadio.getAttribute('data-venue');
                const cap = checkedRadio.getAttribute('data-capacity');
                const status = checkedRadio.getAttribute('data-status');
                const desc = checkedRadio.getAttribute('data-desc');

                // Dialog Fields
                document.getElementById('dialog_event_id').value = id;
                document.getElementById('dialog_name').value = name;
                document.getElementById('dialog_date').value = date;
                document.getElementById('dialog_time').value = time;
                document.getElementById('dialog_venue').value = venue;
                document.getElementById('dialog_capacity').value = cap;
                document.getElementById('dialog_status').value = status;
                document.getElementById('dialog_desc').value = desc;
                
                // Reset checkbox
                document.getElementById('dialog_notify').checked = false;

                // Show Dialog
                document.getElementById('editDialog').style.display = 'flex';
            }

            function closeEditDialog() {
                document.getElementById('editDialog').style.display = 'none';
            }

            function deleteEvent() {
                if(confirm("WARNING: This will permanently delete the event. Continue?")) {
                    document.getElementById('form_action').value = 'delete_event';
                    document.getElementById('actForm').submit();
                }
            }
        </script>
        <style>
            #editDialog {
                display: none;
                position: fixed; z-index: 1000; left: 0; top: 0;
                width: 100%; height: 100%;
                background-color: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                align-items: center; justify-content: center;
                overflow-y: auto; 
            }

            .dialog-content {
                background-color: #1f2937; 
                color: #f3f4f6; 
                padding: 30px;
                width: 100%; max-width: 600px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.5); 
                position: relative;
                margin: 20px;
                border: 1px solid #374151; 
            }
            
            .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 15px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
            }
            
            .full-width {
                grid-column: span 2;
            }

            label {
                font-weight: 500;
                margin-bottom: 5px;
                font-size: 0.9rem;
                color: #d1d5db;
            }

            input, select, textarea {
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 6px;
                font-size: 0.95rem;
                color: #333; /* Ensure text inside inputs is readable */
            }

            .dialog-actions {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 20px;
            }

            .btn-save { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
            .btn-cancel { background: #e5e7eb; color: #374151; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
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
                        <li><a href="create_event.php">Create Event</a></li>
                        <li><a href="manage_events.php"  style="color: white; background: rgba(255,255,255,0.05);">Manage Event</a></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <!-- Content -->
            <div class="main-content">
                <!-- Header -->
                <header class="top-header">
                    <h1>Manage Events</h1>
                </header>

                <div class="content-body">
                <!-- Action Buttons Toolbar -->
                    <div class="action-bar">
                        <button id="btn-edit" class="btn-action btn-reset" disabled onclick="openEditDialog()">
                            Manage
                        </button>
                        <button id="btn-delete" class="btn-delete" disabled onclick="deleteEvent('delete_event')">
                            Delete
                        </button>
                    </div>
                    
                    <div class="table-container">
                        <form id="actForm" method="POST" action="manage_events.php">
                        <!-- Hidden input for action type -->
                        <input type="hidden" name="action" id="form_action">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;"></th>
                                    <th>Event Name</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>By</th>
                                    <th>Description</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- Loop to show every user to the table -->
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="radio" name="event_id" class="event-radio" 
                                                value="<?php echo $event['event_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($event['event_name']); ?>"
                                                data-date="<?php echo htmlspecialchars($event['event_date']); ?>"
                                                /* FORCE 24H FORMAT FOR INPUT VALUE COMPATIBILITY */
                                                data-time="<?php echo date('H:i', strtotime($event['event_time'])); ?>"
                                                data-venue="<?php echo htmlspecialchars($event['venue']); ?>"
                                                data-capacity="<?php echo htmlspecialchars($event['capacity']); ?>"
                                                data-status="<?php echo htmlspecialchars($event['status']); ?>"
                                                data-desc="<?php echo htmlspecialchars($event['desc']); ?>"
                                                onchange="updateButtonState()">
                                    </td>
                                        
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($event['event_name']); ?></td>
                                        <td style="padding: 12px;">
                                            <?php echo htmlspecialchars($event['event_date']); ?> <br>
                                            <!-- FORCE 24H FORMAT FOR DISPLAY -->
                                            <small style="color: #6c757d;"><?php echo date('H:i', strtotime($event['event_time'])); ?></small>
                                        </td>
                                        <td style="padding: 12px;"><?php echo htmlspecialchars($event['venue']); ?></td>
                                        <td style="padding: 12px;">
                                            <span style="
                                                color: <?php echo $event['status'] == 'Active' ? '#28a745' : ($event['status'] == 'Cancelled' ? '#dc3545' : '#6c757d'); ?>
                                            ">
                                                <?php echo htmlspecialchars($event['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['organizer_name']); ?></td>
                                        <td>
                                            <a href="#" onclick="alert('<?php echo !empty($event['desc']) ? addslashes(htmlspecialchars($event['desc'])) : 'No description available.'; ?>'); return false;" style="text-decoration: underline;">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit/Manage Event Dialog -->
            <div id="editDialog" >
                <div class="dialog-content">
                    <h2 style="margin-top: 0;">Edit Event Details</h2>
                    
                    <form action="manage_events.php" method="POST">
                        <input type="hidden" name="action" value="update_event">
                        <input type="hidden" name="edit_event_id" id="dialog_event_id">

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label>Event Name</label>
                                <input type="text" name="event_name" id="dialog_name" required>
                            </div>

                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="event_date" id="dialog_date" required>
                            </div>

                            <div class="form-group">
                                <label>Time</label>
                                <input type="time" name="event_time" id="dialog_time" required>
                            </div>

                            <div class="form-group">
                                <label>Location / Venue</label>
                                <input type="text" name="location" id="dialog_venue" required>
                            </div>

                            <div class="form-group">
                                <label>Capacity (Slots)</label>
                                <input type="number" name="capacity" id="dialog_capacity" required>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="dialog_status">
                                    <option value="Active">Active</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Description</label>
                                <textarea name="description" id="dialog_desc" rows="4" style="resize: vertical;"></textarea>
                            </div>

                            <div class="form-group full-width" style="margin-top: 5px;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: normal; color: #9ca3af;">
                                    <input type="checkbox" name="notify_donors" id="dialog_notify" value="1" style="width: auto;">
                                    Notify registered donors about this update
                                </label>
                            </div>
                        </div>

                        <div class="dialog-actions">
                            <button type="button" class="btn-cancel" onclick="closeEditDialog()">Cancel</button>
                            <button type="submit" class="btn-save">Save</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </body>
</html>