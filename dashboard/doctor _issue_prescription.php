<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info with error handling
$user_query = $conn->query("SELECT name, email FROM users WHERE user_id = $user_id");
if (!$user_query) {
    die("Database error: " . $conn->error);
}
$user = $user_query->fetch_assoc();

if (!$user) {
    die("User not found");
}

// Get doctor profile with error handling
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
$photo = !empty($profile['profile_photo']) ? "../uploads/doctors/" . $profile['profile_photo'] : "../images/default.png";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = intval($_POST['patient_id']);
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
    $medication = $conn->real_escape_string($_POST['medication']);
    $dosage = $conn->real_escape_string($_POST['dosage']);
    $instructions = $conn->real_escape_string($_POST['instructions']);
    $diagnosis = $conn->real_escape_string($_POST['diagnosis']);
    $treatment_plan = $conn->real_escape_string($_POST['treatment_plan']);
    $prescribed_products = $conn->real_escape_string($_POST['prescribed_products']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Insert prescription with error handling
    $insert_query = $conn->query("
        INSERT INTO prescriptions (
            patient_id, 
            doctor_id, 
            booking_id, 
            medication, 
            dosage, 
            instructions,
            diagnosis,
            treatment_plan,
            prescribed_products,
            notes,
            status
        )
        VALUES (
            $patient_id, 
            $doctor_id, 
            $booking_id, 
            '$medication', 
            '$dosage', 
            '$instructions',
            '$diagnosis',
            '$treatment_plan',
            '$prescribed_products',
            '$notes',
            '$status'
        )
    ");
    
    if (!$insert_query) {
        die("Error saving prescription: " . $conn->error);
    }
    
    header("Location: doctor_dashboard.php");
    exit;
}

// Get patient details if provided
$patient = null;
if (isset($_GET['patient_id'])) {
    $patient_id = intval($_GET['patient_id']);
    $patient_query = $conn->query("SELECT * FROM users WHERE user_id = $patient_id");
    if ($patient_query) {
        $patient = $patient_query->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Issue Prescription - Maison Bloom</title>
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

    /* Prescription Form */
    .prescription-form {
      max-width: 600px;
      background: var(--white);
      padding: 30px;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      margin: 0 auto;
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
    .form-section {
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .form-section-title {
      color: var(--primary);
      margin-bottom: 15px;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .form-section-title i {
      font-size: 1rem;
    }
    
    .status-select {
      display: flex;
      gap: 15px;
    }
    
    .status-option {
      display: flex;
      align-items: center;
      gap: 5px;
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
      display: inline-flex;
      align-items: center;
      gap: 10px;
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
    }

    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .prescription-form {
        padding: 20px;
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
    }
  </style>
</head>
<body>
  <div class="main-content">
    <div class="header">
      <h1>Issue Prescription</h1>
    </div>

    <div class="prescription-form">
      <form method="POST">
        <?php if ($patient): ?>
          <input type="hidden" name="patient_id" value="<?php echo $patient['user_id']; ?>">
          <div class="form-group">
            <label>Patient</label>
            <div class="patient-info">
              <?php echo htmlspecialchars($patient['name']); ?> (<?php echo htmlspecialchars($patient['email']); ?>)
            </div>
          </div>
        <?php else: ?>
          <div class="form-group">
            <label for="patient_id">Select Patient</label>
            <select name="patient_id" id="patient_id" required>
              <option value="">-- Select Patient --</option>
              <?php
              $patients = $conn->query("
                  SELECT DISTINCT u.user_id, u.name
                  FROM bookings b
                  JOIN consultation_slots c ON b.slot_id = c.slot_id
                  JOIN users u ON b.patient_id = u.user_id
                  WHERE c.doctor_id = $doctor_id
                  AND b.status = 'approved'
                  ORDER BY u.name
              ");
              
              if ($patients && $patients->num_rows > 0) {
                  while ($p = $patients->fetch_assoc()) {
                      echo "<option value='{$p['user_id']}'>{$p['name']}</option>";
                  }
              }
              ?>
            </select>
          </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['booking_id'])): ?>
          <input type="hidden" name="booking_id" value="<?php echo intval($_GET['booking_id']); ?>">
        <?php endif; ?>
        
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-pills"></i> Medication Details</h3>
          
          <div class="form-group">
            <label for="medication">Medication</label>
            <input type="text" name="medication" id="medication" required>
          </div>
          
          <div class="form-group">
            <label for="dosage">Dosage</label>
            <input type="text" name="dosage" id="dosage" required>
          </div>
          
          <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea name="instructions" id="instructions" rows="5" required></textarea>
          </div>
          
          <div class="form-group">
            <label for="prescribed_products">Prescribed Products (if any)</label>
            <textarea name="prescribed_products" id="prescribed_products" rows="3"></textarea>
          </div>
        </div>
        
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-stethoscope"></i> Medical Information</h3>
          
          <div class="form-group">
            <label for="diagnosis">Diagnosis</label>
            <textarea name="diagnosis" id="diagnosis" rows="5" required></textarea>
          </div>
          
          <div class="form-group">
            <label for="treatment_plan">Treatment Plan</label>
            <textarea name="treatment_plan" id="treatment_plan" rows="5" required></textarea>
          </div>
          
          <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea name="notes" id="notes" rows="3"></textarea>
          </div>
        </div>
        
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Prescription Status</h3>
          
          <div class="form-group">
            <label>Status</label>
            <div class="status-select">
              <label class="status-option">
                <input type="radio" name="status" value="active" checked> Active
              </label>
              <label class="status-option">
                <input type="radio" name="status" value="completed"> Completed
              </label>
            </div>
          </div>
        </div>
        
        <button type="submit" class="btn-submit">
          <i class="fas fa-save"></i> Save Prescription
        </button>
      </form>
    </div>
  </div>
</body>
</html>