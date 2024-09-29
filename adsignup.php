<?php

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Database connection
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Check if the email already exists
    $check_user = "SELECT * FROM admin WHERE email = '$email'";
    $user_result = mysqli_query($conn, $check_user);

    if (!$user_result) {
        // Handle query error
        echo "Error checking user: " . mysqli_error($conn);
        exit;
    }

    if (mysqli_num_rows($user_result) > 0) {
        // If user already exists
        echo "This email is already registered. Please login.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into admin table
        $insert_user = "INSERT INTO admin (username, email, password) VALUES ('$name', '$email', '$hashed_password')";

        if (mysqli_query($conn, $insert_user)) {
            echo "Account created successfully. You can now log in.";
            // Redirect to login page or dashboard
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign Up - ScholarWeb</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&display=swap" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Create an Account</h1>
            <form method="POST">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required maxlength="8">
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required maxlength="8">
                </div>
                <button type="submit" class="btn btn-signup">Sign Up</button>
            </form>
            <p class="alt-option">Already have an account? <a href="adlogin.php">Login</a></p>
        </div>
    </div>
</body>

</html>