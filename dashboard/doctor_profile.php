<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  header("Location: ../login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user and profile info
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();
$profile = $conn->query("SELECT * FROM doctor_profiles WHERE user_id = $user_id")->fetch_assoc();
$current_photo = !empty($profile['profile_photo']) ? $profile['profile_photo'] : "default.png";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $conn->real_escape_string($_POST['name']);
  $email = $conn->real_escape_string($_POST['email']);
  $phone = $conn->real_escape_string($_POST['phone']);
  $specialization = $conn->real_escape_string($_POST['specialization']);
  $license = $conn->real_escape_string($_POST['license']);
  $photo_name = $current_photo;

  // Handle profile photo upload
  if (!empty($_FILES['photo']['name'])) {
    $photo_name = basename($_FILES['photo']['name']);
    $target = "../uploads/doctors/" . $photo_name;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
      // Delete old photo if it's not the default
      if ($current_photo !== "default.png" && file_exists("../uploads/doctors/" . $current_photo)) {
        unlink("../uploads/doctors/" . $current_photo);
      }
    } else {
      $photo_name = $current_photo;
    }
  }

  // Update users table
  $conn->query("UPDATE users SET name='$name', email='$email', phone_number='$phone' WHERE user_id = $user_id");

  // Update doctor_profiles table
  $conn->query("UPDATE doctor_profiles SET specialization='$specialization', license_number='$license', profile_photo='$photo_name' WHERE user_id = $user_id");

  echo "<script>alert('Profile updated successfully!'); window.location='doctor_dashboard.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Doctor Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #6c5ce7;
      --primary-light: #a29bfe;
      --primary-dark: #4834d4;
      --secondary: #fd79a8;
      --accent: #00cec9;
      --light: #f8f9fa;
      --white: #ffffff;
      --text: #2d3436;
      --text-light: #636e72;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      padding: 40px;
      color: var(--text);
    }

    .form-box {
      max-width: 700px;
      margin: auto;
      background: var(--white);
      padding: 40px;
      border-radius: 16px;
      box-shadow: var(--shadow);
      position: relative;
      overflow: hidden;
      transition: var(--transition);
      animation: fadeInUp 0.8s ease;
    }

    .form-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    h2 {
      color: var(--primary-dark);
      margin-bottom: 30px;
      font-size: 2rem;
      text-align: center;
      position: relative;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 2px;
    }

    .form-group {
      margin-bottom: 20px;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-light);
    }

    input, select {
      width: 100%;
      padding: 14px 20px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      transition: var(--transition);
      background-color: var(--light);
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
    }

    button {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: var(--white);
      font-weight: 600;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1rem;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
      margin-top: 20px;
      position: relative;
      overflow: hidden;
    }

    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(108, 92, 231, 0.4);
    }

    button:active {
      transform: translateY(1px);
    }

    button::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: 0.5s;
    }

    button:hover::after {
      left: 100%;
    }

    .profile-photo-container {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .profile-photo {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid var(--white);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: var(--transition);
      cursor: pointer;
    }

    .profile-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .photo-upload {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }

    .photo-upload-label {
      display: inline-block;
      padding: 10px 20px;
      background: var(--light);
      color: var(--text);
      border-radius: 30px;
      cursor: pointer;
      transition: var(--transition);
      font-weight: 500;
      border: 2px dashed #e0e0e0;
    }

    .photo-upload-label:hover {
      background: var(--primary-light);
      color: var(--white);
      border-color: var(--primary-light);
    }

    .photo-input {
      display: none;
    }

    .specialization-select {
      position: relative;
    }

    .specialization-select::after {
      content: '\f078';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      top: 50%;
      right: 20px;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--text-light);
    }

    /* Animations */
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

    @keyframes pulse {
      0% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
      100% {
        transform: scale(1);
      }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      body {
        padding: 20px;
      }
      
      .form-box {
        padding: 30px 20px;
      }
    }

    @media (max-width: 576px) {
      .profile-photo {
        width: 120px;
        height: 120px;
      }
      
      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Edit Your Profile</h2>
  
  <form method="POST" enctype="multipart/form-data">
    <div class="profile-photo-container">
      <div class="photo-upload">
        <img src="../uploads/doctors/<?php echo $current_photo; ?>" alt="Profile Photo" class="profile-photo" id="profile-preview">
        <label for="photo-upload" class="photo-upload-label">
          <i class="fas fa-camera"></i> Change Photo
        </label>
        <input type="file" id="photo-upload" name="photo" accept="image/*" class="photo-input">
      </div>
    </div>

    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>

    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
    </div>

    <div class="form-group specialization-select">
      <label for="specialization">Specialization</label>
      <select id="specialization" name="specialization" required>
        <option value="">Select Specialization</option>
        <option value="Dermatology" <?php if($profile['specialization'] === 'Dermatology') echo "selected"; ?>>Dermatology</option>
        <option value="Cosmetology" <?php if($profile['specialization'] === 'Cosmetology') echo "selected"; ?>>Cosmetology</option>
        <option value="Trichology" <?php if($profile['specialization'] === 'Trichology') echo "selected"; ?>>Trichology</option>
      </select>
    </div>

    <div class="form-group">
      <label for="license">License Number</label>
      <input type="text" id="license" name="license" value="<?php echo htmlspecialchars($profile['license_number']); ?>" required>
    </div>

    <button type="submit" id="submit-btn">
      <i class="fas fa-save"></i> Update Profile
    </button>
  </form>
</div>

<script>
  // Preview uploaded photo
  document.getElementById('photo-upload').addEventListener('change', function(e) {
    const preview = document.getElementById('profile-preview');
    const file = e.target.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.classList.add('pulse-animation');
      setTimeout(() => {
        preview.classList.remove('pulse-animation');
      }, 500);
    }
    
    if (file) {
      reader.readAsDataURL(file);
    }
  });

  // Add pulse animation class
  const style = document.createElement('style');
  style.innerHTML = `
    .pulse-animation {
      animation: pulse 0.5s ease;
    }
  `;
  document.head.appendChild(style);
</script>

</body>
</html>