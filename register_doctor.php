<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $specialization = $_POST['specialization'];
  $license = $_POST['license'];

  // Insert into users table
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


  // Insert into doctor_profiles table
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
  <title>Doctor Registration</title>
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
    }

    h2 {
      text-align: center;
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
    }

    button:hover {
      background-color: #9575cd;
    }
  </style>
</head>
<body>

  <div class="form-box">
    <h2>Doctor Registration</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Upload Profile Photo:</label>
<input type="file" name="photo" accept="image/*" required>

     <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="phone" placeholder="Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="specialization" required>
        <option value="">Select Specialization</option>
        <option value="Dermatology">Dermatology</option>
        <option value="Cosmetology">Cosmetology</option>
        <option value="Trichology">Trichology</option>
      </select>
      <input type="text" name="license" placeholder="License Number" required>
      <button type="submit">Register</button>
    </form>
  </div>

</body>
</html>
