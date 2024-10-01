<?php
session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);


if (!isset($_SESSION["admin_id"])) {
    header("Location: adlogin.php");
    exit;
}

include 'config.php';

$conn = new mysqli($host, $username, $password, $database);

/// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch all user homework submissions
$sql = "SELECT uh.id, u.name, uh.task_no, uh.task_name, uh.description, uh.url, uh.created_at 
        FROM user_homework uh
        JOIN users u ON uh.user_id = u.id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View User Homework</title>
    <style>
        /* Global styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        /* Container for the table */
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Table header styling */
        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e6e6e6;
        }

        /* Table body styling */
        td {
            padding: 12px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 14px;
            color: #555;
        }

        /* Hover effect for table rows */
        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* Links for submission URLs */
        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive table styling for mobile */
        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            th {
                display: none;
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 10px;
                border-bottom: 1px solid #ddd;
                font-size: 14px;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                width: 120px;
                text-transform: uppercase;
                color: #333;
            }
        }

        /* Additional styling for the table and page */
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:nth-child(odd) {
            background-color: #fff;
        }

        /* Optional button styling for admin actions */
        button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            margin: 20px;
            cursor: pointer;
            border-radius: 5px;
            display: block;
            margin: 30px auto;
            font-size: 14px;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <h1>All User Homework Submissions</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>User Name</th>
            <th>Task No</th>
            <th>Task Name</th>
            <th>Description</th>
            <th>Submission URL</th>
            <th>Submission Date</th>
        </tr>

        <?php
        // Step 3: Display data in table format
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . $row["id"] . "</td>
                    <td>" . $row["name"] . "</td>
                    <td>" . $row["task_no"] . "</td>
                    <td>" . $row["task_name"] . "</td>
                    <td>" . $row["description"] . "</td>
                    <td><a href='" . $row["url"] . "' target='_blank'>View Homework</a></td>
                    <td>" . $row["created_at"] . "</td>
                  </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No homework submissions found.</td></tr>";
        }
        ?>

    </table>

</body>

</html>

<?php
// Close the connection
$conn->close();
?>