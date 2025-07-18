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
  echo "<script>alert('Slot added successfully!'); window.location='doctor_add_slot.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Consultation Slot | Maison Bloom</title>
  <style>
    :root {
      --lavender-light: #f3efff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--lavender-light);
      padding: 0;
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .container {
      width: 100%;
      max-width: 1200px;
      padding: 20px;
    }

    .form-container {
      max-width: 500px;
      margin: 0 auto;
      background: var(--white);
      padding: 40px;
      border-radius: 16px;
      box-shadow: var(--shadow);
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--lavender-dark));
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 28px;
      font-weight: 600;
    }

    .form-group {
      margin-bottom: 25px;
      text-align: left;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: var(--purple-dark);
      font-weight: 500;
      font-size: 16px;
    }

    input[type=datetime-local] {
      width: 100%;
      padding: 14px;
      border: 2px solid #e0d6ff;
      border-radius: 10px;
      font-size: 16px;
      color: var(--purple-dark);
      background: var(--lavender-light);
      transition: all 0.3s ease;
    }

    input[type=datetime-local]:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.3);
    }

    button {
      padding: 14px 32px;
      background-color: var(--lavender-dark);
      color: var(--white);
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 10px;
      box-shadow: 0 4px 12px rgba(106, 90, 205, 0.2);
    }

    button:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(106, 90, 205, 0.3);
    }

    .bloom-icon {
      width: 80px;
      height: 80px;
      margin-bottom: 20px;
      opacity: 0.9;
    }

    .back-link {
      display: inline-block;
      margin-top: 25px;
      color: var(--lavender-dark);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s;
    }

    .back-link:hover {
      color: var(--purple-dark);
      text-decoration: underline;
    }

    @media (max-width: 600px) {
      .form-container {
        padding: 30px 20px;
      }
      
      h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-container">
      <svg class="bloom-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3Z" fill="#B39DDB"/>
        <path d="M12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6Z" fill="#6A5ACD"/>
        <path d="M12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9Z" fill="#4B0082"/>
      </svg>
      <h2>Add New Consultation Slot</h2>
      <form method="POST">
        <div class="form-group">
          <label for="datetime">Select Date & Time</label>
          <input type="datetime-local" name="datetime" id="datetime" required>
        </div>
        <button type="submit">Add Consultation Slot</button>
      </form>
      <a href="doctor_manage_slots.php" class="back-link">← Back to My Slots</a>
    </div>
  </div>

  <script>
    // Set default time to next hour with AM/PM format
    document.addEventListener('DOMContentLoaded', function() {
      const now = new Date();
      now.setHours(now.getHours() + 1);
      now.setMinutes(0);
      
      // Format for datetime-local input (YYYY-MM-DDTHH:MM)
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      
      document.getElementById('datetime').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    });
  </script>
</body>
</html>