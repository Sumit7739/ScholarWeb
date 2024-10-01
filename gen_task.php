<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Database connection
require_once 'config.php'; // Use require_once for critical files

// Create a new MySQLi connection
// $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle task insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_task'])) {
    // Validate and sanitize input
    $task_name = filter_input(INPUT_POST, 'task_name', FILTER_SANITIZE_STRING);
    $task_description = filter_input(INPUT_POST, 'task_description', FILTER_SANITIZE_STRING);
    $due_date = filter_input(INPUT_POST, 'due_date', FILTER_SANITIZE_STRING);

    if ($task_name && $task_description && $due_date) {
        $status = 'pending'; // Default status

        try {
            // Start transaction
            $conn->begin_transaction();

            // Insert task into task table
            $stmt_insert = $conn->prepare("INSERT INTO task (task_name, task_description, due_date, status) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $task_name, $task_description, $due_date, $status);
            $stmt_insert->execute();

            // Insert activity log for admin
            $activity_description = "Admin created a new task: " . $task_name;
            $stmt_activity = $conn->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', ?, NOW(), 'task')");
            $stmt_activity->bind_param("s", $activity_description);
            $stmt_activity->execute();

            // Commit transaction
            $conn->commit();

            header("Location: gen_task.php?success=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch tasks
try {
    $stmt_tasks = $conn->prepare("SELECT id, task_name, task_description, due_date, status, created_at FROM task ORDER BY created_at DESC");
    $stmt_tasks->execute();
    $tasks = $stmt_tasks->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $error = "Error fetching tasks: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Task</title>
    <style>
        /* Basic Styles */
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 95%;
            /* max-width: 1000px; */
            margin: 0 auto;
            padding: 20px;
            background-color: #f2f2f2;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Generate Task</h1>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Task created successfully!</p>
        <?php endif; ?>

        <!-- Task Creation Form -->
        <form method="POST" action="">
            <label for="task_name">Task Name<span style="color: red;">*</span></label>
            <input type="text" name="task_name" placeholder="Task Name" required>
            <label for="task_description">Task Description<span style="color: red;">*</span></label>
            <textarea name="task_description" placeholder="Task Description" required></textarea>
            <label for="due_date">Due Date<span style="color: red;">*</span></label>
            <input type="date" name="due_date" required>
            <button type="submit" name="create_task">Create Task</button>
        </form>

        <!-- User Tasks List -->
        <h2>Your Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['status']); ?></td>
                        <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>

</html>