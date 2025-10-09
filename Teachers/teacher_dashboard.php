<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';
$teacher_id = $_SESSION['teacher_id'];

// 1️⃣ Fetch Teacher Details
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// 2️⃣ Fetch Notices
$notices = $conn->query("
    SELECT title, message, created_at 
    FROM notices 
    WHERE target IN ('teachers', 'both')
    ORDER BY created_at DESC 
    LIMIT 5
");

// 3️⃣ Fetch Classes
$stmt_classes = $conn->prepare("
    SELECT c.class_id, c.class_name, c.class_teacher_id
    FROM classes c
    JOIN class_teachers ct ON c.class_id = ct.class_id
    WHERE ct.teacher_id = ?
");
$stmt_classes->bind_param("s", $teacher_id);
$stmt_classes->execute();
$classes = $stmt_classes->get_result();
$stmt_classes->close();

// 4️⃣ Fetch Subjects
$stmt_subjects = $conn->prepare("
    SELECT s.subject_id, s.subject_name, c.class_id, c.class_name
    FROM class_subject_teachers cst
    JOIN subjects s ON cst.subject_id = s.subject_id
    JOIN classes c ON cst.class_id = c.class_id
    WHERE cst.teacher_id = ?
");
$stmt_subjects->bind_param("s", $teacher_id);
$stmt_subjects->execute();
$subjects = $stmt_subjects->get_result();
$stmt_subjects->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f9fafc;
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
.logout-btn {
    background: #dc3545;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
}
.container {
    max-width: 1100px;
    margin: 30px auto;
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.section {
    margin-bottom: 30px;
}
.section h2 {
    border-bottom: 2px solid #0066cc;
    padding-bottom: 6px;
    margin-bottom: 15px;
    color: #0066cc;
}
.profile-box {
    background: #eef4ff;
    padding: 15px 20px;
    border-radius: 6px;
    line-height: 1.6;
}
.notice {
    background: #fff8e1;
    border-left: 5px solid #ffcc00;
    padding: 12px 15px;
    margin-bottom: 10px;
    border-radius: 5px;
}
.notice h4 {
    margin: 0;
    font-size: 16px;
}
.notice small {
    color: #777;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.table th, .table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
.table th {
    background: #f1f1f1;
    color: #333;
}
.table tr:hover {
    background: #f9f9f9;
}
.btn {
    background: #007bff;
    color: white;
    padding: 6px 10px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
}
.btn:hover {
    background: #0056b3;
}
.status {
    font-weight: bold;
    color: green;
}
</style>
</head>
<body>

<header>
    <h1>Teacher Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="container">

    <!-- Profile -->
    <div class="section">
        <h2>Your Profile</h2>
        <div class="profile-box">
            <p><strong>Name:</strong> <?= htmlspecialchars($teacher['name']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']); ?></p>
            <p><strong>Specialization:</strong> <?= htmlspecialchars($teacher['specialization']); ?></p>
        </div>
    </div>

    <!-- Notices -->
    <div class="section">
        <h2>Recent Notices</h2>
        <?php
        if ($notices && $notices->num_rows > 0):
            while ($n = $notices->fetch_assoc()):
        ?>
            <div class="notice">
                <h4><?= htmlspecialchars($n['title']); ?></h4>
                <p><?= nl2br(htmlspecialchars($n['message'])); ?></p>
                <small>Posted on: <?= date('d M Y, h:i A', strtotime($n['created_at'])); ?></small>
            </div>
        <?php
            endwhile;
        else:
            echo "<p>No notices available.</p>";
        endif;
        ?>
    </div>
    <!-- Classes -->
    <div class="section">
        <h2>Your Classes</h2>
        <?php if ($classes->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Class ID</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $classes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                    <td><?= $row['class_id']; ?></td>
                    <td>
                        <?= ($row['class_teacher_id'] === $teacher_id)
                            ? "<span class='status'>Class Teacher</span>"
                            : "Subject Teacher"; ?>
                    </td>
                    <td>
                            <a class="btn" href="view_students.php?class_id=<?= $row['class_id']; ?>">View Students</a>
                            <a class="btn" href="manage_attendance.php?class_id=<?= $row['class_id']; ?>">Manage Attendance</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No classes assigned yet.</p>
        <?php endif; ?>
    </div>

<div class="section">
    <h2>Your Subjects</h2>
    <?php if ($subjects->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <td><?= htmlspecialchars($row['class_name']); ?></td>
                <td>
                    <a class="btn" 
                       href="manage_marks.php?class_id=<?= $row['class_id']; ?>&subject_id=<?= $row['subject_id']; ?>">
                       Manage Marks
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No subjects assigned yet.</p>
    <?php endif; ?>
</div>
<td>
    <a class="btn" href="change_password.php">Change Password</a>
</td>
</div>
</body>
</html>
