<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Include database connection
require 'db.php'; // Assuming your database connection file is 'db.php'

// Handle task status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    // Update task status in the database
    $stmt_update = $pdo->prepare("UPDATE task SET status = :status WHERE id = :task_id");
    $stmt_update->execute([
        'status' => $status,
        'task_id' => $task_id
    ]);

    // Log activity for status change
    $activity_description = "Admin Updated task ID $task_id status to '$status'.";
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', :activity_description, NOW(), 'status change')");
    $stmt_log->execute(['activity_description' => $activity_description]);

    header("Location: edit_task.php?success=1"); // Redirect to avoid form resubmission
    exit;
}

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    // Delete task from the database
    $stmt_delete = $pdo->prepare("DELETE FROM task WHERE id = :task_id");
    $stmt_delete->execute(['task_id' => $task_id]);

    // Log activity for task deletion
    $activity_description = "Admin Deleted task ID $task_id.";
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', :activity_description, NOW(), 'deletion')");
    $stmt_log->execute(['activity_description' => $activity_description]);

    header("Location: edit_task.php?success=1"); // Redirect to avoid form resubmission
    exit;
}

// Fetch all tasks
$stmt_tasks = $pdo->prepare("SELECT id, task_name, task_description, due_date, status, created_at FROM task");
$stmt_tasks->execute();
$tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Tasks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 99%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .status-dropdown {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        button {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .success-message {
            color: green;
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            display: none;
            /* Initially hidden */
        }

        .delete-button {
            background-color: red;
            margin-left: 10px;
        }

        .delete-button:hover {
            background-color: darkred;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Manage Tasks</h1>

        <?php if (isset($_GET['success'])): ?>
            <p id="successMessage" class="success-message">Task status updated or deleted successfully!</p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task Name</th>
                    <th>Task Description</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_description']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <select name="status" class="status-dropdown">
                                    <option value="pending" <?php if ($task['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="completed" <?php if ($task['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                    <option value="overdue" <?php if ($task['status'] == 'overdue') echo 'selected'; ?>>Overdue</option>
                                </select>
                                <button type="submit" name="update_task">Update</button>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <button type="submit" name="delete_task" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript to hide success message after 3 seconds -->
    <script>
        window.onload = function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'block'; // Show the success message
                setTimeout(function() {
                    successMessage.style.display = 'none'; // Hide it after 3 seconds
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        };
    </script>

</body>

</html>