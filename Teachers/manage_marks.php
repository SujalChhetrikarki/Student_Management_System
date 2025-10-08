<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$class_id   = $_GET['class_id'] ?? 0;
$subject_id = $_GET['subject_id'] ?? 0;

// ✅ Fetch class & subject name for header
$stmt_info = $conn->prepare("
    SELECT c.class_name, s.subject_name
    FROM classes c 
    JOIN subjects s 
    WHERE c.class_id = ? AND s.subject_id = ?
");
$stmt_info->bind_param("ii", $class_id, $subject_id);
$stmt_info->execute();
$info_result = $stmt_info->get_result();
$info = $info_result->fetch_assoc();
$stmt_info->close();

// ✅ Fetch exams assigned to this teacher
$stmt = $conn->prepare("
    SELECT e.exam_id, e.exam_date, e.max_marks, e.term
    FROM exams e
    JOIN class_subject_teachers cst
        ON cst.class_id = e.class_id 
       AND cst.subject_id = e.subject_id
    WHERE e.class_id = ? 
      AND e.subject_id = ? 
      AND cst.teacher_id = ?
    ORDER BY e.exam_date DESC
");
$stmt->bind_param("iii", $class_id, $subject_id, $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Marks - <?= htmlspecialchars($info['subject_name']) ?></title>
<style>
/* ===== General Styles ===== */
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6fa;
    margin: 0;
    padding: 0;
    color: #333;
}
header {
    background: #0066cc;
    color: white;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header h1 {
    margin: 0;
    font-size: 24px;
}
header a.logout-btn {
    background: #dc3545;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
}
header a.logout-btn:hover {
    background: #b02a37;
}
footer {
    background: #f1f1f1;
    text-align: center;
    padding: 15px 0;
    color: #555;
    margin-top: 40px;
}
.container {
    max-width: 1100px;
    margin: 30px auto;
    background: #fff;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
h2 {
    color: #0066cc;
    margin-bottom: 20px;
}

/* ===== Table Styles ===== */
.table-wrapper {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}
th, td {
    padding: 12px 15px;
    text-align: center;
}
th {
    background: #0066cc;
    color: #fff;
    font-weight: 600;
    text-transform: uppercase;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
tr:hover {
    background: #e6f0ff;
}
td a.btn {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.2s;
}
td a.btn:hover {
    background: #218838;
}

/* ===== Notes / Messages ===== */
.note {
    background: #fff3cd;
    border-left: 5px solid #ffeeba;
    padding: 12px 15px;
    border-radius: 6px;
    color: #856404;
    margin-top: 20px;
}

/* ===== Back Button ===== */
.back-btn {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 15px;
    font-weight: bold;
}
.back-btn:hover {
    background: #0056b3;
}
</style>
</head>
<body>

<!-- Header -->
<header>
    <h1>Assigned results</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<!-- Main Container -->
<div class="container">

    <a class="back-btn" href="teacher_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Manage Marks for <?= htmlspecialchars($info['class_name']); ?> — <?= htmlspecialchars($info['subject_name']); ?></h2>

    <?php if ($exams->num_rows > 0): ?>
    <div class="table-wrapper">
        <table>
            <tr>
                <th>Exam ID</th>
                <th>Date</th>
                <th>Term</th>
                <th>Max Marks</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $exams->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['exam_id']); ?></td>
                <td><?= htmlspecialchars($row['exam_date']); ?></td>
                <td><?= htmlspecialchars($row['term']); ?></td>
                <td><?= htmlspecialchars($row['max_marks']); ?></td>
                <td>
                    <a class="btn" href="add_marks.php?exam_id=<?= $row['exam_id']; ?>">Add / Update Marks</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php else: ?>
        <p class="note">⚠️ No exams assigned yet for this subject.</p>
    <?php endif; ?>

</div>
</body>
</html>
