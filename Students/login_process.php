<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // update if different
$password = "";     // update if different
$dbname = "sms"; // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Get form inputs
$student_id = $_POST['student_id'];
$student_password = $_POST['password'];

// Protect inputs
$student_id = mysqli_real_escape_string($conn, $student_id);

// Fetch student record
$sql = "SELECT * FROM students WHERE student_id = '$student_id' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();

    // ✅ Verify hashed password
    if (password_verify($student_password, $student['password'])) {
        // Store student details in session
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['name'];
        $_SESSION['student_email'] = $student['email'];
        $_SESSION['class_id'] = $student['class_id'];

        // Redirect to student dashboard
        header("Location: student_dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid Password'); window.location.href='Student.php';</script>";
    }
} else {
    echo "<script>alert('Student ID not found'); window.location.href='Student.php';</script>";
}

$conn->close();
?>
