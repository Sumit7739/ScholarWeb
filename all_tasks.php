<?php


session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Include database connection
require_once 'config.php'; // Make sure to include your database connection

// Fetch all tasks from the database
$stmt_tasks = mysqli_prepare($conn, "SELECT id, task_name, task_description, due_date, status, created_at FROM task ORDER by created_at DESC");
mysqli_stmt_execute($stmt_tasks);
$result = mysqli_stmt_get_result($stmt_tasks);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tasks</title>
    <link rel="stylesheet" href="ham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 90%;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .task-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            /* Space between task containers */
        }

        /* Conditional Styles */
        .task-container.pending {
            border: 2px solid red;
            /* Red border for pending tasks */
        }

        .task-container.completed {
            border: 2px solid green;
            /* Green border for completed tasks */
        }

        .task-container.overdue {
            border: 2px solid orange;
            /* Blue border for overdue tasks */
        }

        .task-header {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .task-details {
            margin-top: 10px;
        }

        .task-details span {
            display: block;
            /* Each detail on a new line */
            margin: 5px 0;
        }

        .no-tasks {
            text-align: center;
            color: #999;
        }

        #task {
            color: red;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">ScholarWeb</div>
            <div class="hamburger" id="hamburger">
                <i class="fa fa-bars"></i> <!-- Hamburger Icon -->
            </div>
            <ul class="menu" id="menu">
                <li><a href="profile.php"><i class="fa fa-user"></i>&nbsp; Profile</a></li>
                <li><a href="feed.php"><i class="fa-solid fa-newspaper"></i>&nbsp; Feed</a></li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks" id="task"></i>&nbsp; All tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; All homework</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; All notifications</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h1>All Tasks</h1>

        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $task): ?>
                <?php
                // Determine if the task is overdue
                $is_overdue = (strtotime($task['due_date']) < time() && $task['status'] !== 'completed');
                ?>
                <div class="task-container 
                    <?php
                    echo htmlspecialchars($task['status']) === 'pending' ? 'pending' : '';
                    echo htmlspecialchars($task['status']) === 'completed' ? 'completed' : '';
                    echo htmlspecialchars($task['status']) === 'overdue' ? 'overdue' : '';
                    // echo $is_overdue ? 'overdue' : '';
                    ?>">
                    <div class="task-header">Task ID: <?php echo htmlspecialchars($task['id']); ?> - <?php echo htmlspecialchars($task['task_name']); ?></div>
                    <div class="task-details">
                        <span><strong>Due Date:</strong> <?php echo htmlspecialchars($task['due_date']); ?></span>
                        <span><strong>Status:</strong> <?php echo htmlspecialchars($task['status']); ?></span>
                        <span><strong>Created At:</strong> <?php echo htmlspecialchars($task['created_at']); ?></span>
                        <span><strong>Description:</strong> <?php echo htmlspecialchars($task['task_description']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-tasks">No tasks found.</div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>

</html>