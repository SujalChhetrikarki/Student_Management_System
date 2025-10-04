<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// Fetch all teachers
$sql = "SELECT * FROM teachers ORDER BY name ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching teachers: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <style>
        body { font-family: Arial; background: #f1f1f1; margin: 0; padding: 0; }
        
        /* Sidebar */
        .sidebar { 
            width: 220px; 
            background: #111; 
            color: #fff; 
            height: 100vh; 
            position: fixed; 
            left: 0; 
            top: 0; 
            padding-top: 20px; 
        }
        .sidebar h2 { 
            text-align: center; 
            margin-bottom: 30px; 
            font-size: 20px; 
            color: #00bfff; 
        }
        .sidebar a { 
            display: block; 
            padding: 12px 20px; 
            margin: 8px 15px; 
            background: #222; 
            color: #fff; 
            text-decoration: none; 
            border-radius: 6px; 
            transition: 0.3s; 
        }
        .sidebar a:hover { background: #00bfff; color: #111; }
        .sidebar a.logout { background: #dc3545; }
        .sidebar a.logout:hover { background: #ff4444; color: #fff; }

        /* Content */
        .container { 
            margin-left: 240px;  /* ✅ Prevents overlap with sidebar */
            max-width: calc(100% - 240px); 
            padding: 20px; 
        }

        h1 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #00bfff; color: #fff; }
        a.btn { padding: 5px 10px; background: #00bfff; color: #fff; text-decoration: none; border-radius: 5px; }
        a.btn:hover { background: #0056b3; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="../index.php">🏠 Home</a>
        <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
        <a href="./Teachersshow.php">👨‍🏫 Manage Teachers</a>
        <a href="../Classes/classes.php">🏫 Manage Classes</a>
        <a href="../subjects.php">📖 Manage Subjects</a>
        <a href="../add_student.php">➕ Add Student</a>
        <a href="../add_teacher.php">➕ Add Teacher</a>
        <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
        <a href="../admin_approve_results.php">✅ Approve Results</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1>👨‍🏫 Manage Teachers</h1>
        <a href="../add_teacher.php" class="btn">➕ Add New Teacher</a>

        <table>
            <tr>
                <th>Teacher ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Class Teacher?</th>
                <th>Assigned Classes</th>
                <th>Assigned Subjects</th>
                <th>Actions</th>
            </tr>

            <?php while ($teacher = $result->fetch_assoc()): ?>
                <?php
                $tid = $teacher['teacher_id'];
                $is_class_teacher = $teacher['is_class_teacher'] ? "✅" : "❌";

                // Fetch assigned classes
                $sql_classes = "SELECT c.class_name 
                                FROM class_teachers ct 
                                JOIN classes c ON ct.class_id = c.class_id 
                                WHERE ct.teacher_id = ?";
                $stmt_classes = $conn->prepare($sql_classes);
                $stmt_classes->bind_param("s", $tid);
                $stmt_classes->execute();
                $res_classes = $stmt_classes->get_result();
                $classes_arr = [];
                while ($c = $res_classes->fetch_assoc()) {
                    $classes_arr[] = $c['class_name'];
                }
                $classes_str = !empty($classes_arr) ? implode(", ", $classes_arr) : "-";

                // Fetch assigned subjects
                $sql_subjects = "SELECT s.subject_name 
                                 FROM teacher_subjects ts 
                                 JOIN subjects s ON ts.subject_id = s.subject_id 
                                 WHERE ts.teacher_id = ?";
                $stmt_subjects = $conn->prepare($sql_subjects);
                $stmt_subjects->bind_param("s", $tid);
                $stmt_subjects->execute();
                $res_subjects = $stmt_subjects->get_result();
                $subjects_arr = [];
                while ($s = $res_subjects->fetch_assoc()) {
                    $subjects_arr[] = $s['subject_name'];
                }
                $subjects_str = !empty($subjects_arr) ? implode(", ", $subjects_arr) : "-";
                ?>

                <tr>
                    <td><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                    <td><?= htmlspecialchars($teacher['name']) ?></td>
                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                    <td><?= htmlspecialchars($teacher['specialization']) ?></td>
                    <td><?= $is_class_teacher ?></td>
                    <td><?= htmlspecialchars($classes_str) ?></td>
                    <td><?= htmlspecialchars($subjects_str) ?></td>
                    <td>
    <a href="edit_teacher.php?teacher_id=<?= urlencode($tid) ?>" 
       style="background:#ffc107; color:#111; padding:6px 12px; border-radius:5px; text-decoration:none; margin-right:5px;">
       ✏ Edit
    </a>

    <a href="delete_teacher.php?teacher_id=<?= urlencode($tid) ?>" 
       style="background:#dc3545; color:#fff; padding:6px 12px; border-radius:5px; text-decoration:none;" 
       onclick="return confirm('Are you sure?')">
       🗑 Delete
    </a>
</td>

                </tr>

            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
