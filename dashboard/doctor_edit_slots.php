<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$slot_id = intval($_GET['id']);
$doctor = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

// Get current slot info
$res = $conn->query("SELECT * FROM consultation_slots WHERE slot_id = $slot_id AND doctor_id = $doctor_id");
if (!$res || $res->num_rows == 0) {
  die("Invalid slot.");
}
$slot = $res->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $new_time = $_POST['datetime'];
  $conn->query("UPDATE consultation_slots SET date_time = '$new_time' WHERE slot_id = $slot_id AND doctor_id = $doctor_id");
  echo "<script>alert('Slot updated.'); window.location='doctor_manage_slots.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Slot</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f3efff;
      padding: 50px;
    }

    .form-container {
      max-width: 500px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    h2 {
      color: #4b0082;
    }

    input[type=datetime-local] {
      width: 100%;
      padding: 10px;
      margin: 20px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
    }

    button {
      padding: 10px 24px;
      background-color: #b39ddb;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: #9575cd;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Edit Consultation Slot</h2>
    <form method="POST">
      <label>Select New Date and Time:</label><br>
      <input type="datetime-local" name="datetime"
             value="<?php echo date('Y-m-d\TH:i', strtotime($slot['date_time'])); ?>"
             required>
      <button type="submit">Update Slot</button>
    </form>
  </div>
</body>
</html>
