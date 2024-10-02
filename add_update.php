<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

// Database connection
include 'config.php'; // Include your database connection file
// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $version = $_POST['version'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $update_date = $_POST['update_date'];

    // Validate inputs
    if (!empty($version) && !empty($title) && !empty($description) && !empty($update_date)) {
        // Prepare the SQL query to insert the update into the database
        $stmt = $conn->prepare("INSERT INTO updates (version, title, description, update_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $version, $title, $description, $update_date);

        // Execute the query
        if ($stmt->execute()) {
            $success_message = "Update added successfully!";
        } else {
            $error_message = "Error adding the update: " . $conn->error;
        }

        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Update</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        h1 {
            text-align: center;
            color: #333;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            /* Allow vertical resizing of textarea */
        }

        /* Button Styles */
        .button-17 {
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .button-17:hover {
            background-color: #4cae4c;
            /* Darker green on hover */
        }

        /* Message Styles */
        .success-message {
            color: #5cb85c;
            margin-bottom: 15px;
            text-align: center;
        }

        .error-message {
            color: #d9534f;
            /* Red color for error messages */
            margin-bottom: 15px;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Add New Update</h1>

        <?php if (isset($success_message)) { ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php } ?>

        <?php if (isset($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <form action="add_update.php" method="POST">
            <label for="version">Version:</label>
            <input type="text" id="version" name="version" required>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="5" required></textarea>

            <label for="update_date">Update Date:</label>
            <input type="date" id="update_date" name="update_date" required>

            <button type="submit" class="button-17">Add Update</button>
        </form>
    </div>
</body>

</html>