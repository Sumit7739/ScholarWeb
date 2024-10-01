<?php
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Include database connection
include 'config.php';

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user name from `user` table using user_id
$user_id = $_SESSION['user_id'];
$stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($user_name);
$stmt_user->fetch();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert homework into the `user_homework` table
    $stmt = $conn->prepare("
        INSERT INTO user_homework (user_id, task_no, task_name, description, url)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Bind parameters
    $stmt->bind_param("iiss", $user_id, $_POST['task_no'], $_POST['task_name'], $_POST['description'], $_POST['url']);

    // Execute the query
    if ($stmt->execute()) {
        // If homework submission was successful, log the activity
        $activity_description = $user_name . " submitted Homework: Task #" . $_POST['task_no'] . " (" . $_POST['task_name'] . ")";
        $activity_type = "Homework Submission";
        $additional_data = "URL: " . $_POST['url'];

        // Insert activity log into `activity` table
        $stmt_activity = $conn->prepare("
            INSERT INTO activity (name, activity_description, date, type, additional_data)
            VALUES (?, ?, NOW(), ?, ?)
        ");
        $stmt_activity->bind_param("sssss", $user_name, $activity_description, $activity_type, $additional_data);

        // Execute the activity log query
        $stmt_activity->execute();

        $message = "Homework submitted successfully";
        header("Location: all_homework.php");
    } else {
        $message = "There was an error submitting the homework.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Homework</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input,
        textarea {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
        }

        input:focus,
        textarea:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .url-info {
            font-size: 14px;
            color: #666;
        }

        .fa-homework {
            margin-right: 8px;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 20px;
        }

        @media screen and (max-width: 600px) {
            .container {
                padding: 10px;
            }

            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2><i class="fa-solid fa-file-alt"></i> Submit Your Homework</h2>

        <!-- Show message if homework was submitted -->
        <?php if (isset($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="task_no">Task Number</label>
            <input type="number" id="task_no" name="task_no" placeholder="Enter Task Number" required>

            <label for="task_name">Task Name</label>
            <input type="text" id="task_name" name="task_name" placeholder="Enter Task Name" required>

            <label for="description">Task Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Describe the task" required></textarea>

            <label for="url">Hosted URL <span class="url-info">(Provide the link where your homework is hosted)</span></label>
            <input type="url" id="url" name="url" placeholder="https://your-homework-link.com" required>

            <button type="submit"><i class="fa fa-paper-plane"></i> Submit Homework</button>
        </form>
    </div>

</body>

</html>