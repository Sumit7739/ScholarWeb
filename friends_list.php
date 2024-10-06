<?php
session_start();
include("config.php");

// Assuming the current user is stored in session
$current_user_id = $_SESSION['user_id'];

// Fetch the user's friends from the database
$sql = "SELECT u.id, u.name, u.profile_pic 
        FROM users u
        JOIN friendships f ON (f.user_id = u.id OR f.friend_id = u.id)
        WHERE (f.user_id = $current_user_id OR f.friend_id = $current_user_id)
        AND u.id != $current_user_id 
        AND f.status = 'accepted'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
    <link rel="stylesheet" href="dashstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .popup-container {
            width: 95%;
            height: auto;
            margin-top: 20px;
            margin-left: 10px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .popup-content {
            padding: 20px;
        }

        .popup-content input {
            width: 90%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 15px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .popup-content input:focus {
            border-color: #3498db;
        }

        .dropdown-content {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 15px;
            background-color: white;
            padding: 20px;
            position: absolute;
            width: 80%;
            z-index: 1001;
            margin-top: 5px;
            display: none;
        }

        .dropdown-content a {
            display: block;

            padding: 10px 20px;

            color: #333;
            text-decoration: none;

            transition: background-color 0.3s;
        }

        .dropdown-content a:hover {
            background-color: #f0f0f0;
        }

        .friends-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .friend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 4px 4px 4px 3px rgba(0, 0, 0, 0.2);
        }

        .friend-profile {
            display: flex;
            align-items: center;
        }

        .friend-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .chat-icon {
            font-size: 24px;
            cursor: pointer;
            color: #3498db;
        }

        .chat-icon:hover {
            color: #2980b9;
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
                <li><a href="friends_list.php"><i class="fa fa-users" id="active"></i>&nbsp; Friends</a></li>
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
    <div id="search-popup" class="popup-container">
        <div class="popup-content">
            <input type="text" id="popup-search-input" placeholder="Search Users..." autocomplete="off">
            <div id="popup-dropdown-list" class="dropdown-content">
                <!-- Search results will appear here -->
            </div>
        </div>
    </div>
    <div class="friends-container">
        <h2>Your Friends</h2>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="friend-item">
                <div class="friend-profile">
                    <img src="uploads/profile_pics/<?php echo $row['profile_pic'] ?: 'default.png'; ?>" alt="Profile Picture">
                    <p><?php echo $row['name']; ?></p>
                </div>
                <i class="fas fa-comments chat-icon" onclick="startChat(<?php echo $row['id']; ?>)"></i>
            </div>
        <?php } ?>
    </div>
    <script src="script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // When the user types in the popup search input
        $('#popup-search-input').on('input', function() {
            var query = $(this).val(); // Get the search input value

            if (query.length > 0) {
                // Perform AJAX request to fetch data from the server
                $.ajax({
                    url: 'fetch_users.php', // Your backend script to query the database
                    method: 'POST',
                    data: {
                        search: query
                    },
                    dataType: 'json', // Expect JSON response
                    success: function(data) {
                        var $dropdownList = $('#popup-dropdown-list');
                        $dropdownList.empty(); // Clear previous results

                        // Check if data is an array
                        if (Array.isArray(data) && data.length > 0) {
                            // Create links for each user result
                            data.forEach(function(user) {
                                var link = $('<a>')
                                    .attr('href', 'user_dashboard.php?id=' + user.id) // URL to the user dashboard
                                    .text(user.name) // Display user's name
                                    .addClass('dropdown-item'); // Add a class for styling

                                $dropdownList.append(link); // Append the link to the dropdown list
                            });

                            $dropdownList.show(); // Show dropdown if there are results
                        } else {
                            $dropdownList.hide(); // Hide dropdown if no results
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        alert('An error occurred while fetching data.'); // Show an error message
                    }
                });
            } else {
                $('#popup-dropdown-list').hide(); // Hide dropdown if input is empty
            }
        });

        function startChat(friendId) {
            window.location.href = 'chat.php?friend_id=' + friendId; // Redirect to chat page with friend's ID
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>