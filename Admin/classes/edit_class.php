<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: ManageClasses.php");
    exit;
}

$class_id = intval($_GET['id']);

// Fetch class details
$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

// Fetch assigned teacher (if any)
$stmt2 = $conn->prepare("SELECT teacher_id FROM class_teachers WHERE class_id=?");
$stmt2->bind_param("i", $class_id);
$stmt2->execute();
$assigned_teacher = $stmt2->get_result()->fetch_assoc();
$assigned_teacher_id = $assigned_teacher['teacher_id'] ?? null;

// Fetch all teachers for dropdown
$teachers = $conn->query("SELECT * FROM teachers ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);
    $teacher_id = intval($_POST['teacher_id']);

    // Update class name
    $stmt = $conn->prepare("UPDATE classes SET class_name=? WHERE class_id=?");
    $stmt->bind_param("si", $class_name, $class_id);
    $stmt->execute();

    // Update class_teacher assignment
    // First, check if an assignment exists
    if ($assigned_teacher_id) {
        $stmt = $conn->prepare("UPDATE class_teachers SET teacher_id=? WHERE class_id=?");
        $stmt->bind_param("ii", $teacher_id, $class_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO class_teachers (class_id, teacher_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $class_id, $teacher_id);
        $stmt->execute();
    }

    header("Location: classes.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class</title>
    <link rel="stylesheet" href="classes.css">
</head>
<body>
<div class="container">
    <h1>✏ Edit Class</h1>

    <form method="post">
        <label>Class Name:</label>
        <input type="text" name="class_name" value="<?= htmlspecialchars($class['class_name']); ?>" required>

        <label>Assign Teacher:</label>
        <select name="teacher_id" required>
            <option value="">-- Select Teacher --</option>
            <?php while($t = $teachers->fetch_assoc()): ?>
                <option value="<?= $t['teacher_id']; ?>" <?= ($t['teacher_id']==$assigned_teacher_id)?'selected':''; ?>>
                    <?= htmlspecialchars($t['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">💾 Update Class</button>
    </form>

    <a href="ManageClasses.php">⬅ Back to Classes</a>
</div>
</body>
</html>
