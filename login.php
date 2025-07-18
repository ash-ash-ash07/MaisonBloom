<?php include "db.php"; session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role, $name);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['name'] = $name; 
        
        if ($role == 'patient') {
            header("Location: dashboard/patient_dashboard.php");
        } elseif ($role == 'doctor') {
            header("Location: dashboard/doctor_dashboard.php");
        } elseif ($role == 'admin') {
            header("Location: dashboard/admin_dashboard.php");
        }
        exit;
    } else {
        $login_error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--lavender-light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-image: url('images/homebg4'), url('images/homebg5.jpg');
      background-size: cover;
      background-position: center;
      background-blend-mode: overlay;
      background-color: rgba(249, 247, 255, 0.9);
      animation: fadeIn 3s ease, bgTransition 16s infinite alternate;
      position: relative;
      overflow: hidden;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 3; }
    }

    @keyframes bgTransition {
      0%, 100% { background-position: center, center; }
      25% { background-position: 20% 30%, 80% 70%; }
      50% { background-position: 80% 20%, 20% 80%; }
      75% { background-position: 30% 80%, 70% 20%; }
    }

    .container {
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow);
      padding: 40px;
      width: 100%;
      max-width: 450px;
      text-align: center;
      transform: translateY(0);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 2;
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(106, 90, 205, 0.3);
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--lavender-dark));
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 28px;
      font-weight: 600;
    }

    .logo {
      font-size: 32px;
      margin-bottom: 20px;
      color: var(--lavender-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    input {
      padding: 14px 20px;
      border: 2px solid #e0d6ff;
      border-radius: 10px;
      font-size: 16px;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.7);
      transition: all 0.3s ease;
      width: 100%;
    }

    input:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.3);
      background: var(--white);
    }

    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--lavender-dark);
      transition: all 0.2s ease;
    }

    .toggle-password:hover {
      color: var(--purple-dark);
      transform: translateY(-50%) scale(1.1);
    }

    .btn {
      background-color: var(--lavender-dark);
      color: white;
      padding: 14px;
      border: none;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .links {
      margin-top: 25px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .links a {
      color: var(--lavender-dark);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .links a:hover {
      color: var(--purple-dark);
      text-decoration: underline;
    }

    .error-message {
      color: #e74c3c;
      margin-top: 15px;
      font-size: 14px;
      animation: shake 0.5s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }

    /* Floating animation for decorative elements */
    .floating {
      position: absolute;
      opacity: 0.1;
      z-index: 1;
      font-size: 24px;
    }

    .floating-1 {
      top: 20%;
      left: 10%;
      animation: float 8s ease-in-out infinite;
    }

    .floating-2 {
      bottom: 15%;
      right: 10%;
      animation: float 10s ease-in-out infinite;
    }

    .floating-3 {
      top: 30%;
      right: 15%;
      animation: float 12s ease-in-out infinite reverse;
    }

    .floating-4 {
      bottom: 25%;
      left: 15%;
      animation: float 9s ease-in-out infinite reverse;
    }
    .floating-5 {
      bottom: 10%;
      left: 10%;
      animation: float 8s ease-in-out infinite;
    }

    .floating-6 {
      top: 15%;
      right: 10%;
      animation: float 10s ease-in-out infinite;
    }

    .floating-7 {
      bottom: 30%;
      right: 15%;
      animation: float 12s ease-in-out infinite reverse;
    }

    .floating-8{
      top: 25%;
      left: 15%;
      animation: float 9s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(5deg); }
    }

    /* Particle background */
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 1;
    }

    .particle {
      position: absolute;
      background-color: rgba(179, 157, 219, 0.3);
      border-radius: 50%;
      animation: float-particle linear infinite;
    }

    @keyframes float-particle {
      0% { transform: translateY(0) rotate(0deg); opacity: 0; }
      10% { opacity: 0.3; }
      90% { opacity: 0.3; }
      100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
        margin: 20px;
      }
      
      body {
        background-image: url('images/homebg4.jpg');
        animation: fadeIn 3s ease;
      }
    }
  </style>
</head>
<body>
  <div class="particles" id="particles"></div>
  <div class="floating floating-1">🌸</div>
  <div class="floating floating-2">🌿</div>
  <div class="floating floating-3">🌼</div>
  <div class="floating floating-4">🌺</div>
  <div class="floating floating-5">🌸</div>
  <div class="floating floating-6">🌿</div>
  <div class="floating floating-7">🌼</div>
  <div class="floating floating-8">🌺</div>

  <div class="container">
    <div class="logo">
      <i class="fas fa-spa"></i> Maison Bloom
    </div>
    <h2>Welcome Back</h2>
    
    <form method="POST">
      <input type="email" name="email" required placeholder="Email Address">
      
      <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword()">
          <i class="far fa-eye"></i>
        </span>
      </div>
      
      <button type="submit" class="btn">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>
    </form>

    <div class="links">
      <?php if (isset($login_error)): ?>
        <p class="error-message"><?php echo $login_error; ?></p>
        <a href="forgot_password.php">
          <i class="fas fa-key"></i> Forgot Password?
        </a>
      <?php endif; ?>
      <a href="register.php">
        <i class="fas fa-user-plus"></i> Create New Account
      </a>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwdInput = document.getElementById("password");
      const toggleIcon = document.querySelector(".toggle-password i");
      
      if (pwdInput.type === "password") {
        pwdInput.type = "text";
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        pwdInput.type = "password";
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }

    // Create floating particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = window.innerWidth < 768 ? 20 : 40;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 10 + 5;
        const posX = Math.random() * 100;
        const duration = Math.random() * 10 + 10;
        const delay = Math.random() * 10;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        
        particlesContainer.appendChild(particle);
      }
    }

    // Initialize particles when page loads
    window.addEventListener('load', createParticles);
  </script>
</body>
</html>