<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject_name = trim($_POST['subject_name']);
    $teacher_id   = trim($_POST['teacher_id']);
    $class_id     = trim($_POST['class_id']);

    if (!empty($subject_name) && !empty($teacher_id) && !empty($class_id)) {

        // Check if subject exists
        $stmt_check = $conn->prepare("SELECT subject_id FROM subjects WHERE subject_name = ?");
        if (!$stmt_check) { die("SQL Error: " . $conn->error); }
        $stmt_check->bind_param("s", $subject_name);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $subject = $res_check->fetch_assoc();
            $subject_id = $subject['subject_id'];
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
            if (!$stmt_insert) { die("SQL Error: " . $conn->error); }
            $stmt_insert->bind_param("s", $subject_name);
            $stmt_insert->execute();
            $subject_id = $stmt_insert->insert_id;
            $stmt_insert->close();
        }
        $stmt_check->close();

        // Assign subject to teacher & class
        $stmt_assign = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id, class_id) VALUES (?, ?, ?)");
        if (!$stmt_assign) { die("SQL Error: " . $conn->error); }
        $stmt_assign->bind_param("sii", $teacher_id, $subject_id, $class_id);

        if ($stmt_assign->execute()) {
            $_SESSION['success'] = "✅ Subject assigned successfully!";
        } else {
            $_SESSION['error'] = "❌ Database Error: " . $stmt_assign->error;
        }
        $stmt_assign->close();

    } else {
        $_SESSION['error'] = "⚠️ Please fill all required fields.";
    }

    header("Location: assign_subject.php");
    exit;
}

// Fetch teachers and classes
$teachers = $conn->query("SELECT teacher_id, name FROM teachers");
$classes  = $conn->query("SELECT class_id, class_name FROM classes");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Subject</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }

    body {
        display: flex;
        min-height: 100vh;
        background: #f4f6f9;
    }

    /* Sidebar */
    .sidebar {
        width: 220px;
        background: #111;
        color: #fff;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        padding-top: 20px;
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
    }

    .sidebar h2 {
        text-align: center;
        color: #00bfff;
        margin-bottom: 30px;
        font-size: 20px;
    }

    .sidebar a {
        display: block;
        padding: 12px 20px;
        margin: 5px 15px;
        background: #222;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        transition: 0.3s;
    }

    .sidebar a:hover { background: #00bfff; color: #111; }
    .sidebar a.logout { background: #dc3545; }
    .sidebar a.logout:hover { background: #ff4444; color: #fff; }

    /* Header */
    .header {
        position: fixed;
        top: 0;
        left: 220px;
        right: 0;
        height: 80px;
        background: #00bfff;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 100;
    }

    .header h1 { font-size: 24px; }

    /* Main content */
    .main {
        margin-left: 220px;
        width: calc(100% - 220px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding-top: 120px;
        padding-bottom: 40px;
    }

    .container {
        width: 100%;
        max-width: 600px;
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }

    h2 { margin-bottom: 20px; color: #333; }

    form label { display: block; margin-top: 10px; font-weight: bold; }
    form input, form select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
    button { width: 100%; padding: 12px; margin-top: 20px; background: #00bfff; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
    button:hover { background: #0056b3; }

    .success { color: green; margin-bottom: 15px; }
    .error { color: red; margin-bottom: 15px; }

    .back { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">🏠 Home</a>
    <a href="../Admin/Manage_student/Managestudent.php">📚 Manage Students</a>
    <a href="../Admin/Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
    <a href="../Admin/Classes/classes.php">🏫 Manage Classes</a>
    <a href="subjects.php">📖 Manage Subjects</a>
    <a href="../Admin/add_student.php">➕ Add Student</a>
    <a href="../Admin/add_teacher.php">➕ Add Teacher</a>
    <a href="../Admin/Add_exam/add_exam.php">➕ Add Exam</a>
    <a href="../Admin/admin_approve_results.php">✅ Approve Results</a>
    <a href="../Admin/logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">
    <h1>📖 Assign Subject to Teacher & Class</h1>
</div>

<div class="main">
    <div class="container">
        <?php if(isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Subject Name:</label>
            <input type="text" name="subject_name" placeholder="Enter Subject (e.g. Mathematics)" required>

            <label>Assign to Teacher:</label>
            <select name="teacher_id" required>
                <option value="">-- Select Teacher --</option>
                <?php while($t = $teachers->fetch_assoc()): ?>
                    <option value="<?php echo $t['teacher_id']; ?>"><?php echo $t['name']; ?> (<?php echo $t['teacher_id']; ?>)</option>
                <?php endwhile; ?>
            </select>

            <label>Assign to Class:</label>
            <select name="class_id" required>
                <option value="">-- Select Class --</option>
                <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?php echo $c['class_id']; ?>"><?php echo $c['class_name']; ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Assign Subject</button>
        </form>

        <a href="subjects.php" class="back">⬅ Back to Subjects</a>
    </div>
</div>

</body>
</html>
