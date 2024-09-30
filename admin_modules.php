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
include 'db.php';

// Handle module creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_module'])) {
    $module_name = htmlspecialchars($_POST['module_name']);

    $stmt_create_module = $pdo->prepare("INSERT INTO modules (name, total_topics, progress) VALUES (:name, 0, 0)");
    $stmt_create_module->execute(['name' => $module_name]);

    // Log the activity
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type, additional_data) VALUES ('admin', 'Created module: $module_name', NOW(), 'create', '')");
    $stmt_log->execute();
}

// Function to update module progress
function updateModuleProgress($pdo, $module_id)
{
    // Count total topics
    $stmt_count = $pdo->prepare("SELECT COUNT(*) as total_topics, SUM(status = 'complete') as completed_topics FROM topics WHERE module_id = :module_id");
    $stmt_count->execute(['module_id' => $module_id]);
    $result = $stmt_count->fetch(PDO::FETCH_ASSOC);

    // Log the results for debugging
    error_log("Module ID: $module_id, Total Topics: " . $result['total_topics'] . ", Completed Topics: " . $result['completed_topics']);

    $total_topics = $result['total_topics'];
    $completed_topics = $result['completed_topics'];

    // Calculate progress
    $progress = $total_topics > 0 ? ($completed_topics / $total_topics) * 100 : 0;

    // Update module progress in the database
    $stmt_update_progress = $pdo->prepare("UPDATE modules SET total_topics = :total_topics, progress = :progress WHERE id = :module_id");
    $stmt_update_progress->execute(['total_topics' => $total_topics, 'progress' => $progress, 'module_id' => $module_id]);
}

// Handle topic addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_topic'])) {
    $module_id = htmlspecialchars($_POST['module_id']);
    $topic_name = htmlspecialchars($_POST['topic_name']);
    $status = htmlspecialchars($_POST['status']);

    $stmt_add_topic = $pdo->prepare("INSERT INTO topics (module_id, topic_name, status) VALUES (:module_id, :topic_name, :status)");
    $stmt_add_topic->execute(['module_id' => $module_id, 'topic_name' => $topic_name, 'status' => $status]);

    // Update module progress after adding the topic
    updateModuleProgress($pdo, $module_id);

    // Log the activity
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type, additional_data) VALUES ('admin', 'Added topic: $topic_name to module ID: $module_id', NOW(), 'create', '')");
    $stmt_log->execute();
}

// Handle topic deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_topic'])) {
    $topic_id = htmlspecialchars($_POST['topic_id']);

    // Fetch the module_id before deleting the topic
    $stmt_get_module_id = $pdo->prepare("SELECT module_id FROM topics WHERE id = :topic_id");
    $stmt_get_module_id->execute(['topic_id' => $topic_id]);
    $topic = $stmt_get_module_id->fetch(PDO::FETCH_ASSOC);

    if ($topic) {
        $module_id = $topic['module_id'];

        // Now proceed to delete the topic
        $stmt_delete_topic = $pdo->prepare("DELETE FROM topics WHERE id = :topic_id");
        $stmt_delete_topic->execute(['topic_id' => $topic_id]);

        // Update module progress after deleting the topic
        updateModuleProgress($pdo, $module_id);

        // Log the activity
        $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type, additional_data) VALUES ('admin', 'Deleted topic ID: $topic_id from module ID: $module_id', NOW(), 'delete', '')");
        $stmt_log->execute();
    } else {
        error_log("Topic ID $topic_id not found.");
    }
}

