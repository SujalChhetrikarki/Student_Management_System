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
    <title>Students in <?= htmlspecialchars($class_name); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #34495e;
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: #ecf0f1;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s;
        }
        ul li:hover {
            background: #dcdde1;
        }
        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 16px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .empty {
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
            text-align: center;
        }
        header { background: #0066cc; color: white; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; }
header h1 { margin: 0; font-size: 24px; }
header a.logout-btn { background: #dc3545; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; }
header a.logout-btn:hover { background: #b02a37; }
    </style>
</head>
<body>

<header>
    <h1>Students</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

    <div class="container">
        <h2>Students in <?= htmlspecialchars($class_name); ?> (ID: <?= htmlspecialchars($class_id); ?>)</h2>

        <?php if ($students->num_rows > 0): ?>
            <ul>
                <?php while ($row = $students->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($row['name']); ?> <small>(ID: <?= $row['student_id']; ?>)</small></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="empty">No students in this class.</p>
        <?php endif; ?>

        <a href="teacher_dashboard.php" class="btn">⬅ Back to teacher dashboard</a>
    </div>

</body>
</html>
