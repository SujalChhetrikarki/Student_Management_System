<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // change if needed
$password = "";     // change if needed
$dbname = "sms"; // update if different

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Get form inputs
$teacher_id = $_POST['teacher_id'];
$teacher_password = $_POST['password'];

// Protect input
$teacher_id = mysqli_real_escape_string($conn, $teacher_id);

// Fetch teacher record
$sql = "SELECT * FROM teachers WHERE teacher_id = '$teacher_id' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();

    // ✅ Verify hashed password
    if (password_verify($teacher_password, $teacher['password'])) {
        // Save teacher info in session
        $_SESSION['teacher_id'] = $teacher['teacher_id'];
        $_SESSION['teacher_name'] = $teacher['name'];
        $_SESSION['teacher_email'] = $teacher['email'];
        $_SESSION['specialization'] = $teacher['specialization'];

        // Redirect to teacher dashboard
        header("Location: teacher_dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid Password'); window.location.href='teacher.php';</script>";
    }
} else {
    echo "<script>alert('Teacher ID not found'); window.location.href='teacher.php';</script>";
}

$conn->close();
?>
