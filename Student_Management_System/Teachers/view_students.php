<?php
session_start();
include '../Database/db_connect.php';

// Get class_id from request (GET or POST)
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    die("Class ID not provided.");
}

// Fetch class name
$sql = "SELECT class_name FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classRow = $result->fetch_assoc();
$class_name = $classRow['class_name'] ?? 'Unknown Class';

// Fetch students in this class
$sqlStudents = "SELECT student_id, name FROM students WHERE class_id = ?";
$stmtStudents = $conn->prepare($sqlStudents);
$stmtStudents->bind_param("i", $class_id);
$stmtStudents->execute();
$students = $stmtStudents->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students in Class</title>
</head>
<body>
    <h2>👨‍🎓 Students in <?= htmlspecialchars($class_name); ?> (ID: <?= htmlspecialchars($class_id); ?>)</h2>

    <?php if ($students->num_rows > 0): ?>
        <ul>
            <?php while ($row = $students->fetch_assoc()): ?>
                <li><?= htmlspecialchars($row['name']); ?> (ID: <?= $row['student_id']; ?>)</li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No students in this class.</p>
    <?php endif; ?>
</body>
</html>
