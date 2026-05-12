<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Bank System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: rgb(11, 20, 38);
        }

        .login-form {
            margin: auto;
            width: 100%;
            max-width: 300px;
            background-color: rgb(42, 44, 76);
            padding: 30px;
            border-radius: 8px;
        }

        .login-form input, 
        .login-form button {
            /* max-width: 250px; */
            width: 100%;      /* Fill the width of the card */
            padding: 12px;    /* Larger touch target for mobile fingers */
            margin-top: 8px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 16px;  /* Prevents iOS zooming in on inputs */
        }
        .title {
            width: 100%;            /* Spans full width */
            padding-left: 20px;
            box-sizing: border-box; /* Ensures padding doesn't create scrollbars */
        }

        label, a, p, .title {
            color: white;
        }
    </style>
</head>

<body>

    <?php
        // Session start to remember the user logged in
        session_start();

        include "classes/Database.php";
        include "classes/User.php";

        function redirect_role($userRole) {
            if ($userRole == 'Donor') {
                    header("location: Donor/view_events.php");
                    exit();
                } elseif ($userRole == "Organizer") {
                    header("location: organizer/create_event.php");
                    exit();
                } elseif ($userRole == "Hospital") {
                    header("location: hospital/dashboard.php");
                    exit();
                } elseif ($userRole == "Admin") {
                    header("location: admin/manage_users.php");
                    exit();
                }
        }

        // If user logged in redirect
        if (isset($_SESSION['user_id'])) {
            $user = new User($con, $_SESSION['user_id']);
            redirect_role($user->getRole());
            exit();
        }

        // If submit button pressed
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $email = $_POST['email'];
            $pass = $_POST['pass'];

            // Create user object and pass db connection
            $user = new User($con);
            
            // Call login method to validate the credential
            if ($user->login($email, $pass)) {
                // Store ID in session if login success
                $_SESSION['user_id'] = $user->getUserID();
                redirect_role($user->getRole());
                
                
            } else {
                // Login failed message
                echo "<script>alert('Wrong Email/Password!')</script>";
            }
        }
    ?>

    <div  class="container">
        
        <div class="title"><h1>Blood Donation Coordinate System</h1></div>
        <div class="login-form">
            <form id="login-form" action="login.php" method="POST">
                <label>Email</label><br>
                <input type="email" name="email" required placeholder="name@email.com"><br>
                
                <label>Password</label><br>
                <input type="password" name="pass" required placeholder="Enter your password"><br>
                <button type="submit" name="login" style="backgroud-color: #333;">Login</button><br>
            </form>
            <a href="forgotpass.php">Forgot Password</a><br>
            <p>New user? <a href="register.php">Register here</a></p><br><br>
            
            <a href="contact.php">Help/Contact</a>
        </div>

    </div>
    
</body>
</html>