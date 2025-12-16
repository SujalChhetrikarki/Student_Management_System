<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Management System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

 <header style="background:#fff; display:flex; justify-content:space-between; align-items:center; padding:12px 40px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
  
  <!-- Logo Section -->
  <div style="display:flex; align-items:center; gap:10px;">
    <img src="Images/logo.jpg" alt="Logo"style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
    <span style="font-weight:bold; font-size:18px; color:#333;">Student Management</span>
  </div>

  <!-- Navigation -->
  <nav>
    <a href="index.php">Home</a>
    <a href="./Admin/admin.php">Admin</a>
    <a href="./Students/student.php">Student</a>
    <a href="./Teachers/teacher.php">Teacher</a>
  </nav>

</header>

  <!-- Main Content -->
  <main class="container">
    <h1>Welcome to Student Management System</h1>
    <div class="cards">

      <a href="./Admin/admin.php" class="card">
        <img src="Images/admin.jpg" alt="Admin">
        <h2>Admin</h2>
      </a>

      <a href="./Students/Student.php" class="card">
        <img src="Images/student.jpg" alt="Student">
        <h2>Student</h2>
      </a>

      <a href="./Teachers/Teacher.php" class="card">
        <img src="Images/teacher.jpg" alt="Teacher">
        <h2>Teacher</h2>
      </a>
      
    </div>
  </main>
<img src="Images/logo.jpg" alt="School" 
     style="width:200px; height:200px; object-fit:cover; border-radius:8px; display:block; margin: 0 auto; position: relative; top: -0.1px;">
<!-- About Section -->
<section style="background:#f0f4f8; padding:40px 20px; text-align:center; border-radius:12px; margin:40px auto; max-width:900px;">
  <h2 style="font-size:28px; color:#333; margin-bottom:15px;">About Diversity Academy</h2>
  <p style="font-size:16px; color:#555; line-height:1.6; max-width:800px; margin:0 auto 20px;">
    Diversity Academy is committed to nurturing students from all backgrounds, fostering creativity, critical thinking, and collaboration. 
    With a focus on <strong>digitalization</strong>, we integrate modern technology into learning, making education accessible, engaging, and innovative.
  </p>
  <p style="font-size:16px; color:#555; line-height:1.6; max-width:800px; margin:0 auto;">
    Our students are empowered to excel academically and personally, using digital tools for research, collaboration, and skill development. 
    We aim to prepare the next generation of leaders for a fast-changing digital world.
  </p>
</section>


  <!-- Footer will be loaded here -->
  <div id="footer"></div>

  <!-- Script to load header & footer -->
  <script>
    // Load Footer
    fetch("./Footer.php")
      .then(res => res.text())
      .then(data => document.getElementById("footer").innerHTML = data);
  </script>

</body>
</html>
