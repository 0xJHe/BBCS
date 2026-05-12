<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .title {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }

        label, a, p, .title, h1, h2 {
            color: white;
        }

        button {
            cursor: pointer;
            font-weight: bold;
            border: none;
            transition: background 0.3s;
        }
        
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <?php
        session_start();
        include "classes/Database.php";
        include "classes/User.php";

        // If user is already logged in, send them to dashboard
        if (isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $email = $_POST['email'];
            $new_pass = $_POST['new_pass'];
            $confirm_pass = $_POST['confirm_pass'];

            if ($new_pass === $confirm_pass) {
                $user = new User($con);
                
                // Attempt to reset
                $result = $user->resetPassword($email, $new_pass);

                if ($result === true) {
                    echo "<script>
                        alert('Password updated successfully! Please login.');
                        window.location.href='login.php';
                    </script>";
                } else {
                    echo "<script>alert('$result')</script>";
                }
            } else {
                echo "<script>alert('Passwords do not match!')</script>";
            }
        }
    ?>

    <div class="container">
        
        <div class="title">
            <h1>Recovery</h1>
        </div>

        <div class="login-form">
            <h2 style="text-align:center; margin-top:0;">Reset Password</h2>
            <p style="font-size: 14px; text-align: center; margin-bottom: 20px; color: #ccc;">
                Enter your email and new password.
            </p>

            <form action="forgotpass.php" method="POST">
                <label>Registered Email</label><br>
                <input type="email" name="email" required placeholder="name@email.com"><br>
                
                <label>New Password</label><br>
                <input type="password" name="new_pass" required placeholder="New password"><br>

                <label>Confirm Password</label><br>
                <input type="password" name="confirm_pass" required placeholder="Confirm new password"><br>

                <button type="submit">Reset Password</button>
            </form>
            
            <div style="text-align: center;">
                <a href="login.php" style="font-size: 14px;">Back to Login</a>
            </div>
        </div>

    </div>
    
</body>
</html>