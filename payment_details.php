<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID
$payments = [];  // Store payments in this array

// Fetch payment details for the logged-in user
$sql = "SELECT * FROM payments WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;  // Add each payment record to the array
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ham.css">
    <style>
        /* Styling for the card layout */
        .payment-details-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 20px;
        }

        .payment-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: box-shadow 0.3s ease;
        }

        .payment-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .payment-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .payment-header i {
            color: #4CAF50;
        }

        .payment-body p {
            font-size: 16px;
            margin: 5px 0;
        }

        .payment-status.paid {
            color: #28a745;
        }

        .payment-status.pending {
            color: #fd7e14;
        }

        .payment-status.overdue {
            color: #dc3545;
        }

        .payment-status.partially-paid {
            color: #007bff;
        }

        @media screen and (max-width: 768px) {
            .payment-card {
                padding: 15px;
            }

            .payment-header {
                font-size: 1.1em;
            }

            .payment-body p {
                font-size: 14px;
            }
        }

        #active {
            color: red;
        }

        a {
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
            color: red;
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
                <li><a href="payment_details.php">
                        <i class="fas fa-money-check-alt" id="active"></i> Payment
                    </a>
                </li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; All tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; All homework</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; All notifications</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp; All progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; All activities</a></li>
                <li><a href="settings.php"><i class="fa fa-cog"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="container mt-5">
        <h2>Payment Details</h2>

        <?php if (!empty($payments)) : ?>
            <div class="payment-details-container">
                <?php foreach ($payments as $payment) : ?>
                    <div class="payment-card">
                        <div class="payment-header">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payment for: <?php echo htmlspecialchars($payment['course_name']); ?></span>
                        </div>
                        <div class="payment-body">
                            <p><strong>Total Amount:</strong> <?php echo htmlspecialchars($payment['total_amount']); ?></p>
                            <p><strong>Amount Paid:</strong> <?php echo htmlspecialchars($payment['amount_paid']); ?></p>
                            <p><strong>Remaining Amount:</strong> <?php echo htmlspecialchars($payment['total_amount'] - $payment['amount_paid']); ?></p>
                            <p class="payment-status <?php echo strtolower(str_replace(' ', '-', $payment['payment_status'])); ?>">
                                <strong>Status:</strong> <?php echo htmlspecialchars($payment['payment_status']); ?>
                            </p>
                            <p><strong>Month:</strong> <?php echo htmlspecialchars($payment['month']); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p>No payments found for your account.</p>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>

</html>