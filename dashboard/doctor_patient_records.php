<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$doctor_result = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = $user_id");
$doctor_row = $doctor_result->fetch_assoc();
$doctor_id = $doctor_row ? $doctor_row['doctor_id'] : 0;

// Get all patients who have had appointments with this doctor
$patients = $conn->query("
    SELECT DISTINCT u.user_id, u.name, u.email
    FROM bookings b
    JOIN consultation_slots c ON b.slot_id = c.slot_id
    JOIN users u ON b.patient_id = u.user_id
    WHERE c.doctor_id = $doctor_id
    ORDER BY u.name
");

// Get patient records if a specific patient is selected
$selected_patient = null;
$patient_records = [];
if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
    $selected_patient = $conn->query("SELECT * FROM users WHERE user_id = $patient_id")->fetch_assoc();
    
    // Debug: Check if we have a valid doctor_id
    if ($doctor_id > 0) {
        $patient_records_result = $conn->query("
            SELECT r.*, b.booking_id, c.date_time as appointment_date
            FROM patient_records r
            LEFT JOIN bookings b ON r.appointment_id = b.booking_id
            LEFT JOIN consultation_slots c ON b.slot_id = c.slot_id
            WHERE r.patient_id = $patient_id AND r.doctor_id = $doctor_id
            ORDER BY r.created_at DESC
        ");
        
        if ($patient_records_result) {
            $patient_records = $patient_records_result;
        }
    } else {
        // If no doctor_id found, show error
        $error = "Doctor profile not found. Please complete your profile.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Records - Maison Bloom</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    /* Your existing CSS styles here */
    :root {
      --primary: #6c5ce7;
      --primary-light: #a29bfe;
      --primary-dark: #4834d4;
      --secondary: #fd79a8;
      --accent: #00cec9;
      --light: #f8f9fa;
      --white: #ffffff;
      --text: #2d3436;
      --text-light: #636e72;
      --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
      --shadow-md: 0 5px 20px rgba(0,0,0,0.12);
      --shadow-lg: 0 10px 30px rgba(0,0,0,0.15);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      color: var(--text);
      line-height: 1.6;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .dashboard-container {
      width: 100%;
      max-width: 1200px;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .main-content {
      padding: 40px;
      width: 100%;
      text-align: center;
    }

    .header {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 40px;
      position: relative;
    }

    .header h1 {
      color: var(--primary-dark);
      font-size: 2.5rem;
      position: relative;
      display: inline-block;
      padding-bottom: 15px;
    }

    .header h1::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 2px;
    }

    .patient-selection {
      margin-bottom: 40px;
      animation: slideUp 0.6s ease-out 0.2s both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .patient-selection h3 {
      color: var(--primary-dark);
      margin-bottom: 20px;
      font-size: 1.5rem;
      position: relative;
      display: inline-block;
    }

    .patient-selection h3::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: var(--accent);
      border-radius: 2px;
    }

    .patient-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
      justify-items: center;
    }

    .patient-card {
      background: var(--white);
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      text-decoration: none;
      color: var(--text);
      width: 100%;
      max-width: 300px;
      text-align: center;
      transform-style: preserve-3d;
      perspective: 1000px;
      position: relative;
      overflow: hidden;
    }

    .patient-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      opacity: 0;
      transition: var(--transition);
      z-index: -1;
    }

    .patient-card:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: var(--shadow-md);
      color: var(--white);
    }

    .patient-card:hover::before {
      opacity: 1;
    }

    .patient-card:hover .patient-email {
      color: rgba(255,255,255,0.9);
    }

    .patient-card.active {
      background: var(--primary);
      color: var(--white);
      border-left: 5px solid var(--accent);
      transform: translateY(-3px);
    }

    .patient-card.active .patient-email {
      color: rgba(255,255,255,0.9);
    }

    .patient-name {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 8px;
    }

    .patient-email {
      font-size: 0.9rem;
      color: var(--text-light);
      transition: var(--transition);
    }

    .patient-details {
      animation: slideUp 0.6s ease-out 0.4s both;
      text-align: center;
    }

    .patient-details h3 {
      color: var(--primary-dark);
      margin-bottom: 25px;
      font-size: 1.8rem;
      position: relative;
      display: inline-block;
    }

    .patient-details h3::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 70px;
      height: 4px;
      background: var(--accent);
      border-radius: 2px;
    }

    .btn-add-record {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 25px;
      background: var(--primary);
      color: var(--white);
      border-radius: 50px;
      text-decoration: none;
      margin-bottom: 30px;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
      gap: 10px;
      font-weight: 500;
    }

    .btn-add-record:hover {
      background: var(--primary-dark);
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 6px 20px rgba(108, 92, 231, 0.4);
    }

    .records-list {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
      margin-top: 20px;
    }

    .record-card {
      background: var(--white);
      padding: 25px;
      border-radius: 15px;
      box-shadow: var(--shadow-sm);
      width: 100%;
      max-width: 800px;
      transition: var(--transition);
      border-left: 4px solid transparent;
    }

    .record-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-left: 4px solid var(--accent);
    }

    .record-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .record-date {
      font-weight: 600;
      color: var(--primary-dark);
      font-size: 1.1rem;
    }

    .record-actions a {
      color: var(--primary);
      margin-left: 15px;
      transition: var(--transition);
      font-size: 1.1rem;
    }

    .record-actions a:hover {
      color: var(--accent);
      transform: scale(1.2);
    }

    .record-diagnosis, .record-treatment {
      margin-bottom: 20px;
      text-align: left;
    }

    .record-diagnosis h4, .record-treatment h4 {
      color: var(--primary-dark);
      margin-bottom: 10px;
      font-size: 1.2rem;
      position: relative;
      display: inline-block;
    }

    .record-diagnosis h4::after, .record-treatment h4::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 40px;
      height: 2px;
      background: var(--accent);
    }

    .record-diagnosis p, .record-treatment p {
      padding-left: 10px;
      line-height: 1.7;
    }

    /* Floating animation for cards */
    @keyframes float {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    .patient-card {
      animation: float 6s ease-in-out infinite;
    }

    .patient-card:nth-child(2) {
      animation-delay: 0.2s;
    }

    .patient-card:nth-child(3) {
      animation-delay: 0.4s;
    }

    .patient-card:nth-child(4) {
      animation-delay: 0.6s;
    }

    /* Pulse animation for active elements */
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.4);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(108, 92, 231, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(108, 92, 231, 0);
      }
    }

    .patient-card.active {
      animation: float 6s ease-in-out infinite, pulse 2s infinite;
    }

    /* Error message styling */
    .alert-error {
      padding: 15px;
      background-color: #ffebee;
      color: #c62828;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #c62828;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .patient-list {
        grid-template-columns: 1fr;
      }
      
      .header h1 {
        font-size: 2rem;
      }
      
      .patient-details h3 {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .main-content {
        padding: 20px;
      }
      
      .record-card {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>
<div style="position: absolute; top: 20px; left: 20px; z-index: 1000;">
    <a href="doctor_dashboard.php" style="text-decoration: none; color: var(--purple-dark); font-size: 1.1rem; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; background: rgba(255, 255, 255, 0.9); padding: 10px 15px; border-radius: 30px; box-shadow: var(--shadow); transition: all 0.3s ease;">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>
  
  <div class="dashboard-container">
    <div class="main-content">
      <div class="header">
        <h1>Patient Records</h1>
      </div>

      <?php if (isset($error)): ?>
        <div class="alert-error">
          <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <div class="patient-selection">
        <h3>Select a Patient</h3>
        <div class="patient-list">
          <?php if ($patients && $patients->num_rows > 0): ?>
            <?php while ($patient = $patients->fetch_assoc()): ?>
              <a href="doctor_patient_records.php?patient_id=<?php echo $patient['user_id']; ?>" 
                 class="patient-card <?php echo isset($_GET['patient_id']) && $_GET['patient_id'] == $patient['user_id'] ? 'active' : ''; ?>">
                <div class="patient-name"><?php echo htmlspecialchars($patient['name']); ?></div>
                <div class="patient-email"><?php echo htmlspecialchars($patient['email']); ?></div>
              </a>
            <?php endwhile; ?>
          <?php else: ?>
            <p>No patients found.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($selected_patient): ?>
        <div class="patient-details">
          <h3>Medical Records for <?php echo htmlspecialchars($selected_patient['name']); ?></h3>
          
          <a href="doctor_add_record.php?patient_id=<?php echo $selected_patient['user_id']; ?>" class="btn-add-record">
            <i class="fas fa-plus"></i> Add New Record
          </a>
          
          <?php if ($patient_records && $patient_records->num_rows > 0): ?>
            <div class="records-list">
              <?php while ($record = $patient_records->fetch_assoc()): ?>
                <div class="record-card">
                  <div class="record-header">
                    <span class="record-date">
                      <?php 
                      if (!empty($record['appointment_date'])) {
                          echo date('M j, Y', strtotime($record['appointment_date']));
                      } else {
                          echo date('M j, Y', strtotime($record['created_at']));
                      }
                      ?>
                    </span>
                    <span class="record-actions">
                      <a href="doctor_edit_record.php?record_id=<?php echo $record['record_id']; ?>"><i class="fas fa-edit"></i></a>
                    </span>
                  </div>
                  <?php if (!empty($record['diagnosis'])): ?>
                  <div class="record-diagnosis">
                    <h4>Diagnosis</h4>
                    <p><?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?></p>
                  </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($record['treatment_plan'])): ?>
                  <div class="record-treatment">
                    <h4>Treatment Plan</h4>
                    <p><?php echo nl2br(htmlspecialchars($record['treatment_plan'])); ?></p>
                  </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($record['notes'])): ?>
                  <div class="record-treatment">
                    <h4>Notes</h4>
                    <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p>No medical records found for this patient.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>