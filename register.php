<?php include "db.php"; session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $role, $phone);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        if (!empty($_FILES['photo']['name'])) {
            $photo_name = basename($_FILES['photo']['name']);
            $target = "uploads/patients/" . $photo_name;
            move_uploaded_file($_FILES['photo']['tmp_name'], $target);
            $conn->query("UPDATE users SET profile_photo = '$photo_name' WHERE user_id = $user_id");
        }
        
        header("Location: login.php");
        exit;
    } else {
        $error = "Registration failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Registration - Maison Bloom</title>
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
      --shadow-hover: 0 12px 40px rgba(106, 90, 205, 0.3);
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
      padding: 20px 0;
      background-image: 
        url('images/homebg5.jpg'), 
        url('homebg4.jpg'),
        radial-gradient(circle at 30% 20%, rgba(179, 157, 219, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 70% 80%, rgba(106, 90, 205, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 20% 70%, rgba(225, 190, 231, 0.25) 0%, transparent 50%);
      background-size: cover, cover, 100% 100%, 100% 100%, 100% 100%;
      background-position: center, center, 0 0, 0 0, 0 0;
      background-blend-mode: overlay, overlay, normal, normal, normal;
      animation: fadeIn 3s ease, bgTransition 16s infinite alternate;
      position: relative;
      overflow-x: hidden;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
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
      background: rgba(255, 255, 255, 0.95);
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
      backdrop-filter: blur(15px);
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
      border-radius: 24px;
      box-shadow: var(--shadow);
      padding: 50px;
      width: 100%;
      max-width: 550px;
      text-align: center;
      transform: translateY(0);
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      z-index: 2;
      backdrop-filter: blur(20px);
      border: 2px solid rgba(255, 255, 255, 0.3);
      margin: 40px 20px;
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
      animation: rotate 25s linear infinite;
      z-index: -1;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .logo {
      font-size: 36px;
      margin-bottom: 15px;
      color: var(--lavender-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      font-weight: 600;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .logo i {
      animation: pulse 2.5s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.15) rotate(5deg); }
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 30px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, var(--purple-dark), var(--lavender-dark));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .form-group {
      text-align: left;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 10px;
      color: var(--purple-dark);
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    input, select {
      width: 100%;
      padding: 16px 24px;
      border: 2px solid rgba(179, 157, 219, 0.3);
      border-radius: 15px;
      font-size: 16px;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.8);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 500;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 4px rgba(179, 157, 219, 0.2);
      background: var(--white);
      transform: scale(1.02);
    }

    input::placeholder {
      color: rgba(75, 0, 130, 0.6);
    }

    select {
      cursor: pointer;
    }

    .file-upload {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-upload-btn {
      border: 3px dashed var(--lavender-medium);
      color: var(--lavender-dark);
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.1), rgba(225, 190, 231, 0.15));
      padding: 20px;
      border-radius: 15px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      min-height: 60px;
    }

    .file-upload-btn:hover {
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.2), rgba(225, 190, 231, 0.25));
      border-color: var(--lavender-dark);
      transform: translateY(-2px);
    }

    .file-upload input[type="file"] {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }

    .btn {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: white;
      padding: 18px;
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
      background: linear-gradient(135deg, var(--purple-dark), var(--lavender-dark));
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(106, 90, 205, 0.4);
    }

    .btn:active {
      transform: translateY(-1px);
    }

    .links {
      margin-top: 30px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .links p {
      margin: 0;
      color: var(--purple-dark);
      font-weight: 500;
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
      background: rgba(179, 157, 219, 0.15);
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
      padding: 15px;
      border-radius: 12px;
      border-left: 5px solid #e74c3c;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-8px); }
      40%, 80% { transform: translateX(8px); }
    }

    /* Enhanced floating elements */
    .floating {
      position: absolute;
      opacity: 0.12;
      z-index: 1;
      font-size: 30px;
      filter: drop-shadow(0 0 15px rgba(179, 157, 219, 0.4));
    }

    .floating-1 {
      top: 15%;
      left: 8%;
      animation: float 9s ease-in-out infinite;
    }

    .floating-2 {
      bottom: 20%;
      right: 8%;
      animation: float 11s ease-in-out infinite;
    }

    .floating-3 {
      top: 35%;
      right: 12%;
      animation: float 13s ease-in-out infinite reverse;
    }

    .floating-4 {
      bottom: 30%;
      left: 12%;
      animation: float 10s ease-in-out infinite reverse;
    }

    .floating-5 {
      top: 60%;
      left: 5%;
      animation: float 8s ease-in-out infinite;
    }

    .floating-6 {
      bottom: 50%;
      right: 5%;
      animation: float 12s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
      33% { transform: translateY(-20px) rotate(5deg) scale(1.1); }
      66% { transform: translateY(-30px) rotate(-5deg) scale(0.9); }
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
      background: linear-gradient(45deg, rgba(179, 157, 219, 0.5), rgba(106, 90, 205, 0.7));
      border-radius: 50%;
      animation: float-particle linear infinite;
      box-shadow: 0 0 15px rgba(179, 157, 219, 0.4);
    }

    @keyframes float-particle {
      0% { transform: translateY(0) rotate(0deg) scale(0); opacity: 0; }
      10% { opacity: 0.7; transform: scale(1); }
      90% { opacity: 0.7; }
      100% { transform: translateY(-100vh) rotate(360deg) scale(0); opacity: 0; }
    }

    /* Form field focus effects */
    .form-group input:focus + label,
    .form-group select:focus + label {
      color: var(--lavender-dark);
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
        max-width: 450px;
      }
      
      .logo {
        font-size: 28px;
      }

      h2 {
        font-size: 24px;
      }

      body {
        background-image: url('images/homebg5.jpg');
        animation: fadeIn 3s ease;
        padding: 10px 0;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
        margin: 15px;
      }

      .home-btn {
        padding: 10px 16px;
        font-size: 12px;
      }

      h2 {
        font-size: 22px;
      }

      .logo {
        font-size: 24px;
      }

      input, select {
        padding: 14px 20px;
        font-size: 15px;
      }
    }

    /* Success state for file upload */
    .file-uploaded {
      border-color: var(--lavender-dark);
      background: linear-gradient(135deg, rgba(106, 90, 205, 0.1), rgba(179, 157, 219, 0.15));
    }

    /* Loading animation for submit button */
    .btn.loading {
      pointer-events: none;
    }

    .btn.loading::after {
      content: '';
      width: 20px;
      height: 20px;
      border: 2px solid transparent;
      border-top: 2px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-left: 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
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

  <div class="container">
    <div class="logo">
      <i class="fas fa-spa"></i> Maison Bloom
    </div>
    <h2>Patient Registration</h2>
    
    <?php if (isset($error)): ?>
      <p class="error-message">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
      </p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" id="registrationForm">
      <div class="form-group">
        <label for="photo">Profile Photo</label>
        <div class="file-upload">
          <div class="file-upload-btn" id="fileUploadText">
            <i class="fas fa-cloud-upload-alt"></i> Choose Profile Image
          </div>
          <input type="file" id="photo" name="photo" accept="image/*">
        </div>
      </div>
      
      <input type="text" name="name" required placeholder="Full Name" autocomplete="name">
      <input type="email" name="email" required placeholder="Email Address" autocomplete="email">
      <input type="password" name="password" required placeholder="Create Password" minlength="6">
      <input type="text" name="phone" placeholder="Phone Number" autocomplete="tel">
      
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="patient">Patient</option>
      </select>
      
      <button type="submit" class="btn" id="submitBtn">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="links">
      <p>Are you a doctor? <a href="register_doctor.php"><i class="fas fa-user-md"></i> Register here</a></p>
      <p>Already registered? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></p>
    </div>
  </div>

  <script>
    // Enhanced file upload handling
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose Profile Image';
      const fileUploadBtn = document.getElementById('fileUploadText');
      
      if (e.target.files[0]) {
        fileUploadBtn.innerHTML = `<i class="fas fa-check-circle"></i> ${fileName}`;
        fileUploadBtn.classList.add('file-uploaded');
      } else {
        fileUploadBtn.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Choose Profile Image`;
        fileUploadBtn.classList.remove('file-uploaded');
      }
    });

    // Enhanced particle creation
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = window.innerWidth < 768 ? 30 : 60;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 8 + 3;
        const posX = Math.random() * 100;
        const duration = Math.random() * 15 + 10;
        const delay = Math.random() * 15;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        
        particlesContainer.appendChild(particle);
      }
    }

    // Form submission loading state
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      submitBtn.classList.add('loading');
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    });

    // Initialize particles when page loads
    window.addEventListener('load', createParticles);

    // Add entrance animation
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.querySelector('.container');
      container.style.opacity = '0';
      container.style.transform = 'translateY(40px) scale(0.95)';
      
      setTimeout(() => {
        container.style.transition = 'all 1s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0) scale(1)';
      }, 300);
    });

    // Form validation feedback
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
      input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
          this.style.borderColor = '#e74c3c';
          this.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.1)';
        } else {
          this.style.borderColor = 'var(--lavender-dark)';
          this.style.boxShadow = '0 0 0 3px rgba(179, 157, 219, 0.1)';
        }
      });

      input.addEventListener('focus', function() {
        this.style.borderColor = 'var(--lavender-dark)';
        this.style.boxShadow = '0 0 0 4px rgba(179, 157, 219, 0.2)';
      });
    });
  </script>
</body>
</html>