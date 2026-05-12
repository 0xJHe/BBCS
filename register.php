<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Blood Bank System</title>
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
            max-width: 400px; /* Slightly wider for registration */
            background-color: rgb(42, 44, 76);
            padding: 30px;
            border-radius: 8px;
        }

        .login-form input, 
        .login-form select,
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

        label, a, p, .title, h1 {
            color: white;
        }

        /* Hidden class for dynamic fields */
        .hidden {
            display: none;
        }
    </style>
</head>

<body>

    <?php
        session_start();
        include "classes/Database.php";
        include "classes/User.php";

        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $message = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            $role = $_POST['role'];
            $name = $_POST['full_name'];
            $email = $_POST['email'];
            $pass = $_POST['pass'];

            // Collect extra data based on role
            $extraData = [];
            if ($role == 'Donor') {
                $extraData['blood_type'] = $_POST['blood_type'];
                $extraData['contact_number'] = $_POST['contact_number'];
            } elseif ($role == 'Hospital') {
                $extraData['address'] = $_POST['address'];
            }

            $user = new User($con);
            
            // Call the register method
            $result = $user->register($name, $email, $pass, $role, $extraData);

            if ($result === true) {
                echo "<script>alert('Registration Successful! Please Login.'); window.location.href='login.php';</script>";
            } else {
                $message = $result; // Error message
                echo "<script>alert('$message')</script>";
            }
        }
    ?>

    <div class="container">
        
        <div class="title"><h1>Join Us</h1></div>
        <div class="login-form">
            <form id="register-form" action="register.php" method="POST">
                
                <label for="role">Register As</label>
                <select id="role" name="role" required onchange="toggleFields()">
                    <option value="Donor">Donor</option>
                    <option value="Organizer">Organizer</option>
                    <option value="Hospital">Hospital</option>
                </select>

                <label>Full Name / Organization Name</label><br>
                <input type="text" name="full_name" required placeholder="Enter full name"><br>

                <label>Email</label><br>
                <input type="email" name="email" required placeholder="name@email.com"><br>
                
                <label>Password</label><br>
                <input type="password" name="pass" required placeholder="Create a password"><br>

                <div id="donor-fields">
                    <label>Blood Type</label><br>
                    <select name="blood_type">
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select><br>

                    <label>Contact Number</label><br>
                    <input type="text" name="contact_number" placeholder="012-3456789"><br>
                </div>

                <div id="hospital-fields" class="hidden">
                    <label>Hospital Address</label><br>
                    <input type="text" name="address" placeholder="Enter full address"><br>
                </div>

                <button type="submit" name="register">Register</button><br>
            </form>
            
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>

    </div>

    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            var donorFields = document.getElementById("donor-fields");
            var hospitalFields = document.getElementById("hospital-fields");
            
            // Hide all first
            donorFields.classList.add("hidden");
            hospitalFields.classList.add("hidden");

            // Disable inputs so they are not required when hidden
            setInputsDisabled(donorFields, true);
            setInputsDisabled(hospitalFields, true);

            if (role === "Donor") {
                donorFields.classList.remove("hidden");
                setInputsDisabled(donorFields, false);
            } else if (role === "Hospital") {
                hospitalFields.classList.remove("hidden");
                setInputsDisabled(hospitalFields, false);
            }
            // Organizer has no extra fields in this form
        }

        // Helper to disable/enable inputs inside a div
        function setInputsDisabled(container, status) {
            var inputs = container.getElementsByTagName("input");
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].disabled = status;
                inputs[i].required = !status; // Toggle required attribute
            }
            var selects = container.getElementsByTagName("select");
            for (var i = 0; i < selects.length; i++) {
                selects[i].disabled = status;
            }
        }

        // Run once on load to set initial state
        window.onload = toggleFields;
    </script>
    
</body>
</html>