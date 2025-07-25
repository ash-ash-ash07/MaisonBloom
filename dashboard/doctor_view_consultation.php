<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$booking_id = intval($_GET['booking_id']);
$doctor_id = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['doctor_id'];

// Get consultation details
$consultation = $conn->query("
    SELECT b.*, u.name as patient_name, u.email as patient_email, 
           c.date_time, cl.meeting_url, cl.status as meeting_status
    FROM bookings b
    JOIN consultation_slots c ON b.slot_id = c.slot_id
    JOIN users u ON b.patient_id = u.user_id
    LEFT JOIN consultation_links cl ON b.booking_id = cl.appointment_id
    WHERE b.booking_id = $booking_id AND c.doctor_id = $doctor_id
")->fetch_assoc();

if (!$consultation) {
    header("Location: doctor_consultations.php");
    exit;
}

// Get patient records for this patient
$records = $conn->query("
    SELECT * FROM patient_records 
    WHERE patient_id = {$consultation['patient_id']} AND doctor_id = $doctor_id
    ORDER BY created_at DESC
");

// Get prescriptions for this patient
$prescriptions = $conn->query("
    SELECT * FROM prescriptions 
    WHERE patient_id = {$consultation['patient_id']} AND doctor_id = $doctor_id
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consultation Details - Maison Bloom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
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
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 280px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: var(--white);
      padding: 30px 20px;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-md);
      z-index: 1;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('../images/pattern.png');
      opacity: 0.05;
      z-index: -1;
    }

    .profile-section {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .profile-photo {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid rgba(255,255,255,0.2);
      margin: 0 auto 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      transition: var(--transition);
    }

    .profile-photo:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .profile-name {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .profile-email {
      font-size: 0.9rem;
      opacity: 0.8;
      margin-bottom: 15px;
    }

    .edit-profile-btn {
      display: inline-block;
      padding: 8px 20px;
      background-color: rgba(255,255,255,0.1);
      color: var(--white);
      border-radius: 30px;
      text-decoration: none;
      font-size: 0.9rem;
      transition: var(--transition);
      border: 1px solid rgba(255,255,255,0.3);
    }

    .edit-profile-btn:hover {
      background-color: rgba(255,255,255,0.2);
      transform: translateY(-2px);
    }

    .nav-menu {
      margin-top: 30px;
    }

    .nav-item {
      margin-bottom: 10px;
      position: relative;
    }

    .nav-item.active::before {
      content: '';
      position: absolute;
      left: -20px;
      top: 0;
      height: 100%;
      width: 4px;
      background-color: var(--white);
      border-radius: 0 2px 2px 0;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 12px 15px;
      color: var(--white);
      text-decoration: none;
      border-radius: 8px;
      transition: var(--transition);
      opacity: 0.9;
    }

    .nav-link:hover, .nav-item.active .nav-link {
      background-color: rgba(255,255,255,0.1);
      opacity: 1;
      transform: translateX(5px);
    }

    .nav-link i {
      margin-right: 12px;
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    .logout-btn {
      position: absolute;
      bottom: 30px;
      left: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      padding: 12px 15px;
      color: var(--white);
      text-decoration: none;
      border-radius: 8px;
      transition: var(--transition);
      background-color: rgba(255,255,255,0.1);
    }

    .logout-btn:hover {
      background-color: rgba(255,255,255,0.2);
    }

    .logout-btn i {
      margin-right: 12px;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
    }

    .header h1 {
      color: var(--primary-dark);
      font-size: 2.2rem;
      position: relative;
      display: inline-block;
    }

    .header h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 2px;
    }

    .date-display {
      background-color: var(--white);
      padding: 10px 20px;
      border-radius: 30px;
      box-shadow: var(--shadow-sm);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .stat-card {
      background-color: var(--white);
      border-radius: 12px;
      padding: 25px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .stat-card i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .stat-title {
      font-size: 1rem;
      color: var(--text-light);
      margin-bottom: 10px;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-dark);
      margin-bottom: 5px;
    }

    .stat-change {
      font-size: 0.9rem;
      color: var(--accent);
      font-weight: 500;
    }

    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .action-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 25px 15px;
      background-color: var(--white);
      border-radius: 12px;
      text-decoration: none;
      color: var(--text);
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
      text-align: center;
    }

    .action-btn:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      color: var(--primary-dark);
    }

    .action-btn i {
      font-size: 2rem;
      margin-bottom: 15px;
      color: var(--primary);
      transition: var(--transition);
    }

    .action-btn:hover i {
      transform: scale(1.1);
    }

    .action-title {
      font-weight: 600;
    }

    .upcoming-appointments {
      background-color: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow-sm);
    }

    .section-title {
      font-size: 1.5rem;
      color: var(--primary-dark);
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .section-title i {
      background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .appointments-table {
      width: 100%;
      border-collapse: collapse;
    }

    .appointments-table th {
      background-color: var(--light);
      padding: 15px;
      text-align: left;
      font-weight: 500;
      color: var(--text-light);
    }

    .appointments-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .appointments-table tr:last-child td {
      border-bottom: none;
    }

    .appointments-table tr:hover {
      background-color: rgba(108, 92, 231, 0.03);
    }

    .patient-name {
      font-weight: 600;
      color: var(--primary-dark);
    }

    .appointment-time {
      display: flex;
      flex-direction: column;
    }

    .appointment-date {
      font-weight: 500;
    }

    .appointment-hour {
      font-size: 0.9rem;
      color: var(--text-light);
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      display: inline-block;
    }

    .status-badge.approved {
      background-color: #d4edda;
      color: #155724;
    }

    .status-badge.pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .action-icon {
      color: var(--primary);
      cursor: pointer;
      transition: var(--transition);
      margin: 0 5px;
    }

    .action-icon:hover {
      color: var(--primary-dark);
      transform: scale(1.2);
    }

    /* Floating Elements */
    .floating {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(5px);
      z-index: 0;
    }

    .floating-1 {
      width: 150px;
      height: 150px;
      top: 10%;
      left: -50px;
      animation: float 8s ease-in-out infinite;
    }

    .floating-2 {
      width: 200px;
      height: 200px;
      bottom: 10%;
      right: -50px;
      animation: float 10s ease-in-out infinite reverse;
    }

    /* Animations */
    @keyframes float {
      0%, 100% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(5deg);
      }
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .sidebar {
        width: 240px;
      }
    }

    @media (max-width: 992px) {
      .dashboard-container {
        flex-direction: column;
      }
      
      .sidebar {
        width: 100%;
        padding: 20px;
      }
      
      .profile-section {
        display: flex;
        align-items: center;
        gap: 20px;
        text-align: left;
        margin-bottom: 20px;
      }
      
      .profile-photo {
        margin: 0;
        width: 80px;
        height: 80px;
      }
      
      .nav-menu {
        margin-top: 20px;
      }
      
      .logout-btn {
        position: static;
        margin-top: 20px;
      }
      
      .main-content {
        padding: 30px;
      }
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .stats-cards {
        grid-template-columns: 1fr;
      }
      
      .quick-actions {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 576px) {
      .profile-section {
        flex-direction: column;
        text-align: center;
      }
      
      .quick-actions {
        grid-template-columns: 1fr;
      }
      
      .main-content {
        padding: 20px;
      }
    }
    .patient-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.patient-card {
  background: var(--white);
  padding: 15px;
  border-radius: 8px;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  text-decoration: none;
  color: var(--text);
}

.patient-card:hover, .patient-card.active {
  background-color: var(--primary-light);
  color: var(--white);
}

.patient-card.active {
  border-left: 4px solid var(--primary-dark);
}

.patient-name {
  font-weight: 600;
}

.patient-email {
  font-size: 0.9rem;
  color: var(--text-light);
}

.patient-card.active .patient-email,
.patient-card:hover .patient-email {
  color: rgba(255,255,255,0.8);
}

.records-list {
  margin-top: 20px;
}

.record-card {
  background: var(--white);
  padding: 20px;
  border-radius: 8px;
  box-shadow: var(--shadow-sm);
  margin-bottom: 15px;
}

.record-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.record-date {
  font-weight: 600;
  color: var(--primary-dark);
}

.record-actions a {
  color: var(--primary);
  margin-left: 10px;
}

.record-diagnosis, .record-treatment {
  margin-bottom: 15px;
}

.record-diagnosis h4, .record-treatment h4 {
  color: var(--primary-dark);
  margin-bottom: 5px;
}

.btn-add-record {
  display: inline-block;
  padding: 10px 20px;
  background: var(--primary);
  color: var(--white);
  border-radius: 8px;
  text-decoration: none;
  margin-bottom: 20px;
  transition: var(--transition);
}

.btn-add-record:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
}

/* Prescription Form */
.prescription-form {
  max-width: 600px;
  background: var(--white);
  padding: 30px;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--primary-dark);
}

.form-group input[type="text"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 15px;
  border: 1px solid rgba(0,0,0,0.1);
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
}

.form-group textarea {
  min-height: 100px;
}

.btn-submit {
  background: var(--primary);
  color: var(--white);
  padding: 12px 25px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: var(--transition);
}

.btn-submit:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
}

