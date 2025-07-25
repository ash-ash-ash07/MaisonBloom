<?php
include 'db.php';

$booking_id = $_POST['booking_id'];
$zoom_link = "https://zoom.us/fake-meeting/".uniqid(); // Replace with real API call

// Fetch patient & doctor
$sql = "SELECT patient_id, doctor_id FROM bookings WHERE id=$booking_id";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

$insert = "INSERT INTO consultations (booking_id, patient_id, doctor_id, zoom_link, scheduled_time) 
           VALUES ($booking_id, {$data['patient_id']}, {$data['doctor_id']}, '$zoom_link', NOW())";
mysqli_query($conn, $insert);

header("Location: doctor_dashboard.php");
exit;
