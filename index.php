<?php
session_start();

// Redirect to dashboard if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); // Redirect to dashboard if already logged in
    exit();
}

require_once 'config.php';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate user credentials
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['user_role'];
        header('Location: dashboard.php');
        exit();
    } else {
        $login_error = "Invalid credentials";
    }
}

// Handle registration request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match!";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $register_error = "Username already exists!";
        } else {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, password, user_role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                $register_success = "Registration successful! You can now log in.";
            } else {
                $register_error = "Error registering user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Library Management System</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            transition: all 0.3s ease-in-out;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #007bff;
            background-color: #fff;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error, .success {
            text-align: center;
            margin: 10px 0;
            font-size: 14px;
        }
        .error {
            color: #ff0000;
        }
        .success {
            color: #28a745;
        }
        .toggle-link {
            text-align: center;
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
        }
        .toggle-link:hover {
            text-decoration: underline;
        }
        .form-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Library Management System</h2>

        <!-- Login Form -->
        <div class="form-section" id="login-form">
            <h3 class="form-title">Login</h3>
            <form method="POST" action="index.php">
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button type="submit" name="login">Login</button>
            </form>

            <!-- Display login error message -->
            <?php if (isset($login_error)) { echo "<p class='error'>$login_error</p>"; } ?>

            <!-- Link to toggle to registration form -->
            <span class="toggle-link" onclick="toggleForm()">Don't have an account? Register</span><br>
            <!-- New link to view events -->
            <span class="toggle-link"><a href="events.php">View Events</a></span>
        </div>

        <!-- Registration Form (Initially hidden) -->
        <div class="form-section" id="register-form" style="display: none;">
            <h3 class="form-title">Register</h3>
            <form method="POST" action="index.php">
                <input type="text" name="new_username" placeholder="Username" required><br>
                <input type="password" name="new_password" placeholder="Password" required><br>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
                <button type="submit" name="register">Register</button>
            </form>

            <!-- Display registration errors or success -->
            <?php
                if (isset($register_error)) {
                    echo "<p class='error'>$register_error</p>";
                } elseif (isset($register_success)) {
                    echo "<p class='success'>$register_success</p>";
                }
            ?>

            <!-- Link to toggle back to login form -->
            <span class="toggle-link" onclick="toggleForm()">Already have an account? Login</span>
        </div>
    </div>

    <script>
        // Toggle between login and registration forms
        function toggleForm() {
            var loginForm = document.getElementById('login-form');
            var registerForm = document.getElementById('register-form');
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>
