<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt_student = $conn->prepare("SELECT s.name, c.class_name FROM students s JOIN classes c ON s.class_id=c.class_id WHERE s.student_id=?");
$stmt_student->bind_param("s", $student_id);
$stmt_student->execute();
$student = $stmt_student->get_result()->fetch_assoc();
if (!$student) die("Student not found.");

// Fetch attendance records
$sql_attendance = "SELECT a.date, sub.subject_name, a.status
                   FROM attendance a
                   JOIN subjects sub ON a.subject_id=sub.subject_id
                   WHERE a.student_id=?
                   ORDER BY a.date DESC";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("s", $student_id);
$stmt_attendance->execute();
$attendance = $stmt_attendance->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Attendance</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin:0; padding:0;}
.container { width: 90%; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
h2 { text-align: center; color: #333; margin-bottom: 10px;}
table { width: 100%; border-collapse: collapse; margin-top: 20px;}
th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
th { background: #4CAF50; color: white; }
tr:nth-child(even) { background: #f9f9f9; }
</style>
</head>
<body>
<div class="container">
<h2>📋 My Attendance Records</h2>
<p><strong>Student:</strong> <?= htmlspecialchars($student['name']); ?> | <strong>Class:</strong> <?= htmlspecialchars($student['class_name']); ?></p>

<table>
<tr>
<th>Date</th>
<th>Status</th>
</tr>
<?php if($attendance->num_rows == 0): ?>
<tr><td colspan="3">No attendance records available yet.</td></tr>
<?php else: ?>
<?php while ($a = $attendance->fetch_assoc()): ?>
<tr>
<td><?= $a['date']; ?></td>
<td><?= $a['status']; ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</table>

<p style="text-align:center;"><a href="student_dashboard.php">⬅ Back to Dashboard</a></p>
</div>
</body>
</html>
