<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$record_id = isset($_GET['record_id']) ? intval($_GET['record_id']) : 0;

if ($record_id === 0) {
    header("Location: doctor_add_record.php?error=invalid_record");
    exit;
}

// Get doctor profile to verify ownership
$profile_query = $conn->query("SELECT * FROM doctor_profiles WHERE user_id = $user_id");
if ($profile_query && $profile_query->num_rows > 0) {
    $profile = $profile_query->fetch_assoc();
    $doctor_id = $profile['doctor_id'];
} else {
    header("Location: doctor_add_record.php?error=doctor_not_found");
    exit;
}

// Get record to verify ownership and get patient_id for redirect
$record_query = $conn->query("
    SELECT patient_id FROM patient_records 
    WHERE record_id = $record_id AND doctor_id = $doctor_id
");

if ($record_query && $record_query->num_rows > 0) {
    $record = $record_query->fetch_assoc();
    $patient_id = $record['patient_id'];
    
    // Delete the record
    $delete_query = "DELETE FROM patient_records WHERE record_id = $record_id AND doctor_id = $doctor_id";
    
    if ($conn->query($delete_query)) {
        header("Location: doctor_add_record.php?patient_id=$patient_id&success=record_deleted");
    } else {
        header("Location: doctor_add_record.php?patient_id=$patient_id&error=delete_failed");
    }
} else {
    header("Location: doctor_add_record.php?error=record_not_found");
}
exit;