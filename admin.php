<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        a {
            display: block;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #3e8e41;
        }
    </style>
</head>

<body>

    <a href="adduser.php">Add user</a>
    <a href="gen_task.php">Generate Task</a>
    <a href="edit_task.php">Edit Tasks</a>
    <a href="schedules.php">Schedules</a>
    <a href="addnotifications.php">Add Notifications</a>
    <a href="admin_modules.php">Admin Modules</a>
</body>

</html>