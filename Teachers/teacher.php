<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - Student Management System</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }
        
        #header, #footer, main {
            position: relative;
            z-index: 1;
        }
        
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 3rem;
            border-radius: 2rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.6s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }
        
        .login-header img:hover {
            transform: scale(1.05) rotate(5deg);
        }
        
        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #f5576c;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #f5576c;
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #f5576c;
            border: none;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }
        
        .btn-login:hover {
            background: #e63950;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <main>
        <div class="login-container">
            <div class="login-header">
                <a href="../index.php">
                    <img src="../Images/logo.jpg" alt="Logo">
                </a>
                <h2>Teacher Login</h2>
                <p>Access your teaching portal</p>
            </div>

            <form action="teacher_login.php" method="post">
                <div class="form-group">
                    <label for="teacher_id">Teacher ID</label>
                    <input type="text" id="teacher_id" name="teacher_id" class="form-control" required placeholder="Enter your teacher ID">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </main>

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
