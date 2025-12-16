<?php
session_start();
include '../Database/db_connect.php'; // make sure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = trim($_POST['admin_id']);
    $password = trim($_POST['password']);

    // Correct table name = admin (not admins)
    $stmt = $conn->prepare("SELECT admin_id, name, password FROM admins WHERE admin_id = ?");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        // Verify hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id']   = $row['admin_id'];
            $_SESSION['admin_name'] = $row['name']; // ✅ store admin name

            // Redirect to home
            header("Location: index.php");
            exit();
        } else {
            header("Location: admin.php?error=wrong_password");
            exit();
        }
    } else {
        header("Location: admin.php?error=not_found");
        exit();
    }
}
?>
