<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $result = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
  
  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $uid = $user['user_id'];
    header("Location: reset_password.php?id=$uid");
    exit;
  } else {
    $error = "Email not found!";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <style>
    body { background-color: #f4f0ff; font-family: Arial; padding: 50px; text-align: center; }
    input { padding: 10px; width: 300px; margin: 10px 0; }
    button { padding: 10px 20px; background: #b39ddb; color: #fff; border: none; border-radius: 8px; }
  </style>
</head>
<body>

<h2>Forgot Password</h2>
<form method="POST">
  <input type="email" name="email" placeholder="Enter your email" required><br>
  <button type="submit">Reset Password</button>
</form>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

</body>
</html>
