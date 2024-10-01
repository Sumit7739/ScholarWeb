<?php
session_start();
include 'config.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Initialize variables for user data
$user = [];

// Fetch user data from the user table
try {
    $userStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->bind_param("i", $userId); // Bind the user ID
    $userStmt->execute();
    $result = $userStmt->get_result();
    $user = $result->fetch_assoc();
} catch (mysqli_sql_exception $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission for updating user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request, using existing user data if not set
    $name = isset($_POST['name']) ? $_POST['name'] : $user['name'];
    $email = isset($_POST['email']) ? $_POST['email'] : $user['email'];
    $phone = isset($_POST['phone']) ? $_POST['phone'] : $user['phone'];
    $semester = isset($_POST['semester']) ? $_POST['semester'] : $user['semester'];
    $college_name = isset($_POST['college_name']) ? $_POST['college_name'] : $user['college_name'];
    $year = isset($_POST['year']) ? $_POST['year'] : $user['year'];
    $password = isset($_POST['password']) ? trim($_POST['password']) : ''; // Trim whitespace

    // Prepare the SQL update statement
    try {
        $updateStmt = $conn->prepare("UPDATE users SET 
            name = ?, 
            email = ?, 
            phone = ?, 
            semester = ?, 
            college_name = ?, 
            year = ?" . ($password ? ", password = ?" : "") . " 
            WHERE id = ?");

        // Bind parameters
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt->bind_param("ssssssi", $name, $email, $phone, $semester, $college_name, $year, $hashedPassword, $userId);
        } else {
            $updateStmt->bind_param("ssssssi", $name, $email, $phone, $semester, $college_name, $year, $userId);
        }

        // Execute the update
        $updateStmt->execute();

        // Success message
        $success = "Settings updated successfully.";
    } catch (mysqli_sql_exception $e) {
        // Handle error
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Import data from reg_stud table
if (isset($_POST['import_data'])) {
    // Import data logic
    try {
        $importStmt = $conn->prepare("SELECT * FROM reg_stud WHERE email = ?");
        $importStmt->bind_param("s", $user['email']);
        $importStmt->execute();
        $result = $importStmt->get_result();
        $regData = $result->fetch_assoc();

        if ($regData) {
            $updateImportStmt = $conn->prepare("UPDATE users SET 
                college_name = ?, 
                semester = ?, 
                year = ? 
                WHERE email = ?");

            $updateImportStmt->bind_param("ssss", $regData['college_name'], $regData['semester'], $regData['year'], $user['email']);
            $updateImportStmt->execute();

            // Success message
            $success = "Data imported successfully from registration.";
        } else {
            $error = "No registration data found for this email.";
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Error importing data: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4caf50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Settings</h2>

        <?php if (isset($error)): ?>
            <p class="message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <label for="phone">Phone No:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

            <label for="semester">Semester:</label>
            <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($user['semester']); ?>">

            <label for="college_name">College Name:</label>
            <input type="text" id="college_name" name="college_name" value="<?php echo htmlspecialchars($user['college_name']); ?>">

            <label for="year">Year:</label>
            <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($user['year']); ?>">

            <label for="password">Change Password:</label>
            <input type="password" id="password" name="password">

            <button type="submit">Update Settings</button>
        </form>

        <form method="post" action="" style="margin-top: 20px;">
            <button type="submit" name="import_data">Import Data from Registration</button>
        </form>
    </div>
</body>

</html>