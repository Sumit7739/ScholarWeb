<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include("config.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user ID is set in the query string
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Ensure it's an integer

    // Fetching user details from the database
    $sql = "SELECT id, name, email, profile_pic, college_name, semester FROM users WHERE id = $user_id LIMIT 1"; // Fetch user with the specified ID
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("User not found.");
    }

    // Fetching homework assignments submitted by the user
    $homework_sql = "SELECT COUNT(*) AS homework_count FROM user_homework WHERE user_id = $user_id";
    $homework_result = $conn->query($homework_sql);
    $homework_count = 0;

    if ($homework_result->num_rows > 0) {
        $homework_row = $homework_result->fetch_assoc();
        $homework_count = $homework_row['homework_count']; // Get the count of homework assignments
    }

    // Fetching homework assignments details for the user
    $homework_details_sql = "SELECT task_no, task_name, description, url FROM user_homework WHERE user_id = $user_id";
    $homework_details_result = $conn->query($homework_details_sql);
} else {
    die("No user ID provided.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> <!-- Link to Font Awesome -->
    <style>
        /* Reset some default styles */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            /* Light gray background similar to Facebook */
            color: #333;
        }

        .dashboard-header {
            background-color: #4267B2;
            /* Facebook blue */
            color: white;
            padding: 20px;
            text-align: center;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .dashboard-header a {
            color: white;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
            font-weight: bold;
        }

        .dashboard-header a:hover {
            text-decoration: underline;
        }

        .user-details {
            max-width: 90%;
            margin: 20px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            justify-content: center;
            align-items: center;
            /* Center the content */
        }

        .profile-pic-container {
            position: relative;
            /* To position the icons */
        }

        .user-details p {
            margin: 10px 0;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
        }

        hr {
            color: #ccc;
        }

        .profile-pic {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 2px solid #4267B2;
            /* Blue border around profile picture */
            margin: 0 auto;
            text-align: center;
            align-items: center;
            /* Center the profile picture */
        }

        .icon-container {
            margin-top: 10px;
            /* Space between the profile picture and icons */
        }

        .icon {
            display: inline-block;
            margin: 0 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            line-height: 40px;
            text-align: center;
            font-size: 20px;
            transition: background-color 0.3s;
        }

        .icon:hover {
            font-size: 22px;
            /* Darker blue on hover */
        }

        .message {
            margin-top: 10px;
            /* Space above the message */
            font-size: 16px;
            /* Change font size as needed */
            padding: 10px;
            border-radius: 5px;
            /* Rounded corners */
            display: none;
            /* Initially hidden */
        }

        .message.success {
            background-color: #d4edda;
            /* Light green background */
            color: #155724;
            /* Dark green text */
            border: 1px solid #c3e6cb;
            /* Darker green border */
        }

        .message.error {
            background-color: #f8d7da;
            /* Light red background */
            color: #721c24;
            /* Dark red text */
            border: 1px solid #f5c6cb;
            /* Darker red border */
        }

        .user-details h2 {
            text-align: center;
            margin: 10px 0;
            color: #4267B2;
        }

        #h2{
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .user-details p {
            line-height: 1.6;
            margin: 10px 0;
        }

        .user-details table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-details table th,
        .user-details table td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .user-details table th {
            background-color: #4267B2;
            color: white;
            font-weight: bold;
        }

        .user-details table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .user-details table tr:hover {
            background-color: #e9ecef;
        }

        .user-details table td {
            font-size: 14px;
        }

        .user-details table td a {
            color: #4267B2;
            text-decoration: none;
        }

        .user-details table td a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .user-details {
                padding: 10px;
            }

            .user-details h2{
                text-align: center;
            }

            .profile-pic {
                width: 100px;
                height: 100px;
            }

            .icon-container {
                margin-top: 5px;
            }

            .icon {
                width: 30px;
                height: 30px;
                line-height: 30px;
                font-size: 16px;
            }

            .user-details table {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">ScholarWeb</div>
            <div class="hamburger" id="hamburger">
                <i class="fa fa-bars"></i> <!-- Hamburger Icon -->
            </div>
            <ul class="menu" id="menu">
                <li><a href="profile.php"><i class="fa fa-user"></i>&nbsp; Profile</a></li>
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

    <div class="profile-pic-container user-details">
        <h2 id="h2"><strong>User Details of- &nbsp;</strong> <?php echo $user['name']; ?></h2>
        <br>
        <?php
        // Define the upload directory
        $uploadDirectory = 'uploads/profile_pics/';
        $profilePicPath = $uploadDirectory . ($user['profile_pic'] ? $user['profile_pic'] : 'default.png');
        ?>
        <!-- Display the user's profile picture -->
        <img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" class="profile-pic">
        <div class="icon-container">
            <br>
            <!-- Add Friend Icon -->
            <div class="icon add-friend" data-friend-id="<?php echo $user['id']; ?>" title="Add Friend">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="icon" title="Chat">
                <i class="fas fa-comment-dots"></i> <!-- Font Awesome icon for Chat -->
            </div>
        </div>
        <div id="message" class="message"></div> <!-- Message display area -->
    </div>

    <div class="user-details">
        <br>
        <p><strong>Semester:</strong> <?php echo $user['semester']; ?></p>
        <p><strong>College:</strong> <?php echo $user['college_name']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <br>
        <hr>
        <h3>Homework Assignments</h3>
        <p><strong>Number of Homework Assignments Submitted:</strong> <?php echo $homework_count; ?></p>
        <br>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Task No</th>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>URL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($homework_details_result->num_rows > 0) {
                    while ($homework_row = $homework_details_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $homework_row['task_no'] . "</td>";
                        echo "<td>" . $homework_row['task_name'] . "</td>";
                        echo "<td>" . $homework_row['description'] . "</td>";
                        echo "<td><a href='" . $homework_row['url'] . "' target='_blank'>View Homework</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No homework assignments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
    </div>
    <script src="script.js"></script>
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

        $(document).ready(function() {
            // Assuming you have a button with the class 'add-friend'
            $('.add-friend').on('click', function() {
                var friendId = $(this).data('friend-id'); // Get the friend ID from the icon's data attribute

                $.ajax({
                    url: 'add_friend.php', // The PHP script that handles the request
                    type: 'POST',
                    data: {
                        friend_id: friendId
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Clear previous messages
                        $('#message').removeClass('success error').hide();

                        // Set the message and class based on response
                        $('#message').text(response.message).addClass(response.status).fadeIn();
                    },
                    error: function() {
                        $('#message').text('An error occurred while processing the request.').addClass('error').fadeIn();
                    }
                });
            });

        });
    </script>

</body>

</html>

<?php
$conn->close();
?>