<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection
include('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Handle profile picture upload
if (isset($_POST['submit'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Define allowed file extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadDirectory = 'uploads/profile_pics/';

        if (in_array($fileExtension, $allowedExtensions)) {
            // Create unique file name
            $newFileName = $userId . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDirectory . $newFileName;

            // Move the file to the destination folder
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Update the database with the new profile picture path
                $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $newFileName, $userId);
                if ($stmt->execute()) {
                    echo "Profile picture updated successfully!";
                } else {
                    echo "Error updating profile picture in the database.";
                }
                $stmt->close();
            } else {
                echo "Error moving the uploaded file.";
            }
        } else {
            echo "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        echo "No file uploaded or there was an error.";
    }
}

// Fetch user's profile picture from the database
$sql = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($profilePic);
$stmt->fetch();
$stmt->close();

// Define the path to the uploaded profile pictures
$uploadDirectory = 'uploads/profile_pics/';
$profilePicPath = $uploadDirectory . ($profilePic ? $profilePic : 'default.png');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        .container {
            text-align: center;
            padding: 20px;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ccc;
            margin-bottom: 20px;
        }

        input[type="file"] {
            padding: 10px;
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Your Profile</h1>

        <!-- Display user's profile picture -->
        <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture" class="profile-pic">

        <!-- Form to upload a new profile picture -->
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_pic" required>
            <button type="submit" name="submit">Upload Profile Picture</button>
        </form>
    </div>

    <a href="profile.php">Back to Home page</a>

</body>

</html>