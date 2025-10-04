<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';
$teacher_id = $_SESSION['teacher_id'];

// =======================
// 1. Fetch Teacher Details
// =======================
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// =======================
// 2. Fetch Notices (For Teachers or Both)
// =======================
$notice_sql = "
    SELECT title, message, created_at 
    FROM notices 
    WHERE target IN ('teachers', 'both')
    ORDER BY created_at DESC 
    LIMIT 5
";
$notices = $conn->query($notice_sql);

// =======================
// 3. Fetch Classes Assigned
// =======================
$stmt_classes = $conn->prepare("
    SELECT c.class_id, c.class_name, c.class_teacher_id
    FROM classes c
    JOIN class_teachers ct ON c.class_id = ct.class_id
    WHERE ct.teacher_id = ?
");
if (!$stmt_classes) die("SQL Error: " . $conn->error);
$stmt_classes->bind_param("s", $teacher_id);
$stmt_classes->execute();
$classes = $stmt_classes->get_result();
$stmt_classes->close();

// =======================
// 4. Fetch Subjects Assigned per Class
// =======================
$stmt_subjects = $conn->prepare("
    SELECT s.subject_id, s.subject_name, c.class_id, c.class_name
    FROM class_subject_teachers cst
    JOIN subjects s ON cst.subject_id = s.subject_id
    JOIN classes c ON cst.class_id = c.class_id
    WHERE cst.teacher_id = ?
");
if (!$stmt_subjects) die("SQL Error: " . $conn->error);
$stmt_subjects->bind_param("s", $teacher_id);
$stmt_subjects->execute();
$subjects = $stmt_subjects->get_result();
$stmt_subjects->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<style>
body { font-family: Arial; margin: 0; background: #f4f4f4; }
header { background: #007bff; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
header h1 { margin: 0; font-size: 24px; }
.logout-btn { color: #fff; background: #dc3545; padding: 5px 10px; text-decoration: none; border-radius: 5px; }
.container { max-width: 1200px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; }
h2 { margin-top: 0; }
ul { list-style: none; padding: 0; }
li { padding: 10px; border-bottom: 1px solid #ccc; display: flex; justify-content: space-between; align-items: center; }
.btn { background: #007bff; color: #fff; text-decoration: none; padding: 5px 10px; border-radius: 5px; margin-left: 5px; }
.btn:hover { background: #0056b3; }
.profile-card { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
.data-section { margin-bottom: 20px; }
.notice-card { background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
.notice-card h3 { margin: 0 0 5px; color: #856404; }
.notice-card small { color: #6c757d; font-size: 12px; }
</style>
</head>
<body>

<header>
    <h1>👨‍🏫 Teacher Dashboard</h1>
    <a href="logout.php" class="logout-btn">🚪 Logout</a>
</header>

<div class="container">

<!-- Teacher Profile -->
<section class="profile-card">
    <h2>Welcome, <?= htmlspecialchars($teacher['name']); ?> 🎉</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']); ?></p>
    <p><strong>Specialization:</strong> <?= htmlspecialchars($teacher['specialization']); ?></p>
</section>

<!-- Notices Section -->
<section class="data-section">
    <h2>📢 Latest Notices</h2>
    <?php
    if (!$notices) {
        echo "<p style='color:red;'>Error fetching notices: " . $conn->error . "</p>";
    } elseif ($notices->num_rows > 0) {
        while ($notice = $notices->fetch_assoc()) {
            echo "<div class='notice-card'>";
            echo "<h3>" . htmlspecialchars($notice['title']) . "</h3>";
            echo "<p>" . nl2br(htmlspecialchars($notice['message'])) . "</p>";
            echo "<small>🕒 Posted on " . date('d M Y, h:i A', strtotime($notice['created_at'])) . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p>No new notices.</p>";
    }
    ?>
</section>

<!-- Classes Assigned -->
<section class="data-section">
    <h2>📘 Your Classes</h2>
    <?php if ($classes->num_rows > 0): ?>
        <ul>
            <?php while ($row = $classes->fetch_assoc()): ?>
                <li>
                    <span><?= htmlspecialchars($row['class_name']); ?> (ID: <?= $row['class_id']; ?>)</span>
                    <div>
                        <a class="btn" href="view_students.php?class_id=<?= $row['class_id']; ?>">👨‍🎓 View Students</a>
                        <?php if ($row['class_teacher_id'] === $teacher_id): ?>
                            <span style="color: green;">Class Teacher ✅</span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No classes assigned yet.</p>
    <?php endif; ?>
</section>

<!-- Subjects Assigned -->
<section class="data-section">
    <h2>📖 Your Subjects</h2>
    <?php if ($subjects->num_rows > 0): ?>
        <ul>
            <?php while ($row = $subjects->fetch_assoc()): ?>
                <li>
                    <span><?= htmlspecialchars($row['subject_name']); ?> (Class: <?= htmlspecialchars($row['class_name']); ?>)</span>
                    <div>
                        <a class="btn" href="Showresults.php?subject_id=<?= $row['subject_id']; ?>">📊 Manage Results</a>
                        <?php
                        // Allow attendance button if teacher is a class teacher
                        $stmt_ct = $conn->prepare("SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ?");
                        $stmt_ct->bind_param("is", $row['class_id'], $teacher_id);
                        $stmt_ct->execute();
                        $ct_result = $stmt_ct->get_result();
                        if ($ct_result->num_rows > 0):
                        ?>
                            <a class="btn" href="manage_attendance.php?subject_id=<?= $row['subject_id']; ?>&class_id=<?= $row['class_id']; ?>">📝 Add Attendance</a>
                        <?php
                        endif;
                        $stmt_ct->close();
                        ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No subjects assigned yet.</p>
    <?php endif; ?>
</section>

</div>
</body>
</html>
