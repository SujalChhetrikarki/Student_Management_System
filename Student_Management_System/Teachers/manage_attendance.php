<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];

// Get subject_id and class_id from GET
$subject_id = $_GET['subject_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d'); // default today

if (!$subject_id || !$class_id) {
    die("Invalid request.");
}

// Fetch students
$stmt_students = $conn->prepare("SELECT student_id, name FROM students WHERE class_id = ?");
$stmt_students->bind_param("s", $class_id); // "s" for string IDs
$stmt_students->execute();
$students = $stmt_students->get_result()->fetch_all(MYSQLI_ASSOC);

// Loop to create attendance if not exists
foreach ($students as $student) {
    $stmt_att = $conn->prepare("SELECT attendance_id FROM attendance WHERE student_id=? AND class_id=? AND subject_id=? AND date=?");
    $stmt_att->bind_param("ssss", $student['student_id'], $class_id, $subject_id, $date);
    $stmt_att->execute();
    $res_att = $stmt_att->get_result();

    if ($res_att->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO attendance (student_id, class_id, subject_id, date, status) VALUES (?, ?, ?, ?, 'Absent')");
        $stmt_insert->bind_param("ssss", $student['student_id'], $class_id, $subject_id, $date);
        $stmt_insert->execute();
    }
}

// ================================
// 2. Handle form submission
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['attendance'] as $attendance_id => $status) {
        $stmt_update = $conn->prepare("UPDATE attendance SET status = ? WHERE attendance_id = ?");
        $stmt_update->bind_param("si", $status, $attendance_id);
        $stmt_update->execute();
    }
    $msg = "✅ Attendance updated successfully!";
}

// ================================
// 3. Ensure attendance records exist
// ================================
foreach ($students as $student) {
    $stmt_att = $conn->prepare("SELECT attendance_id FROM attendance WHERE student_id=? AND class_id=? AND subject_id=? AND date=?");
    $stmt_att->bind_param("iiis", $student['student_id'], $class_id, $subject_id, $date);
    $stmt_att->execute();
    $res_att = $stmt_att->get_result();

    if ($res_att->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO attendance (student_id, class_id, subject_id, date, status) VALUES (?, ?, ?, ?, 'Absent')");
        $stmt_insert->bind_param("iiis", $student['student_id'], $class_id, $subject_id, $date);
        $stmt_insert->execute();
    }
}

// ================================
// 4. Fetch attendance records for display
// ================================
$stmt_attendance = $conn->prepare("
    SELECT a.attendance_id, s.name, a.status
    FROM attendance a
    JOIN students s ON a.student_id = s.student_id
    WHERE a.class_id=? AND a.subject_id=? AND a.date=?
");
$stmt_attendance->bind_param("iis", $class_id, $subject_id, $date);
$stmt_attendance->execute();
$attendance_records = $stmt_attendance->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Attendance</title>
<style>
body { font-family: Arial; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #007bff; color: #fff; }
.btn { padding: 5px 10px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.btn:hover { background: #0056b3; }
</style>
</head>
<body>

<h2>📝 Manage Attendance - <?= htmlspecialchars($date) ?></h2>
<?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

<form method="POST">
<table>
<tr>
    <th>Student Name</th>
    <th>Present</th>
    <th>Absent</th>
</tr>
<?php while ($row = $attendance_records->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><input type="radio" name="attendance[<?= $row['attendance_id'] ?>]" value="Present" <?= $row['status'] == 'Present' ? 'checked' : '' ?>></td>
    <td><input type="radio" name="attendance[<?= $row['attendance_id'] ?>]" value="Absent" <?= $row['status'] == 'Absent' ? 'checked' : '' ?>></td>
</tr>
<?php endwhile; ?>
</table>
<br>
<button type="submit" class="btn">Update Attendance</button>
</form>

</body>
</html>
