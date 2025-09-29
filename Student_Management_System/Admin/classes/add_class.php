<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
include '../../Database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name']);
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : NULL;

    $stmt = $conn->prepare("INSERT INTO classes (class_name, class_teacher_id) VALUES (?, ?)");
    $stmt->bind_param("ss", $class_name, $teacher_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Class added successfully!";
        header("Location: manage_classes.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Error: " . $stmt->error;
    }
}

$teachers = $conn->query("SELECT teacher_id, name FROM teachers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Class</title>
    <link rel="stylesheet" href="classes.css">
</head>
<body>
<div class="container">
    <h2>➕ Add Class</h2>

    <!-- Show success/error -->
    <?php if (!empty($_SESSION['error'])): ?>
        <p style="color:red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <p style="color:green;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Class Name</label>
        <select name="class_name" required>
            <option value="">-- Select Class --</option>
            <option value="Nursery">Nursery</option>
            <option value="LKG">LKG</option>
            <option value="UKG">UKG</option>
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <label>Assign Teacher (optional)</label>
        <select name="teacher_id">
            <option value="">-- None --</option>
            <?php while($t = $teachers->fetch_assoc()): ?>
                <option value="<?= $t['teacher_id']; ?>"><?= $t['name']; ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Save</button>
    </form>

    <a href="manage_classes.php">⬅ Back</a>
</div>
</body>
</html>
