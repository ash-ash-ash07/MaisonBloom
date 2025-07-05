<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$id = intval($_GET['id']);

// Get user info
$user = $conn->query("SELECT * FROM users WHERE user_id = $id")->fetch_assoc();

// Get doctor profile if doctor
$doctor = null;
if ($user['role'] === 'doctor') {
  $res = $conn->query("SELECT * FROM doctor_profiles WHERE user_id = $id");
  if ($res && $res->num_rows > 0) {
    $doctor = $res->fetch_assoc();
  }
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];

  $conn->query("UPDATE users SET name='$name', email='$email', phone_number='$phone' WHERE user_id = $id");

  if ($user['role'] === 'doctor') {
    $spec = $_POST['specialization'];
    $license = $_POST['license'];
    $conn->query("UPDATE doctor_profiles SET specialization='$spec', license_number='$license' WHERE user_id = $id");
  }

  echo "<script>alert('User updated!'); window.location='admin_manage_users.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit User</title>
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
    <h2>Edit User</h2>
    <form method="POST">
      <input type="text" name="name" value="<?php echo $user['name']; ?>" required>
      <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
      <input type="text" name="phone" value="<?php echo $user['phone_number']; ?>" required>

      <?php if ($user['role'] === 'doctor'): ?>
        <select name="specialization" required>
          <option value="Dermatology" <?php if($doctor['specialization']=='Dermatology') echo "selected"; ?>>Dermatology</option>
          <option value="Cosmetology" <?php if($doctor['specialization']=='Cosmetology') echo "selected"; ?>>Cosmetology</option>
          <option value="Trichology" <?php if($doctor['specialization']=='Trichology') echo "selected"; ?>>Trichology</option>
        </select>
        <input type="text" name="license" value="<?php echo $doctor['license_number']; ?>" required>
      <?php endif; ?>

      <button type="submit">Update</button>
    </form>
  </div>

</body>
</html>
