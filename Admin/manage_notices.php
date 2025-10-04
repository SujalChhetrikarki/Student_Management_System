<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php'; // ✅ DB connection

// ✅ Add Notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target = $_POST['target'];

    if ($title && $message) {
        $stmt = $conn->prepare("INSERT INTO notices (title, message, target) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $message, $target);

        if ($stmt->execute()) {
            // ✅ Redirect to avoid resubmission
            header("Location: manage_notices.php?success=1");
            exit;
        } else {
            header("Location: manage_notices.php?error=" . urlencode("Database error: " . $stmt->error));
            exit;
        }
    } else {
        header("Location: manage_notices.php?error=" . urlencode("Please fill in all fields."));
        exit;
    }
}

// ✅ Delete Notice
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM notices WHERE notice_id = $id")) {
        header("Location: manage_notices.php?deleted=1");
        exit;
    } else {
        header("Location: manage_notices.php?error=" . urlencode("Error deleting notice."));
        exit;
    }
}

// ✅ Feedback messages
$success = $error = "";
if (isset($_GET['success'])) $success = "✅ Notice added successfully!";
if (isset($_GET['deleted'])) $success = "🗑️ Notice deleted successfully!";
if (isset($_GET['error'])) $error = htmlspecialchars($_GET['error']);

// ✅ Fetch all notices
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Notices</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
h2 {
    color: #00bfff;
    text-align: center;
    margin-bottom: 20px;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input[type="text"], textarea, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    background: #00bfff;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    opacity: 0.85;
}
.notice-list {
    margin-top: 30px;
}
.notice-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: #fafafa;
}
.notice-item h4 {
    margin: 0 0 5px;
    color: #333;
}
.notice-item small {
    color: #666;
}
.notice-item p {
    margin: 8px 0;
}
.notice-item a {
    color: #dc3545;
    text-decoration: none;
    font-weight: bold;
}
.notice-item a:hover {
    text-decoration: underline;
}
.alert {
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
}
.success {
    background: #d4edda;
    color: #155724;
}
.error {
    background: #f8d7da;
    color: #721c24;
}
.back {
    display: inline-block;
    margin-bottom: 20px;
    text-decoration: none;
    color: #00bfff;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back">⬅ Back to Dashboard</a>
    <h2>📢 Manage Notices</h2>

    <?php if ($success) echo "<div class='alert success'>$success</div>"; ?>
    <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>

    <form method="POST">
        <input type="text" name="title" placeholder="Enter notice title" required>
        <textarea name="message" rows="4" placeholder="Write your notice message here..." required></textarea>
        <select name="target" required>
            <option value="both">All (Students & Teachers)</option>
            <option value="students">Students Only</option>
            <option value="teachers">Teachers Only</option>
        </select>
        <button type="submit" name="add_notice">Post Notice</button>
    </form>

    <div class="notice-list">
        <h3>📄 Existing Notices</h3>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($n = $result->fetch_assoc()): ?>
                <div class="notice-item">
                    <h4><?= htmlspecialchars($n['title']); ?></h4>
                    <small>🕒 <?= htmlspecialchars($n['created_at']); ?> | 🎯 <?= ucfirst($n['target']); ?></small>
                    <p><?= nl2br(htmlspecialchars($n['message'])); ?></p>
                    <a href="?delete=<?= $n['notice_id']; ?>" onclick="return confirm('Delete this notice?')">🗑️ Delete</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No notices available.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
