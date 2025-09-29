<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';
$teacher_id = $_SESSION['teacher_id'];

// Fetch exams assigned to this teacher
$stmt = $conn->prepare("
    SELECT e.exam_id, e.exam_date, e.max_marks,
           s.subject_name, c.class_name
    FROM exams e
    JOIN subjects s ON e.subject_id = s.subject_id
    JOIN classes c ON e.class_id = c.class_id
    JOIN class_subject_teachers cst 
         ON cst.class_id = e.class_id AND cst.subject_id = e.subject_id
    WHERE cst.teacher_id = ?
    ORDER BY e.exam_date DESC
");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select Exam</title>
<style>
body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
h2 { color: #333; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
th { background: #007bff; color: #fff; }
a.btn { background: #28a745; color: #fff; padding: 6px 12px; text-decoration: none; border-radius: 4px; }
a.btn:hover { background: #218838; }
</style>
</head>
<body>
<h2>📅 Select an Exam</h2>
<?php if ($exams->num_rows > 0): ?>
<table>
<tr>
    <th>Exam ID</th>
    <th>Subject</th>
    <th>Class</th>
    <th>Date</th>
    <th>Max Marks</th>
    <th>Action</th>
</tr>
<?php while ($row = $exams->fetch_assoc()): ?>
<tr>
    <td><?= $row['exam_id'] ?></td>
    <td><?= htmlspecialchars($row['subject_name']) ?></td>
    <td><?= htmlspecialchars($row['class_name']) ?></td>
    <td><?= htmlspecialchars($row['exam_date']) ?></td>
    <td><?= htmlspecialchars($row['max_marks']) ?></td>
    <td><a class="btn" href="manage_results.php?exam_id=<?= $row['exam_id'] ?>">Manage Results</a></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>⚠️ No exams assigned to you yet.</p>
<?php endif; ?>
</body>
</html>
