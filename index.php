<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Management System - Diversity Academy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    .hero-section {
      position: relative;
      padding: 5rem 2rem;
      text-align: center;
      color: white;
      overflow: hidden;
    }
    
    .hero-content {
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      z-index: 2;
    }
    
    .hero-title {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 1rem;
      line-height: 1.2;
      animation: fadeInUp 0.8s ease;
    }
    
    .hero-subtitle {
      font-size: 1.25rem;
      margin-bottom: 3rem;
      opacity: 0.95;
      animation: fadeInUp 1s ease 0.2s both;
    }
    
    .about-section {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      padding: 4rem 2rem;
      margin: 4rem auto;
      max-width: 1000px;
      border-radius: 2rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    }
    
    .about-content {
      text-align: center;
    }
    
    .about-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1.5rem;
    }
    
    .about-text {
      font-size: 1.1rem;
      color: var(--text-secondary);
      line-height: 1.8;
      max-width: 800px;
      margin: 0 auto 1.5rem;
    }
    
    .logo-showcase {
      text-align: center;
      margin: 3rem 0;
      animation: fadeInUp 1.2s ease 0.4s both;
    }
    
    .logo-showcase img {
      width: 180px;
      height: 180px;
      object-fit: cover;
      border-radius: 2rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      border: 4px solid rgba(255, 255, 255, 0.3);
      transition: var(--transition);
    }
    
    .logo-showcase img:hover {
      transform: scale(1.05) rotate(5deg);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    }
    
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      .hero-subtitle {
        font-size: 1.1rem;
      }
      .about-title {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

  <header>
    <a href="index.php" class="logo">
      <img src="Images/logo.jpg" alt="Logo">
      <span>Student Management</span>
    </a>
    <nav>
      <a href="index.php">Home</a>
      <a href="./Admin/admin.php">Admin</a>
      <a href="./Students/Student.php">Student</a>
      <a href="./Teachers/Teacher.php">Teacher</a>
    </nav>
  </header>

  <!-- Hero Section -->
  <main class="container">
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Welcome to Student Management System</h1>
        <p class="hero-subtitle">Empowering Education Through Digital Innovation</p>
        
        <div class="logo-showcase">
          <img src="Images/logo.jpg" alt="Diversity Academy Logo">
        </div>
        
        <div class="cards">
          <a href="./Admin/admin.php" class="card">
            <img src="Images/admin.jpg" alt="Admin">
            <h2>Admin Portal</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.9rem;">Manage system settings and users</p>
          </a>

          <a href="./Students/Student.php" class="card">
            <img src="Images/student.jpg" alt="Student">
            <h2>Student Portal</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.9rem;">Access your academic information</p>
          </a>

          <a href="./Teachers/Teacher.php" class="card">
            <img src="Images/teacher.jpg" alt="Teacher">
            <h2>Teacher Portal</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.9rem;">Manage classes and students</p>
          </a>
        </div>
      </div>
    </div>
  </main>

  <!-- About Section -->
  <section class="about-section">
    <div class="about-content">
      <h2 class="about-title">About Diversity Academy</h2>
      <p class="about-text">
        Diversity Academy is committed to nurturing students from all backgrounds, fostering creativity, critical thinking, and collaboration. 
        With a focus on <strong style="color: var(--primary);">digitalization</strong>, we integrate modern technology into learning, making education accessible, engaging, and innovative.
      </p>
      <p class="about-text">
        Our students are empowered to excel academically and personally, using digital tools for research, collaboration, and skill development. 
        We aim to prepare the next generation of leaders for a fast-changing digital world.
      </p>
    </div>
  </section>

  <!-- Footer -->
  <div id="footer"></div>

  <!-- Script to load footer -->
  <script>
    fetch("./footer.php")
      .then(res => res.text())
      .then(data => document.getElementById("footer").innerHTML = data)
      .catch(err => console.error("Error loading footer:", err));
  </script>

</body>
</html>
