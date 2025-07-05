<?php
session_start();
include "../db.php";

// Ensure only doctors can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
  header("Location: ../login.php");
  exit;
}

// Get doctor_id from profile table
$doctor = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

// Add slot
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $datetime = $_POST['datetime'];
  $stmt = $conn->prepare("INSERT INTO consultation_slots (doctor_id, date_time) VALUES (?, ?)");
  $stmt->bind_param("is", $doctor_id, $datetime);
  $stmt->execute();
  echo "<script>alert('Slot added!'); window.location='doctor_add_slot.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Consultation Slot</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f0ff;
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
    <h2>Add Consultation Slot</h2>
    <form method="POST">
      <label>Select Date and Time:</label><br>
      <input type="datetime-local" name="datetime" required>
      <button type="submit">Add Slot</button>
    </form>
  </div>
</body>
</html>
