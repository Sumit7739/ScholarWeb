<?php
session_start();

// error_reporting(E_ALL); // Report all PHP errors
// ini_set('display_errors', 1); // Display errors on the page


include 'config.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Create a new MySQLi connection
// $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the import data request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['user_email']; // Get the logged-in user's email from session

    // Fetch data from reg_stud based on the user's email
    $fetchStmt = $conn->prepare("SELECT college_name, semester, year, father_name FROM reg_stud WHERE email = ?");
    $fetchStmt->bind_param("s", $email);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $college_name = $data['college_name'];
        $semester = $data['semester'];
        $year = $data['year'];
        $father_name = $data['father_name'];

        // Prepare to update the user table with the fetched data
        $updateStmt = $conn->prepare("UPDATE users SET college_name = ?, semester = ?, year = ?, father_name = ? WHERE email = ?");
        $updateStmt->bind_param("sssss", $college_name, $semester, $year, $father_name, $email);

        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                // Log the import activity
                $activityStmt = $conn->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'import')");
                $activityDescription = "Imported data from registration";
                $activityStmt->bind_param("ss", $_SESSION['user_name'], $activityDescription);
                $activityStmt->execute();

                echo json_encode(['success' => true, 'message' => 'Data imported successfully']);
                header('Location: settings.php');
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes were made. Data might be already up to date.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user data: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found in registration table.']);
    }

    $fetchStmt->close();
    $updateStmt->close();
    $conn->close();

    exit; // End the script after handling the request
}
