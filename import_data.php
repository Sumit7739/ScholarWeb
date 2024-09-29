<?php
session_start();
include 'db.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Handle the import data request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['user_email']; // Get the logged-in user's email from session

    // Fetch data from reg_stud based on the user's email
    $fetchStmt = $pdo->prepare("SELECT college_name, semester, year, father_name FROM reg_stud WHERE email = :email");
    $fetchStmt->bindParam(':email', $email);
    $fetchStmt->execute();

    if ($fetchStmt->rowCount() > 0) {
        $data = $fetchStmt->fetch(PDO::FETCH_ASSOC);

        // Prepare to update the user table with the fetched data
        $updateStmt = $pdo->prepare("UPDATE users SET college_name = :college_name, semester = :semester, year = :year, father_name = :father_name WHERE email = :email");

        // Bind parameters
        $updateStmt->bindParam(':college_name', $data['college_name']);
        $updateStmt->bindParam(':semester', $data['semester']);
        $updateStmt->bindParam(':year', $data['year']);
        $updateStmt->bindParam(':father_name', $data['father_name']);
        $updateStmt->bindParam(':email', $email);

        // Execute the update
        if ($updateStmt->execute()) {
            // Log the import activity
            $activityStmt = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES (:name, :activity_description, NOW(), 'import')");
            $activityDescription = "Imported data from registration";
            $activityStmt->bindParam(':name', $_SESSION['user_name']); // Assuming you store user name in session
            $activityStmt->bindParam(':activity_description', $activityDescription);
            $activityStmt->execute();

            echo json_encode(['success' => true, 'message' => 'Data imported successfully']);
            header('Location: settings.php');
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found in registration table.']);
    }
    exit; // End the script after handling the request
}
