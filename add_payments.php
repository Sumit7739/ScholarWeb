<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Include your database connection
include('config.php');

// Initialize variables
$student_name = $total_amount = $amount_paid = $course_name = $payment_status = $payment_method = $payment_note = $month = '';
$user_id = null;
$payments = [];

// Fetch user details when searched by user_id
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($user_id) {
        $sql = "SELECT name FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $student_name = htmlspecialchars($row['name']);
        } else {
            echo "No user found with the given ID.";
        }

        $stmt->close();
    }
}

// Handle adding new payments
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $course_name = trim($_POST['course_name']);
    $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
    $amount_paid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    $remaining_amount = $total_amount - $amount_paid;

    $payment_status = trim($_POST['payment_status']);
    $payment_method = trim($_POST['payment_method']);
    $payment_note = trim($_POST['payment_note']);
    $month = trim($_POST['month']);

    if ($user_id) {
        // Insert new payment
        $insert_sql = "INSERT INTO payments (user_id, course_name, total_amount, amount_paid, payment_status, payment_method, payment_note, month, created_at, updated_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isddssss", $user_id, $course_name, $total_amount, $amount_paid, $payment_status, $payment_method, $payment_note, $month);

        if ($insert_stmt->execute()) {
            echo "Payment record inserted successfully!";
        } else {
            echo "Error inserting payment: " . $conn->error;
        }

        $insert_stmt->close();
    } else {
        echo "Error: User ID is required.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Payment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function calculateRemaining() {
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
            const remainingAmount = totalAmount - amountPaid;
            document.getElementById('remainingAmount').value = remainingAmount.toFixed(2);
        }
    </script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2,
        h4 {
            color: #007bff;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: block;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 90%;
            padding: 0.8rem;
            margin-top: 0.3rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        textarea {
            height: 100px;
        }

        button {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 0.8rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            background-color: #17a2b8;
            color: #fff;
            text-decoration: none;
        }

        button.btn-info {
            background-color: #17a2b8;
            color: white;
        }

        button.btn-info:hover {
            background-color: #138496;
        }

        button.btn-success {
            background-color: #28a745;
            color: white;
        }

        button.btn-success:hover {
            background-color: #218838;
        }

        button.btn-primary {
            background-color: #007bff;
            color: white;
        }

        button.btn-primary:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        table th,
        table td {
            padding: 0.75rem;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Fetch Student by ID</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="user_id">Student ID</label>
                <input type="number" class="form-control" id="user_id" name="user_id" required>
                <button type="submit" class="btn btn-info mt-2" name="fetch_user">Fetch User</button>
            </div>
        </form>

        <?php if (!empty($student_name)): ?>
            <h4>Adding Payment for: <?php echo $student_name; ?></h4>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" class="form-control" name="course_name" required>
                </div>

                <div class="form-group">
                    <label for="totalAmount">Total Amount</label>
                    <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" value="<?php echo htmlspecialchars($payment['total_amount']); ?>" required oninput="calculateRemaining()">
                </div>

                <div class="form-group">
                    <label for="amountPaid">Amount Paid</label>
                    <input type="number" class="form-control" id="amountPaid" name="amount_paid" step="0.01" value="<?php echo htmlspecialchars($payment['amount_paid']); ?>" required oninput="calculateRemaining()">
                </div>

                <div class="form-group">
                    <label for="remainingAmount">Remaining Amount</label>
                    <input type="text" class="form-control" id="remainingAmount" name="remaining_amount" readonly>
                </div>

                <div class="form-group">
                    <label for="month">Month</label>
                    <input type="text" class="form-control" name="month" required>
                </div>

                <div class="form-group">
                    <label for="paymentStatus">Payment Status</label>
                    <select class="form-control" name="payment_status">
                        <option value="Pending">Pending</option>
                        <option value="Partially-Paid">Partially-Paid</option>
                        <option value="Paid">Paid</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <input type="text" class="form-control" name="payment_method" required>
                </div>

                <div class="form-group">
                    <label for="paymentNote">Payment Note</label>
                    <textarea class="form-control" name="payment_note"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" name="add_payment">Add Payment</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>