<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$class_id = $_GET['class_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

if (!$class_id) {
    die("Invalid request: Missing class ID.");
}

// ✅ Fetch students in this class
$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE class_id = ?");
$stmt->bind_param("s", $class_id);
$stmt->execute();
$students = $stmt->get_result();

// ✅ Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ensure table has unique record per student per date
    $conn->query("
        ALTER TABLE attendance 
        ADD UNIQUE KEY IF NOT EXISTS unique_attendance (student_id, class_id, date)
    ");

    foreach ($_POST['attendance'] as $student_id => $status) {
        // Insert or update automatically
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, class_id, date, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");
        $stmt->bind_param("ssss", $student_id, $class_id, $date, $status);
        $stmt->execute();
    }

    $msg = "✅ Attendance updated successfully!";
}

// ✅ Fetch attendance for display
$stmt2 = $conn->prepare("
    SELECT s.student_id, s.name, COALESCE(a.status, 'Absent') AS status
    FROM students s
    LEFT JOIN attendance a 
        ON s.student_id = a.student_id 
        AND a.class_id = ? 
        AND a.date = ?
    WHERE s.class_id = ?
    GROUP BY s.student_id
");
$stmt2->bind_param("sss", $class_id, $date, $class_id);
$stmt2->execute();
$attendance = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Attendance</title>
<style>
body {
    font-family: "Segoe UI", Arial;
    background: #f9fafc;
    margin: 0;
    padding: 30px;
}
h2 {
    color: #007bff;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}
th {
    background: #007bff;
    color: #fff;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
.btn {
    padding: 8px 14px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    margin-right: 10px;
}
.btn:hover {
    background: #0056b3;
}
.success {
    color: green;
    font-weight: bold;
}
.date-form {
    margin-bottom: 10px;
}
</style>
</head>
<body>

<h2>🗓 Manage Attendance</h2>

<form method="GET" class="date-form">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
    <label><strong>Select Date:</strong></label>
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    <button type="submit" class="btn">Go</button>
</form>

<?php if (!empty($msg)) echo "<p class='success'>$msg</p>"; ?>

<form method="POST">
<table>
<tr>
    <th>Student Name</th>
    <th>Present</th>
    <th>Absent</th>
</tr>
<?php while ($row = $attendance->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><input type="radio" name="attendance[<?= $row['student_id'] ?>]" value="Present" <?= $row['status'] == 'Present' ? 'checked' : '' ?>></td>
    <td><input type="radio" name="attendance[<?= $row['student_id'] ?>]" value="Absent" <?= $row['status'] == 'Absent' ? 'checked' : '' ?>></td>
</tr>
<?php endwhile; ?>
</table>
<br>
<button type="submit" class="btn">Save Attendance</button>
<a href="teacher_dashboard.php" class="btn">⬅ Back</a>
</form>

</body>
</html>
