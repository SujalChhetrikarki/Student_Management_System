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
    height: 45px;  /* thinner header */
    background: #00bfff;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;  /* smaller font */
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 100;
}

/* Container */
.container {
    max-width: 650px;
    width: 100%;
    background: #fff;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    margin: 60px auto 40px auto; /* top, horizontal center, bottom */
}


.container h2 { text-align:center; margin-bottom:25px; color:#333; }

/* Form Styling */
form label { display:block; margin-top:12px; font-weight:bold; color:#555; }
form input, form select { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:1px solid #ccc; font-size:14px; }
form input[type="checkbox"] { width:auto; margin-right:5px; }
form select[multiple] { height:auto; }
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
.subjects-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-top:5px; }
.subjects-grid label { font-weight: normal; }
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

    <form action="add_teacher_process.php" method="POST">
        <input type="text" name="teacher_id" placeholder="Teacher ID" required>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="specialization" placeholder="Specialization (e.g. Math, Science)" required>

        <label><input type="checkbox" name="is_class_teacher" value="1"> Make this teacher a Class Teacher</label>

        <label for="class_id">Assign Classes (if Class Teacher):</label>
        <select name="class_id[]" multiple>
            <?php
            $classQuery = $conn->query("SELECT class_id, class_name FROM classes");
            while ($row = $classQuery->fetch_assoc()) {
                echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
            }
            ?>
        </select>
        <p class="note">Hold Ctrl (Cmd on Mac) to select multiple classes</p>

        <label>Assign Subjects:</label>
        <div class="subjects-grid">
        <?php
        $subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
        while ($row = $subjectQuery->fetch_assoc()) {
            echo "<label><input type='checkbox' name='subjects[]' value='{$row['subject_id']}'> {$row['subject_name']}</label>";
        }
        ?>
        </div>

        <button type="submit">➕ Add Teacher</button>
    </form>
</div>
</body>
</html>
