<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

// ✅ Total students and teachers
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$total_teachers = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;

// ✅ Pass/Fail trend by exam
$sql_exams = "SELECT exam_id, exam_date FROM exams ORDER BY exam_date ASC";
$result_exams = $conn->query($sql_exams);

$exam_dates = [];
$pass_counts = [];
$fail_counts = [];

while ($exam = $result_exams->fetch_assoc()) {
    $exam_dates[] = $exam['exam_date'];

    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN average_marks >= 40 THEN 1 ELSE 0 END) AS pass_count,
            SUM(CASE WHEN average_marks < 40 THEN 1 ELSE 0 END) AS fail_count
        FROM results 
        WHERE exam_id=?
    ");
    $stmt->bind_param("i", $exam['exam_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $pass_counts[] = $res['pass_count'] ?? 0;
    $fail_counts[] = $res['fail_count'] ?? 0;
}

// ✅ Upcoming birthdays (next 7 days only + class name)
$sql_birthdays = "
    SELECT s.name, s.date_of_birth, c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    WHERE DATE_FORMAT(s.date_of_birth, '%m-%d') 
          BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
          AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
    ORDER BY DATE_FORMAT(s.date_of_birth, '%m-%d') ASC
";
$birthdays = $conn->query($sql_birthdays);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ===== General Layout ===== */
body { margin: 0; font-family: Arial, sans-serif; background: #f4f6f9; display: flex; }
.sidebar { width: 220px; background: #111; color: #fff; height: 100vh; position: fixed; left: 0; top: 0; padding-top: 20px; }
.sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 20px; color: #00bfff; }
.sidebar a { display: block; padding: 12px 20px; margin: 8px 15px; background: #222; color: #fff; text-decoration: none; border-radius: 6px; transition: 0.3s; }
.sidebar a:hover { background: #00bfff; color: #111; }
.sidebar a.logout { background: #dc3545; }
.sidebar a.logout:hover { background: #ff4444; color: #fff; }

.main { margin-left: 220px; padding: 20px; flex: 1; }
.header { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 22px; color: #333; }

/* ===== Cards ===== */
.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); text-align: center; transition: transform 0.3s; }
.card:hover { transform: translateY(-5px); }
.card h3 { margin: 10px 0; font-size: 18px; color: #333; }
.card p { font-size: 16px; font-weight: bold; color: #111; }

/* ===== Birthday List Styling ===== */
.birthday-card ul { list-style: none; padding: 0; margin: 15px 0 0; }
.birthday-list { display: flex; flex-direction: column; gap: 10px; }

.birthday-item { display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 10px 12px; border-radius: 8px; font-size: 14px; transition: 0.3s ease; }
.birthday-item:hover { background: #eef5ff; }

.birthday-info { font-weight: 600; color: #333; }
.bday-class { font-size: 13px; color: #666; }

.birthday-date { text-align: right; font-size: 13px; color: #444; }
.date { margin-right: 6px; font-weight: 500; }

.today-badge { background: #ff4444; color: #fff; font-size: 12px; padding: 3px 7px; border-radius: 6px; }
.upcoming-badge { background: #00bfff; color: #fff; font-size: 12px; padding: 3px 7px; border-radius: 6px; }

/* ===== Charts ===== */
.chart-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="index.php">🏠 Home</a>
  <a href="../Admin/Manage_student/Managestudent.php">📚 Manage Students</a>
  <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
  <a href="./classes/classes.php">🏫 Manage Classes</a>
  <a href="subjects.php">📖 Manage Subjects</a>
  <a href="add_student.php">➕ Add Student</a>
  <a href="add_teacher.php">➕ Add Teacher</a>
  <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
  <a href="admin_approve_results.php">✅ Approve Results</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="main">
  <div class="header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']); ?> 👋</h1>
  </div>

  <div class="cards">
    <div class="card">
      <h3>Total Students</h3>
      <p><?= $total_students ?></p>
    </div>
    <div class="card">
      <h3>Total Teachers</h3>
      <p><?= $total_teachers ?></p>
    </div>
    <div class="card">
  <h3>📢 Manage Notices</h3>
  <p><a href="manage_notices.php" style="
      background:#00bfff; color:white; text-decoration:none; 
      padding:8px 14px; border-radius:6px; display:inline-block;">
      ➕ Add / Manage Notices
  </a></p>
</div>


    <div class="card birthday-card">
      <h3>🎂 Birthdays This Week</h3>
      <?php if ($birthdays->num_rows > 0): ?>
          <ul class="birthday-list">
              <?php while ($b = $birthdays->fetch_assoc()): 
                  $dob = strtotime($b['date_of_birth']);
                  $dobThisYear = date("Y") . "-" . date("m-d", $dob);

                  // Handle passed dates
                  $nextBirthday = (strtotime($dobThisYear) < strtotime(date("Y-m-d"))) 
                                  ? strtotime("+1 year", strtotime($dobThisYear)) 
                                  : strtotime($dobThisYear);

                  $daysLeft = (int)(($nextBirthday - time()) / (60 * 60 * 24));
                  $isToday = ($daysLeft === 0);
              ?>
                  <li class="birthday-item <?= $isToday ? 'today' : '' ?>">
                      <div class="birthday-info">
                          <?= htmlspecialchars($b['name']) ?> 
                          <span class="bday-class">(<?= htmlspecialchars($b['class_name']) ?>)</span>
                      </div>
                      <div class="birthday-date">
                          <span class="date"><?= date("M d", $dob) ?></span>
                          <span class="<?= $isToday ? 'today-badge' : 'upcoming-badge' ?>">
                              <?= $isToday ? '🎉 Today!' : "in $daysLeft days" ?>
                          </span>
                      </div>
                  </li>
              <?php endwhile; ?>
          </ul>
      <?php else: ?>
          <p>No birthdays this week 🎉</p>
      <?php endif; ?>
    </div>
  </div>
  

  <div class="chart-container">
    <h3>📊 Students Pass vs Fail Trend</h3>
    <canvas id="passFailLineChart" height="150"></canvas>
  </div>
</div>

<script>
const ctx = document.getElementById('passFailLineChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($exam_dates) ?>,
        datasets: [
            {
                label: 'Pass',
                data: <?= json_encode($pass_counts) ?>,
                borderColor: '#4CAF50',
                backgroundColor: '#4CAF50',
                fill: false,
                tension: 0.2
            },
            {
                label: 'Fail',
                data: <?= json_encode($fail_counts) ?>,
                borderColor: '#FF4444',
                backgroundColor: '#FF4444',
                fill: false,
                tension: 0.2
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Pass/Fail Trend by Exam' }
        },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>
