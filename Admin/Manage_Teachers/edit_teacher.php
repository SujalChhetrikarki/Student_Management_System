<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (!isset($_GET['teacher_id'])) {
    header("Location: Teachersshow.php");
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
if (!$teacher) die("Teacher not found.");

// Fetch class teacher (single class if any)
$class_teacher_class = null;
$ct = $conn->prepare("SELECT class_id FROM class_teachers WHERE teacher_id = ?");
$ct->bind_param("s", $teacher_id);
$ct->execute();
$res_ct = $ct->get_result();
if ($row = $res_ct->fetch_assoc()) {
    $class_teacher_class = $row['class_id'];
}
$ct->close();

// Fetch mapping: class_id => [subject_id, ...] from class_subject_teachers
$assigned_subjects_for_class = [];
$map = $conn->prepare("SELECT class_id, subject_id FROM class_subject_teachers WHERE teacher_id = ?");
$map->bind_param("s", $teacher_id);
$map->execute();
$res_map = $map->get_result();
while ($r = $res_map->fetch_assoc()) {
    $cid = $r['class_id'];
    $sid = $r['subject_id'];
    if (!isset($assigned_subjects_for_class[$cid])) $assigned_subjects_for_class[$cid] = [];
    $assigned_subjects_for_class[$cid][] = $sid;
}
$map->close();

// Determine teaching classes (distinct class ids from class_subject_teachers)
$teaching_classes = array_keys($assigned_subjects_for_class);

// Fetch all classes & subjects
$all_classes = $conn->query("SELECT class_id, class_name FROM classes");
$all_subjects = [];
$subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
while ($s = $subjectQuery->fetch_assoc()) {
    $all_subjects[] = $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Teacher</title>
<style>
/* Global */
* { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
body { background: #f4f6f9; display: flex; min-height: 100vh; }

/* Sidebar */
.sidebar { width: 220px; background: #111; color: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; }
.sidebar h2 { text-align:center; color:#00bfff; margin-bottom:30px; font-size:20px; }
.sidebar a { display:block; padding:10px 20px; margin:6px 15px; background:#222; color:#fff; text-decoration:none; border-radius:6px; transition:0.3s; }
.sidebar a:hover { background:#00bfff; color:#111; }
.sidebar a.logout { background:#dc3545; }
.sidebar a.logout:hover { background:#ff4444; color:#fff; }

/* Header */
#header { position: fixed; top: 0; left: 220px; right: 0; height: 45px; background: #00bfff; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 100; }

/* Container */
.container { max-width: 850px; width: 100%; background: #fff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); margin: 60px auto 40px auto; }
.container h2 { text-align:center; margin-bottom:25px; color:#333; }

/* Form Styling */
form label { display:block; margin-top:12px; font-weight:bold; color:#555; }
form input, form select, form button { font-family: inherit; }
form input, form select { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
form input[type="checkbox"] { width:auto; margin-right:8px; }
form button { width:100%; padding:12px; margin-top:20px; background:#00bfff; color:#fff; font-size:16px; font-weight:bold; border:none; border-radius:8px; cursor:pointer; transition:0.3s; }
form button:hover { background:#007bb5; }

/* Messages */
.msg { text-align:center; margin-bottom:15px; font-weight:bold; }
.error { color:#dc3545; }
.success { color:#28a745; }

/* Notes */
.note { font-size:12px; color:#555; margin-top:5px; }

/* Subjects grid */
.subjects-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top:5px; }
.subjects-grid label { font-weight: normal; }

/* class-subject box */
.class-box { border:1px solid #e0e0e0; padding:10px; border-radius:6px; margin-bottom:10px; background:#fafafa; }
.class-box .class-title { font-weight:bold; margin-bottom:6px; }
.class-box .subjects-list label { display:block; font-weight:normal; margin-bottom:3px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="../index.php">🏠 Home</a>
        <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
        <a href="./Teachersshow.php">👨‍🏫 Manage Teachers</a>
        <a href="../Classes/classes.php">🏫 Manage Classes</a>
        <a href="../subjects.php">📖 Manage Subjects</a>
        <a href="../add_student.php">➕ Add Student</a>
        <a href="../add_teacher.php">➕ Add Teacher</a>
        <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
        <a href="../admin_approve_results.php">✅ Approve Results</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div id="header">Edit Teacher</div>

<div class="container">
    <h2>Edit Teacher — <?= htmlspecialchars($teacher['name']) ?></h2>

    <?php if(isset($_SESSION['error'])): ?>
        <p class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <p class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form action="edit_teacher_process.php" method="POST" id="editTeacherForm">
        <input type="hidden" name="original_teacher_id" value="<?= htmlspecialchars($teacher_id) ?>">

        <input type="text" name="teacher_id" placeholder="Teacher ID" value="<?= htmlspecialchars($teacher['teacher_id']) ?>" required>
        <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($teacher['name']) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($teacher['email']) ?>" required>
        <input type="password" name="password" placeholder="Password (leave blank to keep current)">
        <input type="text" name="specialization" placeholder="Specialization" value="<?= htmlspecialchars($teacher['specialization']) ?>" required>

        <label>
            <input type="checkbox" id="is_class_teacher" name="is_class_teacher" value="1" <?= $class_teacher_class ? 'checked' : '' ?>>
            Make this teacher a Class Teacher
        </label>

        <div id="class_teacher_select" style="display:<?= $class_teacher_class ? 'block' : 'none' ?>; margin-top:10px;">
            <label for="class_teacher_class">Select Class:</label>
            <select name="class_teacher_class" id="class_teacher_class">
                <option value="">-- Select Class --</option>
                <?php
                $classQuery = $conn->query("SELECT class_id, class_name FROM classes");
                while ($row = $classQuery->fetch_assoc()) {
                    $cid = $row['class_id'];
                    $sel = ($class_teacher_class && $class_teacher_class == $cid) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($cid, ENT_QUOTES) . "' $sel>" . htmlspecialchars($row['class_name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <label>Assign Subjects to Classes:</label>
        <div class="class-subject-container">
            <?php
            $classes = $conn->query("SELECT class_id, class_name FROM classes");
            while ($class = $classes->fetch_assoc()) {
                $cid = $class['class_id'];
                $isTeaching = in_array($cid, $teaching_classes, true);
                echo "<div class='class-box'>";
                echo "<div class='class-title'><label><input type='checkbox' class='teaching_class_checkbox' name='teaching_classes[]' value='" . htmlspecialchars($cid, ENT_QUOTES) . "' " . ($isTeaching ? 'checked' : '') . "> " . htmlspecialchars($class['class_name']) . "</label></div>";
                echo "<div class='subjects-list' style='margin-left:18px; margin-top:6px;'>";
                foreach ($all_subjects as $sub) {
    $sid = $sub['subject_id'];
    $checked = '';
    if (isset($assigned_subjects_for_class[$cid]) && in_array($sid, $assigned_subjects_for_class[$cid], false)) {
        $checked = 'checked';
    }
    echo "<label><input type='checkbox' class='subject-checkbox' data-class='{$cid}' name='subjects_for_class[{$cid}][]' value='" . htmlspecialchars($sid, ENT_QUOTES) . "' $checked> " . htmlspecialchars($sub['subject_name']) . "</label>";
}

                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>

        <button type="submit">💾 Update Teacher</button>
    </form>
</div>

<script>
const classTeacherCheckbox = document.getElementById('is_class_teacher');
const classTeacherSelectDiv = document.getElementById('class_teacher_select');
const classTeacherSelect = document.getElementById('class_teacher_class');

classTeacherCheckbox.addEventListener('change', function() {
    classTeacherSelectDiv.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) classTeacherSelect.value = '';
});

document.querySelectorAll('.subject-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() {
        const classId = this.getAttribute('data-class');
        if (this.checked) {
            const classBox = document.querySelector('.class-box input.teaching_class_checkbox[value="' + classId + '"]');
            if (classBox && !classBox.checked) classBox.checked = true;
        }
    });
});

// Optional validation: ensure class checkbox if subjects selected
document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
    const classBoxes = document.querySelectorAll('.class-box');
    let valid = true;

    classBoxes.forEach(box => {
        const classCheckbox = box.querySelector('.teaching_class_checkbox');
        const subjectChecks = box.querySelectorAll('input.subject-checkbox[data-class]');
        let anySubjectChecked = false;

        subjectChecks.forEach(s => { 
            if(s.checked) anySubjectChecked = true; 
        });

        // If any subject is selected but class is not checked, invalid
        if(anySubjectChecked && !classCheckbox.checked) {
            valid = false;
            classCheckbox.focus(); // optional: focus on the checkbox
        }
    });

    if(!valid){
        e.preventDefault();
        alert("If you select subjects for a class, make sure the corresponding class checkbox is checked.");
    }
});

</script>

</body>
</html>
