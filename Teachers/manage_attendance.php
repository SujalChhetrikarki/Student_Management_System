<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$class_id = $_GET['class_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

if (!$class_id) {
    die("❌ Invalid request: Missing class ID.");
}

// ==============================
// Fetch subjects assigned to this teacher & class
// ==============================
$stmt = $conn->prepare("
    SELECT s.subject_id, s.subject_name
    FROM subjects s
    JOIN class_subject_teachers cst ON cst.subject_id = s.subject_id
    WHERE cst.teacher_id = ? AND cst.class_id = ?
");
$stmt->bind_param("ii", $teacher_id, $class_id);
$stmt->execute();
$subjects = $stmt->get_result();
$stmt->close();

// Default subject (first one assigned)
$subject_id = $subjects->fetch_assoc()['subject_id'] ?? null;
$subjects->data_seek(0); // reset pointer

// ==============================
// Fetch students in this class
// ==============================
$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();

// ==============================
// Handle attendance submission
// ==============================
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {

    $subject_id = $_POST['subject_id'] ?? null;
    if (!$subject_id) {
        die("<p style='color:red;'>❌ Please select a subject before saving attendance.</p>");
    }

    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, class_id, subject_id, date, status)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");
        $stmt->bind_param("iiiss", $student_id, $class_id, $subject_id, $date, $status);
        $stmt->execute();

        if ($stmt->error) {
            echo "<p style='color:red;'>⚠ Error for student ID $student_id: {$stmt->error}</p>";
        }

        $stmt->close();
    }

    $msg = "✅ Attendance saved successfully!";
}

// ==============================
// Fetch existing attendance for display
// ==============================
$attendance = [];
if ($subject_id) {
    $stmt2 = $conn->prepare("
        SELECT s.student_id, s.name, COALESCE(a.status, 'Absent') AS status
        FROM students s
        LEFT JOIN attendance a 
            ON s.student_id = a.student_id 
            AND a.class_id = ? 
            AND a.subject_id = ? 
            AND a.date = ?
        WHERE s.class_id = ?
        ORDER BY s.name
    ");
    $stmt2->bind_param("iisi", $class_id, $subject_id, $date, $class_id);
    $stmt2->execute();
    $attendance = $stmt2->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Attendance</title>
<style>
body { font-family: "Segoe UI", Arial; background: #f9fafc; margin: 0; padding: 0; color: #333; }
header { background: #0066cc; color: white; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; }
header h1 { margin: 0; font-size: 24px; }
header a.logout-btn { background: #dc3545; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; }
header a.logout-btn:hover { background: #b02a37; }
.container { max-width: 1000px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
h2 { color: #007bff; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
th { background: #007bff; color: #fff; }
tr:nth-child(even) { background: #f2f2f2; }
.btn { padding: 8px 14px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-right: 10px; }
.btn:hover { background: #0056b3; }
.success { color: green; font-weight: bold; }
.date-form { margin-bottom: 10px; }
input[type="date"], select { padding: 5px; border-radius: 5px; border: 1px solid #ccc; }
</style>
</head>
<body>

<header>
    <h1>Manage Attendance</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="container">
<h2>🗓 Manage Attendance</h2>

<!-- Date Selector -->
<form method="GET" class="date-form">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
    <label><strong>Select Date:</strong></label>
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    <button type="submit" class="btn">Go</button>
</form>

<?php if (!empty($msg)) echo "<p class='success'>$msg</p>"; ?>

<?php if (!empty($attendance)): ?>
<form method="POST">
<input type="hidden" name="subject_id" value="<?= $subject_id ?>">

<table>
<tr>
    <th>Student Name</th>
    <th>Present</th>
    <th>Absent</th>
</tr>
<?php while ($row = $attendance->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><input type="radio" name="attendance[<?= $row['student_id'] ?>]" value="Present" <?= $row['status'] == 'Present' ? 'checked' : '' ?>></td>
    <td><input type="radio" name="attendance[<?= $row['student_id'] ?>]" value="Absent" <?= $row['status'] == 'Absent' ? 'checked' : '' ?>></td>
</tr>
<?php endwhile; ?>
</table>
<br>
<button type="submit" class="btn">Save Attendance</button>
<a href="teacher_dashboard.php" class="btn">⬅ Back to Dashboard</a>
</form>
<?php else: ?>
<p><strong>⚠ No students found for this class.</strong></p>
<?php endif; ?>

</div>
</body>
</html>
