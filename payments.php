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

$student_name = $total_amount = $amount_paid = $course_name = $remaining_amount = $user_id = $payment_status = $payment_method = $payment_note = '';

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

            $payment_sql = "SELECT course_name, total_amount, amount_paid, payment_status, payment_method, payment_note FROM payments WHERE user_id = ?";
            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param("i", $user_id);
            $payment_stmt->execute();
            $payment_result = $payment_stmt->get_result();

            if ($payment_result->num_rows > 0) {
                $payment_row = $payment_result->fetch_assoc();
                $course_name = htmlspecialchars($payment_row['course_name']);
                $total_amount = htmlspecialchars($payment_row['total_amount']);
                $amount_paid = htmlspecialchars($payment_row['amount_paid']);
                $payment_status = htmlspecialchars($payment_row['payment_status']);
                $payment_method = htmlspecialchars($payment_row['payment_method']);
                $payment_note = htmlspecialchars($payment_row['payment_note']);
                $remaining_amount = $total_amount - $amount_paid;
            }
        } else {
            echo "No user found with the given ID.";
        }

        $stmt->close();
        $payment_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $course_name = trim($_POST['course_name']);
    $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
    $amount_paid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    $payment_status = trim($_POST['payment_status']);
    $payment_method = trim($_POST['payment_method']);
    $payment_note = trim($_POST['payment_note']);

    if ($user_id) {
        // Check if a payment record exists for the given user_id
        $check_sql = "SELECT * FROM payments WHERE user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update the existing payment record
            $update_sql = "UPDATE payments
                           SET course_name = ?, total_amount = ?, amount_paid = ?, payment_status = ?, payment_method = ?, payment_note = ?, updated_at = NOW()
                           WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sddsssi", $course_name, $total_amount, $amount_paid, $payment_status, $payment_method, $payment_note, $user_id);

            if ($update_stmt->execute()) {
                echo "Payment record updated successfully!";
            } else {
                echo "Error updating payment: " . $conn->error;
            }

            $update_stmt->close();
        } else {
            // Insert a new payment record
            $insert_sql = "INSERT INTO payments (user_id, course_name, total_amount, amount_paid, payment_status, payment_method, payment_note, created_at, updated_at)
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isddsss", $user_id, $course_name, $total_amount, $amount_paid, $payment_status, $payment_method, $payment_note);

            if ($insert_stmt->execute()) {
                echo "Payment record inserted successfully!";
            } else {
                echo "Error inserting payment: " . $conn->error;
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
        header("Location: payments.php");
        exit();
    } else {
        echo "Error: User ID is required to update payment.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Payments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        function calculateRemaining() {
            const totalAmount = parseFloat(document.getElementById('totalAmount').value) || 0;
            const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
            const remainingAmount = totalAmount - amountPaid;
            document.getElementById('remainingAmount').value = remainingAmount.toFixed(2);
        }
    </script>
</head>

<body>
    <!-- <h1>You will be redirected in 3 seconds...</h1> -->
    <div class="container mt-5">
        <h2>Fetch Student Details</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="user_id">Student ID</label>
                <input type="number" class="form-control" id="user_id" name="user_id" required>
                <button type="submit" class="btn btn-info mt-2" name="fetch_user">Fetch User Details</button>
            </div>
        </form>

        <h2>Student Payment Management</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="mb-4">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <div class="form-group">
                <label for="student_name">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course_name); ?>" required>
            </div>

            <div class="form-group">
                <label for="totalAmount">Total Amount</label>
                <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" value="<?php echo htmlspecialchars($total_amount); ?>" required oninput="calculateRemaining()">
            </div>

            <div class="form-group">
                <label for="amountPaid">Amount Paid</label>
                <input type="number" class="form-control" id="amountPaid" name="amount_paid" step="0.01" value="<?php echo htmlspecialchars($amount_paid); ?>" oninput="calculateRemaining()">
            </div>

            <div class="form-group">
                <label for="remainingAmount">Remaining Amount</label>
                <input type="text" class="form-control" id="remainingAmount" name="remaining_amount" readonly value="<?php echo htmlspecialchars($remaining_amount); ?>">
            </div>

            <div class="form-group">
                <label for="paymentStatus">Payment Status</label>
                <select class="form-control" id="paymentStatus" name="payment_status">
                    <option value="Pending" <?php echo ($payment_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Partialy-Paid" <?php echo ($payment_status == 'Partialy-Paid') ? 'selected' : ''; ?>>Partialy-Paid</option>
                    <option value="Paid" <?php echo ($payment_status == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="Overdue" <?php echo ($payment_status == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                </select>
            </div>

            <div class="form-group">
                <label for="paymentMethod">Payment Method</label>
                <input type="text" class="form-control" id="paymentMethod" name="payment_method" value="<?php echo htmlspecialchars($payment_method); ?>" required>
            </div>

            <div class="form-group">
                <label for="paymentNote">Payment Note</label>
                <textarea class="form-control" id="paymentNote" name="payment_note"><?php echo htmlspecialchars($payment_note); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success" name="update_payment">Update Payment</button>
        </form>
    </div>
    <script>
        function redirectAfterDelay() {
            setTimeout(function() {
                window.location.href = "payments.php"; // Replace with your target URL
            }, 3000); // 3000 milliseconds = 3 seconds
        }

        // Attach the redirect function to the form submission
        function handleFormSubmit(event) {
            event.preventDefault(); // Prevent the default form submission
            // Perform form validation or any other tasks here if needed
            this.submit(); // Submit the form

            // Redirect after a delay
            redirectAfterDelay();
        }
    </script>
</body>

</html>