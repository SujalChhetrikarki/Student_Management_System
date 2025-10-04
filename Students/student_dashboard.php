<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

// Check DB connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch student info
$sql = "SELECT s.student_id, s.name, s.email, s.date_of_birth, s.gender, c.class_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE s.student_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Fetch attendance summary for chart
$attendance_sql = "SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date ASC";
$stmt2 = $conn->prepare($attendance_sql);
$attendance_data = [];
if ($stmt2) {
    $stmt2->bind_param("s", $_SESSION['student_id']);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($row = $res->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt2->close();
}

// Process attendance for chart
$present = 0;
$absent = 0;
$late = 0;
foreach ($attendance_data as $a) {
    switch (strtolower($a['status'])) {
        case 'present': $present++; break;
        case 'absent': $absent++; break;
        case 'late': $late++; break;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        .header {
            background: #00bfff;
            color: #fff;
            text-align: center;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .card {
            margin-bottom: 20px;
            padding: 15px 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #00bfff;
            color: #fff;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .actions a {
            background: #00bfff;
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            margin: 5px;
            transition: 0.3s;
        }
        .actions a.logout {
            background: #dc3545;
        }
        .actions a:hover {
            opacity: 0.9;
        }
canvas {
    width: 100% !important;
    height: 100% !important;
}

        h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<div class="header">
    🎓 Welcome, <?php echo htmlspecialchars($student['name']); ?>
</div>

<div class="container">
    <div class="card">
        <h2>📌 Student Information</h2>
        <table>
            <tr><th>Student ID</th><td><?php echo htmlspecialchars($student['student_id']); ?></td></tr>
            <tr><th>Name</th><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
            <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['date_of_birth']); ?></td></tr>
            <tr><th>Gender</th><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
            <tr><th>Class</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
        </table>
    </div>

 <div class="card">
    <h2>📊 Attendance Summary</h2>
    <div style="width: 300px; height: 300px; margin: 0 auto;">
        <canvas id="attendanceChart"></canvas>
    </div>
</div>


    <div class="actions">
        <a href="attendance.php">📅 View Attendance</a>
        <a href="results.php">📈 View Results</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
</div>

<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                label: 'Attendance',
                data: [<?php echo $present; ?>, <?php echo $absent; ?>, <?php echo $late; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)', // green
                    'rgba(220, 53, 69, 0.7)', // red

                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>
