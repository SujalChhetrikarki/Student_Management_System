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
        body {font-family: Arial, sans-serif; background:#f1f1f1; margin:0; padding:0;}
        #header {background:#6dd5ed; color:#fff; padding:15px; text-align:center;}
        .container {max-width:600px; margin:30px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
        h2 {text-align:center; margin-bottom:20px;}
        input, select {width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:5px;}
        button {padding:10px 20px; background:#00bfff; color:#fff; border:none; border-radius:5px; cursor:pointer;}
        button:hover {background:#0056b3;}
        .msg {text-align:center; margin-bottom:15px;}
        .error {color:red;}
        .success {color:green;}
        a {display:inline-block; margin-top:10px; text-decoration:none; color:#007bff;}
        label {display:block; margin-top:8px;}
        .sidebar {
            width: 220px;
            background: #111;
            color: #fff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }
        .sidebar h2 {text-align: center; margin-bottom: 30px; font-size: 20px; color: #00bfff;}
        .sidebar a {display: block; padding: 12px 20px; margin: 8px 15px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s;}
        .sidebar a:hover {background: #00bfff; color: #111;}
        .sidebar a.logout {background: #dc3545;}
        .sidebar a.logout:hover {background: #ff4444; color: #fff;}
    </style>
</head>
<body>
    <div id="header">
        <h1>Admin Panel - Add Teacher</h1>
    </div>

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

    <div class="container">
        <h2>Register New Teacher</h2>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <p class="msg error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="msg success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form action="add_teacher_process.php" method="POST">
            <input type="text" name="teacher_id" placeholder="Teacher ID" required>
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="specialization" placeholder="Specialization (e.g. Math, Science)" required>

            <!-- Make Class Teacher -->
            <label>
                <input type="checkbox" name="is_class_teacher" value="1"> Make this teacher a Class Teacher
            </label>

            <!-- Assign Class -->
           <label for="class_id">Assign Classes (if Class Teacher):</label>
<select name="class_id[]" multiple size="5">
    <?php
    $classQuery = $conn->query("SELECT class_id, class_name FROM classes");
    while ($row = $classQuery->fetch_assoc()) {
        echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
    }
    ?>
</select>
<p style="font-size:12px;">Hold Ctrl (Cmd on Mac) to select multiple classes</p>


            <!-- Assign Subjects -->
            <label>Assign Subjects:</label>
            <?php
            $subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
            while ($row = $subjectQuery->fetch_assoc()) {
                echo "<label><input type='checkbox' name='subjects[]' value='{$row['subject_id']}'> {$row['subject_name']}</label>";
            }
            ?>

            <button type="submit">Add Teacher</button>
        </form>
    </div>
</body>
</html>
