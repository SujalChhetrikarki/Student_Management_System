<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

// Check DB connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Prepare student data query
$sql = "SELECT s.student_id, s.name, s.email, s.date_of_birth, s.gender, c.class_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE s.student_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>
<body>
    <div class="header">
        <h1>🎓 Welcome, <?php echo htmlspecialchars($student['name']); ?></h1>
    </div>

    <div class="container">
        <div class="card">
            <h2>📌 Student Information</h2>
            <table>
                <tr><th>Student ID</th><td><?php echo htmlspecialchars($student['student_id']); ?></td></tr>
                <tr><th>Name</th><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
                <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['date_of_birth']); ?></td></tr>
                <tr><th>Gender</th><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
                <tr><th>Class</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
            </table>
        </div>

        <div class="actions">
            <a href="attendance.php">📅 View Attendance</a>
            <a href="results.php">📊 View Results</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </div>
    </div>
</body>
</html>
