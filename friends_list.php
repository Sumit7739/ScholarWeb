<?php
session_start();
include("config.php");

// Assuming the current user is stored in session
$current_user_id = $_SESSION['user_id'];

// Fetch the user's friends and count unread messages from the database
$sql = "SELECT u.id, u.name, u.profile_pic, 
               (SELECT COUNT(*) FROM chats c 
                WHERE c.sender_id = u.id 
                AND c.receiver_id = $current_user_id 
                AND c.is_read = 0) AS unread_count
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
            width: 96%;
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
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
            /* margin-right: 20px; */
            font-size: 24px;
            cursor: pointer;
            color: #3498db;
        }

        .chat-icon:hover {
            color: #2980b9;
        }

        /* Badge styles */
        .badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 10px;
        }

        /* Container for friend requests */
        #friendRequestContainer {
            background-color: #f9f9f9;
            /* Light background color */
            border: 1px solid #ddd;
            /* Border for the container */
            border-radius: 8px;
            /* Rounded corners */
            padding: 15px;
            /* Padding inside the container */
            width: 90%;
            max-width: 400px;
            /* Maximum width of the container */
            margin: 20px auto;
            /* Centering the container */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            /* Subtle shadow effect */
        }

        /* Title for the container */
        #friendRequestContainer h2 {
            font-size: 18px;
            /* Font size for the title */
            margin-bottom: 10px;
            /* Space below the title */
            color: #333;
            /* Dark color for the title */
        }

        /* Dropdown for friend requests */
        #friendRequestDropdown {
            max-height: 300px;
            /* Maximum height for the dropdown */
            overflow-y: auto;
            /* Enable vertical scrolling */
        }

        /* Individual friend request items */
        .dropdown-item {
            display: flex;
            /* Flex layout for items */
            justify-content: space-between;
            /* Space between text and buttons */
            align-items: center;
            /* Center items vertically */
            padding: 10px;
            /* Padding for each item */
            border-bottom: 1px solid #ddd;
            /* Bottom border for separation */
            transition: background-color 0.3s;
            /* Smooth background change */
        }

        .dropdown-item:hover {
            background-color: #f0f0f0;
            /* Change background on hover */
        }

        /* Empty state for no requests */
        .empty {
            color: #777;
            /* Grey color for empty state text */
            text-align: center;
            /* Center text */
            padding: 10px;
            /* Padding for empty state */
        }

        /* Error message style */
        .error {
            color: red;
            /* Red color for error messages */
            text-align: center;
            /* Center text */
            padding: 10px;
            /* Padding for error messages */
        }

        /* Accept and Decline buttons */
        .accept-btn,
        .decline-btn {
            border: none;
            /* Remove default border */
            padding: 5px 10px;
            /* Padding for buttons */
            border-radius: 5px;
            /* Rounded corners for buttons */
            cursor: pointer;
            /* Pointer cursor on hover */
        }

        /* Accept button style */
        .accept-btn {
            background-color: #4CAF50;
            /* Green background */
            color: white;
            /* White text */
        }

        /* Decline button style */
        .decline-btn {
            background-color: #f44336;
            /* Red background */
            color: white;
            /* White text */
        }

        /* Button hover effects */
        .accept-btn:hover {
            background-color: #45a049;
            /* Darker green on hover */
        }

        .decline-btn:hover {
            background-color: #e53935;
            /* Darker red on hover */
        }

        /* Toast message styles */
        #message {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Fixed position */
            bottom: 20px;
            /* Position from the bottom */
            left: 50%;
            /* Center horizontally */
            transform: translateX(-50%);
            /* Adjust for center */
            background-color: rgba(0, 0, 0, 0.8);
            /* Semi-transparent background */
            color: white;
            /* White text */
            padding: 10px 20px;
            /* Padding for toast */
            border-radius: 5px;
            /* Rounded corners */
            z-index: 1000;
            /* Ensure it appears above other content */
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
    <div id="friendRequestContainer">
        <h2>Friend Requests</h2>
        <div id="friendRequestDropdown" class="dropdown">
            <!-- Friend requests will be dynamically loaded here -->
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
                <div class="chat-icon-container">
                    <i class="fas fa-comments chat-icon" onclick="startChat(<?php echo $row['id']; ?>)"></i>
                    <?php if ($row['unread_count'] > 0) { ?>
                        <span class="badge"><?php echo $row['unread_count']; ?></span>
                    <?php } ?>
                </div>
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
        $(document).ready(function() {
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
                                <button class="decline-btn" data-id="${request.id}">Decline</button>
                            </div>
                        `);
                            });
                        } else {
                            dropdown.append('<div class="empty">No friend requests</div>');
                        }
                    },
                    error: function() {
                        $('#friendRequestDropdown').html('<div class="error">Error loading friend requests.</div>');
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

<?php $conn->close(); ?>