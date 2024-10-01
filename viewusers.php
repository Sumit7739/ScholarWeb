<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_id'])) {
    header("Location: adlogin.php");
    exit;
}

include 'config.php'; // Assuming database connection


// Create connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch all users from the users table
$sql = "SELECT id, name, email, college_name, semester, created_at FROM users";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View All Users</title>
    <style>
        /* Add your enhanced CSS here for a better UI */
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

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e6e6e6;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 14px;
            color: #555;
        }

        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:nth-child(odd) {
            background-color: #fff;
        }

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
    </style>
</head>

<body>

    <h1>All Registered Users</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>College</th>
            <th>Semester</th>
            <th>Registration Date</th>
        </tr>

        <?php
        // Step 3: Display data in table format
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . $row["id"] . "</td>
                    <td>" . $row["name"] . "</td>
                    <td>" . $row["email"] . "</td>
                    <td>" . $row["college_name"] . "</td>
                    <td>" . $row["semester"] . "</td>
                    <td>" . $row["created_at"] . "</td>
                  </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No users found.</td></tr>";
        }
        ?>

    </table>

</body>

</html>

<?php
// Close the connection
$conn->close();
?>