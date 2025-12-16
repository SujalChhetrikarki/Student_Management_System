<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (!isset($_GET['teacher_id'])) {
    header("Location: teachers.php");
    exit;
}

$teacher_id = $_GET['teacher_id'];

// Fetch teacher info
$sql = "SELECT * FROM teachers WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
if (!$teacher) {
    die("Teacher not found.");
}

// Fetch assigned classes
$sql_classes = "SELECT class_id FROM class_teachers WHERE teacher_id = ?";
$stmt_classes = $conn->prepare($sql_classes);
$stmt_classes->bind_param("s", $teacher_id);
$stmt_classes->execute();
$res_classes = $stmt_classes->get_result();
$assigned_classes = [];
while ($row = $res_classes->fetch_assoc()) {
    $assigned_classes[] = $row['class_id'];
}

// Fetch assigned subjects
$sql_subjects = "SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?";
$stmt_subjects = $conn->prepare($sql_subjects);
$stmt_subjects->bind_param("s", $teacher_id);
$stmt_subjects->execute();
$res_subjects = $stmt_subjects->get_result();
$assigned_subjects = [];
while ($row = $res_subjects->fetch_assoc()) {
    $assigned_subjects[] = $row['subject_id'];
}

// Fetch all classes and subjects
$all_classes = $conn->query("SELECT class_id, class_name FROM classes");
$all_subjects = $conn->query("SELECT subject_id, subject_name FROM subjects");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <style>
        body { font-family: Arial; background: #f1f1f1; margin:0; padding:0; }
        .container { max-width:700px; margin:30px auto; background:#fff; padding:20px; border-radius:8px; }
        input, select { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:5px; }
        button { padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#0056b3; }
        label { font-weight: bold; margin-top: 10px; display: block; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Teacher - <?php echo htmlspecialchars($teacher['name']); ?></h2>

    <form action="edit_teacher_process.php" method="POST">
        <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">

        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>

        <label>Password: (leave blank to keep current)</label>
        <input type="password" name="password">

        <label>Specialization:</label>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($teacher['specialization']); ?>" required>

        <label>
            <input type="checkbox" name="is_class_teacher" value="1" <?php if($teacher['is_class_teacher']) echo 'checked'; ?>> Class Teacher
        </label>

        <label>Assign Classes:</label>
        <?php while ($row = $all_classes->fetch_assoc()): ?>
            <label>
                <input type="checkbox" name="classes[]" value="<?php echo $row['class_id']; ?>" 
                <?php if(in_array($row['class_id'], $assigned_classes)) echo 'checked'; ?>>
                <?php echo htmlspecialchars($row['class_name']); ?>
            </label>
        <?php endwhile; ?>

        <label>Assign Subjects:</label>
        <?php while ($row = $all_subjects->fetch_assoc()): ?>
            <label>
                <input type="checkbox" name="subjects[]" value="<?php echo $row['subject_id']; ?>" 
                <?php if(in_array($row['subject_id'], $assigned_subjects)) echo 'checked'; ?>>
                <?php echo htmlspecialchars($row['subject_name']); ?>
            </label>
        <?php endwhile; ?>

        <button type="submit">Update Teacher</button>
    </form>
    <a href="teachers.php">⬅ Back to Manage Teachers</a>
</div>
</body>
</html>
