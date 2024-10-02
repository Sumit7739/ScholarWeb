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
    $userStmt->bind_param("i", $userId); // "i" means integer
    $userStmt->execute();
    $result = $userStmt->get_result();
    $user = $result->fetch_assoc(); // Fetch the user data as an associative array
} catch (mysqli_sql_exception $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission for updating user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store previous values for comparison
    $previousValues = [
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'semester' => $user['semester'],
        'college_name' => $user['college_name'],
        'year' => $user['year'],
    ];

    // Check if the keys exist in the $_POST array
    $name = $_POST['name'] ?? $previousValues['name'];
    $email = $_POST['email'] ?? $previousValues['email'];
    $phone = $_POST['phone'] ?? $previousValues['phone'];
    $semester = $_POST['semester'] ?? $previousValues['semester'];
    $college_name = $_POST['college_name'] ?? $previousValues['college_name'];
    $year = $_POST['year'] ?? $previousValues['year'];
    $password = $_POST['password'] ?? '';

    // Prepare the SQL update statement
    try {
        $updateQuery = "UPDATE users SET name = ?, email = ?, phone = ?, semester = ?, college_name = ?, year = ?" . ($password ? ", password = ?" : "") . " WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);

        // Bind parameters
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt->bind_param("sssssisi", $name, $email, $phone, $semester, $college_name, $year, $hashedPassword, $userId); // Last parameter is the user ID
        } else {
            $updateStmt->bind_param("ssssssi", $name, $email, $phone, $semester, $college_name, $year, $userId);
        }

        // Execute the update
        $updateStmt->execute();

        // Prepare the activity log description
        $changedFields = [];
        foreach ($previousValues as $key => $value) {
            if ($$key !== $value) {
                $changedFields[] = ucfirst(str_replace('_', ' ', $key)); // Format the field name
            }
        }
        $changedFieldsStr = implode(', ', $changedFields) ?: 'No changes made';

        // Log the activity for updating settings
        $activityStmt = $conn->prepare("INSERT INTO activity (name, activity_description, date, type) VALUES (?, ?, NOW(), 'update')");
        $activityDescription = "Updated settings for " . $changedFieldsStr;
        $activityStmt->bind_param("ss", $user['name'], $activityDescription);
        $activityStmt->execute();

        // Success message
        $success = "Settings updated successfully.";
        header("Location: settings.php");
        exit;
    } catch (mysqli_sql_exception $e) {
        // Handle error
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Close statements
$userStmt->close();
if (isset($updateStmt)) {
    $updateStmt->close();
}
if (isset($activityStmt)) {
    $activityStmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="ham.css">
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

        button a {
            text-decoration: none;
            color: white;
        }

        .message {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
        }

        #active {
            color: red;
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
                <li><a href="feed.php"><i class="fa-solid fa-newspaper"></i>&nbsp; Feed</a></li>
                <li><a href="all_tasks.php"><i class="fa fa-tasks"></i>&nbsp; Tasks</a></li>
                <li><a href="all_homework.php"><i class="fa fa-book"></i>&nbsp; Homework</a></li>
                <li><a href="all_progress.php"><i class="fa fa-chart-line"></i>&nbsp;Progress</a></li>
                <li><a href="all_activities.php"><i class="fa fa-clock"></i>&nbsp; Activities</a></li>
                <li><a href="all_notifications.php"><i class="fa fa-bell"></i>&nbsp; Notifications</a></li>
                <li><a href="settings.php"><i class="fa fa-cog" id="active"></i>&nbsp; Settings</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out"></i>&nbsp; Logout</a></li>
            </ul>
        </nav>
    </header>
    <br>
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
            <input type="text" id="name" name="name" value="<?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>

            <label for="phone">Phone No:</label>
            <input type="text" id="phone" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">

            <label for="semester">Semester:</label>
            <input type="text" id="semester" name="semester" value="<?php echo isset($user['semester']) ? htmlspecialchars($user['semester']) : ''; ?>">

            <label for="college_name">College Name:</label>
            <input type="text" id="college_name" name="college_name" value="<?php echo isset($user['college_name']) ? htmlspecialchars($user['college_name']) : ''; ?>">

            <label for="year">Year:</label>
            <input type="text" id="year" name="year" value="<?php echo isset($user['year']) ? htmlspecialchars($user['year']) : ''; ?>">

            <label for="password">Change Password:</label>
            <input type="password" id="password" name="password" maxlength="8">

            <button type="submit">Update Settings</button>
            <br><br>
        </form>
        <button><a href="upload_profile_pic.php">Upload Profile Picture</a></button>

        <form method="post" action="import_data.php" style="margin-top: 20px;">
            <button type="submit" name="import_data">Import Data from Registration</button>
        </form>
    </div>


    <script src="script.js"></script>
</body>

</html>