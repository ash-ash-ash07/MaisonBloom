<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$prescription_id = isset($_GET['prescription_id']) ? intval($_GET['prescription_id']) : 0;
$user_id = $_SESSION['user_id'];

// Check if user is either patient or doctor
$is_patient = ($_SESSION['role'] == 'patient');
$is_doctor = ($_SESSION['role'] == 'doctor');

if (!$is_patient && !$is_doctor) {
    header("Location: ../login.php");
    exit;
}

$prescription_query = "
    SELECT p.*, 
       u.name AS doctor_name, 
       d.specialization, 
       DATE_FORMAT(p.created_at, '%M %d, %Y') AS prescription_date,
       cs.date_time AS appointment_date
FROM prescriptions p
JOIN doctor_profiles d ON p.doctor_id = d.doctor_id
JOIN users u ON d.user_id = u.user_id
LEFT JOIN bookings b ON p.booking_id = b.booking_id
LEFT JOIN consultation_slots cs ON b.slot_id = cs.slot_id
WHERE p.prescription_id = $prescription_id 
AND (p.patient_id = $user_id OR d.user_id = $user_id)
";
$prescription_result = $conn->query($prescription_query);

if (!$prescription_result) {
    die("Database error: " . $conn->error);
}

$prescription = $prescription_result->fetch_assoc();

if (!$prescription) {
    header("Location: " . ($is_patient ? "patient_dashboard.php" : "doctor_dashboard.php") . "?error=prescription_not_found");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prescription - Maison Bloom</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .prescription-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .prescription-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #8a63d2;
        }
        
        .prescription-header h1 {
            color: #8a63d2;
            margin-bottom: 10px;
        }
        
        .prescription-body {
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h3 {
            color: #8a63d2;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .medication-item {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #f9f5ff;
            border-radius: 8px;
        }
        
        .medication-name {
            font-weight: 600;
            color: #6c5ce7;
        }
        
        .doctor-signature {
            margin-top: 50px;
            text-align: right;
        }
        
        .doctor-info {
            margin-top: 30px;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
        
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #8a63d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .prescription-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>

    <div class="prescription-container">
        <div class="prescription-header">
            <h1>Medical Prescription</h1>
            <p>Issued on: <?php echo htmlspecialchars($prescription['prescription_date']); ?></p>
            <?php if (!empty($prescription['appointment_date'])): ?>
                <p>For appointment on: <?php echo date("F j, Y, g:i a", strtotime($prescription['appointment_date'])); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="prescription-body">
            <?php if (!empty($prescription['diagnosis'])): ?>
            <div class="section">
                <h3>Diagnosis</h3>
                <p><?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($prescription['treatment_plan'])): ?>
            <div class="section">
                <h3>Treatment Plan</h3>
                <p><?php echo nl2br(htmlspecialchars($prescription['treatment_plan'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <h3>Medication</h3>
                <div class="medication-item">
                    <div class="medication-name"><?php echo htmlspecialchars($prescription['medication']); ?></div>
                    <div><strong>Dosage:</strong> <?php echo htmlspecialchars($prescription['dosage']); ?></div>
                    <div><strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?></div>
                </div>
            </div>
            
            <?php if (!empty($prescription['prescribed_products'])): ?>
            <div class="section">
                <h3>Recommended Products</h3>
                <p><?php echo nl2br(htmlspecialchars($prescription['prescribed_products'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($prescription['notes'])): ?>
            <div class="section">
                <h3>Additional Notes</h3>
                <p><?php echo nl2br(htmlspecialchars($prescription['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="doctor-info">
            <p><strong>Prescribed by:</strong></p>
            <p><?php echo htmlspecialchars($prescription['doctor_name']); ?></p>
            <p><?php echo htmlspecialchars($prescription['specialization']); ?></p>
            
            <div class="doctor-signature">
                <p>_________________________</p>
                <p>Signature</p>
            </div>
        </div>
        
        <button onclick="window.print()" class="print-btn no-print">
            <i class="fas fa-print"></i> Print Prescription
        </button>
    </div>
</body>
</html>