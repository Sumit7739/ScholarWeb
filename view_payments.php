<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Include your database connection
include('config.php');

$payments = []; // Initialize an array to store payment details
$user_id = ''; // Initialize user_id

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_payments'])) {
    // Fetch payments based on user_id
    $user_id = $_POST['user_id'];

    // Check if user_id is not empty
    if (!empty($user_id)) {
        // SQL query to fetch payment details and user name
        $payment_sql = "
            SELECT 
                u.name AS user_name, 
                p.course_name, 
                p.total_amount, 
                p.amount_paid, 
                p.payment_status, 
                p.payment_method, 
                p.payment_note, 
                p.updated_at 
            FROM 
                payments p
            JOIN 
                users u ON p.user_id = u.id 
            WHERE 
                p.user_id = ?
        ";
        $payment_stmt = $conn->prepare($payment_sql);
        $payment_stmt->bind_param("i", $user_id);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();

        // Fetch payment details into an array
        while ($row = $payment_result->fetch_assoc()) {
            $payments[] = $row;
        }

        // Close statement
        $payment_stmt->close();
    } else {
        echo "Please enter a valid User ID.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Payments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* General body styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            /* Light gray background */
            color: #333;
            /* Dark text color */
        }

        .container{
            width: 95%;
            /* max-width: 600px; */
            margin: 0 auto;
            padding: 20px;
            background-color: #f2f2f2;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Table container */
        .table-container {
            margin-top: 20px;
            /* Spacing above the table */
        }

        /* Table styles */
        .table {
            width: 100%;
            /* Full width */
            margin-bottom: 1rem;
            /* Spacing below the table */
            color: #212529;
            /* Text color */
            border-collapse: collapse;
            /* Collapse borders */
        }

        /* Table header styles */
        .table thead th {
            background-color: #007bff;
            /* Bootstrap primary color */
            color: white;
            /* White text for headers */
            text-align: left;
            /* Align text to the left */
            padding: 12px;
            /* Padding around cells */
        }

        /* Table body styles */
        .table tbody tr {
            background-color: white;
            /* White background for rows */
            border: 1px solid #dee2e6;
            /* Light gray border */
        }

        /* Row hover effect */
        .table tbody tr:hover {
            background-color: #f1f1f1;
            /* Light gray on hover */
        }

        /* Table cell styles */
        .table td {
            padding: 12px;
            /* Padding around cells */
            border: 1px solid #dee2e6;
            /* Light gray border */
        }

        /* Media queries for responsiveness */
        @media (max-width: 768px) {
            .table {
                font-size: 14px;
                /* Smaller text on smaller screens */
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>View Student Payment Details</h2>
        <form action="" method="POST" class="mb-4">
            <div class="form-group">
                <label for="user_id">User ID</label>
                <input type="number" class="form-control" id="user_id" name="user_id" required>
            </div>
            <button type="submit" class="btn btn-info" name="fetch_payments">Fetch Payment Details</button>
        </form>

        <?php if (!empty($payments)): ?>
            <h3>Payment Records for User ID: <?php echo htmlspecialchars($user_id); ?></h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Course Name</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Payment Note</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($payment['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['amount_paid']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_note']); ?></td>
                            <td><?php echo htmlspecialchars($payment['updated_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p>No payment records found for this User ID.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>