<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../classes/Database.php';
require_once '../classes/Admin.php';

// If db connection is set then new admin else set null
$admin = isset($con) ? new Admin($con, $_SESSION['user_id']) : null;

// If request POST & action label is set & admin is not null
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $admin !== null) {
    $action = $_POST['action'];
    $user_id = isset($_POST['user']) ? (int)$_POST['user'] : 0;

    try {
        if ($action === 'reset_password') {
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';

            if ($new_pass === $confirm_pass && !empty($new_pass) && $user_id > 0) {
                if ($admin->resetUserPassword($user_id, $new_pass)) {
                    echo "<script>alert('Reset Password Successfully.')</script>";
                }
            }
        } elseif ($action === 'status' && $user_id > 0) {
            $admin->toggleUserStatus($user_id);
        } elseif ($action === 'delete' && $user_id > 0) {
            if ($admin->deleteUserById($user_id)) {
                echo "<script>alert('Account Deleted.')</script>";
            }
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}

// Fetch users list for display
$users = [];
if ($admin !== null) {
    $users = $admin->getAllUsers();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../style.css">
    <!-- Script -->
    <script>
        // Change Button Enable/Disable State
        function updateButtonState() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            
            const btnIds = ['btn-reset', 'btn-status', 'btn-delete'];
            
            btnIds.forEach(id => {
                const btn = document.getElementById(id);
                // Enable if at least one checkbox is checked
                btn.disabled = checkedCount === 0;
            });
        }

        function showResetForm() {
            document.getElementById('passwordDialog').style.display = 'flex';
        }

        function cancelResetForm() {
            document.getElementById('passwordDialog').style.display = 'none';
        }

        function submitPasswordReset() {

            const newPass = document.getElementById('new_pass').value;
            const confirmPass = document.getElementById('confirm_pass').value;

            if (!newPass) { alert("Please enter a new password."); return; }
            if (newPass !== confirmPass) { alert("Passwords do not match."); return; }
            
            // Set action
            document.getElementById('form_action').value = 'reset_password';
            // alert("tes");
            // Submit main form (Inputs in modal will be included because of form="mainForm")
            document.getElementById('actForm').submit();
        
        }

        function submitStatusToggle() {
            if(confirm(`Are you sure you want to Active/Deactivate this user?`)) {
                document.getElementById('form_action').value = 'status';
                document.getElementById('actForm').submit();
            }
        }

        function submitDelete() {
            if(confirm("WARNING: This will permanently delete the user account. Continue?")) {
                document.getElementById('form_action').value = 'delete';
                document.getElementById('actForm').submit();
            }
        }


    </script>
    <!-- Style -->
    <style>
        #passwordDialog {
            display: none;
            position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        #userModal {
            display: none;
            position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            align-items: center; justify-content: center;
        }
        #userModal.show { display: flex; }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            width: 100%; max-width: 400px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-icon {
            width: 50px; height: 50px;
            background-color: #e0f2fe; color: #0284c7;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px auto; font-size: 24px; font-weight: bold;
        }

        .modal-btn {
            width: 100%; padding: 12px; margin-bottom: 10px;
            border-radius: 6px; font-weight: 600; cursor: pointer;
            border: none; font-size: 0.95rem;
        }

        .btn-mgn { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
        .btn-mgn:hover { background: #e5e7eb; }

        .modal-input {
            width: 100%; padding: 10px; margin-bottom: 10px;
            border: 1px solid #d1d5db; border-radius: 6px;
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
                    <li><a href="manage_users.php" style="color: white; background: rgba(255,255,255,0.05);">Manage Users</a></li>
                    <li><a href="pending_reg.php">Registrations</a></li>
                    <li><a href="view_report.php">Annual Report</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="main-content">
            
            <!-- Header -->
            <header class="top-header">
                <h1>User Account Management</h1>
            </header>

            <!-- Content -->
            <div class="content-body">
                <!-- Action Buttons Toolbar -->
                <div class="action-bar">
                    <button id="btn-reset" class="btn-action btn-reset" disabled onclick="showResetForm()">
                        Reset Password
                    </button>
                    <button id="btn-status" class="btn-action btn-status" disabled onclick="submitStatusToggle()">
                        Active/Deactive
                    </button>
                    <button id="btn-delete" class="btn-action btn-delete" disabled onclick="submitDelete()">
                        Delete Account
                    </button>
                </div>
                
                <div class="table-container">
                    <form id="actForm" method="POST" action="manage_users.php">
                    <!-- Hidden input for action type -->
                    <input type="hidden" name="action" id="form_action">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;"></th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Loop to show every user to the table -->
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="radio" name="user" class="user-checkbox" value="<?php echo $user['user_id']; ?>" onchange="updateButtonState()">
                                    </td>
                                    
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $user['status'] == 'Active' ? '#4ade80' : '#f87171'; ?>">
                                            <?php echo htmlspecialchars($user['status']); ?>
                                        </span>
                                    </td>
                                
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                    </form>
                </div>

            </div>
            

        </div>
        <!-- Reset password dialog -->
        <div id="passwordDialog" >
            <div class="dialog-content>
                <h2 style="margin-top:0;">Reset Password</h2>
                <p style="color:gray; margin-bottom:20px;">Enter new password for selected user(s)</p>

                <div style="text-align:left; margin-bottom:5px; font-weight:bold;">New Password</div>
                <input type="password" name="new_password" form="actForm" id="new_pass" class="modal-input" placeholder="Type new password">
                
                <div style="text-align:left; margin-bottom:5px; font-weight:bold;">Confirm Password</div>
                <input type="password" name="confirm_password" form="actForm" id="confirm_pass" class="modal-input" placeholder="Re-type password">
                
                <div style="display:flex; gap:10px; margin-top:15px; justify-content: flex-end;">
                    <button type="button" onclick="cancelResetForm()" class="modal-btn btn-cancel">Cancel</button>
                    <button type="button" onclick="submitPasswordReset()" class="modal-btn btn-save">Update Password</button>
                </div>
            </div>
        </div>

    </div>
</body>
</html>