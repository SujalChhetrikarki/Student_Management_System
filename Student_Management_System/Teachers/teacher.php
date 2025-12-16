<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #6dd5ed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        #header, #footer {
            background: #fff;
            color: #333;
            padding: 15px;
            text-align: center;
        }
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #aaa;
            width: 300px;
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <!-- Header will be loaded -->
    <div id="header"></div>

    <!-- Main Content -->
    <main>
        <div class="login-box">
            <h2>Teacher Login</h2>
            <form action="teacher_login.php" method="post">
                <label for="teacher_id">ID :</label>
                <input type="text" id="teacher_id" name="teacher_id" required>

                <label for="password">Password :</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </div>
    </main>

    <!-- Footer will be loaded -->
    <div id="footer"></div>

    <!-- Script to load header & footer -->
    <script>
        // Load Header
        fetch("../Header.php")
          .then(res => res.text())
          .then(data => document.getElementById("header").innerHTML = data);

        // Load Footer
        fetch("../Footer.php")
          .then(res => res.text())
          .then(data => document.getElementById("footer").innerHTML = data);
    </script>
</body>
</html>
