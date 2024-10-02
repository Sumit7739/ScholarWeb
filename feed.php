<?php
session_start();
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

include 'config.php'; // Database connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ham.css">
    <title>Feed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .feed-container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .post-form textarea {
            width: 70%;
            padding: 15px 20px;
            border: 1px solid #ccc;
            border-radius: 15px;
            margin-bottom: 10px;
            font-size: 16px;
            resize: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }


        .post-form button {
            background-color: #4CAF50;
            color: white;
            padding: 5px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .post-form button:hover {
            background-color: #45a049;
        }

        .feed-posts {
            margin-top: 20px;
        }

        .post {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
        }

        .post-header {
            font-size: 14px;
            color: #555;
        }

        .post-content {
            margin-top: 10px;
            font-size: 16px;
        }

        .post-footer {
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .post-footer .icon {
            cursor: pointer;
            margin-right: 15px;
            font-size: 18px;
        }

        .like-btn {
            color: #3b5998;
        }

        .reply-btn {
            color: #45a049;
        }

        .reply-section {
            margin-top: 10px;
            padding-left: 20px;
            border-left: 2px solid #f0f2f5;
        }

        .reply-section form textarea {
            width: 80%;
            padding: 18px 10px;
            /* padding-left: 10px; */
            border: 1px solid #ccc;
            border-radius: 15px;
            margin-bottom: 5px;
            font-size: 14px;
            resize: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .reply-section form button {
            background-color: #45a049;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .reply-section .reply {
            font-size: 14px;
            margin-top: 5px;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            vertical-align: middle;
        }

        .image-upload-label {
            cursor: pointer;
            font-size: 1.5em;
            color: #555;
            margin-right: 10px;
        }

        #image-preview {
            margin-top: 10px;
            border: 1px solid #ccc;
            margin-bottom: 30px;
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
                <li><a href="feed.php"><i class="fa-solid fa-newspaper" id="active"></i>&nbsp; Feed</a></li>
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
    <!-- End of Header -->
    <div class="feed-container">
        <div class="post-form">
            <form action="post_feed.php" method="POST" enctype="multipart/form-data">
                <textarea name="post_content" placeholder="What's your doubt?" required></textarea>

                <!-- Camera Icon and File Input -->
                <label for="image-upload" class="image-upload-label">
                    <i class="fa fa-camera"></i>
                </label>
                <input type="file" id="image-upload" name="post_image" accept="image/*" style="display:none;" onchange="showPreview(event);">

                <img id="image-preview" src="" alt="Image Preview" style="display:none; width: 250px; height: auto;" />

                <button type="submit" class="button-17">Post</button>
            </form>
        </div>
    </div>

    <div class="feed-posts feed-container">
        <!-- Loop through feed posts here -->

        <?php
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.html");
            exit;
        }

        $query = "SELECT posts.*, users.name, users.profile_pic FROM posts
              JOIN users ON posts.user_id = users.id
              ORDER BY post_date DESC";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $post_id = $row['id'];
            $likes_count = $row['likes_count'];
            $profilePic = $row['profile_pic'] ? $row['profile_pic'] : 'default.png'; // Use default pic if none is set

            // Define the profile picture path
            $profilePicPath = "uploads/profile_pics/" . $profilePic;

            // Define the post image path (if any)
            $postImagePath = !empty($row['image']) ? htmlspecialchars($row['image']) : '';

            echo "<div class='post'>
        <div class='post-header'>
            <img src='" . htmlspecialchars($profilePicPath) . "' alt='Profile Picture' class='profile-pic ' />
            <strong>{$row['name']}</strong> - <small>{$row['post_date']}</small>
        </div>
                <div class='post-content'>
                    {$row['content']}
                </div> <br>";

            // Display post image if it exists
            if (!empty($postImagePath)) {
                echo "<div class='post-image'>
                    <img src='" . $postImagePath . "' alt='Post Image' style='max-width: 60%; height: auto; border-radius:8px;' />
                  </div>";
            }

            echo "<div class='post-footer'>
                    <div>
                        <span class='icon like-btn' onclick='likePost($post_id)'>&nbsp; 👍</span> $likes_count Likes
                        <span class='icon reply-btn' onclick='toggleReply($post_id)'>&nbsp; &nbsp; &nbsp; 💬</span>Reply
                    </div>
                </div>
                <div class='reply-section' id='reply-section-$post_id' style='display:none;'>
                    <!-- Display replies here -->
                    <form action='reply_post.php' method='POST'>
                        <textarea name='reply_content' placeholder='Write your reply...' required></textarea>
                        <input type='hidden' name='post_id' value='$post_id'>
                        <button type='submit' class='button-17'>Reply</button>
                    </form>";

            // Fetch and display replies for each post
            $reply_query = "SELECT replies.*, users.name FROM replies
                        JOIN users ON replies.user_id = users.id
                        WHERE post_id = $post_id ORDER BY replies.created_at ASC";
            $reply_result = mysqli_query($conn, $reply_query);

            while ($reply_row = mysqli_fetch_assoc($reply_result)) {
                echo " <br> <div class='reply'>
            <img src='" . htmlspecialchars($profilePicPath) . "' alt='Profile Picture' class='profile-pic ' />
                        <strong>{$reply_row['name']}</strong> - <small>{$reply_row['created_at']}</small><br><br>
                        {$reply_row['reply_content']}
                    </div> <hr> <br>";
            }

            echo "</div></div>";
        }
        ?>
    </div>
    <script>
        function likePost(post_id) {
            // Send an AJAX request to like/unlike the post
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'like_post.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    location.reload(); // Reload page after like/unlike action
                }
            };
            xhr.send('post_id=' + post_id);
        }

        function toggleReply(post_id) {
            // Toggle the reply form for the post
            var replySection = document.getElementById('reply-section-' + post_id);
            if (replySection.style.display === 'none') {
                replySection.style.display = 'block';
            } else {
                replySection.style.display = 'none';
            }
        }

        function showPreview(event) {
            var preview = document.getElementById('image-preview');
            var file = event.target.files[0];

            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
                preview.style.display = 'none';
            }
        }
    </script>

    <script src="script.js"></script>
</body>

</html>