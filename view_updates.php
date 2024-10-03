<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Fetch updates from the database
$query = "SELECT * FROM updates ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Check for errors in the query
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Updates</title>
    <style>
        /* General Container Styles */
        .container {
            width: 90%;
            /* max-width: 800px; */
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Updates Title */
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Updates Card Container */
        .updates-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            /* Space between cards */
        }

        /* Individual Update Card Styles */
        .update-card {
            background-color: #ffffff;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        /* Update Card Hover Effect */
        .update-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Update Card Title */
        .update-card h3 {
            margin: 0;
            color: #007bff;
        }

        /* Update Date Styles */
        .update-date {
            font-size: 0.9em;
            color: #888;
        }

        /* Update Description Styles */
        .update-description {
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Updates</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="updates-container">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="update-card">
                        <h2><?php echo htmlspecialchars($row['version']); ?></h2>
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="update-date"><strong>Date:</strong> <?php echo htmlspecialchars($row['update_date']); ?></p>
                        <p class="update-description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No updates available.</p>
        <?php endif; ?>

    </div>
</body>

</html>

<?php
mysqli_close($conn); // Close the database connection
?>