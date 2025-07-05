<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

// Fetch doctor data
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT name FROM users WHERE user_id = $user_id")->fetch_assoc();
$profile = $conn->query("SELECT * FROM doctor_profiles WHERE user_id = $user_id")->fetch_assoc();
$photo = !empty($profile['profile_photo']) ? "../uploads/doctors/" . $profile['profile_photo'] : "../images/default.png";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Doctor Dashboard - Maison Bloom</title>
  <style>
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f0ff;
      height: 100vh;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      text-align: center;
      padding: 20px;
    }

    .profile-photo {
      width: 160px;
      height: 160px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #d1c4e9;
      margin-bottom: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .edit-photo {
      margin-bottom: 20px;
    }

    .edit-photo a {
      color: #6a5acd;
      font-weight: bold;
      text-decoration: none;
    }

    .edit-photo a:hover {
      text-decoration: underline;
    }

    h2 {
      color: #4b0082;
      margin-bottom: 5px;
    }

    p {
      color: #444;
      margin-bottom: 30px;
    }

    .btn {
      display: block;
      width: 240px;
      padding: 12px;
      margin: 10px auto;
      background-color: #b39ddb;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      text-decoration: none;
      font-size: 15px;
      transition: background 0.3s;
    }

    .btn:hover {
      background-color: #9575cd;
    }

    .btn.logout {
      background-color: #9e9e9e;
    }

    .btn.logout:hover {
      background-color: #757575;
    }
  </style>
</head>
<body>

<div class="container">
  <img src="<?php echo $photo; ?>" class="profile-photo" alt="Profile Photo">

  <div class="edit-photo">
    <a href="doctor_profile.php">Edit Profile Photo</a>
  </div>

  <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
  <p>Manage your consultations and patients.</p>

  <a href="doctor_profile.php" class="btn">Edit Profile</a>
  <a href="doctor_add_slot.php" class="btn">Add Slot</a>
  <a href="doctor_manage_slots.php" class="btn">Manage Consultation Slots</a>
  <a href="#" class="btn">View Patient Records</a>
  <a href="#" class="btn">Issue Prescriptions</a>
  <a href="#" class="btn">Join Virtual Consultations</a>
  <a href="../logout.php" class="btn logout">Logout</a>
</div>

</body>
</html>