.patient-info {
  background: rgba(108, 92, 231, 0.1);
  padding: 10px 15px;
  border-radius: 8px;
  margin-top: 5px;
}

/* Consultation Tables */
.consultation-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.consultation-table th {
  background-color: var(--primary);
  color: var(--white);
  padding: 15px;
  text-align: left;
}

.consultation-table td {
  padding: 15px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.consultation-table tr:last-child td {
  border-bottom: none;
}

.btn-join, .btn-start, .btn-view {
  display: inline-block;
  padding: 8px 15px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.9rem;
  transition: var(--transition);
}

.btn-join {
  background: var(--accent);
  color: var(--white);
}

.btn-start {
  background: var(--primary);
  color: var(--white);
}

.btn-view {
  background: var(--primary-light);
  color: var(--primary-dark);
}

.btn-join:hover, .btn-start:hover, .btn-view:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.consultation-section {
  margin-bottom: 40px;
}
  </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-container">
  <div class="sidebar">
    <div class="floating floating-1"></div>
    <div class="floating floating-2"></div>
    
    <div class="profile-section">
      <img src="<?php echo $photo; ?>" class="profile-photo" alt="Profile Photo">
      <div>
        <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
        <a href="doctor_profile.php" class="edit-profile-btn">
          <i class="fas fa-edit"></i> Edit Profile
        </a>
      </div>
    </div>

    <nav class="nav-menu">
      <div class="nav-item active">
        <a href="#" class="nav-link">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="doctor_add_slot.php" class="nav-link">
          <i class="fas fa-plus-circle"></i>
          <span>Add Slot</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="doctor_manage_slots.php" class="nav-link">
          <i class="fas fa-calendar-alt"></i>
          <span>Manage Slots</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="doctor_patient_records.php" class="nav-link">
          <i class="fas fa-users"></i>
          <span>Patient Records</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="doctor_issue_prescription.php" class="nav-link">
          <i class="fas fa-file-prescription"></i>
          <span>Prescriptions</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-video"></i>
          <span>Consultations</span>
        </a>
      </div>
    </nav>

    <a href="../logout.php" class="logout-btn">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </a>
  </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Consultation Details</h1>
            </div>
            
            <div class="consultation-details">
                <h3>Consultation with <?php echo htmlspecialchars($consultation['patient_name']); ?></h3>
                <p>Date: <?php echo date('M j, Y h:i A', strtotime($consultation['date_time'])); ?></p>
                <p>Status: <span class="status-badge <?php echo $consultation['meeting_status']; ?>">
                    <?php echo ucfirst($consultation['meeting_status']); ?>
                </span></p>
                
                <?php if ($consultation['meeting_url']): ?>
                    <a href="<?php echo $consultation['meeting_url']; ?>" target="_blank" class="btn-join">
                        <i class="fas fa-video"></i> Meeting Link
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="patient-records">
                <h3>Patient Records</h3>
                <?php if ($records && $records->num_rows > 0): ?>
                    <!-- Display records table -->
                <?php else: ?>
                    <p>No records found for this patient.</p>
                <?php endif; ?>
                
                <a href="doctor_add_record.php?patient_id=<?php echo $consultation['patient_id']; ?>" class="btn-add-record">
                    <i class="fas fa-plus"></i> Add New Record
                </a>
            </div>
            
            <div class="prescriptions">
                <h3>Prescriptions</h3>
                <?php if ($prescriptions && $prescriptions->num_rows > 0): ?>
                    <!-- Display prescriptions table -->
                <?php else: ?>
                    <p>No prescriptions found for this patient.</p>
                <?php endif; ?>
                
                <a href="doctor_issue_prescription.php?patient_id=<?php echo $consultation['patient_id']; ?>&booking_id=<?php echo $booking_id; ?>" class="btn-add-prescription">
                    <i class="fas fa-plus"></i> Issue New Prescription
                </a>
            </div>
        </div>
    </div>
</body>
</html>