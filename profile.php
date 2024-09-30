<?php
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Database connection
include 'db.php'; // Include your database connection file

// Get user data from session
$user_id = $_SESSION['user_id'];

// Fetch user information (optional, if needed)
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

// Initialize data arrays
$classes = [];
$tasks = [];
$activities = [];
$notifications = [];
$progress = [];

// Check for class schedule
$tableExists = $pdo->query("SHOW TABLES LIKE 'class_schedules'")->rowCount() > 0;
if ($tableExists) {
    $stmt_classes = $pdo->prepare("SELECT class_name, schedule_date, time FROM class_schedules ORDER BY id DESC");
    $stmt_classes->execute();
    $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
}

// Check for tasks
$tableExists = $pdo->query("SHOW TABLES LIKE 'task'")->rowCount() > 0;
if ($tableExists) {
    $stmt_tasks = $pdo->prepare("SELECT id, task_name, due_date FROM task ORDER BY id DESC ");
    $stmt_tasks->execute();
    $tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);
}

// Check for recent activities
$tableExists = $pdo->query("SHOW TABLES LIKE 'activity'")->rowCount() > 0;
if ($tableExists) {
    $stmt_activities = $pdo->prepare("SELECT activity_description, date FROM activity ORDER BY date DESC");
    $stmt_activities->execute();
    $activities = $stmt_activities->fetchAll(PDO::FETCH_ASSOC);
}

// Check for notifications
$tableExists = $pdo->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
if ($tableExists) {
    $stmt_notifications = $pdo->prepare("SELECT notification_text, created_at FROM notifications ORDER BY created_at DESC");
    $stmt_notifications->execute();
    $notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);
}

// Check for progress tracker data
$tableExists = $pdo->query("SHOW TABLES LIKE 'modules'")->rowCount() > 0;
if ($tableExists) {
    $stmt_progress = $pdo->prepare("SELECT name, progress FROM modules ORDER BY id DESC");
    $stmt_progress->execute();
    $progress = $stmt_progress->fetchAll(PDO::FETCH_ASSOC);
}


function getActivityColor($activity_description)
{
    if (stripos($activity_description, 'added') !== false) {
        return 'green'; // Color for added activities
    } elseif (stripos($activity_description, 'updated') !== false) {
        return 'blue'; // Color for updated activities
    } elseif (stripos($activity_description, 'deleted') !== false) {
        return 'red'; // Color for deleted activities
    } elseif (stripos($activity_description, 'changed') !== false) {
        return 'orange'; // Color for changed activities
    } elseif (stripos($activity_description, 'created') !== false) {
        return 'green'; // Color for changed activities
    } else {
        return 'black'; // Default color
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


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
                <li><a href="profile.php"><i class="fa fa-user" id="active"></i>&nbsp; Profile</a></li>
                <li><a href="feed.php"><i class="fa-solid fa-newspaper"></i>&nbsp; Feed</a></li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; Tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; Homework</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp;Progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; Activities</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; Notifications</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>


    <section class="hero">
        <div class="hero-content">

            <!-- User Info Section -->
            <div class="dashboard-container">
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Your email: <?php echo htmlspecialchars($user['email']); ?></p>
                <!-- <a href="profile.php" class="btn">Edit Profile</a> -->
            </div>

            <!-- Class Schedule Section -->
            <div class="section class-info-container">
                <h2><i class="fas fa-calendar-alt"></i> Class Schedule</h2>
                <hr>
                <ul>
                    <?php if (empty($classes)): ?>
                        <li>No classes found.</li>
                    <?php else: ?>
                        <?php foreach ($classes as $class): ?>
                            <li> <?php echo htmlspecialchars($class['class_name']) . " - " . htmlspecialchars($class['schedule_date']) . ", " . htmlspecialchars($class['time']); ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Tasks Section -->
            <div class="section tasks-container">
                <h2><i class="fas fa-tasks"></i> Your Tasks</h2>
                <hr> <br>
                <ul>
                    <?php if (empty($tasks)): ?>
                        <li>No tasks found.</li>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <li><?php echo htmlspecialchars($task['id']) . ". " . htmlspecialchars($task['task_name']) . " (Due: " . htmlspecialchars($task['due_date']) . ")"; ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <!-- <a href="#" class="button-17">Submit Homework</a> -->
            </div>

            <!-- Homework Container -->
            <div class="section homework-container">
                <h2><i class="fas fa-book"></i> Homework</h2>
                <hr>
                <br>
                <h2><a href="homework.php" class="button">Submit your Homework</a></h2>
                <hr>
                <h2><a href="all_homework.php" class="button">View all Homeworks</a></h2>
            </div>

            <!-- Notifications Section -->
            <div class="section notifications-container">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                <hr>
                <ul>
                    <?php if (empty($notifications)): ?>
                        <li>No notifications found.</li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <li><?php echo htmlspecialchars($notification['notification_text']) . " (date: " . htmlspecialchars($notification['created_at']) . ")"; ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>


            <div class="section progress-container">
                <h2><i class="fas fa-chart-line"></i> Progress Tracker</h2>
                <hr>
                <ul>
                    <?php if (empty($progress)): ?>
                        <li>No progress data found.</li>
                    <?php else: ?>
                        <?php foreach ($progress as $item): ?>
                            <li>
                                <div>
                                    <?php echo htmlspecialchars($item['name']); ?>:
                                    <?php echo htmlspecialchars($item['progress']); ?>% completed
                                </div>
                                <div class="progress-wrapper">
                                    <div class="progress-bar" style="width: <?php echo htmlspecialchars($item['progress']); ?>%;"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Resources Section -->
            <div class="section resources-container">
                <h2>Resources</h2>
                <hr>
                <ul>
                    <li><a href="https://sumit7739.github.io/Webdev/" target="_blank">Webdev Documentation</a></li>
                    
                </ul>
            </div>
        </div>
        <!-- Recent Activity Section -->
        <div class="section recent-activity-container">
            <h2><i class="fas fa-clock"></i> Recent Activity</h2>
            <ul>
                <?php if (empty($activities)): ?>
                    <li>No recent activities found.</li>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <li style="color: <?php echo getActivityColor($activity['activity_description']); ?>;">
                            <?php echo htmlspecialchars($activity['activity_description']) . " (On: " . htmlspecialchars($activity['date']) . ")"; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 ScholarWeb. All Rights Reserved.</p>
        <ul class="footer-links">
            <li><a href="contact.php">Contact Support</a></li>
            <li><a href="terms.php">Terms of Service</a></li>
        </ul>
    </footer>

    <script src="script.js"></script>

</body>

</html>