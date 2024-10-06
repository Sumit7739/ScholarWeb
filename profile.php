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
include 'config.php'; // Ensure this path is correct

// Get user data from session
$user_id = $_SESSION['user_id'];

// Fetch user information (optional, if needed)
$stmt = $conn->prepare("SELECT name, email, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize data arrays
$classes = [];
$tasks = [];
$activities = [];
$notifications = [];
$progress = [];

// Check for class schedule
$tableExists = $conn->query("SHOW TABLES LIKE 'class_schedules'")->num_rows > 0;
if ($tableExists) {
    $stmt_classes = $conn->prepare("SELECT class_name, schedule_date, time FROM class_schedules ORDER BY id DESC");
    $stmt_classes->execute();
    $result_classes = $stmt_classes->get_result();
    $classes = $result_classes->fetch_all(MYSQLI_ASSOC);
}

// Check for tasks
$tableExists = $conn->query("SHOW TABLES LIKE 'task'")->num_rows > 0;
if ($tableExists) {
    $stmt_tasks = $conn->prepare("SELECT id, task_name, due_date FROM task ORDER BY id DESC");
    $stmt_tasks->execute();
    $result_tasks = $stmt_tasks->get_result();
    $tasks = $result_tasks->fetch_all(MYSQLI_ASSOC);
}

// Fetch the count of new tasks
$stmt_new_tasks = $conn->prepare("SELECT COUNT(*) as new_count FROM task WHERE is_new = 1");
$stmt_new_tasks->execute();
$result_new_tasks = $stmt_new_tasks->get_result();
$new_task_count = $result_new_tasks->fetch_assoc()['new_count'];

// Fetch the count of new notifications
$stmt_new_notifications = $conn->prepare("SELECT COUNT(*) as new_count FROM notifications WHERE is_new = 1");
$stmt_new_notifications->execute();
$result_new_notifications = $stmt_new_notifications->get_result();
$new_notification_count = $result_new_notifications->fetch_assoc()['new_count'];

// Fetch the count of new posts
$stmt_new_posts = $conn->prepare("SELECT COUNT(*) as new_count FROM posts WHERE is_new = 1");
$stmt_new_posts->execute();
$result_new_posts = $stmt_new_posts->get_result();
$new_post_count = $result_new_posts->fetch_assoc()['new_count'];

// Check for recent activities
$tableExists = $conn->query("SHOW TABLES LIKE 'activity'")->num_rows > 0;
if ($tableExists) {
    $stmt_activities = $conn->prepare("SELECT activity_description, date FROM activity ORDER BY date DESC");
    $stmt_activities->execute();
    $result_activities = $stmt_activities->get_result();
    $activities = $result_activities->fetch_all(MYSQLI_ASSOC);
}

// Check for notifications
$tableExists = $conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
if ($tableExists) {
    $stmt_notifications = $conn->prepare("SELECT notification_text, created_at FROM notifications ORDER BY created_at DESC");
    $stmt_notifications->execute();
    $result_notifications = $stmt_notifications->get_result();
    $notifications = $result_notifications->fetch_all(MYSQLI_ASSOC);
}

// Check for progress tracker data
$tableExists = $conn->query("SHOW TABLES LIKE 'modules'")->num_rows > 0;
if ($tableExists) {
    $stmt_progress = $conn->prepare("SELECT name, progress FROM modules ORDER BY id DESC");
    $stmt_progress->execute();
    $result_progress = $stmt_progress->get_result();
    $progress = $result_progress->fetch_all(MYSQLI_ASSOC);
}

function getActivityColor($activity_description)
{
    if (stripos($activity_description, 'added') !== false) {
        return 'green';
    } elseif (stripos($activity_description, 'updated') !== false) {
        return 'blue';
    } elseif (stripos($activity_description, 'deleted') !== false) {
        return 'red';
    } elseif (stripos($activity_description, 'changed') !== false) {
        return 'orange';
    } elseif (stripos($activity_description, 'created') !== false) {
        return 'green';
    } else {
        return 'black';
    }
}



// Close the statement and connection if necessary
$stmt->close();
$conn->close();
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
    <style>
        .badge {
            position: relative;
            top: -10px;
            /* right: -10px; */
            background: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .icon {
            font-size: 24px;
            cursor: pointer;
            position: relative;
            margin-right: 10px;
        }

        .icon .count {
            position: absolute;
            top: -5px;
            right: -13px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 12px;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 40px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 300px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .dropdown.active {
            display: block;
        }

        .dropdown-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .dropdown-item button.decline {
            background-color: #dc3545;
        }

        .empty {
            text-align: center;
            padding: 10px;
        }

        @media screen and (max-width: 768px) {
            .friend-requests-container {
                position: fixed;
                top: 19px;
                right: 80px;
                cursor: pointer;
                display: inline-block;
            }


            .dropdown {
                position: absolute;
                top: 40px;
                right: -30px;
                width: 210px;
                padding: 10px;
            }

            footer a {
                font-size: 10px;
            }

            footer p {
                font-size: 10px;
            }
        }
    </style>

</head>

<body>
    <header>
        <nav>
            <div class="logo">ScholarWeb</div>
            <div class="friend-requests-container">
                <div class="icon" id="friendRequestIcon">
                    <i class="fas fa-bell"></i>
                    <span class="count" id="requestCount">0</span>
                </div>

                <div class="dropdown" id="friendRequestDropdown">
                </div>
            </div>

            <div class="hamburger" id="hamburger">
                <i class="fa fa-bars"></i>
            </div>
            <ul class="menu" id="menu">
                <li><a href="profile.php"><i class="fa fa-user" id="active"></i>&nbsp; Profile</a></li>
                <li><a href="friends_list.php"><i class="fa fa-users"></i>&nbsp; Friends</a></li>
                <li><a href="feed.php">
                        <i class="fa-solid fa-newspaper"></i>&nbsp; Feed
                        <?php if ($new_post_count > 0): ?>
                            <span class="badge"><?php echo $new_post_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                <li><a href="all_tasks.php">
                        <i class="fa fa-tasks"></i>&nbsp; Tasks
                        <?php if ($new_task_count > 0): ?>
                            <span class="badge"><?php echo $new_task_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; Homework</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp;Progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; Activities</a></li>
                <li><a href="all_notifications.php">
                        <i class="fa fa-bell"></i>&nbsp; Notifications
                        <?php if ($new_notification_count > 0): ?>
                            <span class="badge"><?php echo $new_notification_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <section class="hero">
        <div class="hero-content">

            <!-- User Info Section -->
            <div class="dashboard-container">
                <div class="profile-section">
                    <?php

                    $uploadDirectory = 'uploads/profile_pics/';


                    $profilePicPath = $uploadDirectory . ($user['profile_pic'] ? $user['profile_pic'] : 'default.png');
                    ?>
                    <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%;">

                </div>
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Your email: <?php echo htmlspecialchars($user['email']); ?></p>
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
        <br>
        <p>Version 2.1.2 </p>
        <ul class="footer-links">
            <li><a href="contact.php">Contact Support</a></li>
            <li><a href="terms.php">Terms of Service</a></li>
            <li><a href="view_updates.php">View Updates</a></li>
        </ul>
    </footer>

    <!-- <script src="script.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.getElementById('hamburger').onclick = function(event) {
            event.stopPropagation();
            const menu = document.getElementById('menu');
            const hamburger = document.getElementById('hamburger');
            menu.classList.toggle('show');
            hamburger.classList.toggle('active');
        };


        document.addEventListener('click', function(event) {
            const menu = document.getElementById('menu');
            const hamburger = document.getElementById('hamburger');


            if (!hamburger.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.remove('show');
                hamburger.classList.remove('active');
            }
        });

        var newTaskCount = <?php echo $new_task_count; ?>;
        var newNotificationCount = <?php echo $new_notification_count; ?>;
        var newPostCount = <?php echo $new_post_count; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            if (newTaskCount > 0) {
                document.querySelector('i.fa-tasks').classList.add('glow');
            }
            if (newNotificationCount > 0) {
                document.querySelector('i.fa-bell').classList.add('glow');
            }
            if (newPostCount > 0) {
                document.querySelector('i.fa-newspaper').classList.add('glow');
            }
        });

        $(document).ready(function() {
            // Friend request icon click event to toggle dropdown
            $('#friendRequestIcon').on('click', function() {
                $('#friendRequestDropdown').toggleClass('active');
            });

            // Load friend requests from the server
            function loadFriendRequests() {
                $.ajax({
                    url: 'load_friend_requests.php', // Backend script to fetch friend requests
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var dropdown = $('#friendRequestDropdown');
                        dropdown.empty(); // Clear previous content

                        if (response.requests.length > 0) {
                            response.requests.forEach(function(request) {
                                dropdown.append(`
                                    <div class="dropdown-item">
                                        <span>${request.name}</span>
                                        <button class="accept-btn" data-id="${request.id}">Accept</button>
                                        <button class="decline-btn decline" data-id="${request.id}">Decline</button>
                                    </div>
                                `);
                            });
                        } else {
                            dropdown.append('<div class="empty">No friend requests</div>');
                        }

                        // Update the request count badge
                        $('#requestCount').text(response.requests.length);
                    }
                });
            }

            // Call this function to load friend requests on page load
            loadFriendRequests();

            // Handle Accept button click
            $(document).on('click', '.accept-btn', function() {
                var requestId = $(this).data('id');
                $.ajax({
                    url: 'accept_friend_request.php',
                    method: 'POST',
                    data: {
                        request_id: requestId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('Friend request accepted!');
                            loadFriendRequests(); // Reload requests
                        } else {
                            showToast(response.message);
                        }
                    },
                    error: function() {
                        showToast('Error accepting friend request.');
                    }
                });
            });

            // Handle Decline button click
            $(document).on('click', '.decline-btn', function() {
                var requestId = $(this).data('id');
                $.ajax({
                    url: 'decline_friend_request.php',
                    method: 'POST',
                    data: {
                        request_id: requestId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('Friend request declined.');
                            loadFriendRequests(); // Reload requests
                        } else {
                            showToast(response.message);
                        }
                    },
                    error: function() {
                        showToast('Error declining friend request.');
                    }
                });
            });

            // Show toast notification
            function showToast(message) {
                $('#message').text(message).fadeIn().delay(2000).fadeOut();
            }
        });
    </script>

</body>

</html>