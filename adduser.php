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
include 'db.php'; // Include your database connection file

// Fetch users
$stmt_users = $pdo->prepare("SELECT id, name, email, college_name, semester, year, admission, father_name FROM reg_stud");
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a new user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $college_name = htmlspecialchars($_POST['college_name']);
    $semester = htmlspecialchars($_POST['semester']);
    $year = (int)$_POST['year'];
    $admission = (int)$_POST['admission'];
    $father_name = htmlspecialchars($_POST['father_name']);


    $stmt_add = $pdo->prepare("INSERT INTO reg_stud (name, email, college_name, semester, year, admission, father_name) VALUES (:name, :email, :college_name, :semester, :year, :admission, :father_name)");
    $stmt_add->execute([
        'name' => $name,
        'email' => $email,
        'college_name' => $college_name,
        'semester' => $semester,
        'year' => $year,
        'admission' => $admission,
        'father_name' => $father_name
    ]);
    header("Location: adduser.php"); // Redirect to avoid resubmission
}

// Handle editing a user
if (isset($_GET['edit_id'])) {
    $stmt_edit = $pdo->prepare("SELECT id, name, email, college_name, semester, year, admission, father_name FROM reg_stud WHERE id = :id");
    $stmt_edit->execute(['id' => $_GET['edit_id']]);
    $edit_user = $stmt_edit->fetch();
}

// Handle updating a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $college_name = htmlspecialchars($_POST['college_name']);
    $semester = htmlspecialchars($_POST['semester']);
    $year = (int)$_POST['year'];
    $admission = (int)$_POST['admission'];
    $father_name = htmlspecialchars($_POST['father_name']);

    $stmt_update = $pdo->prepare("UPDATE reg_stud SET name = :name, email = :email, college_name = :college_name, semester = :semester, year = :year, admission = :admission, father_name = :father_name WHERE id = :id");
    $stmt_update->execute([
        'name' => $name,
        'email' => $email,
        'college_name' => $college_name,
        'semester' => $semester,
        'year' => $year,
        'id' => $user_id,
        'admission' => $admission,
        'father_name' => $father_name
    ]);
    header("Location: adduser.php"); // Redirect to avoid resubmission
}

// Handle deleting a user
if (isset($_GET['delete_id'])) {
    $stmt_delete = $pdo->prepare("DELETE FROM reg_stud WHERE id = :id");
    $stmt_delete->execute(['id' => $_GET['delete_id']]);
    header("Location: adduser.php"); // Redirect to avoid resubmission
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Management</title>
    <!-- <link rel="stylesheet" href="adminstyles.css"> -->
    <style>
        .admin-content {
            width: 95%;
            /* max-width: 1000px; */
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .admin-content h1 {
            font-size: 24px;
            font-weight: 400;
            margin-bottom: 20px;
            color: #333;
        }

        .admin-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .admin-content th,
        .admin-content td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .admin-content th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .admin-content a {
            color: #4285f4;
            text-decoration: none;
        }

        .admin-content a:hover {
            text-decoration: underline;
        }

        .admin-content form {
            margin-top: 20px;
        }

        .admin-content input[type="text"],
        .admin-content input[type="email"],
        .admin-content input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .admin-content button[type="submit"] {
            background-color: #4285f4;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .admin-content button[type="submit"]:hover {
            background-color: #34a853;
        }

        .admin-content .error {
            color: red;
            margin-bottom: 10px;
        }

        .admin-content .success {
            color: green;
            margin-bottom: 10px;
        }

        /* Header */
        header {
            background-color: #4285f4;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        header .logo {
            font-size: 24px;
            font-weight: bold;
        }

        header .menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: inline-block;
        }

        header .menu li {
            display: inline-block;
            margin: 0 10px;
        }

        header .menu a {
            color: white;
            text-decoration: none;
        }

        header .menu a:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background-color: #f2f2f2;
            padding: 10px 0;
            text-align: center;
        }

        footer p {
            margin: 0;
        }

        footer .footer-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: inline-block;
        }

        footer .footer-links li {
            display: inline-block;
            margin: 0 10px;
        }

        footer .footer-links a {
            color: #333;
            text-decoration: none;
        }

        footer .footer-links a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header .menu {
                display: block;
                text-align: center;
            }

            header .menu li {
                display: block;
                margin: 5px 0;
            }
        }

        @media (max-width: 480px) {
            header .logo {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <div class="logo">User Management</div>
            <ul class="menu">
                <!-- <li><a href="profile.php">User Dashboard</a></li> -->
                <!-- <li><a href="logout.php">Logout</a></li> -->
                <li><a href="addschedules.php">Class Schedules</a></li>
            </ul>
        </nav>
    </header>

    <section class="admin-content">
        <h1>User Management</h1>

        <!-- Add User Form -->
        <h2>Add New User</h2>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="college_name" placeholder="College Name" required>
            <input type="text" name="semester" placeholder="Semester" required>
            <input type="number" name="year" placeholder="Year" required>
            <input type="number" name="admission" placeholder="Admission (1 for Yes, 0 for No)" required>
            <input type="text" name="father_name" placeholder="Father's Name" required>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <!-- User List -->
        <h2>User List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>College Name</th>
                    <th>Semester</th>
                    <th>Year</th>
                    <th>Admission</th>
                    <th>Father's Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['college_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['semester']); ?></td>
                        <td><?php echo htmlspecialchars($user['year']); ?></td>
                        <td><?php echo htmlspecialchars($user['admission']); ?></td>
                        <td><?php echo htmlspecialchars($user['father_name']); ?></td>
                        <td>
                            <a href="adduser.php?edit_id=<?php echo $user['id']; ?>">Edit</a> |
                            <a href="adduser.php?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit User Form -->

        <?php if (isset($edit_user)): ?>
            <h2>Edit User</h2>
            <form method="POST" action="">
                
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                <input type="text" name="college_name" value="<?php echo htmlspecialchars($edit_user['college_name']); ?>" required>
                <input type="text" name="semester" value="<?php echo htmlspecialchars($edit_user['semester']); ?>" required>
                <input type="number" name="year" value="<?php echo htmlspecialchars($edit_user['year']); ?>" required>
                <input type="number" name="admission" value="<?php echo htmlspecialchars($edit_user['admission']); ?>" required>
                <input type="text" name="father_name" value="<?php echo htmlspecialchars($edit_user['father_name']); ?>" required>
                <button type="submit" name="update_user">Update User</button>
            </form>
        <?php endif; ?>

    </section>
</body>

</html>