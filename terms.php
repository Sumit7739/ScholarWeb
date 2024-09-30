<?php
// Start session to get the logged-in user's data
session_start();

error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors on the page

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .terms-container {
            max-width: 800px;
            margin: auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            margin-top: 20px;
            color: #444;
        }

        p {
            margin-bottom: 15px;
        }

        ol {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="terms-container">
        <h1>Terms and Conditions</h1>

        <h2>1. Introduction</h2>
        <p>
            Welcome to our educational platform! By using this website, you agree to comply with and be bound by the following terms and conditions. These terms govern your use of the platform’s services and features designed for enrolled students. Please read these terms carefully.
        </p>

        <h2>2. Use of the Website</h2>
        <p>
            As a student using this platform, you are expected to use the site for educational purposes, including accessing class notes, assignments, schedules, and other academic resources provided by your instructor.
        </p>

        <h2>3. Features Provided</h2>
        <p>
            The platform offers several features to support your learning experience:
        </p>
        <ul>
            <li>Access to class notes, homework, and assignments.</li>
            <li>Submission of homework and tasks directly on the platform.</li>
            <li>Progress tracking for individual modules based on topics completed in class.</li>
            <li>A student feed for posting questions and interacting with peers and instructors.</li>
            <li>Notifications about upcoming classes, homework deadlines, and system updates.</li>
            <li>A personal dashboard displaying your schedule, progress, and any announcements.</li>
        </ul>

        <h2>4. Student Responsibilities</h2>
        <p>
            Students are expected to:
        </p>
        <ol>
            <li>Use the platform respectfully and avoid posting inappropriate content in the feed.</li>
            <li>Submit tasks and assignments by the specified deadlines.</li>
            <li>Regularly check the dashboard for updates on homework, tasks, and classes.</li>
            <li>Engage with the learning materials provided, and use the platform’s features to enhance their learning experience.</li>
            <li>Ensure that personal data and profile information are accurate and up to date.</li>
        </ol>

        <h2>5. Intellectual Property Rights</h2>
        <p>
            All content on this platform, including class materials, homework assignments, and user-generated posts, is owned by the instructors or the platform and is protected by intellectual property laws. You may not share, reproduce, or distribute content without permission.
        </p>

        <h2>6. Limitation of Liability</h2>
        <p>
            The platform and its administrators are not liable for any loss of data, missed deadlines, or other damages arising from the use of the website. It is your responsibility to ensure that your submissions and account information are maintained correctly.
        </p>

        <h2>7. Termination</h2>
        <p>
            We reserve the right to suspend or terminate student accounts that violate these terms or engage in misconduct on the platform.
        </p>

        <h2>8. Changes to Terms</h2>
        <p>
            These terms and conditions may be updated periodically. Any changes will be posted here, and continued use of the platform signifies acceptance of the new terms.
        </p>
        <br>
<hr>
        <p>Last updated: 30th September 2024.</p>
    </div>
</body>

</html>