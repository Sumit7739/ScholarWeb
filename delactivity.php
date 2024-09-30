<?php
// Start session if needed
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

include 'config.php'; // Assuming database connection

$admin_name = 'admin'; // Hardcoded as 'admin'
$messageStatus = ""; // Message variable

// Check if the deletion request is triggered
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Begin database transaction to ensure both delete and log actions happen together
    $conn->begin_transaction();

    try {
        // Delete activities older than 7 days
        $sql = "DELETE FROM activity WHERE date < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        if ($conn->query($sql) === TRUE) {
            // Log this deletion action in the activity table
            $deletionLog = "Deleted all activities older than 7 days";

            // Prepare the log entry
            $stmt = $conn->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'deletion')");
            $stmt->bind_param('ss', $admin_name, $deletionLog);
            $stmt->execute();

            // Commit the transaction after both operations succeed
            $conn->commit();

            // Set success message
            $messageStatus = "<div class='message-success'>Old activities deleted successfully and action logged.</div>";
        } else {
            throw new Exception("Error deleting records: " . $conn->error);
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of failure
        $conn->rollback();
        $messageStatus = "<div class='message-error'>" . $e->getMessage() . "</div>";
    }

    // Close statement
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Old Activities</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 50%;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
        }

        .btn-delete {
            padding: 10px 20px;
            background-color: #dc3545;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .message-success,
        .message-error {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Delete Activities Older than 7 Days</h2>

        <!-- Display success or error message -->
        <?php echo $messageStatus; ?>

        <!-- Form to trigger deletion -->
        <form method="POST">
            <button class="btn-delete" type="submit">Delete Old Activities</button>
        </form>
    </div>
</body>

</html>