<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Teacher</title>
<style>
/* Global */
* { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
body { background: #f4f6f9; display: flex; min-height: 100vh; }

/* Sidebar */
.sidebar {
    width: 220px;
    background: #111;
    color: #fff;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 20px;
}
.sidebar h2 { text-align:center; color:#00bfff; margin-bottom:30px; font-size:20px; }
.sidebar a {
    display:block; padding:10px 20px; margin:6px 15px;
    background:#222; color:#fff; text-decoration:none; border-radius:6px;
    transition:0.3s;
}
.sidebar a:hover { background:#00bfff; color:#111; }
.sidebar a.logout { background:#dc3545; }
.sidebar a.logout:hover { background:#ff4444; color:#fff; }

/* Header */
#header {
    position: fixed;
    top: 0;
    left: 220px;
    right: 0;
    height: 45px;
    background: #00bfff;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 100;
}

/* Container */
.container {
    max-width: 850px;
    width: 100%;
    background: #fff;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    margin: 60px auto 40px auto;
}

.container h2 { text-align:center; margin-bottom:25px; color:#333; }

/* Form Styling */
form label { display:block; margin-top:12px; font-weight:bold; color:#555; }
form input, form select, form button { font-family: inherit; }
form input, form select { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
form input[type="checkbox"] { width:auto; margin-right:8px; }
form button {
    width:100%;
    padding:12px;
    margin-top:20px;
    background:#00bfff;
    color:#fff;
    font-size:16px;
    font-weight:bold;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
}
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
    <a href="./index.php">🏠 Home</a>
    <a href="./Manage_student/Managestudent.php">📚 Manage Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
    <a href="./classes/classes.php">🏫 Manage Classes</a>
    <a href="./subjects.php">📖 Manage Subjects</a>
    <a href="add_student.php">➕ Add Student</a>
    <a href="./add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
    <a href="./admin_approve_results.php">✅ Approve Results</a>
    <a href="./logout.php" class="logout">🚪 Logout</a>
</div>

<div id="header">Add New Teacher</div>

<div class="container">
    <h2>Register Teacher</h2>

    <?php if(isset($_SESSION['error'])): ?>
        <p class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <p class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form action="add_teacher_process.php" method="POST" id="addTeacherForm">
        <input type="text" name="teacher_id" placeholder="Teacher ID" required>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="specialization" placeholder="Specialization (e.g. Math, Science)" required>

        <!-- Class Teacher Checkbox -->
        <label style="margin-top:12px;">
            <input type="checkbox" id="is_class_teacher" name="is_class_teacher" value="1">
            Make this teacher a Class Teacher (only one class allowed)
        </label>

        <!-- Class Teacher select (single class) -->
        <div id="class_teacher_select" style="display:none; margin-top:10px;">
            <label for="class_teacher_class">Select Class (Only 1 - Class Teacher):</label>
            <select name="class_teacher_class" id="class_teacher_class">
                <option value="">-- Select Class --</option>
                <?php
                $classQuery = $conn->query("SELECT class_id, class_name FROM classes");
                while ($row = $classQuery->fetch_assoc()) {
                    echo "<option value='".htmlspecialchars($row['class_id'], ENT_QUOTES)."'>".htmlspecialchars($row['class_name'])."</option>";
                }
                ?>
            </select>
            <p class="note">If checked, teacher will be class teacher of the selected class only.</p>
        </div>

        <!-- Assign Subjects to Classes (teaching roles) -->
        <label style="margin-top:15px;">Assign Subjects to Classes (teaching roles):</label>
        <div class="class-subject-container">
            <?php
            // Fetch classes and subjects
            $classes = $conn->query("SELECT class_id, class_name FROM classes");
            $subjectsAll = [];
            $subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
            while ($s = $subjectQuery->fetch_assoc()) {
                $subjectsAll[] = $s;
            }

            while ($class = $classes->fetch_assoc()) {
                $cid = (int)$class['class_id'];
                echo "<div class='class-box'>";
                echo "<div class='class-title'><label><input type='checkbox' class='teaching_class_checkbox' name='teaching_classes[]' value='{$cid}'> " . htmlspecialchars($class['class_name']) . "</label></div>";
                echo "<div class='subjects-list' style='margin-left:18px; margin-top:6px;'>";
                foreach ($subjectsAll as $sub) {
                    $sid = (int)$sub['subject_id'];
                    echo "<label><input type='checkbox' name='subjects_for_class[{$cid}][]' value='{$sid}'> " . htmlspecialchars($sub['subject_name']) . "</label>";
                }
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>

        <button type="submit">➕ Add Teacher</button>
    </form>

    <p class="note">Note: Class Teacher role is limited to only one selected class. The teacher can still teach selected subjects in multiple classes.</p>
</div>

<script>
// Show/hide class teacher select & enforce required when checked
const classTeacherCheckbox = document.getElementById('is_class_teacher');
const classTeacherSelectDiv = document.getElementById('class_teacher_select');
const classTeacherSelect = document.getElementById('class_teacher_class');

classTeacherCheckbox.addEventListener('change', function() {
    classTeacherSelectDiv.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        classTeacherSelect.value = '';
    }
});

// Optional client-side validation: if any subjects are selected for a class, ensure that class's checkbox is also checked
document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
    const classBoxes = document.querySelectorAll('.class-box');
    let valid = true;

    classBoxes.forEach(box => {
        const classCheckbox = box.querySelector('.teaching_class_checkbox');
        // if a subject checkbox is checked but class checkbox is not, mark valid false
        const subjectChecks = box.querySelectorAll('input[type="checkbox"][name^="subjects_for_class"]');
        let anySubject = false;
        subjectChecks.forEach(s => { if (s.checked) anySubject = true; });
        if (anySubject && !classCheckbox.checked) {
            valid = false;
            classCheckbox.focus();
        }
    });

    if (!valid) {
        e.preventDefault();
        alert("If you select subjects for a class, make sure the corresponding class checkbox is checked (so the teacher is marked as teaching that class).");
    }
});
</script>

</body>
</html>
