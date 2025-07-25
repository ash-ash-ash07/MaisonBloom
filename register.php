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
      background-image: url('images/homebg5.jpg'), url('homebg4.jpg');
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
      to { opacity: 1; }
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
      max-width: 500px;
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

    .logo {
      font-size: 32px;
      margin-bottom: 15px;
      color: var(--lavender-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 25px;
      font-size: 28px;
      font-weight: 600;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .form-group {
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: var(--purple-dark);
      font-weight: 500;
    }

    input, select {
      width: 100%;
      padding: 14px 20px;
      border: 2px solid #e0d6ff;
      border-radius: 10px;
      font-size: 16px;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.7);
      transition: all 0.3s ease;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.3);
      background: var(--white);
    }

    .file-upload {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-upload-btn {
      border: 2px dashed var(--lavender-medium);
      color: var(--lavender-dark);
      background-color: rgba(179, 157, 219, 0.1);
      padding: 14px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .file-upload-btn:hover {
      background-color: rgba(179, 157, 219, 0.2);
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
      gap: 5px;
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
        background-image: url('images/homebg5.jpg');
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

  <div class="container">
    <div class="logo">
      <i class="fas fa-spa"></i> Maison Bloom
    </div>
    <h2>Patient Registration</h2>
    
    <?php if (isset($error)): ?>
      <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="photo">Profile Photo</label>
        <div class="file-upload">
          <div class="file-upload-btn" id="fileUploadText">
            <i class="fas fa-cloud-upload-alt"></i> Choose Profile Image
          </div>
          <input type="file" id="photo" name="photo" accept="image/*">
        </div>
      </div>
      
      <input type="text" name="name" required placeholder="Full Name">
      <input type="email" name="email" required placeholder="Email Address">
      <input type="password" name="password" required placeholder="Password">
      <input type="text" name="phone" placeholder="Phone Number">
      
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="patient">Patient</option>
      </select>
      
      <button type="submit" class="btn">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="links">
      <p>Are you a doctor? <a href="register_doctor.php"><i class="fas fa-user-md"></i> Register here</a></p>
      <p>Already registered? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></p>
    </div>
  </div>

  <script>
    // Update file upload display
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose Profile Image';
      document.getElementById('fileUploadText').innerHTML = `<i class="fas fa-file-image"></i> ${fileName}`;
    });

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