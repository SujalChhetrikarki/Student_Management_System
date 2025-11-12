<?php

// Extend session lifetime (e.g. 7 days)
$lifetime = 60 * 60 * 24 * 7; // 7 days

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'domain' => '', 
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]); 
session_start();
include '../Database/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = trim($_POST['admin_id']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT admin_id, name, password FROM admins WHERE admin_id = ?");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {

        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id']   = $row['admin_id'];
            $_SESSION['admin_name'] = $row['name'];

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
