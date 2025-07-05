<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rating = intval($_POST['rating']);
  $message = $conn->real_escape_string($_POST['message']);
  $patient_id = $_SESSION['user_id'];

  $conn->query("INSERT INTO feedbacks (patient_id, rating, message) VALUES ($patient_id, $rating, '$message')");
  echo "<script>alert('Thank you for your feedback!'); window.location='patient_dashboard.php';</script>";
}
?>

<form method="POST" style="max-width:500px; margin:auto;">
  <h3>Submit Feedback</h3>
  <label>Rating:</label>
  <select name="rating" required>
    <option value="5">★★★★★</option>
    <option value="4">★★★★☆</option>
    <option value="3">★★★☆☆</option>
    <option value="2">★★☆☆☆</option>
    <option value="1">★☆☆☆☆</option>
  </select><br><br>

  <textarea name="message" rows="5" placeholder="Write your feedback..." required style="width:100%;"></textarea><br><br>
  <button type="submit">Submit</button>
</form>
