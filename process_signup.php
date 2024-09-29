<?php

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

        // Log the failed registration attempt
        $activityDescription = "Failed registration attempt for email: " . $email . " - Passwords do not match.";
        $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'failed_registration')";
        $activityStmt = mysqli_prepare($conn, $activityQuery);
        mysqli_stmt_bind_param($activityStmt, 'ss', $name, $activityDescription);
        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);

        exit;
    }

    // Check if email exists in reg_stud (pre-registered students)
    $query = "SELECT * FROM reg_stud WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // If email found in reg_stud, check if user is already registered
        $check_user = "SELECT * FROM users WHERE email = '$email'";
        $user_result = mysqli_query($conn, $check_user);

        if (mysqli_num_rows($user_result) > 0) {
            // If user already exists
            echo "This email is already registered. Please login.";

            // Log the failed registration attempt
            $activityDescription = "Failed registration attempt - email already registered: " . $email;
            $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'failed_registration')";
            $activityStmt = mysqli_prepare($conn, $activityQuery);
            mysqli_stmt_bind_param($activityStmt, 'ss', $name, $activityDescription);
            mysqli_stmt_execute($activityStmt);
            mysqli_stmt_close($activityStmt);
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into users table
            $insert_user = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

            if (mysqli_query($conn, $insert_user)) {
                echo "Account created successfully. You can now log in.";

                // Log the successful registration activity
                $activityDescription = "User registered successfully: " . $email;
                $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'registration')";
                $activityStmt = mysqli_prepare($conn, $activityQuery);
                mysqli_stmt_bind_param($activityStmt, 'ss', $name, $activityDescription);
                mysqli_stmt_execute($activityStmt);
                mysqli_stmt_close($activityStmt);

                // Redirect to login page or dashboard
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    } else {
        // Email not found in reg_stud
        echo "You are not registered or use the email you used during registration.";

        // Log the failed registration attempt
        $activityDescription = "Failed registration attempt - email not found in registration: " . $email;
        $activityQuery = "INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'failed_registration')";
        $activityStmt = mysqli_prepare($conn, $activityQuery);
        mysqli_stmt_bind_param($activityStmt, 'ss', $name, $activityDescription);
        mysqli_stmt_execute($activityStmt);
        mysqli_stmt_close($activityStmt);
    }
}
