<?php
session_start();
include '../../Database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$exam_id = $_GET['exam_id'] ?? 0;

// Fetch exam
$sql = "SELECT * FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) die("Exam not found.");

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $exam_date = $_POST['exam_date'];
    $max_marks = $_POST['max_marks'];

    $sql_update = "UPDATE exams SET exam_date = ?, max_marks = ? WHERE exam_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sii", $exam_date, $max_marks, $exam_id);
    if ($stmt->execute()) {
        header("Location: admin_add_exam.php?msg=" . urlencode("✅ Exam updated successfully!"));
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Exam</title>
</head>
<body>
<h2>Edit Exam #<?= $exam_id; ?></h2>

<form method="POST">
    <label>Exam Date:</label>
    <input type="date" name="exam_date" value="<?= $exam['exam_date']; ?>" required>

    <label>Maximum Marks:</label>
    <input type="number" name="max_marks" value="<?= $exam['max_marks']; ?>" min="1" required>

    <button type="submit">Update Exam</button>
</form>

<p><a href="admin_add_exam.php">⬅ Back to Exams</a></p>
</body>
</html>
