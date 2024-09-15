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

            // Redirect to the dashboard or home page
            header("Location: dashboard.php");
            exit;
        } else {
            // Invalid password
            echo "Invalid password. Please try again.";
        }
    } else {
        // User not found
        echo "No account found with that email. Please sign up.";
    }
}
?>
