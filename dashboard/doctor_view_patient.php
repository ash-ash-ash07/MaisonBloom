<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

// Get doctor ID with error handling
$doctor_query = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}");
if (!$doctor_query) {
    die("Database error: " . $conn->error);
}
$doctor = $doctor_query->fetch_assoc();
if (!$doctor) {
    die("Doctor profile not found");
}
$doctor_id = $doctor['doctor_id'];

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
if ($patient_id <= 0) {
    header("Location: doctor_dashboard.php");
    exit;
}

// Get patient details with error handling
$patient_query = $conn->query("
    SELECT u.*, p.phone, p.gender, p.date_of_birth, p.address, p.medical_history
    FROM users u
    LEFT JOIN patient_profiles p ON u.user_id = p.user_id
    WHERE u.user_id = $patient_id
");
if (!$patient_query) {
    die("Database error: " . $conn->error);
}
$patient = $patient_query->fetch_assoc();

if (!$patient) {
    header("Location: doctor_dashboard.php");
    exit;
}

// Get consultation history with error handling
$consultations = $conn->query("
    SELECT b.booking_id, c.date_time, b.status
    FROM bookings b
    JOIN consultation_slots c ON b.slot_id = c.slot_id
    WHERE b.patient_id = $patient_id AND c.doctor_id = $doctor_id
    ORDER BY c.date_time DESC
");
if (!$consultations) {
    die("Database error: " . $conn->error);
}

// Get prescriptions history with error handling
$prescriptions = $conn->query("
    SELECT * FROM prescriptions 
    WHERE patient_id = $patient_id AND doctor_id = $doctor_id
    ORDER BY created_at DESC
");
if (!$prescriptions) {
    die("Database error: " . $conn->error);
}

// Get doctor profile photo for sidebar
$profile_query = $conn->query("SELECT profile_photo FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}");
if (!$profile_query) {
    die("Database error: " . $conn->error);
}
$profile = $profile_query->fetch_assoc();
$photo = !empty($profile['profile_photo']) ? "../uploads/doctors/" . $profile['profile_photo'] : "../images/default.png";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Details - Maison Bloom</title>
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

    .patient-profile {
      background-color: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 30px;
    }

    .profile-header {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
    }

    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 30px;
      border: 3px solid var(--primary-light);
    }

    .profile-info h2 {
      color: var(--primary-dark);
      margin-bottom: 10px;
    }

    .profile-info p {
      color: var(--text-light);
      margin-bottom: 5px;
    }

    .profile-details {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }

    .detail-card {
      background-color: var(--light);
      padding: 20px;
      border-radius: 8px;
    }

    .detail-card h3 {
      color: var(--primary);
      margin-bottom: 15px;
      font-size: 1.1rem;
    }

    .detail-item {
      margin-bottom: 10px;
    }

    .detail-label {
      font-weight: 500;
      color: var(--text-light);
      display: block;
      margin-bottom: 3px;
    }

    .detail-value {
      color: var(--text);
    }

    .medical-history {
      white-space: pre-line;
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

    .consultation-history, .prescription-history {
      background-color: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 30px;
    }

    .history-table {
      width: 100%;
      border-collapse: collapse;
    }

    .history-table th {
      background-color: var(--primary);
      color: var(--white);
      padding: 15px;
      text-align: left;
    }

    .history-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .history-table tr:last-child td {
      border-bottom: none;
    }

    .history-table tr:hover {
      background-color: rgba(108, 92, 231, 0.03);
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
      display: inline-block;
    }

    .status-badge.completed {
      background-color: #d4edda;
      color: #155724;
    }

    .status-badge.approved {
      background-color: #cce5ff;
      color: #004085;
    }

    .status-badge.cancelled {
      background-color: #f8d7da;
      color: #721c24;
    }

    .action-btn {
      display: inline-block;
      padding: 8px 15px;
      background-color: var(--primary);
      color: var(--white);
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.9rem;
      transition: var(--transition);
      margin-top: 20px;
    }

    .action-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }

    .action-btn i {
      margin-right: 8px;
    }

    /* Responsive Design */
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

      .profile-header {
        flex-direction: column;
        text-align: center;
      }

      .profile-avatar {
        margin-right: 0;
        margin-bottom: 20px;
      }
    }

    @media (max-width: 576px) {
      .main-content {
        padding: 20px;
      }
      
      .profile-details {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>


  <div class="main-content">
    <div class="header">
      <h1>Patient Details</h1>
      <a href="doctor_dashboard.php" class="action-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

    <div class="patient-profile">
      <div class="profile-header">
        <img src="../images/default-patient.png" alt="Patient Avatar" class="profile-avatar">
        <div class="profile-info">
          <h2><?php echo htmlspecialchars($patient['name']); ?></h2>
          <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?></p>
          <?php if (!empty($patient['phone'])): ?>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['phone']); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-details">
        <div class="detail-card">
          <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
          <div class="detail-item">
            <span class="detail-label">Gender</span>
            <span class="detail-value"><?php echo !empty($patient['gender']) ? htmlspecialchars($patient['gender']) : 'Not specified'; ?></span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Date of Birth</span>
            <span class="detail-value"><?php echo !empty($patient['date_of_birth']) ? date('M j, Y', strtotime($patient['date_of_birth'])) : 'Not specified'; ?></span>
          </div>
        </div>

        <div class="detail-card">
          <h3><i class="fas fa-map-marker-alt"></i> Contact Information</h3>
          <div class="detail-item">
            <span class="detail-label">Address</span>
            <span class="detail-value"><?php echo !empty($patient['address']) ? htmlspecialchars($patient['address']) : 'Not specified'; ?></span>
          </div>
        </div>

        <?php if (!empty($patient['medical_history'])): ?>
        <div class="detail-card">
          <h3><i class="fas fa-notes-medical"></i> Medical History</h3>
          <div class="detail-item">
            <span class="detail-value medical-history"><?php echo htmlspecialchars($patient['medical_history']); ?></span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="consultation-history">
      <h2 class="section-title">
        <i class="fas fa-calendar-check"></i>
        <span>Consultation History</span>
      </h2>
      
      <?php if ($consultations && $consultations->num_rows > 0): ?>
        <table class="history-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($consultation = $consultations->fetch_assoc()): ?>
              <tr>
                <td><?php echo date('M j, Y h:i A', strtotime($consultation['date_time'])); ?></td>
                <td><span class="status-badge <?php echo $consultation['status']; ?>"><?php echo ucfirst($consultation['status']); ?></span></td>
                <td>
                  <a href="doctor_view_consultation.php?booking_id=<?php echo $consultation['booking_id']; ?>" class="action-icon" title="View Details">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if ($consultation['status'] == 'approved' || $consultation['status'] == 'completed'): ?>
                    <a href="doctor_issue_prescription.php?patient_id=<?php echo $patient_id; ?>&booking_id=<?php echo $consultation['booking_id']; ?>" class="action-icon" title="Create Prescription">
                      <i class="fas fa-file-prescription"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No consultation history found.</p>
      <?php endif; ?>
    </div>

    <div class="prescription-history">
      <h2 class="section-title">
        <i class="fas fa-prescription-bottle-alt"></i>
        <span>Prescription History</span>
      </h2>
      
      <?php if ($prescriptions && $prescriptions->num_rows > 0): ?>
        <table class="history-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Medication</th>
              <th>Dosage</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($prescription = $prescriptions->fetch_assoc()): ?>
              <tr>
                <td><?php echo date('M j, Y', strtotime($prescription['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($prescription['medication']); ?></td>
                <td><?php echo htmlspecialchars($prescription['dosage']); ?></td>
                <td>
                  <a href="view_prescription.php?id=<?php echo $prescription['prescription_id']; ?>" class="action-icon" title="View Prescription">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No prescription history found.</p>
      <?php endif; ?>
      
      <a href="doctor_issue_prescription.php?patient_id=<?php echo $patient_id; ?>" class="action-btn">
        <i class="fas fa-plus"></i> Issue New Prescription
      </a>
    </div>
  </div>
</div>
</body>
</html>