<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

if (!isset($_GET['teacher_id'])) {
    header("Location: teachers.php");
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
if (!$teacher) {
    die("Teacher not found.");
}

// Fetch assigned classes
$sql_classes = "SELECT class_id FROM class_teachers WHERE teacher_id = ?";
$stmt_classes = $conn->prepare($sql_classes);
$stmt_classes->bind_param("s", $teacher_id);
$stmt_classes->execute();
$res_classes = $stmt_classes->get_result();
$assigned_classes = [];
while ($row = $res_classes->fetch_assoc()) {
    $assigned_classes[] = $row['class_id'];
}

// Fetch assigned subjects
$sql_subjects = "SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?";
$stmt_subjects = $conn->prepare($sql_subjects);
$stmt_subjects->bind_param("s", $teacher_id);
$stmt_subjects->execute();
$res_subjects = $stmt_subjects->get_result();
$assigned_subjects = [];
while ($row = $res_subjects->fetch_assoc()) {
    $assigned_subjects[] = $row['subject_id'];
}

// Fetch all classes and subjects
$all_classes = $conn->query("SELECT class_id, class_name FROM classes");
$all_subjects = $conn->query("SELECT subject_id, subject_name FROM subjects");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            margin: 40px auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 600px;
            animation: fadeIn 0.8s ease-in-out;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: #444;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            transition: border 0.3s;
        }
        input:focus {
            border: 1px solid #667eea;
            box-shadow: 0 0 8px rgba(102,126,234,0.3);
        }
        .checkbox-group {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #ddd;
            max-height: 180px;
            overflow-y: auto;
        }
        .checkbox-group label {
            font-weight: normal;
            margin: 6px 0;
            display: block;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #667eea;
            font-weight: bold;
            transition: 0.3s;
            text-align: center;
            width: 100%;
        }
        a:hover {
            color: #0056b3;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
<div class="container">
    <h2>✏ Edit Teacher - <?php echo htmlspecialchars($teacher['name']); ?></h2>

    <form action="edit_teacher_process.php" method="POST">
        <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">

        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>

        <label>Password: (leave blank to keep current)</label>
        <input type="password" name="password">

        <label>Specialization:</label>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($teacher['specialization']); ?>" required>

        <label>
            <input type="checkbox" name="is_class_teacher" value="1" <?php if($teacher['is_class_teacher']) echo 'checked'; ?>> Class Teacher
        </label>

        <label>Assign Classes:</label>
        <div class="checkbox-group">
            <?php while ($row = $all_classes->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="classes[]" value="<?php echo $row['class_id']; ?>" 
                    <?php if(in_array($row['class_id'], $assigned_classes)) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($row['class_name']); ?>
                </label>
            <?php endwhile; ?>
        </div>

        <label>Assign Subjects:</label>
        <div class="checkbox-group">
            <?php while ($row = $all_subjects->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="subjects[]" value="<?php echo $row['subject_id']; ?>" 
                    <?php if(in_array($row['subject_id'], $assigned_subjects)) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($row['subject_name']); ?>
                </label>
            <?php endwhile; ?>
        </div>

        <button type="submit">💾 Update Teacher</button>
    </form>
    <a href="Teachersshow.php">⬅ Back to Manage Teachers</a>
</div>
</body>
</html>
