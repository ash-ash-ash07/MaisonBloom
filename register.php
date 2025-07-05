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
        header("Location: login.php");
        exit;
    } else {
        echo "Registration failed: " . $stmt->error;
    }
    if (!empty($_FILES['photo']['name'])) {
  $photo_name = basename($_FILES['photo']['name']);
  $target = "uploads/patients/" . $photo_name;
  move_uploaded_file($_FILES['photo']['tmp_name'], $target);
  $conn->query("UPDATE users SET profile_photo = '$photo_name' WHERE user_id = $user_id");
}

}
?>


<!DOCTYPE html>
<html>
  
<head>
  <title>Register - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
  <div class="container">
    <h2>Register</h2>
      <form method="POST" enctype="multipart/form-data">
  <input type="file" name="photo" accept="image/*">
      <input type="text" name="name" required placeholder="Full Name">
      <input type="email" name="email" required placeholder="Email">
      <input type="password" name="password" required placeholder="Password">
      <input type="text" name="phone" placeholder="Phone Number">
      <select name="role" required>
        <option value="">Select Role</option>
        <option value="patient">Patient</option>
    </select>
      <button type="submit" class="btn">Register</button>
    </form>
    <p>Are you a doctor? <a href="register_doctor.php">Register here</a></p>

    <p>Already registered? <a href="login.php">Login</a></p>
  </div>
</body>
</html>
