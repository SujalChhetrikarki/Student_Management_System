<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

// ✅ Check DB connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// ✅ Fetch Notices
$notice_sql = "
    SELECT title, message, created_at 
    FROM notices 
    WHERE target IN ('students', 'both')
    ORDER BY created_at DESC 
    LIMIT 5
";
$notices = $conn->query($notice_sql);

// ✅ Fetch student info
$sql = "SELECT s.student_id, s.name, s.email, s.date_of_birth, s.gender, c.class_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE s.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// ✅ Fetch attendance summary
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

$present = $absent = $late = 0;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Management System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #4facfe 0%, #00f2fe 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 1.5rem;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        
        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            padding: 1rem;
            margin: 0.5rem 0;
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar a.logout {
            margin-top: 2rem;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .sidebar a.logout:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .main {
            margin-left: 260px;
            padding: 2rem;
        }
        
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: #fff;
            padding: 1.5rem 2rem;
            border-radius: 1rem;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }
        
        .card {
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card h2 {
            margin-top: 0;
            color: #4facfe;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 3px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .notice-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #4facfe;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.1);
            transition: all 0.3s ease;
        }
        
        .notice-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.2);
        }
        
        .notice-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #4facfe;
        }
        
        .notice-card p {
            margin: 0.5rem 0;
            color: #475569;
            line-height: 1.6;
        }
        
        .notice-card small {
            color: #64748b;
            font-size: 0.85rem;
        }
        
        .chart-container {
            width: 100%;
            max-width: 400px;
            height: 400px;
            margin: 2rem auto;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Dashboard</h2>
        <a href="student_dashboard.php">🏠 Home</a>
        <a href="attendance.php">📅 Attendance</a>
        <a href="results.php">📊 Results</a>
        <a href="profile.php">👤 Profile</a>
        <a href="change_password.php">🔑 Change Password</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="header">🎓 Welcome, <?php echo htmlspecialchars($student['name']); ?></div>

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
            <h2>📢 Latest Notices</h2>
            <?php
            if ($notices && $notices->num_rows > 0) {
                while ($notice = $notices->fetch_assoc()) {
                    echo "<div class='notice-card'>";
                    echo "<h3>" . htmlspecialchars($notice['title']) . "</h3>";
                    echo "<p>" . nl2br(htmlspecialchars($notice['message'])) . "</p>";
                    echo "<small>🕒 " . date('d M Y, h:i A', strtotime($notice['created_at'])) . "</small>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color: #64748b; text-align: center; padding: 2rem;'>No new notices at this time.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
