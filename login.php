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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --purple-light: #e1bee7;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
      --shadow-hover: 0 10px 40px rgba(106, 90, 205, 0.3);
      --gradient: linear-gradient(135deg, var(--lavender-light), var(--lavender-medium));
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--gradient);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-image: 
        url('images/homebg4.jpg'), 
        url('images/homebg5.jpg'),
        radial-gradient(circle at 20% 50%, rgba(179, 157, 219, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(106, 90, 205, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(225, 190, 231, 0.2) 0%, transparent 50%);
      background-size: cover, cover, 100% 100%, 100% 100%, 100% 100%;
      background-position: center, center, 0 0, 0 0, 0 0;
      background-blend-mode: overlay, overlay, normal, normal, normal;
      animation: fadeIn 3s ease, bgTransition 16s infinite alternate;
      position: relative;
      overflow: hidden;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes bgTransition {
      0%, 100% { background-position: center, center, 0 0, 0 0, 0 0; }
      25% { background-position: 20% 30%, 80% 70%, 0 0, 0 0, 0 0; }
      50% { background-position: 80% 20%, 20% 80%, 0 0, 0 0, 0 0; }
      75% { background-position: 30% 80%, 70% 20%, 0 0, 0 0, 0 0; }
    }

    /* Navigation */
    .nav-home {
      position: absolute;
      top: 30px;
      left: 30px;
      z-index: 1000;
    }

    .home-btn {
      background: rgba(255, 255, 255, 0.9);
      color: var(--purple-dark);
      padding: 12px 20px;
      border: none;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .home-btn:hover {
      background: var(--white);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(106, 90, 205, 0.2);
      color: var(--lavender-dark);
    }

    .container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: var(--shadow);
      padding: 50px;
      width: 100%;
      max-width: 480px;
      text-align: center;
      transform: translateY(0);
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      z-index: 2;
      backdrop-filter: blur(20px);
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .container:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-hover);
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--lavender-dark), var(--purple-dark));
    }

    .container::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(179, 157, 219, 0.05) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
      z-index: -1;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 32px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .logo {
      font-size: 36px;
      margin-bottom: 20px;
      color: var(--lavender-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      font-weight: 600;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .logo i {
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    input {
      padding: 16px 24px;
      border: 2px solid rgba(179, 157, 219, 0.3);
      border-radius: 15px;
      font-size: 16px;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.8);
      transition: all 0.3s ease;
      width: 100%;
      font-weight: 500;
    }

    input:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 4px rgba(179, 157, 219, 0.2);
      background: var(--white);
      transform: scale(1.02);
    }

    input::placeholder {
      color: rgba(75, 0, 130, 0.6);
    }

    .password-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--lavender-dark);
      transition: all 0.3s ease;
      font-size: 18px;
    }

    .toggle-password:hover {
      color: var(--purple-dark);
      transform: translateY(-50%) scale(1.2);
    }

    .btn {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: white;
      padding: 16px;
      border: none;
      border-radius: 25px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(106, 90, 205, 0.4);
      background: linear-gradient(135deg, var(--purple-dark), var(--lavender-dark));
    }

    .btn:active {
      transform: translateY(-1px);
    }

    .links {
      margin-top: 30px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .links a {
      color: var(--lavender-dark);
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 8px 16px;
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .links a:hover {
      color: var(--purple-dark);
      background: rgba(179, 157, 219, 0.1);
      transform: translateY(-2px);
      text-decoration: none;
    }

    .error-message {
      color: #e74c3c;
      margin-top: 15px;
      font-size: 14px;
      font-weight: 600;
      animation: shake 0.5s ease;
      background: rgba(231, 76, 60, 0.1);
      padding: 12px;
      border-radius: 10px;
      border-left: 4px solid #e74c3c;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-8px); }
      40%, 80% { transform: translateX(8px); }
    }

    /* Enhanced floating elements */
    .floating {
      position: absolute;
      opacity: 0.15;
      z-index: 1;
      font-size: 28px;
      filter: drop-shadow(0 0 10px rgba(179, 157, 219, 0.3));
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

    .floating-8 {
      top: 25%;
      left: 15%;
      animation: float 9s ease-in-out infinite reverse;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
      33% { transform: translateY(-15px) rotate(3deg) scale(1.1); }
      66% { transform: translateY(-25px) rotate(-3deg) scale(0.9); }
    }

    /* Enhanced particles */
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 1;
    }

    .particle {
      position: absolute;
      background: linear-gradient(45deg, rgba(179, 157, 219, 0.4), rgba(106, 90, 205, 0.6));
      border-radius: 50%;
      animation: float-particle linear infinite;
      box-shadow: 0 0 10px rgba(179, 157, 219, 0.3);
    }

    @keyframes float-particle {
      0% { transform: translateY(0) rotate(0deg) scale(0); opacity: 0; }
      10% { opacity: 0.6; transform: scale(1); }
      90% { opacity: 0.6; }
      100% { transform: translateY(-100vh) rotate(360deg) scale(0); opacity: 0; }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .nav-home {
        top: 20px;
        left: 20px;
      }

      .container {
        padding: 40px 30px;
        margin: 20px;
        max-width: 400px;
      }
      
      .logo {
        font-size: 28px;
      }

      h2 {
        font-size: 26px;
      }

      body {
        background-image: url('images/homebg4.jpg');
        animation: fadeIn 3s ease;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }

      .home-btn {
        padding: 10px 16px;
        font-size: 12px;
      }
    }
  </style>
</head>
<body>
  <div class="nav-home">
    <a href="home.php" class="home-btn">
      <i class="fas fa-home"></i> Home
    </a>
  </div>

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
        <p class="error-message">
          <i class="fas fa-exclamation-triangle"></i> <?php echo $login_error; ?>
        </p>
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

    // Enhanced particle creation
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = window.innerWidth < 768 ? 25 : 50;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 8 + 4;
        const posX = Math.random() * 100;
        const duration = Math.random() * 12 + 8;
        const delay = Math.random() * 12;
        
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

    // Add subtle entrance animation
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.querySelector('.container');
      container.style.opacity = '0';
      container.style.transform = 'translateY(30px) scale(0.9)';
      
      setTimeout(() => {
        container.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0) scale(1)';
      }, 200);
    });
  </script>
</body>
</html>