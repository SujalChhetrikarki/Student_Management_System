<?php
session_start();
include '../Database/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle approval form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $result_ids = $_POST['result_id'] ?? [];
    foreach ($result_ids as $rid) {
        $stmt = $conn->prepare("UPDATE results SET status='Approved' WHERE result_id=?");
        $stmt->bind_param("i", $rid);
        $stmt->execute();
    }
    header("Location: admin_approve_results.php?msg=" . urlencode("✅ Selected results approved!"));
    exit;
}

// Fetch all pending results grouped by class
$sql = "SELECT r.result_id, r.marks_obtained, r.average_marks, e.exam_date, e.term,
               s.subject_name, c.class_name, st.name as student_name
        FROM results r
        JOIN exams e ON r.exam_id=e.exam_id
        JOIN subjects s ON e.subject_id=s.subject_id
        JOIN classes c ON e.class_id=c.class_id
        JOIN students st ON r.student_id=st.student_id
        WHERE r.status='Pending'
        ORDER BY c.class_name, e.term, e.exam_date ASC";

$results = $conn->query($sql);
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Approve Results</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
.sidebar { width: 220px; background: #111; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 20px; }
.sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; color: #00bfff; }
.sidebar a { display: block; padding: 12px 20px; margin: 8px 15px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
.sidebar a:hover { background: #00bfff; color: #111; }
.sidebar a.logout { background: #dc3545; }
.sidebar a.logout:hover { background: #ff4444; color: #fff; }

.container { margin-left: 240px; padding: 20px; }
h2 { margin-top: 0; }
table { border-collapse: collapse; width: 100%; background: #fff; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #007bff; color: #fff; }
.btn { padding: 8px 14px; background: green; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.btn:hover { background: darkgreen; }
</style>
</head>
<body>

    <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="../Admin/index.php">🏠 Home</a>
    <a href="./Manage_student/Managestudent.php">📚 Manage Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
    <a href="./classes/classes.php">🏫 Manage Classes</a>
    <a href="./subjects.php">📖 Manage Subjects</a>
    <a href="./add_student.php">➕ Add Student</a>
    <a href="./add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
    <a href="./admin_approve_results.php">✅ Approve Results</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
  </div>

<!-- Main Content -->
<div class="container">
    <h2>✅ Approve Pending Results (Class-wise)</h2>
    <?php if($msg) echo "<p style='color:green;'>{$msg}</p>"; ?>

    <form method="POST">
    <table>
        <tr>
            <th>Select</th>
            <th>Student</th>
            <th>Class</th>
            <th>Subject</th>
            <th>Term</th>
            <th>Exam Date</th>
            <th>Marks</th>
            <th>Average</th>
        </tr>

        <?php if($results->num_rows==0): ?>
        <tr><td colspan="8">No pending results.</td></tr>
        <?php else: ?>
        <?php while($r=$results->fetch_assoc()): ?>
        <tr>
            <td><input type="checkbox" name="result_id[]" value="<?= $r['result_id'] ?>"></td>
            <td><?= htmlspecialchars($r['student_name']) ?></td>
            <td><?= htmlspecialchars($r['class_name']) ?></td>
            <td><?= htmlspecialchars($r['subject_name']) ?></td>
            <td><?= htmlspecialchars($r['term']) ?></td>
            <td><?= htmlspecialchars($r['exam_date']) ?></td>
            <td><?= htmlspecialchars($r['marks_obtained']) ?></td>
            <td><?= number_format($r['average_marks'],2) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
    </table>
    <br>
    <button type="submit" name="approve" class="btn">Approve Selected Results</button>
    </form>
</div>

</body>
</html>
