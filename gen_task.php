<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Database connection
include 'db.php'; // Include your database connection file

// Handle task insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_task'])) {
    // Admin is inserting the task, so we'll log it with "admin" as the name
    $task_name = htmlspecialchars($_POST['task_name']);
    $task_description = htmlspecialchars($_POST['task_description']);
    $due_date = $_POST['due_date'];
    $status = 'pending'; // Default status

    // Insert task into task table
    $stmt_insert = $pdo->prepare("INSERT INTO task (task_name, task_description, due_date, status) VALUES (:task_name, :task_description, :due_date, :status)");
    $stmt_insert->execute([
        'task_name' => $task_name,
        'task_description' => $task_description,
        'due_date' => $due_date,
        'status' => $status
    ]);

    // Insert activity log for admin
    $activity_description = "Admin created a new task: $task_name";
    $stmt_activity = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', :activity_description, NOW(), 'task')");
    $stmt_activity->execute([
        'activity_description' => $activity_description
    ]);

    header("Location: gen_task.php?success=1"); // Redirect to avoid resubmission
}

// Fetch tasks
$stmt_tasks = $pdo->prepare("SELECT id, task_name, task_description, due_date, status, created_at FROM task");
$stmt_tasks->execute();
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
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
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f2f2f2;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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