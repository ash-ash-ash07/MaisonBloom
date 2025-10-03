<?php
include "db.php"; session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $specialization = $_POST['specialization'];
    $license = $_POST['license'];

    $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number) VALUES (?, ?, ?, 'doctor', ?)");
    $stmt1->bind_param("ssss", $name, $email, $password, $phone);
    $stmt1->execute();

    $user_id = $conn->insert_id;
    $photo_name = null;
    
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = basename($_FILES['photo']['name']);
        $target = "uploads/doctors/" . $photo_name;
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);
    }

    $stmt2 = $conn->prepare("INSERT INTO doctor_profiles (user_id, specialization, license_number, profile_photo) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("isss", $user_id, $specialization, $license, $photo_name);
    $stmt2->execute();

    echo "<script>alert('Doctor registered successfully!'); window.location='login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Doctor Registration - Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --purple-light: #e1bee7;
      --teal-accent: #26a69a;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
      --shadow-hover: 0 15px 45px rgba(106, 90, 205, 0.35);
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
      padding: 30px 0;
      background-image: 
        url('images/lavender-bg.jpg'),
        radial-gradient(circle at 25% 25%, rgba(38, 166, 154, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(179, 157, 219, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 50% 10%, rgba(106, 90, 205, 0.2) 0%, transparent 50%),
        radial-gradient(circle at 10% 90%, rgba(225, 190, 231, 0.3) 0%, transparent 50%);
      background-size: cover, 100% 100%, 100% 100%, 100% 100%, 100% 100%;
      background-position: center, 0 0, 0 0, 0 0, 0 0;
      background-blend-mode: overlay, normal, normal, normal, normal;
      animation: fadeIn 3s ease, bgTransition 20s infinite alternate;
      position: relative;
      overflow-x: hidden;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95) rotate(-1deg); }
      to { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    @keyframes bgTransition {
      0%, 100% { background-position: center, 0 0, 0 0, 0 0, 0 0; }
      33% { background-position: 30% 70%, 0 0, 0 0, 0 0, 0 0; }
      66% { background-position: 70% 30%, 0 0, 0 0, 0 0, 0 0; }
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
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 10px 30px rgba(106, 90, 205, 0.25);
      color: var(--lavender-dark);
    }

    .container {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 28px;
      box-shadow: var(--shadow);
      padding: 55px;
      width: 100%;
      max-width: 650px;
      text-align: center;
      transform: translateY(0);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      z-index: 2;
      backdrop-filter: blur(25px);
      border: 3px solid rgba(255, 255, 255, 0.4);
      margin: 40px 20px;
    }

    .container:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-hover);
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--teal-accent), var(--lavender-medium), var(--lavender-dark), var(--purple-dark));
    }

    .container::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(38, 166, 154, 0.05) 0%, rgba(179, 157, 219, 0.03) 50%, transparent 70%);
      animation: rotate 30s linear infinite;
      z-index: -1;
    }

    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .logo {
      font-size: 40px;
      margin-bottom: 15px;
      color: var(--lavender-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      font-weight: 700;
      text-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .logo i {
      animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.2) rotate(10deg); }
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 35px;
      font-size: 32px;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, var(--purple-dark), var(--teal-accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
    }

    h2::after {
      content: '👩‍⚕️';
      position: absolute;
      right: -50px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 24px;
      animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(-50%); }
      40% { transform: translateY(-60%); }
      60% { transform: translateY(-55%); }
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 28px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-group {
      text-align: left;
      position: relative;
    }

    .form-group.full-width {
      grid-column: 1 / -1;
    }

    label {
      display: block;
      margin-bottom: 10px;
      color: var(--purple-dark);
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      position: relative;
    }

    label::before {
      content: '';
      position: absolute;
      left: -15px;
      top: 50%;
      transform: translateY(-50%);
      width: 8px;
      height: 8px;
      background: linear-gradient(45deg, var(--lavender-dark), var(--teal-accent));
      border-radius: 50%;
    }

    input, select {
      width: 100%;
      padding: 18px 24px;
      border: 2px solid rgba(179, 157, 219, 0.3);
      border-radius: 18px;
      font-size: 16px;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.9);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 500;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--teal-accent);
      box-shadow: 0 0 0 4px rgba(38, 166, 154, 0.15);
      background: var(--white);
      transform: scale(1.02);
    }

    input::placeholder {
      color: rgba(75, 0, 130, 0.6);
    }

    select {
      cursor: pointer;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236a5acd' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 16px center;
      background-size: 20px;
      padding-right: 50px;
    }

    .file-upload {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-upload-btn {
      border: 3px dashed var(--teal-accent);
      color: var(--teal-accent);
      background: linear-gradient(135deg, rgba(38, 166, 154, 0.08), rgba(179, 157, 219, 0.12));
      padding: 25px;
      border-radius: 18px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      min-height: 70px;
      position: relative;
      overflow: hidden;
    }

    .file-upload-btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 0;
      height: 0;
      background: radial-gradient(circle, rgba(38, 166, 154, 0.1), transparent);
      transition: all 0.3s ease;
      border-radius: 50%;
    }

    .file-upload-btn:hover::before {
      width: 200%;
      height: 200%;
    }

    .file-upload-btn:hover {
      background: linear-gradient(135deg, rgba(38, 166, 154, 0.15), rgba(179, 157, 219, 0.2));
      border-color: var(--lavender-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(38, 166, 154, 0.2);
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
      background: linear-gradient(135deg, var(--teal-accent), var(--lavender-dark));
      color: white;
      padding: 20px;
      border: none;
      border-radius: 30px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.6s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 12px 35px rgba(38, 166, 154, 0.4);
    }

    .btn:active {
      transform: translateY(-2px) scale(1.01);
    }

    .links {
      margin-top: 35px;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .links p {
      margin: 0;
      color: var(--purple-dark);
      font-weight: 500;
      font-size: 15px;
    }

    .links a {
      color: var(--teal-accent);
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 10px 20px;
      border-radius: 25px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: rgba(38, 166, 154, 0.05);
      border: 2px solid transparent;
    }

    .links a:hover {
      color: var(--purple-dark);
      background: rgba(38, 166, 154, 0.15);
      border-color: rgba(38, 166, 154, 0.3);
      transform: translateY(-3px);
      text-decoration: none;
      box-shadow: 0 6px 20px rgba(38, 166, 154, 0.2);
    }

    /* Doctor-specific styling */
    .specialization-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 10px;
    }

    .specialization-option {
      position: relative;
    }

    .specialization-option input[type="radio"] {
      display: none;
    }

    .specialization-label {
      display: block;
      padding: 15px 20px;
      background: rgba(38, 166, 154, 0.05);
      border: 2px solid rgba(38, 166, 154, 0.2);
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      font-weight: 500;
      color: var(--purple-dark);
    }

    .specialization-option input[type="radio"]:checked + .specialization-label {
      background: rgba(38, 166, 154, 0.2);
      border-color: var(--teal-accent);
      color: var(--teal-accent);
      transform: scale(1.05);
    }

    .specialization-label:hover {
      background: rgba(38, 166, 154, 0.1);
      transform: translateY(-2px);
    }

    /* Enhanced floating elements */
    .floating {
      position: absolute;
      opacity: 0.08;
      z-index: 1;
      font-size: 32px;
      filter: drop-shadow(0 0 20px rgba(38, 166, 154, 0.3));
    }

    .floating-1 {
      top: 12%;
      left: 5%;
      animation: float 10s ease-in-out infinite;
    }

    .floating-2 {
      bottom: 18%;
      right: 8%;
      animation: float 12s ease-in-out infinite;
    }

    .floating-3 {
      top: 40%;
      right: 5%;
      animation: float 14s ease-in-out infinite reverse;
    }

    .floating-4 {
      bottom: 35%;
      left: 8%;
      animation: float 11s ease-in-out infinite reverse;
    }

    .floating-5 {
      top: 65%;
      left: 3%;
      animation: float 9s ease-in-out infinite;
    }

    .floating-6 {
      bottom: 55%;
      right: 3%;
      animation: float 13s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
      25% { transform: translateY(-25px) rotate(8deg) scale(1.1); }
      50% { transform: translateY(-35px) rotate(-5deg) scale(0.95); }
      75% { transform: translateY(-20px) rotate(3deg) scale(1.05); }
    }

    /* Medical-themed particles */
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
      z-index: 1;
    }

    .particle {
      position: absolute;
      background: linear-gradient(45deg, rgba(38, 166, 154, 0.6), rgba(179, 157, 219, 0.8));
      border-radius: 50%;
      animation: float-particle linear infinite;
      box-shadow: 0 0 20px rgba(38, 166, 154, 0.4);
    }

    .particle:nth-child(3n) {
      background: linear-gradient(45deg, rgba(106, 90, 205, 0.6), rgba(225, 190, 231, 0.8));
    }

    @keyframes float-particle {
      0% { transform: translateY(0) rotate(0deg) scale(0); opacity: 0; }
      10% { opacity: 0.8; transform: scale(1); }
      90% { opacity: 0.8; }
      100% { transform: translateY(-100vh) rotate(360deg) scale(0); opacity: 0; }
    }

    /* Success state for file upload */
    .file-uploaded {
      border-color: var(--teal-accent);
      background: linear-gradient(135deg, rgba(38, 166, 154, 0.15), rgba(179, 157, 219, 0.1));
      color: var(--purple-dark);
    }

    /* Loading animation for submit button */
    .btn.loading {
      pointer-events: none;
      background: linear-gradient(135deg, rgba(38, 166, 154, 0.7), rgba(106, 90, 205, 0.7));
    }

    .btn.loading::after {
      content: '';
      width: 22px;
      height: 22px;
      border: 3px solid transparent;
      border-top: 3px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-left: 10px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .nav-home {
        top: 20px;
        left: 20px;
      }

      .container {
        padding: 45px 35px;
        margin: 20px;
        max-width: 500px;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 25px;
      }
      
      .logo {
        font-size: 32px;
      }

      h2 {
        font-size: 26px;
      }

      h2::after {
        right: -40px;
        font-size: 20px;
      }

      .specialization-grid {
        grid-template-columns: 1fr;
      }

      body {
        background-image: url('images/lavender-bg.jpg');
        animation: fadeIn 3s ease;
        padding: 15px 0;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 35px 25px;
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
        font-size: 26px;
      }

      h2::after {
        display: none;
      }

      input, select {
        padding: 16px 20px;
        font-size: 15px;
      }

      .file-upload-btn {
        padding: 20px;
        min-height: 60px;
      }
    }

    /* Input validation styles */
    .form-group.error input,
    .form-group.error select {
      border-color: #e74c3c;
      box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .form-group.success input,
    .form-group.success select {
      border-color: var(--teal-accent);
      box-shadow: 0 0 0 3px rgba(38, 166, 154, 0.1);
    }

    /* License verification indicator */
    .license-verified {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--teal-accent);
      font-size: 18px;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .license-verified.show {
      opacity: 1;
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
  <div class="floating floating-1">🩺</div>
  <div class="floating floating-2">💊</div>
  <div class="floating floating-3">🔬</div>
  <div class="floating floating-4">🏥</div>
  <div class="floating floating-5">⚕️</div>
  <div class="floating floating-6">🧬</div>

  <div class="container">
    <div class="logo">
      <i class="fas fa-user-md"></i> Maison Bloom
    </div>
    <h2>Doctor Registration</h2>
    
    <form method="POST" enctype="multipart/form-data" id="doctorForm">
      <div class="form-group full-width">
        <label for="photo">Professional Profile Photo</label>
        <div class="file-upload">
          <div class="file-upload-btn" id="fileUploadText">
            <i class="fas fa-camera-retro"></i> Upload Professional Photo
          </div>
          <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" placeholder="Dr. John Doe" required autocomplete="name">
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="doctor@example.com" required autocomplete="email">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="text" id="phone" name="phone" placeholder="+1 (555) 123-4567" required autocomplete="tel">
        </div>
        <div class="form-group">
          <label for="password">Create Password</label>
          <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required minlength="8">
        </div>
      </div>
      
      <div class="form-group full-width">
        <label for="specialization">Medical Specialization</label>
        <select name="specialization" id="specialization" required>
          <option value="">Select Your Specialization</option>
          <option value="Dermatology">Dermatology - Skin Health & Beauty</option>
          <option value="Cosmetology">Cosmetology - Aesthetic Medicine</option>
          <option value="Trichology">Trichology - Hair & Scalp Specialist</option>
          <option value="Plastic Surgery">Plastic & Reconstructive Surgery</option>
          <option value="Wellness">Wellness & Holistic Medicine</option>
        </select>
      </div>
      
      <div class="form-group full-width">
        <label for="license">Medical License Number</label>
        <div style="position: relative;">
          <input type="text" id="license" name="license" placeholder="Enter your medical license number" required>
          <i class="fas fa-check-circle license-verified" id="licenseVerified"></i>
        </div>
      </div>
      
      <button type="submit" class="btn" id="submitBtn">
        <i class="fas fa-stethoscope"></i> Register as Doctor
      </button>
    </form>

    <div class="links">
      <p>Need to register as a patient instead? <a href="register.php"><i class="fas fa-user"></i> Patient Registration</a></p>
      <p>Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a></p>
    </div>
  </div>

  <script>
    // Enhanced file upload handling with preview
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const fileName = e.target.files[0] ? e.target.files[0].name : 'Upload Professional Photo';
      const fileUploadBtn = document.getElementById('fileUploadText');
      
      if (e.target.files[0]) {
        const file = e.target.files[0];
        if (file.size > 5000000) { // 5MB limit
          alert('File size should be less than 5MB');
          this.value = '';
          return;
        }
        
        fileUploadBtn.innerHTML = `<i class="fas fa-check-circle"></i> ${fileName}`;
        fileUploadBtn.classList.add('file-uploaded');
      } else {
        fileUploadBtn.innerHTML = `<i class="fas fa-camera-retro"></i> Upload Professional Photo`;
        fileUploadBtn.classList.remove('file-uploaded');
      }
    });

    // License number validation simulation
    document.getElementById('license').addEventListener('input', function(e) {
      const licenseNumber = e.target.value;
      const verifiedIcon = document.getElementById('licenseVerified');
      
      if (licenseNumber.length >= 6) {
        setTimeout(() => {
          verifiedIcon.classList.add('show');
        }, 500);
      } else {
        verifiedIcon.classList.remove('show');
      }
    });

    // Enhanced particle creation with medical theme
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = window.innerWidth < 768 ? 35 : 70;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 6 + 3;
        const posX = Math.random() * 100;
        const duration = Math.random() * 18 + 12;
        const delay = Math.random() * 20;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;
        
        particlesContainer.appendChild(particle);
      }
    }

    // Form submission handling
    document.getElementById('doctorForm').addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('submitBtn');
      submitBtn.classList.add('loading');
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering Doctor...';
    });

    // Form validation with visual feedback
    const requiredInputs = document.querySelectorAll('input[required], select[required]');
    requiredInputs.forEach(input => {
      input.addEventListener('blur', function() {
        const formGroup = this.closest('.form-group');
        
        if (this.value.trim() === '') {
          formGroup.classList.add('error');
          formGroup.classList.remove('success');
        } else {
          formGroup.classList.remove('error');
          formGroup.classList.add('success');
        }
      });

      input.addEventListener('focus', function() {
        const formGroup = this.closest('.form-group');
        formGroup.classList.remove('error');
      });
    });

    // Email validation
    document.getElementById('email').addEventListener('blur', function() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const formGroup = this.closest('.form-group');
      
      if (!emailRegex.test(this.value)) {
        formGroup.classList.add('error');
        formGroup.classList.remove('success');
      }
    });

    // Phone number formatting
    document.getElementById('phone').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
      } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
      }
      e.target.value = value;
    });

    // Initialize particles when page loads
    window.addEventListener('load', createParticles);

    // Enhanced entrance animation
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.querySelector('.container');
      container.style.opacity = '0';
      container.style.transform = 'translateY(50px) scale(0.9) rotate(-2deg)';
      
      setTimeout(() => {
        container.style.transition = 'all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0) scale(1) rotate(0deg)';
      }, 400);
    });

    // Specialization selection enhancement
    document.getElementById('specialization').addEventListener('change', function() {
      if (this.value) {
        this.style.background = 'linear-gradient(135deg, rgba(38, 166, 154, 0.1), rgba(255, 255, 255, 1))';
      } else {
        this.style.background = 'rgba(249, 247, 255, 0.9)';
      }
    });
  </script>
</body>
</html>