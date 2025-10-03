doctor_add_record -dashboard

<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get doctor profile
$profile_query = $conn->query("SELECT * FROM doctor_profiles WHERE user_id = $user_id");
if (!$profile_query) {
    die("Database error: " . $conn->error);
}
$profile = $profile_query->fetch_assoc();

if (!$profile) {
    header("Location: doctor_create_profile.php");
    exit;
}

$doctor_id = $profile['doctor_id'];

// Get user details including email
$user_query = $conn->query("SELECT name, email FROM users WHERE user_id = $user_id");
$user_data = $user_query->fetch_assoc();
$doctor_name = $user_data['name'];
$doctor_email = $user_data['email'];

// Get patient ID from URL if provided
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Check for success/error messages from delete operation
if (isset($_GET['success']) && $_GET['success'] === 'record_deleted') {
    $success = "Record deleted successfully!";
}
if (isset($_GET['error']) && $_GET['error'] === 'delete_failed') {
    $error = "Error deleting record. Please try again.";
}

$records = [];
if ($patient_id > 0) {
    $records_query = $conn->query("
        SELECT pr.*, u.name as doctor_name, c.date_time as appointment_date
        FROM patient_records pr
        JOIN doctor_profiles d ON pr.doctor_id = d.doctor_id
        JOIN users u ON d.user_id = u.user_id
        LEFT JOIN bookings b ON pr.appointment_id = b.booking_id
        LEFT JOIN consultation_slots c ON b.slot_id = c.slot_id
        WHERE pr.patient_id = $patient_id AND pr.doctor_id = $doctor_id
        ORDER BY pr.created_at DESC
    ");
    
    if ($records_query) {
        while ($record = $records_query->fetch_assoc()) {
            $records[] = $record;
        }
    }
}

// Get booking details if booking_id is provided
$booking = null;
if ($booking_id > 0) {
    $booking_query = $conn->query("
        SELECT b.*, u.name as patient_name, c.date_time 
        FROM bookings b
        JOIN users u ON b.patient_id = u.user_id
        JOIN consultation_slots c ON b.slot_id = c.slot_id
        WHERE b.booking_id = $booking_id
        AND c.doctor_id = $doctor_id
    ");
    if ($booking_query && $booking_query->num_rows > 0) {
        $booking = $booking_query->fetch_assoc();
    }
}

// Get all patients for this doctor
$patients_query = $conn->query("
    SELECT DISTINCT u.user_id, u.name, u.email 
    FROM users u
    JOIN bookings b ON u.user_id = b.patient_id
    JOIN consultation_slots c ON b.slot_id = c.slot_id
    WHERE c.doctor_id = $doctor_id
    ORDER BY u.name
");

// Initialize variables
$success = $success ?? "";
$error = $error ?? "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    $patient_id = intval($_POST['patient_id']);
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $treatment_plan = $conn->real_escape_string($_POST['treatment_plan']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $appointment_id = !empty($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
    
    // Validate required fields
    if (empty($diagnosis)) {
        $error = "Diagnosis is required!";
    } else {
        // Insert into database
        $insert_query = "
            INSERT INTO patient_records 
            (patient_id, doctor_id, appointment_id, diagnosis, treatment_plan, notes) 
            VALUES ($patient_id, $doctor_id, " . ($appointment_id ? $appointment_id : 'NULL') . ", '$diagnosis', '$treatment_plan', '$notes')
        ";
        
        if ($conn->query($insert_query)) {
            $success = "Patient record added successfully!";
            
            // If this is associated with a booking, update booking status if needed
            if ($appointment_id) {
                $conn->query("UPDATE bookings SET has_record = 1 WHERE booking_id = $appointment_id");
            }
            
            // Clear form if not staying on the same patient
            if (!isset($_POST['stay_on_patient'])) {
                // Reset form fields but keep patient selected
                $_POST['diagnosis'] = '';
                $_POST['treatment_plan'] = '';
                $_POST['notes'] = '';
            }
        } else {
            $error = "Error adding record: " . $conn->error;
        }
    }
}


// Get patient records if a patient is selected
$patient = null;
if ($patient_id > 0) {
    $patient_query = $conn->query("
        SELECT u.*, pp.phone, pp.date_of_birth 
        FROM users u 
        LEFT JOIN patient_profiles pp ON u.user_id = pp.user_id 
        WHERE u.user_id = $patient_id
    ");
    if ($patient_query && $patient_query->num_rows > 0) {
        $patient = $patient_query->fetch_assoc();
    }
}
?>

<?php if ($patient): ?>
  <div class="patient-info">
    <h3>Patient Information</h3>
    <div class="patient-details">
      <div class="patient-detail"><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></div>
      <div class="patient-detail"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></div>
      <div class="patient-detail"><strong>Phone:</strong> <?php echo !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'Not provided'; ?></div>
      <div class="patient-detail"><strong>Age:</strong> 
        <?php 
        if (!empty($patient['date_of_birth'])) {
            $birthDate = new DateTime($patient['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            echo $age . ' years';
        } else {
            echo 'Not provided';
        }
        ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Patient Record - Maison Bloom</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
      --success: #00b894;
      --danger: #d63031;
      --warning: #fdcb6e;
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
    }

    .profile-photo {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid rgba(255,255,255,0.2);
      margin: 0 auto 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .profile-name {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 5px;
    }

    .profile-email {
      font-size: 0.9rem;
      opacity: 0.8;
    }

    .nav-menu {
      margin-top: 30px;
    }

    .nav-item {
      margin-bottom: 10px;
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
      margin-bottom: 30px;
    }

    .header h1 {
      color: var(--primary-dark);
      font-size: 2rem;
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

    .back-btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      background-color: var(--white);
      color: var(--primary);
      text-decoration: none;
      border-radius: 30px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }

    .back-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .back-btn i {
      margin-right: 8px;
    }

    /* Content Layout */
    .content-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    @media (max-width: 992px) {
      .content-wrapper {
        grid-template-columns: 1fr;
      }
    }

    /* Form Section */
    .form-section {
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

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--primary-dark);
    }

    .form-group select,
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid rgba(0,0,0,0.1);
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      transition: var(--transition);
    }

    .form-group select:focus,
    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
    }

    .form-group textarea {
      min-height: 120px;
      resize: vertical;
    }

    .patient-info {
      background-color: rgba(108, 92, 231, 0.05);
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid var(--primary);
    }

    .patient-info h3 {
      color: var(--primary-dark);
      margin-bottom: 10px;
    }

    .patient-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .patient-detail {
      font-size: 0.9rem;
    }

    .patient-detail strong {
      color: var(--primary-dark);
    }

    .btn-group {
      display: flex;
      gap: 15px;
      margin-top: 25px;
    }

    .btn {
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn i {
      margin-right: 8px;
    }

    .btn-primary {
      background-color: var(--primary);
      color: var(--white);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .btn-secondary {
      background-color: var(--light);
      color: var(--text);
    }

    .btn-secondary:hover {
      background-color: #e9ecef;
      transform: translateY(-2px);
    }

    /* Records Section */
    .records-section {
      background-color: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow-sm);
      max-height: 800px;
      overflow-y: auto;
    }

    .records-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 20px;
    }

    .records-count {
      font-size: 0.9rem;
      color: var(--text-light);
    }

    .record-card {
      background-color: var(--light);
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 15px;
      border-left: 4px solid var(--accent);
      transition: var(--transition);
    }

    .record-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }

    .record-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
    }

    .record-date {
      font-weight: 600;
      color: var(--primary-dark);
    }

    .record-actions {
      display: flex;
      gap: 10px;
    }

    .action-btn {
      color: var(--primary);
      text-decoration: none;
      font-size: 0.9rem;
      transition: var(--transition);
      padding: 5px 10px;
      border-radius: 4px;
      background: rgba(108, 92, 231, 0.1);
    }
    
    .action-btn:hover {
      color: var(--white);
      background: var(--primary);
    }
    
    .action-btn.edit {
      color: var(--accent);
      background: rgba(0, 206, 201, 0.1);
    }
    
    .action-btn.edit:hover {
      color: var(--white);
      background: var(--accent);
    }
    
    .action-btn.delete {
      color: var(--danger);
      background: rgba(214, 48, 49, 0.1);
    }
    
    .action-btn.delete:hover {
      color: var(--white);
      background: var(--danger);
    }

    .record-content {
      margin-bottom: 15px;
    }

    .record-field {
      margin-bottom: 10px;
    }

    .record-field strong {
      color: var(--primary-dark);
      display: block;
      margin-bottom: 5px;
    }

    .record-field p {
      color: var(--text);
      line-height: 1.5;
    }

    .no-records {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-light);
    }

    .no-records i {
      font-size: 3rem;
      margin-bottom: 15px;
      color: var(--primary-light);
    }

    /* Alerts */
    .alert {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    .alert i {
      margin-right: 10px;
      font-size: 1.2rem;
    }

    .alert-success {
      background-color: rgba(0, 184, 148, 0.1);
      color: var(--success);
      border-left: 4px solid var(--success);
    }

    .alert-error {
      background-color: rgba(214, 48, 49, 0.1);
      color: var(--danger);
      border-left: 4px solid var(--danger);
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
      
      .patient-details {
        grid-template-columns: 1fr;
      }
      
      .btn-group {
        flex-direction: column;
      }
    }

    @media (max-width: 576px) {
      .profile-section {
        flex-direction: column;
        text-align: center;
      }
      
      .main-content {
        padding: 20px;
      }
      
      .form-section, .records-section {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="profile-section">
      <img src="../uploads/doctors/<?php echo !empty($profile['profile_photo']) ? $profile['profile_photo'] : 'default.png'; ?>" class="profile-photo" alt="Profile Photo">
      <div>
        <div class="profile-name"><?php echo htmlspecialchars($doctor_name); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($doctor_email); ?></div>
      </div>
    </div>

    <nav class="nav-menu">
      <div class="nav-item">
        <a href="doctor_dashboard.php" class="nav-link">
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
      <div class="nav-item active">
        <a href="doctor_add_record.php" class="nav-link">
          <i class="fas fa-file-medical"></i>
          <span>Add Record</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="doctor_consultations.php" class="nav-link">
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

  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <h1>Add Patient Record</h1>
      <a href="doctor_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <div class="content-wrapper">
      <!-- Form Section -->
      <div class="form-section">
        <h2 class="section-title">
          <i class="fas fa-file-medical"></i>
          <span>New Patient Record</span>
        </h2>

        <form method="POST" action="">
          <div class="form-group">
            <label for="patient_id">Select Patient</label>
            <select name="patient_id" id="patient_id" required onchange="this.form.submit()">
              <option value="">-- Select a Patient --</option>
              <?php 
              // Reset the pointer and loop through patients again
              $patients_query->data_seek(0);
              while ($p = $patients_query->fetch_assoc()): ?>
                <option value="<?php echo $p['user_id']; ?>" <?php echo $p['user_id'] == $patient_id ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['email']); ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <?php if ($booking_id > 0): ?>
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
          <?php endif; ?>

          <?php if ($patient): ?>
  <div class="patient-info">
    <h3>Patient Information</h3>
    <div class="patient-details">
      <div class="patient-detail"><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></div>
      <div class="patient-detail"><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></div>
      <div class="patient-detail"><strong>Phone:</strong> <?php echo !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'Not provided'; ?></div>
      <div class="patient-detail"><strong>Age:</strong> 
        <?php 
        if (!empty($patient['date_of_birth'])) {
            $birthDate = new DateTime($patient['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            echo $age . ' years';
        } else {
            echo 'Not provided';
        }
        ?>
      </div>
    </div>
  </div>
<?php endif; ?>

          <div class="form-group">
            <label for="diagnosis">Diagnosis *</label>
            <textarea name="diagnosis" id="diagnosis" placeholder="Enter diagnosis details" required><?php echo isset($_POST['diagnosis']) ? htmlspecialchars($_POST['diagnosis']) : ''; ?></textarea>
          </div>

          <div class="form-group">
            <label for="treatment_plan">Treatment Plan</label>
            <textarea name="treatment_plan" id="treatment_plan" placeholder="Enter treatment plan"><?php echo isset($_POST['treatment_plan']) ? htmlspecialchars($_POST['treatment_plan']) : ''; ?></textarea>
          </div>

          <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea name="notes" id="notes" placeholder="Enter any additional notes"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
          </div>

          <div class="btn-group">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Record
            </button>
            <?php if ($patient_id > 0): ?>
              <button type="submit" name="stay_on_patient" value="1" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Save and Add Another
              </button>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Records Section -->
      <div class="records-section">
        <div class="records-header">
          <h2 class="section-title">
            <i class="fas fa-history"></i>
            <span>Past Records</span>
          </h2>
          <div class="records-count">
            <?php echo count($records); ?> record(s)
          </div>
        </div>

        <?php if (count($records) > 0): ?>
          <?php foreach ($records as $record): ?>
            <div class="record-card">
              <div class="record-header">
                <div class="record-date">
                  <?php echo date('M j, Y', strtotime($record['created_at'])); ?>
                  <?php if (!empty($record['appointment_date'])): ?>
                    <br><small>Appointment: <?php echo date('M j, Y', strtotime($record['appointment_date'])); ?></small>
                  <?php endif; ?>
                </div>
                <div class="record-actions">
                  <a href="doctor_edit_record.php?record_id=<?php echo $record['record_id']; ?>" class="action-btn edit" title="Edit">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="javascript:void(0)" onclick="deleteRecord(<?php echo $record['record_id']; ?>)" class="action-btn delete" title="Delete">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </div>
              </div>
              
              <div class="record-content">
                <?php if (!empty($record['diagnosis'])): ?>
                  <div class="record-field">
                    <strong>Diagnosis:</strong>
                    <p><?php echo nl2br(htmlspecialchars($record['diagnosis'])); ?></p>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($record['treatment_plan'])): ?>
                  <div class="record-field">
                    <strong>Treatment Plan:</strong>
                    <p><?php echo nl2br(htmlspecialchars($record['treatment_plan'])); ?></p>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($record['notes'])): ?>
                  <div class="record-field">
                    <strong>Notes:</strong>
                    <p><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-records">
            <i class="fas fa-file-medical"></i>
            <h3>No Records Found</h3>
            <p>No medical records found for this patient yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  function deleteRecord(recordId) {
    if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
      window.location.href = 'doctor_delete_record.php?record_id=' + recordId;
    }
  }
</script>

</body>
</html>