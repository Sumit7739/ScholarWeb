<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

include 'config.php'; // Assuming database connection

// Fetch the number of users, tasks, messages, and schedules from the database
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$tasks_count = $conn->query("SELECT COUNT(*) as total FROM task")->fetch_assoc()['total'];
$messages_count = $conn->query("SELECT COUNT(*) as total FROM contact_messages")->fetch_assoc()['total'];
$schedules_count = $conn->query("SELECT COUNT(*) as total FROM class_schedules")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-size: 18px;
        }

        .navbar a:hover {
            color: #ffc107;
        }

        .container {
            display: flex;
            flex-direction: column;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-box h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #343a40;
        }

        .stat-box p {
            font-size: 24px;
            color: #007bff;
            font-weight: bold;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #343a40;
        }

        .card a {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .card a:hover {
            background-color: #0056b3;
        }

        .logout {
            position: absolute;
            right: 20px;
            top: 15px;
        }

        .logout a {
            color: white;
            background-color: #dc3545;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout a:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <div class="navbar">
        Admin Dashboard
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $users_count; ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Tasks</h3>
                <p><?php echo $tasks_count; ?></p>
            </div>

            <div class="stat-box">
                <h3>Messages Received</h3>
                <p><?php echo $messages_count; ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Schedules</h3>
                <p><?php echo $schedules_count; ?></p>
            </div>
        </div>

        <!-- Dashboard actions -->
        <div class="admin-actions">
            <div class="card">
                <h3>Add User</h3>
                <a href="adduser.php">Manage Users</a>
            </div>

            <div class="card">
                <h3>Generate Task</h3>
                <a href="gen_task.php">Create Tasks</a>
            </div>

            <div class="card">
                <h3>Edit Tasks</h3>
                <a href="edit_task.php">Edit / Delete Tasks</a>
            </div>

            <div class="card">
                <h3>Manage Schedules</h3>
                <a href="schedules.php">Manage Class Schedules</a>
            </div>

            <div class="card">
                <h3>Add Notifications</h3>
                <a href="addnotifications.php">Send Notifications</a>
            </div>

            <div class="card">
                <h3>Manage Modules</h3>
                <a href="admin_modules.php">View / Edit Modules</a>
            </div>

            <div class="card">
                <h3>Messages</h3>
                <a href="admin_messages.php">View User Messages</a>
            </div>

            <div class="card">
                <h3>View Logs</h3>
                <a href="all_activities.php">Activity Logs</a>
            </div> 
            <div class="card">
                <h3>Delete Activity</h3>
                <a href="delactivity.php">Delete Activity</a>
            </div>
            <div class="card">
                <h3>View Homework</h3>
                <a href="userhomework.php">User Homework</a>
            </div>
             <div class="card">
                <h3>View User</h3>
                <a href="viewusers.php">View Users</a>
            </div>
        </div>
    </div>

</body>

</html>
