<?php
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch user name and homework data
$stmt = $pdo->prepare("
    SELECT uh.task_no, uh.task_name, uh.description, uh.url, uh.created_at AS timestamp, u.name 
    FROM user_homework uh 
    JOIN users u ON uh.user_id = u.id 
    WHERE uh.user_id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$homeworkData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Homework</title>
    <link rel="stylesheet" href="ham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 10px;
        }

        h2 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 30px;
        }

        .homework-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .homework-header {
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .homework-content {
            font-size: 16px;
            margin-bottom: 15px;
            color: #555;
        }

        .homework-timestamp {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }

        .homework-url {
            font-size: 14px;
            color: #007BFF;
            text-decoration: none;
            margin-top: 10px;
            word-wrap: break-word;
        }

        .homework-url:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .homework-box {
                padding: 15px;
            }

            .homework-header {
                font-size: 16px;
            }

            .homework-content {
                font-size: 14px;
            }
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
                <li><a href="all_homework.php"><i class="fa fa-book" id="active"></i>&nbsp; All homework</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; All notifications</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2>Submitted Homework</h2>

        <?php if ($homeworkData): ?>
            <?php foreach ($homeworkData as $homework): ?>
                <div class="homework-box">
                    <div class="homework-header">
                        <i class="fa fa-tasks"></i> Task #<?php echo htmlspecialchars($homework['task_no']); ?>:
                        <?php echo htmlspecialchars($homework['task_name']); ?>
                    </div>
                    <div class="homework-content">
                        <strong>Description:</strong> <?php echo htmlspecialchars($homework['description']); ?>
                    </div>
                    <div class="homework-timestamp">
                        <strong>Submitted by:</strong> <?php echo htmlspecialchars($homework['name']); ?> on
                        <?php echo htmlspecialchars($homework['timestamp']); ?>
                    </div>
                    <a href="<?php echo htmlspecialchars($homework['url']); ?>" target="_blank" class="homework-url">
                        <i class="fa fa-link"></i> View Homework
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No homework submissions found.</p>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>

</html>