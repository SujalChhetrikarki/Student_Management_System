<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #232526, #414345);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .admin-box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0,0,0,0.3);
            width: 350px;
        }
        .admin-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .admin-box input[type="text"],
        .admin-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #bbb;
            border-radius: 6px;
            font-size: 15px;
        }
        .admin-box button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .admin-box button:hover {
            background: #0056b3;
        }
        .admin-box .note {
            font-size: 13px;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }
        .signup-btn {
            display: block;
            margin-top: 15px;
            text-align: center;
            background: #28a745;
            color: white;
            padding: 10px;
            border-radius: 6px;
            text-decoration: none;
        }
        .signup-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div id="header"></div>

<main>
    <div class="admin-box">
        <h2>Admin Login</h2>
        <a href="../index.php">
            <img src="../Images/logo.jpg" alt="Logo" 
                 style="display:block; margin:0 auto 20px auto; width:80px; height:80px; object-fit:cover; border-radius:10px;">
        </a>

        <form action="admin_login_process.php" method="post">
            <label for="admin_id">Admin ID :</label>
            <input type="text" id="admin_id" name="admin_id" required>
            
            <label for="password">Password :</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>

        <a href="#" class="signup-btn" id="secureSignUp">Sign Up as Admin</a>
        <p class="note">⚠ Only authorized administrators are allowed.</p>

        <!-- Hidden form to send secret code -->
        <form action="verify_admin_code.php" method="POST" id="codeForm" style="display:none;">
            <input type="hidden" name="code" id="hiddenCode">
        </form>
    </div>
</main>

<div id="footer"></div>

<script>
    // Load header and footer
    fetch("adminheader.php")
      .then(res => res.text())
      .then(data => document.getElementById("header").innerHTML = data);

    fetch("adminfooter.php")
      .then(res => res.text())
      .then(data => document.getElementById("footer").innerHTML = data);

    // Secure Admin Signup Code
    document.getElementById("secureSignUp").addEventListener("click", function(e) {
        e.preventDefault();
        const code = prompt("🔒 Enter Admin Access Code:");
        if (code) {
            document.getElementById("hiddenCode").value = code;
            document.getElementById("codeForm").submit();
        }
    });
</script>

</body>
</html>
