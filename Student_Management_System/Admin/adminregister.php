<?php
session_start();
include '../Database/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = trim($_POST['admin_id']);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.location='admin_register.php';</script>";
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO admins (admin_id, name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $admin_id, $name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin registered successfully!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#e9ecef;
            display:flex;
            flex-direction:column;
            min-height:100vh;
            margin:0;
        }
        main {
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .register-box {
            background:#fff;
            padding:30px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.2);
            width:350px;
        }
        .register-box h2 {
            text-align:center;
            margin-bottom:20px;
        }
        .register-box input {
            width:100%;
            padding:10px;
            margin:8px 0;
            border:1px solid #ccc;
            border-radius:5px;
        }
        .register-box button {
            width:100%;
            padding:10px;
            background:#28a745;
            border:none;
            color:white;
            font-size:16px;
            border-radius:5px;
            cursor:pointer;
        }
        .register-box button:hover {
            background:#218838;
        }
        .register-box a {
            display:block;
            text-align:center;
            margin-top:10px;
            color:#007bff;
            text-decoration:none;
        }
        .register-box a:hover {
            text-decoration:underline;
        }
    </style>
</head>
<body>
    <!-- Header will load here -->
    <div id="header"></div>

    <main>
        <div class="register-box">
            <h2>Admin Registration</h2>
            <form method="post" action="">
                <input type="text" name="admin_id" placeholder="Admin ID" required>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Register</button>
            </form>
            <a href="admin.php">Already have an account? Login</a>
        </div>
    </main>

    <!-- Footer will load here -->
    <div id="footer"></div>

    <script>
        // Load Header
        fetch("adminheader.php")
          .then(res => res.text())
          .then(data => document.getElementById("header").innerHTML = data);

        // Load Footer
        fetch("adminfooter.php")
          .then(res => res.text())
          .then(data => document.getElementById("footer").innerHTML = data);
    </script>
</body>
</html>
