<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Database connection
include 'db.php'; // Include your database connection file

// Handle adding a new schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_schedule'])) {
    $class_name = htmlspecialchars($_POST['class_name']);
    $schedule_date = htmlspecialchars($_POST['schedule_date']);
    $time = htmlspecialchars($_POST['time']);

    // Insert the new schedule into the database
    $stmt_add = $pdo->prepare("INSERT INTO class_schedules (class_name, schedule_date, time, created_at) VALUES (:class_name, :schedule_date, :time, NOW())");
    $stmt_add->execute([
        'class_name' => $class_name,
        'schedule_date' => $schedule_date,
        'time' => $time
    ]);

    // Log the activity of adding a new schedule
    $activity_description = "Added a new schedule for class '$class_name' on '$schedule_date' at '$time'.";
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES ('admin', :activity_description, NOW(), 'addition')");
    $stmt_log->execute(['activity_description' => $activity_description]);

    header("Location: addschedules.php"); // Redirect to avoid resubmission
    exit;
}

// Fetch existing schedules for display (optional)
$stmt_schedules = $pdo->prepare("SELECT * FROM class_schedules");
$stmt_schedules->execute();
$schedules = $stmt_schedules->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Class Schedule</title>
    <style>
        /* Basic Styles */
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 600px;
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

        input[type="text"],
        input[type="date"],
        input[type="time"] {
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
        <h1>Add Class Schedule</h1>
        <form method="POST" action="">
            <input type="text" name="class_name" placeholder="Class Name" required>
            <input type="date" name="schedule_date" required>
            <input type="time" name="time" required>
            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>

        <h2>Existing Schedules</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class Name</th>
                    <th>Schedule Date</th>
                    <th>Time</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($schedule['id']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['schedule_date']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                        <td><?php echo htmlspecialchars($schedule['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <br>

    <a href="gen_task.php">Generate Task</a>
</body>

</html>