// Handle updating topic status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['topic_id']) && isset($_POST['status'])) {
    $topic_id = htmlspecialchars($_POST['topic_id']);
    $status = htmlspecialchars($_POST['status']);
    $module_id = htmlspecialchars($_POST['module_id']);

    $stmt_update_topic = $pdo->prepare("UPDATE topics SET status = :status WHERE id = :topic_id");
    $stmt_update_topic->execute(['status' => $status, 'topic_id' => $topic_id]);

    // Update module progress after updating the topic status
    updateModuleProgress($pdo, $module_id);

    // Log the activity
    $stmt_log = $pdo->prepare("INSERT INTO activity (name, activity_description, date, type, additional_data) VALUES ('admin', 'Updated status of topic ID: $topic_id to $status', NOW(), 'update', '')");
    $stmt_log->execute();
}

// Fetch existing modules
$stmt_modules = $pdo->query("SELECT * FROM modules");
$modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Module Management</title>
    <link rel="stylesheet" href="dashstyles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4 {
            color: #333;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: bold;
        }

        h1 {
            font-size: 32px;
        }

        h2 {
            font-size: 26px;
        }

        .module-container {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .module-container h3 {
            font-size: 24px;
            color: #0073e6;
        }

        .module-container p {
            font-size: 16px;
            color: #555;
        }

        .topic-container {
            background-color: #f9f9f9;
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #d1d1d1;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0073e6;
            color: #fff;
        }

        td select {
            padding: 5px;
            font-size: 14px;
        }

        button {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        button.delete-button {
            background-color: #dc3545;
        }

        button.delete-button:hover {
            background-color: #c82333;
        }

        form input[type="text"],
        form select {
            padding: 10px;
            font-size: 14px;
            width: 100%;
            max-width: 300px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        form input[type="text"]:focus,
        form select:focus {
            border-color: #0073e6;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 115, 230, 0.1);
        }

        button[type="submit"] {
            background-color: #0073e6;
        }

        button[type="submit"]:hover {
            background-color: #005bb5;
        }

        #modules {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <ul>
                <li> <a href="admin.php">Back to Admin Dashboard</a></li>
            </ul>
        </nav>
    </header>
    <h1>Module Management</h1>

    <!-- Module Creation Form -->
    <form method="POST">
        <input type="text" name="module_name" placeholder="Module Name" required>
        <button type="submit" name="create_module">Create Module</button>
    </form>

    <h2>Existing Modules</h2>
    <div id="modules">
        <?php
        // Fetching modules in descending order
        $stmt_modules = $pdo->query("SELECT * FROM modules ORDER BY id DESC"); // Make sure to add 'created_at' in your modules table
        $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

        foreach ($modules as $module): ?>
            <div class="module-container">
                <h3><?php echo htmlspecialchars($module['name']); ?></h3>
                <p><?php echo htmlspecialchars($module['created_at']); ?></p>
                <p>Total Topics: <?php echo htmlspecialchars($module['total_topics']); ?>, Progress:
                    <?php echo htmlspecialchars($module['progress']); ?>%</p>
                <button
                    onclick="document.getElementById('add-topic-<?php echo $module['id']; ?>').style.display='block'">Add
                    Topic</button>
                <div id="add-topic-<?php echo $module['id']; ?>" style="display:none;">
                    <form method="POST">
                        <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                        <input type="text" name="topic_name" placeholder="Topic Name" required>
                        <select name="status">
                            <option value="incomplete">Incomplete</option>
                            <option value="complete">Complete</option>
                        </select>
                        <button type="submit" name="add_topic">Add Topic</button>
                    </form>
                </div>

                <h4>Topics</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Topic Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch and display topics for the current module in descending order
                        $stmt_topics = $pdo->prepare("SELECT * FROM topics WHERE module_id = :module_id");
                        $stmt_topics->execute(['module_id' => $module['id']]);
                        $topics = $stmt_topics->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($topics as $topic): ?>
                            <tr class="topic-container">
                                <td><?php echo htmlspecialchars($topic['topic_name']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                        <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="incomplete" <?php echo $topic['status'] == 'incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                            <option value="complete" <?php echo $topic['status'] == 'complete' ? 'selected' : ''; ?>>Complete</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                        <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                        <button type="submit" name="delete_topic">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>