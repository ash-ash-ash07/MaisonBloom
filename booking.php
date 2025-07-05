<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
  header("Location: login.php");
  exit;
}

$patient_id = $_SESSION['user_id'];

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $doctor_id = $_POST['doctor_id'];
  $slot_id = $_POST['slot_id'];

  // Book slot and insert consultation
  $conn->query("UPDATE consultation_slots SET status='booked' WHERE slot_id=$slot_id");

  $stmt = $conn->prepare("INSERT INTO consultations (patient_id, doctor_id, slot_id) VALUES (?, ?, ?)");
  $stmt->bind_param("iii", $patient_id, $doctor_id, $slot_id);
  $stmt->execute();

  echo "<script>alert('Appointment booked successfully!'); window.location='booking.php';</script>";
  exit;
}

// Fetch all available slots with doctor info
$query = "
  SELECT s.slot_id, s.date_time, d.doctor_id, u.name AS doctor_name, d.specialization
  FROM consultation_slots s
  JOIN doctor_profiles d ON s.doctor_id = d.doctor_id
  JOIN users u ON d.user_id = u.user_id
  WHERE s.status = 'available'
  ORDER BY s.date_time ASC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Book Appointment - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f0ff;
      padding: 40px;
    }

    .container {
      max-width: 900px;
      margin: auto;
    }

    h2 {
      color: #4b0082;
      text-align: center;
      margin-bottom: 30px;
    }

    .card {
      background: #fff;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .card h3 {
      margin-bottom: 5px;
      color: #6a5acd;
    }

    .card p {
      margin: 0 0 10px;
      color: #333;
    }

    form {
      display: inline;
    }

    .btn {
      background-color: #b39ddb;
      color: white;
      padding: 8px 18px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
    }

    .btn:hover {
      background-color: #9575cd;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Book a Consultation</h2>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="card">
        <h3> <?php echo $row['doctor_name']; ?></h3>
        <p><strong>Specialization:</strong> <?php echo $row['specialization']; ?></p>
        <p><strong>Date & Time:</strong> <?php echo date('D, M j, Y - h:i A', strtotime($row['date_time'])); ?></p>
        <form method="POST">
          <input type="hidden" name="doctor_id" value="<?php echo $row['doctor_id']; ?>">
          <input type="hidden" name="slot_id" value="<?php echo $row['slot_id']; ?>">
          <button class="btn" type="submit">Book Now</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center;">No consultation slots are currently available.</p>
  <?php endif; ?>
</div>

</body>
</html>
