<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// ✅ Get search input
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// ✅ Fetch students with performance (filtered by search if provided)
$sql = "
    SELECT s.student_id, s.name, s.email, s.class_id, c.class_name,
           IFNULL(ROUND(AVG(r.average_marks),2), 0) as avg_marks
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN results r ON s.student_id = r.student_id
    WHERE s.name LIKE '%$search%'
    GROUP BY s.student_id, s.name, s.email, s.class_id, c.class_name
    ORDER BY avg_marks DESC
";

$students = $conn->query($sql);
if (!$students) {
    die("❌ SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="managestudent.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f6f9; display: flex; }
        .sidebar { width: 220px; background: #111; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; color: #00bfff; }
        .sidebar a { display: block; padding: 12px 20px; margin: 8px 15px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
        .sidebar a:hover { background: #00bfff; color: #111; }
        .sidebar a.logout { background: #dc3545; }
        .sidebar a.logout:hover { background: #ff4444; color: #fff; }
        .main { margin-left: 220px; padding: 20px; flex: 1; }
        .header { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .header h1 { margin: 0; font-size: 22px; color: #333; flex: 1 0 100%; margin-bottom:10px; }
        .search-box input { padding:8px; border-radius:6px; border:1px solid #ccc; }
        .search-box button, .search-box a { padding:8px 12px; border:none; background:#00bfff; color:white; border-radius:6px; text-decoration:none; cursor: pointer; }
        .search-box a { background:#6c757d; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #00bfff; color: white; }
        tr:hover { background: #f1f1f1; }
        .btn { padding: 6px 10px; border-radius: 6px; text-decoration: none; }
        .btn.edit { background: #ffc107; color: #111; }
        .btn.delete { background: #dc3545; color: #fff; }
        .btn.edit:hover { background: #e0a800; }
        .btn.delete:hover { background: #c82333; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="../index.php">🏠 Home</a>
        <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
        <a href="../Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
        <a href="../classes/classes.php">🏫 Manage Classes</a>
        <a href="../subjects.php">📖 Manage Subjects</a>
        <a href="../add_student.php">➕ Add Student</a>
        <a href="../add_teacher.php">➕ Add Teacher</a>
        <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
        <a href="../admin_approve_results.php">✅ Approve Results</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="header">
            <h1>👨‍🎓 Manage Students</h1>

            <!-- Search Form -->
            <form method="get" action="" class="search-box">
                <input type="text" name="search" placeholder="🔍 Search by Name" value="<?= htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <a href="Managestudent.php">Reset</a>
            </form>
        </div>

        <!-- Students Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Class</th>
                    <th>Performance (Avg Marks)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['student_id']; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td>
                                <?php
                                $performance = $row['avg_marks'];
                                if ($performance >= 75) echo "🌟 Excellent ($performance%)";
                                elseif ($performance >= 50) echo "👍 Good ($performance%)";
                                elseif ($performance > 0) echo "⚠ Needs Improvement ($performance%)";
                                else echo "❌ No Results";
                                ?>
                            </td>
                            <td>
                                <a href="edit_student.php?student_id=<?= $row['student_id']; ?>" class="btn edit">✏ Edit</a>
                               <a href="delete_student.php?student_id=<?= urlencode($row['student_id']); ?>"
   class="btn delete"
   onclick="return confirm('Are you sure you want to delete this student?');">
   🗑 Delete
</a>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No matching students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
