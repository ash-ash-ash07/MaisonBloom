<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Get booking details
$booking_query = "SELECT b.*, u.name AS patient_name, u.email AS patient_email, 
                  u.phone AS patient_phone, c.date_time, cl.meeting_url
                  FROM bookings b
                  JOIN users u ON b.patient_id = u.user_id
                  JOIN consultation_slots c ON b.slot_id = c.slot_id
                  LEFT JOIN consultation_links cl ON b.booking_id = cl.appointment_id
                  WHERE b.booking_id = $booking_id AND c.doctor_id = (SELECT doctor_id FROM doctor_profiles WHERE user_id = $doctor_id)";

$booking_result = $conn->query($booking_query);
$booking = $booking_result->fetch_assoc();

// Handle starting consultation
if (isset($_POST['start_consultation']) {
    // Generate Zoom meeting details (in a real app, you'd call Zoom API here)
    $meeting_url = "https://zoom.us/j/" . rand(100000000, 999999999);
    $meeting_password = substr(md5(time()), 0, 8);
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO consultation_links (appointment_id, meeting_url, meeting_password) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $booking_id, $meeting_url, $meeting_password);
    $stmt->execute();
    
    // Update booking status
    $conn->query("UPDATE bookings SET status = 'in_progress' WHERE booking_id = $booking_id");
    
    header("Location: doctor_consultation.php?booking_id=$booking_id");
    exit;
}

// Handle prescription submission
if (isset($_POST['submit_prescription'])) {
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $treatment_plan = $conn->real_escape_string($_POST['treatment_plan']);
    $prescribed_products = $conn->real_escape_string($_POST['prescribed_products']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $stmt = $conn->prepare("INSERT INTO prescriptions (booking_id, doctor_id, patient_id, diagnosis, treatment_plan, prescribed_products, notes) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissss", $booking_id, $doctor_id, $booking['patient_id'], $diagnosis, $treatment_plan, $prescribed_products, $notes);
    $stmt->execute();
    
    // Update booking status
    $conn->query("UPDATE bookings SET status = 'completed' WHERE booking_id = $booking_id");
    
    header("Location: doctor_dashboard.php?success=prescription_saved");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation - Maison Bloom</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include "doctor_nav.php"; ?>

    <div class="container">
        <h1>Consultation Session</h1>
        
        <div class="patient-info">
            <h2>Patient Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['patient_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['patient_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['patient_phone']); ?></p>
            <p><strong>Appointment Time:</strong> <?php echo date("F j, Y, g:i a", strtotime($booking['date_time'])); ?></p>
        </div>

        <?php if (empty($booking['meeting_url'])): ?>
            <form method="POST">
                <button type="submit" name="start_consultation" class="btn btn-primary">
                    <i class="fas fa-video"></i> Start Video Consultation
                </button>
            </form>
        <?php else: ?>
            <div class="video-consultation">
                <h2>Video Consultation</h2>
                <a href="<?php echo htmlspecialchars($booking['meeting_url']); ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-video"></i> Join Consultation Room
                </a>
                <p>Meeting Password: <?php echo htmlspecialchars($booking['meeting_password']); ?></p>
                
                <div class="iframe-container">
                    <!-- In a real implementation, you would embed the Zoom iframe here -->
                    <p>Video consultation will open in a new window</p>
                </div>
            </div>

            <div class="prescription-form">
                <h2>Create Prescription</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis:</label>
                        <textarea id="diagnosis" name="diagnosis" required class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="treatment_plan">Treatment Plan:</label>
                        <textarea id="treatment_plan" name="treatment_plan" required class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="prescribed_products">Recommended Products:</label>
                        <textarea id="prescribed_products" name="prescribed_products" class="form-control"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes:</label>
                        <textarea id="notes" name="notes" class="form-control"></textarea>
                    </div>
                    
                    <button type="submit" name="submit_prescription" class="btn btn-primary">
                        <i class="fas fa-file-prescription"></i> Submit Prescription
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>