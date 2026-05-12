<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Admin.php';

$admin = new Admin($con, $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $admin !== null) {
    $action = $_POST['action'];
    $req_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
    $hosp_id = isset($_POST['hospital_id']) ? (int)$_POST['hospital_id'] : 0;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;

    if ($action === 'approve' && $req_id > 0 && $hosp_id > 0) {
        if ($admin->approveRegistration($req_id, $hosp_id)) {
            echo "<script>alert('Hospital registration verified successfully.');</script>";
        }
    } elseif ($action === 'reject' && $req_id > 0 && $hosp_id > 0 && $reason !== null) {
        if ($admin->rejectRegistration($req_id, $hosp_id, $reason)) {
            echo "<script>alert('Hospital registration Rejected.');</script>";
        }
    }
}

// Fetch pending registrations for display
$requests = [];
if ($admin !== null) {
    $requests = $admin->getPendingRegistrations();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Pending Registrations</title>
        <link rel="stylesheet" href="../style.css">
        <style>
            .reg-info {
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                padding: 20px 25px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .btn {
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 0.9rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                border: 1px solid transparent;
            }
        </style>
        <script>
            function buttonsAction(action, reqId, hospId) {
                let reason = "";
                
                if (action === 'approve') {
                    if (!confirm("Are you sure you want to approve this hospital registration?")) return;
                } 
                else if (action === 'reject') {
                    reason = prompt("Please enter a reason for rejection:", "Incomplete documents");
                    if (reason === null) return; // User cancelled
                    if (reason.trim() === "") {
                        alert("Rejection reason is required.");
                        return;
                    }
                }

                // Populate hidden form
                document.getElementById('form_action').value = action;
                document.getElementById('form_req_id').value = reqId;
                document.getElementById('form_hosp_id').value = hospId;
                document.getElementById('form_reason').value = reason;

                // Submit
                document.getElementById('actionForm').submit();
            }

        </script>
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
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="pending_reg.php" style="color: white; background: rgba(255,255,255,0.05);">Registrations</a></li>
                        <li><a href="view_report.php">Annual Report</a></li>
                        <li><a href="../logout.php">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <div class="main-content">
                <!-- Header -->
                <header class="top-header">
                    <h1>Pending Hospital Registrations</h1>
                </header>

                <!-- Content -->
                <?php foreach ($requests as $req): ?>
                    <div class="card" style="margin: 10px 20px 20px;">
                        <!-- Info -->
                        <div class="reg-info">
                            <div>
                                <h3 style="margin: 0 0 5px 0;"><?php 
                                    echo htmlspecialchars($req['hospital_name']); 
                                    ?></h3>
                                <p style="margin: 0; font-size: 0.85rem;">Submitted: <?php echo htmlspecialchars($req['submit_date']); ?>  | License Verified: Yes</p>
                            </div>

                            <!-- Buttons -->
                            <div class="reg-btn">
                                <a href="<?php echo htmlspecialchars($req['documents']); ?>" target="_blank" class="btn btn-view" style="color: white;">
                                    View Documents
                                </a>
                                <button class="btn btn-approve" onclick="buttonsAction('approve', 
                                    <?php echo $req['request_id']; ?>, 
                                    <?php echo $req['hospital_id']; ?>)">Approve
                                </button>
                                <button class="btn btn-reject" onclick="buttonsAction('reject', 
                                    <?php echo $req['request_id']; ?>, 
                                    <?php echo $req['hospital_id']; ?>)">Reject
                                </button>
                            </div>
                        </div>

                        
                        
                    </div>
                <?php endforeach; ?>
            </div>


        </div>

        <!-- Hidden Form pass value to php -->
        <form id="actionForm" method="POST" action="pending_reg.php" style="display:none;">
            <input type="hidden" name="action" id="form_action">
            <input type="hidden" name="request_id" id="form_req_id">
            <input type="hidden" name="hospital_id" id="form_hosp_id">
            <input type="hidden" name="reason" id="form_reason">
        </form>
        
    </body>
</html>
