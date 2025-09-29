<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// Check class_id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: classes.php");
    exit;
}
$class_id = intval($_GET['id']);

// Fetch class info with teacher
$class_sql = "
    SELECT c.class_name, t.name AS teacher_name 
    FROM classes c 
    LEFT JOIN class_teachers ct ON c.class_id = ct.class_id
    LEFT JOIN teachers t ON ct.teacher_id = t.teacher_id
    WHERE c.class_id = ?
";
$stmt = $conn->prepare($class_sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
$class_info = $class_result->fetch_assoc();
$stmt->close();

if (!$class_info) {
    $_SESSION['error'] = "Class not found!";
    header("Location: classes.php");
    exit;
}

// Fetch students in this class
$sql = "SELECT student_id, name, email, date_of_birth, gender 
        FROM students 
        WHERE class_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$total_students = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Students</title>
    <link rel="stylesheet" href="classes.css">
</head>
<body>
<div class="container">
    <h1>👨‍🎓 Students in <?= htmlspecialchars($class_info['class_name']); ?></h1>
    <p><strong>Class Teacher:</strong> <?= $class_info['teacher_name'] ?? 'Unassigned'; ?></p>
    <p><strong>Total Students:</strong> <?= $total_students; ?></p>

    <a class="btn" href="classes.php">⬅ Back to Classes</a>

    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($total_students > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_id']); ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['date_of_birth']); ?></td>
                    <td><?= htmlspecialchars($row['gender']); ?></td>
                    <td>
                        <a class="btn-sm danger" href="delete_student.php?student_id=<?= $row['student_id']; ?>&class_id=<?= $class_id; ?>" 
                           onclick="return confirm('Are you sure you want to delete this student?');">🗑 Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No students found in this class.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>
