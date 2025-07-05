<?php include "db.php"; session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role , name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role, $name);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['name'] = $name; 

        // Redirect based on role
        if ($role == 'patient') {
            header("Location: dashboard/patient_dashboard.php");
        } elseif ($role == 'doctor') {
            header("Location: dashboard/doctor_dashboard.php");
        } elseif ($role == 'admin') {
            header("Location: dashboard/admin_dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
 
  <title>Login - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
</head>
 
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
      <input type="email" name="email" required placeholder="Email">
      <input type="password" name="password" required placeholder="Password">
      <button type="submit" class="btn">Login</button>
    </form>
    <p>New user? <a href="register.php">Register</a></p>
  </div>
</body>
</html>
