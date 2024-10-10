<?php
// Database connection
require 'config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the user exists in the users table
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        // Fetch user data
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Log the successful login activity
            $activityDescription = "User logged in successfully with email: ";
            $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'login')";
            $activityStmt = mysqli_prepare($conn, $activityQuery);
            mysqli_stmt_bind_param($activityStmt, 'ss', $_SESSION['user_name'], $activityDescription);
            mysqli_stmt_execute($activityStmt);
            mysqli_stmt_close($activityStmt);

            // Redirect to the dashboard or home page
            header("Location: welcome.php");
            exit;
        } else {
            // Invalid password
            echo "Invalid password. Please try again.";

            // Log the failed login attempt
            $activityDescription = "Failed login attempt for email: ";
            $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'failed_login')";
            $activityStmt = mysqli_prepare($conn, $activityQuery);
            mysqli_stmt_bind_param($activityStmt, 'ss', $email, $activityDescription);
            mysqli_stmt_execute($activityStmt);
            mysqli_stmt_close($activityStmt);
        }
    } else {
        // User not found
        echo "No account found with that email. Please sign up.";

        // Log the failed login attempt
        $activityDescription = "Failed login attempt for non-existing email: ";
        $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'failed_login')";
        $activityStmt = mysqli_prepare($conn, $activityQuery);
        mysqli_stmt_bind_param($activityStmt, 'ss', $email, $activityDescription);
        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);
    }
}
