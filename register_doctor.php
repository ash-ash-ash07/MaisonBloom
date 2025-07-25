<?php
include "db.php";session_start();

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
      background-image: url('images/lavender-bg.jpg');
      background-size: cover;
      background-position: center;
      background-blend-mode: overlay;
      background-color: rgba(249, 247, 255, 0.9);
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .container {
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow);
      padding: 40px;
      width: 100%;
      max-width: 600px;
      text-align: center;
      transform: translateY(0);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
      overflow: hidden;
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
      gap: 20px;
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
      background: var(--lavender-light);
      transition: all 0.3s ease;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.3);
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

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
        margin: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">🌸 Maison Bloom</div>
    <h2>Doctor Registration</h2>
    
    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="photo">Profile Photo</label>
        <div class="file-upload">
          <div class="file-upload-btn">Choose Profile Image</div>
          <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>
      </div>
      
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="phone" placeholder="Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      
      <div class="form-group">
        <label for="specialization">Specialization</label>
        <select name="specialization" required>
          <option value="">Select Specialization</option>
          <option value="Dermatology">Dermatology</option>
          <option value="Cosmetology">Cosmetology</option>
          <option value="Trichology">Trichology</option>
        </select>
      </div>
      
      <input type="text" name="license" placeholder="License Number" required>
      
      <button type="submit" class="btn">Register as Doctor</button>
    </form>

    <div class="links">
      <p>Already registered? <a href="login.php">Login</a></p>
    </div>
  </div>

  <script>
    // Update file upload display
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
      const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose Profile Image';
      document.querySelector('.file-upload-btn').textContent = fileName;
    });
  </script>
</body>
</html>