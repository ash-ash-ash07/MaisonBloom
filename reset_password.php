<?php
include "db.php";

$user_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $conn->query("UPDATE users SET password = '$new_password' WHERE user_id = $user_id");
  echo "<script>alert('Password updated successfully!'); window.location='login.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <style>
    body { background-color: #f4f0ff; font-family: Arial; padding: 50px; text-align: center; }
    input { padding: 10px; width: 300px; margin: 10px 0; }
    button { padding: 10px 20px; background: #b39ddb; color: #fff; border: none; border-radius: 8px; }
  </style>
</head>
<body>

<h2>Reset Your Password</h2>
<form method="POST">
  <input type="password" name="password" placeholder="Enter new password" required><br>
  <button type="submit">Update Password</button>
</form>

</body>
</html>
