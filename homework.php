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
$stmt_user->close(); // Close the statement after fetching the result

// Fetch tasks from the `task` table
$stmt_tasks = mysqli_prepare($conn, "SELECT id, task_name, task_description, due_date, status, created_at FROM task ORDER by created_at DESC");
mysqli_stmt_execute($stmt_tasks);
$result = mysqli_stmt_get_result($stmt_tasks);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_tasks); // Close the statement after fetching the tasks

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert homework into the `user_homework` table
    $stmt = $conn->prepare("
        INSERT INTO user_homework (user_id, task_no, task_name, description, url)
        VALUES (?, ?, ?, ?, ?)
    ");

    // Bind parameters
    $stmt->bind_param("iisss", $user_id, $_POST['task_no'], $_POST['task_name'], $_POST['description'], $_POST['url']);

    // Execute the query
    if ($stmt->execute()) {
        // Log activity
        $activity_description = $user_name . " submitted Homework: Task #" . $_POST['task_no'] . " (" . $_POST['task_name'] . ")";
        $activity_type = "Homework Submission";
        $additional_data = "URL: " . $_POST['url'];

        // Insert into activity log
        $stmt_activity = $conn->prepare("
            INSERT INTO activity (name, activity_description, date, type, additional_data)
            VALUES (?, ?, NOW(), ?, ?)
        ");
        $stmt_activity->bind_param("ssss", $user_name, $activity_description, $activity_type, $additional_data);
        $stmt_activity->execute();
        $stmt_activity->close(); // Close the activity statement

        $message = "Homework submitted successfully";
        header("Location: all_homework.php");
        exit;
    } else {
        $message = "There was an error submitting the homework.";
    }
    $stmt->close(); // Close the homework statement
}

$conn->close(); // Close the database connection
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

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            max-width: 800px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e9ecef;
        }

        table td {
            font-size: 14px;
        }

        table td a {
            color: #007bff;
            text-decoration: none;
        }

        table td a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            form,
            table {
                width: 90%;
            }
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
    <!-- Display Tasks -->
    <h2>Task List</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Task Number</th>
                <th>Task Name</th>
                <th>Task Description</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($tasks)) : ?>
                <?php foreach ($tasks as $task) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No tasks found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>