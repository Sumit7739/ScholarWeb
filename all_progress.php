<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}


$modulesStmt = $pdo->prepare("SELECT * FROM modules ORDER by id DESC");
$modulesStmt->execute();
$modules = $modulesStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracker</title>
    <link rel="stylesheet" href="ham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .module-container {
            background-color: #ffffcc;

            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }


        .progress-container {
            background-color: #000;
            padding: 10px;
            border-radius: 5px;
        }

        .progress-bar {
            height: 20px;
            background-color: #4caf50;

            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .progress-wrapper {
            background-color: #ddd;

            border-radius: 5px;
            overflow: hidden;

            height: 20px;

        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .module-title {
            font-size: 18px;
            font-weight: bold;
        }

        .progress-text {
            font-size: 14px;
            font-weight: bold;
        }

        .topic-container {

            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .topic-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topic-title {
            font-size: 16px;
        }

        .topic-status {
            font-size: 14px;
            font-weight: bold;
        }

        #active {
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
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; All tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; All homework</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; All notifications</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line" id="active"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>

    <h2>Progress Tracker</h2>

    <?php foreach ($modules as $module): ?>
        <div class="module-container">
            <div class="module-header">
                <div class="module-title"><?php echo htmlspecialchars($module['name']); ?></div>
                <div class="progress-text"><?php echo htmlspecialchars($module['progress']); ?>% completed</div>
            </div>
            <div class="progress-wrapper">
                <div class="progress-bar" style="width: <?php echo htmlspecialchars($module['progress']); ?>%;"></div>
            </div>
            <br>
            <hr>
            <!-- Topics List -->
            <?php

            $topicsStmt = $pdo->prepare("SELECT * FROM topics WHERE module_id = :module_id");
            $topicsStmt->bindParam(':module_id', $module['id']);
            $topicsStmt->execute();
            $topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="topics">
                <h4>Topics:</h4>
                <?php foreach ($topics as $topic): ?>
                    <div class="topic-container">
                        <div class="topic-header">
                            <div class="topic-title"><?php echo htmlspecialchars($topic['topic_name']); ?></div>
                            <div class="topic-status">
                                <?php echo htmlspecialchars($topic['status']); ?>
                            </div>
                        </div>
                        <div class="progress-wrapper">
                            <!-- If topic is "complete", the progress bar is full, otherwise it's empty or partial -->
                            <div class="progress-bar" style="width: <?php echo ($topic['status'] === 'complete') ? '100' : '0'; ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="script.js"></script>
</body>

</html>