<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (!isset($_GET['student_id'])) {
    header("Location: Managestudent.php");
    exit;
}

$student_id = intval($_GET['student_id']);

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch classes for dropdown
$classes = $conn->query("SELECT * FROM classes");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class_id = intval($_POST['class_id']);
    
    $stmt = $conn->prepare("UPDATE students SET name=?, email=?, class_id=? WHERE student_id=?");
    $stmt->bind_param("ssii", $name, $email, $class_id, $student_id);
    $stmt->execute();
    
    header("Location: Managestudent.php?msg=updated");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="managestudent.css">
</head>
<body>
<div class="container">
    <h1>✏ Edit Student</h1>
    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name']); ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']); ?>" required>
        
        <label>Class:</label>
        <select name="class_id" required>
            <?php while($c = $classes->fetch_assoc()): ?>
                <option value="<?= $c['class_id']; ?>" <?= ($c['class_id']==$student['class_id'])?'selected':''; ?>>
                    <?= htmlspecialchars($c['class_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        
        <button type="submit">💾 Update Student</button>
    </form>
    <a href="Managestudent.php">⬅ Back</a>
</div>
</body>
</html>
