<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// ✅ Fetch classes with assigned teacher, student count, subject count
$sql = "
    SELECT 
        c.class_id,
        c.class_name,
        t.name AS teacher_name,
        COUNT(DISTINCT s.student_id) AS total_students,
        COUNT(DISTINCT sub.subject_id) AS total_subjects
    FROM classes c
    LEFT JOIN class_teachers ct ON c.class_id = ct.class_id
    LEFT JOIN teachers t ON ct.teacher_id = t.teacher_id
    LEFT JOIN students s ON s.class_id = c.class_id
    LEFT JOIN subjects sub ON sub.class_id = c.class_id
    GROUP BY c.class_id, c.class_name, t.name
    ORDER BY c.class_id ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes</title>
    <link rel="stylesheet" href="classes.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6f9; display: flex; }
        .sidebar { width: 220px; background: #111; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; color: #00bfff; }
        .sidebar a { display: block; padding: 12px 20px; margin: 8px 15px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
        .sidebar a:hover { background: #00bfff; color: #111; }
        .sidebar a.logout { background: #dc3545; }
        .sidebar a.logout:hover { background: #ff4444; color: #fff; }
        .container { margin-left: 240px; padding: 20px; flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; border-radius: 8px; overflow: hidden; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        table th { background: #007bff; }
        .btn, .btn-sm { text-decoration: none; padding: 6px 12px; border-radius: 4px; background: #3498db; color: white; }
        .btn:hover, .btn-sm:hover { background: #2980b9; }
        .btn-sm.danger { background: #e74c3c; }
        .btn-sm.danger:hover { background: #c0392b; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="../index.php">🏠 Home</a>
  <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
  <a href="../Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
  <a href="classes.php">🏫 Manage Classes</a>
  <a href="../subjects.php">📖 Manage Subjects</a>
  <a href="../add_student.php">➕ Add Student</a>
  <a href="../add_teacher.php">➕ Add Teacher</a>
  <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
  <a href="../admin_approve_results.php">✅ Approve Results</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="container">
    <h1>📚 Manage Classes</h1>

    <a class="btn" href="Classes.php">➕ Add New Class</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Teacher</th>
                <th>Total Students</th>
                <th>Total Subjects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['class_id']; ?></td>
                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                    <td><?= $row['teacher_name'] ?? 'Unassigned'; ?></td>
                    <td><?= $row['total_students']; ?></td>
                    <td><?= $row['total_subjects']; ?></td>
                    <td>
                        <a class="btn-sm" href="edit_class.php?id=<?= $row['class_id']; ?>">✏ Edit</a>
                        <a class="btn-sm danger" href="delete_class.php?id=<?= $row['class_id']; ?>" onclick="return confirm('Delete this class?')">🗑 Delete</a>
                        <a class="btn-sm" href="view_students.php?id=<?= $row['class_id']; ?>">👨‍🎓 Students</a>
                        <a class="btn-sm" href="view_subjects.php?id=<?= $row['class_id']; ?>">📖 Subjects</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No classes found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="../index.php">⬅ Back</a>
</div>

</body>
</html>
<?php $conn->close(); ?>
