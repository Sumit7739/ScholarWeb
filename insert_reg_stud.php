<?php
// Database connection
require 'config.php';

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $college_name = mysqli_real_escape_string($conn, $_POST['college_name']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $year = (int)$_POST['year'];
    $admission = (int)$_POST['admission'];
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);

    // Insert data into reg_stud table
    $query = "INSERT INTO reg_stud (name, email, college_name, semester, year, admission, father_name)
              VALUES ('$name', '$email', '$college_name', '$semester', $year, $admission, '$father_name')";

    if (mysqli_query($conn, $query)) {
        echo "Student data inserted successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Insert Student Data</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <style>
        /* Form Container */
        .form-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-container h1 {
            font-size: 24px;
            font-weight: 400;
            margin-bottom: 20px;
            color: #333;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4285f4;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            border-radius: 50px;
            color: white;
            background-color: #34a853;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #2c8f47;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Insert Student Data</h1>
        <form action="insert_reg_stud.php" method="POST">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="college_name">College Name</label>
                <input type="text" id="college_name" name="college_name" required>
            </div>
            <div class="input-group">
                <label for="semester">Semester</label>
                <input type="text" id="semester" name="semester" required>
            </div>
            <div class="input-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" required>
            </div>
            <div class="input-group">
                <label for="admission">Admission (1 for Yes, 0 for No)</label>
                <input type="number" id="admission" name="admission" min="0" max="1" required>
            </div>
            <div class="input-group">
                <label for="father_name">Father's Name</label>
                <input type="text" id="father_name" name="father_name" required>
            </div>
            <button type="submit" class="btn btn-submit">Submit</button>
        </form>
    </div>
</body>

</html>