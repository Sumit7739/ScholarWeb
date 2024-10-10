<?php
// Start the session
session_start();

// Include database connection file
include("config.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_name = $user['name'];

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
        }

        .welcome-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 98%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .welcome-message h1 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .welcome-message p {
            font-size: 1rem;
            color: #666;
        }

        .loading-screen {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        /* Bouncing Dots Loading Animation */
        .loading-bounce {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80px;
        }

        .bounce-dot {
            width: 16px;
            height: 16px;
            background-color: #3498db;
            border-radius: 50%;
            animation: bounce 1.2s infinite ease-in-out;
        }

        .bounce-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .bounce-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }


        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.5);
                opacity: 0.5;
            }
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .welcome-message h1 {
                font-size: 1.2rem;
            }

            .welcome-message p {
                font-size: 0.9rem;
            }

            .loading-dots {
                width: 50px;
            }

            .dot {
                width: 10px;
                height: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="welcome-container">
        <div class="welcome-message">
            <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p>We are redirecting you to your profile page...</p>
        </div>
        <div class="loading-screen">
            <div class="loading-bounce">
                <div class="bounce-dot"></div>
                <div class="bounce-dot"></div>
                <div class="bounce-dot"></div>
            </div>
        </div>

    </div>

    <script>
        setTimeout(function() {
            window.location.href = "profile.php";
        }, 3000);
    </script>
</body>

</html>