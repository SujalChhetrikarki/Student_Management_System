<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

// Handle adding new subject
if (isset($_POST['add_subject'])) {
    $new_subject = trim($_POST['new_subject']);
    if (!empty($new_subject)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
        $stmt->bind_param("s", $new_subject);
        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Subject added successfully!";
        } else {
            $_SESSION['error'] = "❌ Error adding subject: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: subjects.php");
    exit;
}

// Handle deleting a subject
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM subjects WHERE subject_id = $del_id");
    $_SESSION['success'] = "🗑 Subject deleted successfully!";
    header("Location: subjects.php");
    exit;
}

// Fetch all subjects
$subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }

    body { display: flex; min-height: 100vh; background: #f4f6f9; }

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
        top: 0; left: 0; bottom: 0;
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
        padding-top: 120px;
        padding-bottom: 40px;
    }

    .container {
        width: 100%;
        max-width: 700px;
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }

    h2 { margin-bottom: 20px; color: #333; }

    form label { display: block; margin-top: 10px; font-weight: bold; }
    form input { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
    button { width: 100%; padding: 12px; margin-top: 20px; background: #00bfff; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
    button:hover { background: #0056b3; }

    .success { color: green; margin-bottom: 15px; }
    .error { color: red; margin-bottom: 15px; }

    .back { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }

    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #00bfff; color: #fff; }
    tr:hover { background: #f1f1f1; }
    a.delete { background: #dc3545; color: #fff; padding: 5px 10px; border-radius:5px; text-decoration:none; }
    a.delete:hover { background: #c82333; }
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
    <h1>📖 Subject Management</h1>
</div>

<div class="main">
    <div class="container">

        <?php if(isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Add new subject -->
        <form method="POST">
            <label>Add New Subject:</label>
            <input type="text" name="new_subject" placeholder="Enter subject name" required>
            <button type="submit" name="add_subject">➕ Add Subject</button>
        </form>

        <!-- List subjects -->
        <table>
            <tr>
                <th>ID</th>
                <th>Subject Name</th>
                <th>Action</th>
            </tr>
            <?php if ($subjects->num_rows > 0): ?>
                <?php while($s = $subjects->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $s['subject_id']; ?></td>
                        <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
                        <td>
                            <a href="subjects.php?delete_id=<?php echo $s['subject_id']; ?>" 
                               onclick="return confirm('Are you sure to delete this subject?');" 
                               class="delete">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center; color:#777;">No subjects found</td></tr>
            <?php endif; ?>
        </table>

    </div>
</div>

</body>
</html>
