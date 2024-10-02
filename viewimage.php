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
// Assume $userId is the ID of the user whose profile picture you want to display
$userId = $_SESSION['user_id']; // Get user ID from query string or session

// Fetch the user's profile picture from the database
$sql = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($profilePic);
$stmt->fetch();
$stmt->close();

// If the profile picture is not set, use the default picture
$profilePicPath = 'uploads/profile_pics/' . ($profilePic ? $profilePic : 'default.png');

// Display the profile picture
?>
<img src="<?php echo $profilePicPath; ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%;">