<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) die("No exam selected.");

// ============================
// 1. Fetch Exam + Subject + Class
// ============================
$stmt_exam = $conn->prepare("
    SELECT e.exam_id, e.exam_date, e.max_marks,
           s.subject_name, c.class_id, c.class_name
    FROM exams e
    JOIN subjects s ON e.subject_id = s.subject_id
    JOIN classes c ON e.class_id = c.class_id
    JOIN class_subject_teachers cst 
         ON cst.class_id = e.class_id AND cst.subject_id = e.subject_id
    WHERE e.exam_id = ? AND cst.teacher_id = ?
");
$stmt_exam->bind_param("ii", $exam_id, $teacher_id);
$stmt_exam->execute();
$exam = $stmt_exam->get_result()->fetch_assoc();
if (!$exam) die("You are not authorized for this exam.");
$class_id = $exam['class_id'];
$max_marks = $exam['max_marks'];

// ============================
// 2. Fetch students of this class
// ============================
$stmt_students = $conn->prepare("SELECT student_id, name FROM students WHERE class_id=? ORDER BY name ASC");
$stmt_students->bind_param("i", $class_id);
$stmt_students->execute();
$res_students = $stmt_students->get_result();
$students = $res_students->fetch_all(MYSQLI_ASSOC);

// ============================
// 3. Fetch existing results
// ============================
$results = [];
$stmt_results = $conn->prepare("SELECT student_id, marks_obtained FROM results WHERE exam_id=?");
$stmt_results->bind_param("i", $exam_id);
$stmt_results->execute();
$res_results = $stmt_results->get_result();
while ($r = $res_results->fetch_assoc()) {
    $results[$r['student_id']] = $r['marks_obtained'];
}

// ============================
// 4. Handle form submission
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marks'])) {
    foreach ($_POST['marks'] as $student_id => $marks) {
        if ($marks === '') continue;

        // Check student exists in this class
        $stmt_check_student = $conn->prepare("SELECT student_id FROM students WHERE student_id=? AND class_id=?");
        $stmt_check_student->bind_param("si", $student_id, $class_id);
        $stmt_check_student->execute();
        $res_check_student = $stmt_check_student->get_result();
        if ($res_check_student->num_rows === 0) continue;

        // Check if result exists
        $stmt_check = $conn->prepare("SELECT result_id FROM results WHERE student_id=? AND exam_id=?");
        $stmt_check->bind_param("si", $student_id, $exam_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            // Update
            $stmt_update = $conn->prepare("UPDATE results SET marks_obtained=?, average_marks=? WHERE student_id=? AND exam_id=?");
            $average = round(($marks / $max_marks) * 100, 2);
            $stmt_update->bind_param("dsii", $marks, $average, $student_id, $exam_id);
            $stmt_update->execute();
        } else {
            // Insert
            $average = round(($marks / $max_marks) * 100, 2);
            $stmt_insert = $conn->prepare("INSERT INTO results (student_id, exam_id, marks_obtained, average_marks) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("sidd", $student_id, $exam_id, $marks, $average);
            $stmt_insert->execute();
        }
    }

    $msg = "✅ Results updated successfully!";
    header("Location: manage_results.php?exam_id=$exam_id&msg=" . urlencode($msg));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Results</title>
<style>
table{border-collapse:collapse;width:100%;margin-top:20px;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
th{background:#007bff;color:#fff;}
input[type=number]{width:80px;text-align:center;}
</style>
</head>
<body>
<h2>📊 Manage Results - <?=htmlspecialchars($exam['subject_name'])?> (Class: <?=htmlspecialchars($exam['class_name'])?>)</h2>
<p><strong>Exam Date:</strong> <?=htmlspecialchars($exam['exam_date'])?> | <strong>Max Marks:</strong> <?=$max_marks?></p>

<?php if (!empty($_GET['msg'])): ?>
<p style="color:green"><?= htmlspecialchars($_GET['msg']) ?></p>
<?php endif; ?>

<form method="POST">
<table>
<tr><th>Student Name</th><th>Marks (out of <?=$max_marks?>)</th></tr>
<?php foreach($students as $student): ?>
<tr>
    <td><?=htmlspecialchars($student['name'])?></td>
    <td>
        <input type="number" name="marks[<?=$student['student_id']?>]" 
               value="<?= $results[$student['student_id']] ?? '' ?>" 
               min="0" max="<?=$max_marks?>">
    </td>
</tr>
<?php endforeach; ?>
</table>
<br>
<button type="submit">💾 Save Results</button>
</form>
</body>
</html>
