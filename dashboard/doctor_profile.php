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
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $specialization = $_POST['specialization'];
  $license = $_POST['license'];
  $photo_name = $current_photo;

  // Handle profile photo upload
  if (!empty($_FILES['photo']['name'])) {
    $photo_name = basename($_FILES['photo']['name']);
    $target = "../uploads/doctors/" . $photo_name;
    move_uploaded_file($_FILES['photo']['tmp_name'], $target);
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
  <style>
    body {
      background-color: #f3efff;
      font-family: 'Segoe UI', sans-serif;
      padding: 40px;
    }

    .form-box {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2 {
      color: #4b0082;
      margin-bottom: 20px;
    }

    input, select {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #b39ddb;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 10px;
      margin-top: 15px;
    }

    button:hover {
      background-color: #9575cd;
    }

    .profile-photo {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      margin: 15px auto;
      border: 3px solid #d1c4e9;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h2>Edit Profile</h2>
  <form method="POST" enctype="multipart/form-data">
    <img src="../uploads/doctors/<?php echo $current_photo; ?>" alt="Profile Photo" class="profile-photo">

    <label>Change Profile Photo:</label>
    <input type="file" name="photo" accept="image/*">

    <input type="text" name="name" value="<?php echo $user['name']; ?>" placeholder="Full Name" required>
    <input type="email" name="email" value="<?php echo $user['email']; ?>" placeholder="Email" required>
    <input type="text" name="phone" value="<?php echo $user['phone_number']; ?>" placeholder="Phone Number" required>

    <select name="specialization" required>
      <option value="">Select Specialization</option>
      <option value="Dermatology" <?php if($profile['specialization'] === 'Dermatology') echo "selected"; ?>>Dermatology</option>
      <option value="Cosmetology" <?php if($profile['specialization'] === 'Cosmetology') echo "selected"; ?>>Cosmetology</option>
      <option value="Trichology" <?php if($profile['specialization'] === 'Trichology') echo "selected"; ?>>Trichology</option>
    </select>

    <input type="text" name="license" value="<?php echo $profile['license_number']; ?>" placeholder="License Number" required>

    <button type="submit">Update Profile</button>
  </form>
</div>

</body>
</html>
