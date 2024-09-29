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


// Fetch all activity logs
$stmt = $pdo->prepare("SELECT * FROM activity ORDER BY date DESC");
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Add viewport meta tag for responsiveness -->
    <title>All Activity Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ham.css">
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-size: 16px;
            /* Set a base font size */
        }

        /* CSS */
        .button {
            background-color: #ffffff;
            border: 0;
            border-radius: 0.5rem;
            box-sizing: border-box;
            color: #111827;
            font-family: "Inter var", ui-sans-serif, system-ui, -apple-system, system-ui,
                "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif,
                "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
            padding: 0.75rem 1rem;
            text-align: center;
            text-decoration: none #d1d5db solid;
            text-decoration-thickness: auto;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
        }

        .button-38:hover {
            background-color: rgb(249, 250, 251);
        }

        .button:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        .button:focus-visible {
            box-shadow: none;
        }

        .activity-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .activity-container ul {
            list-style-type: none;
            padding: 0;
        }

        .activity-container li {
            margin-bottom: 10px;
            font-size: 16px;
            line-height: 1.5;
        }

        .activity-container li strong {
            color: #007BFF;
        }

        .activity-container li em {
            color: #666;
        }

        footer {
            background-color: #333;
            /* Dark background for the footer */
            color: #fff;
            /* White text color */
            padding: 20px;
            /* Padding for the footer */
            text-align: center;
            /* Center align text */
            position: relative;
            /* Relative positioning for better control */
            bottom: 0;
            /* Position at the bottom */
            width: 99%;
            /* Full width */
        }

        footer p {
            margin: 0;
            /* Removes default margin */
            font-size: 14px;
            /* Font size for copyright text */
            line-height: 1.5;
            /* Line height for better readability */
        }

        .footer-links {
            list-style-type: none;
            /* Removes bullets from the list */
            padding: 0;
            /* Removes default padding */
            margin: 10px 0 0;
            /* Margin for spacing above */
        }

        .footer-links li {
            display: inline;
            /* Displays links in a single line */
            margin: 0 15px;
            /* Spacing between links */
        }

        .footer-links a {
            color: #ffffff;
            /* White color for links */
            text-decoration: none;
            /* Removes underline */
            transition: color 0.3s;
            /* Smooth transition for hover effect */
        }

        .footer-links a:hover {
            color: #007bff;
            /* Change color on hover for better visibility */
            text-decoration: underline;
            /* Underline on hover for emphasis */
        }

        /* Responsive styles */
        @media (max-width: 600px) {
            body {
                font-size: 14px;
                /* Reduce base font size for smaller screens */
            }

            header {
                padding: 10px;
                /* Increased padding for touch-friendly navigation */
            }

            .activity-container {
                padding: 15px;
                /* Adjust padding for smaller screens */
                margin: 10px;
                /* Reduce margin for smaller screens */
            }

            .activity-container li {
                font-size: 14px;
                /* Adjust font size for logs */
            }

            .logo {
                font-size: 20px;
                /* Smaller logo font size */
            }

            header nav ul li {
                margin-left: 10px;
                /* Adjust spacing for navigation items */
            }
            footer{
                width:auto;
            }
        }

        #active{
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
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock" id="active"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="section activity-container">
        <ul>
            <?php if (empty($activities)): ?>
                <li>No activity logs found.</li>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($activity['name']); ?></strong> -
                        <?php echo htmlspecialchars($activity['activity_description']); ?>
                        <em>(On: <?php echo htmlspecialchars($activity['date']); ?>)</em>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

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