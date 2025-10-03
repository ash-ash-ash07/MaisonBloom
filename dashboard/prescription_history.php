prescription_history

<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Get all prescriptions for the patient
$prescriptions_query = "
    SELECT p.*, 
           u.name AS doctor_name, 
           d.specialization,
           DATE_FORMAT(p.created_at, '%M %e, %Y') AS formatted_date,
           cs.date_time AS appointment_date
    FROM prescriptions p
    JOIN doctor_profiles d ON p.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    LEFT JOIN bookings b ON p.booking_id = b.booking_id
    LEFT JOIN consultation_slots cs ON b.slot_id = cs.slot_id
    WHERE p.patient_id = $user_id
    ORDER BY p.created_at DESC
";

$prescriptions_result = $conn->query($prescriptions_query);
$total_prescriptions = $prescriptions_result ? $prescriptions_result->num_rows : 0;
?>

<!DOCTYPE html>
<html>
<head>
  <title>Prescription History - Maison Bloom</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #8a63d2;
      --primary-light: #b39ddb;
      --primary-dark: #4b0082;
      --secondary: #ff85a2;
      --accent: #63d2a0;
      --light: #f9f6ff;
      --white: #ffffff;
      --text: #333333;
      --text-light: #666666;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light);
      color: var(--text);
      line-height: 1.6;
    }

    nav {
      background-color: var(--white);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo i {
      color: var(--secondary);
    }

    .nav-links {
      display: flex;
      gap: 25px;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--text);
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      padding: 5px 0;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background-color: var(--primary);
      transition: width 0.3s ease;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    .page-header {
      background: linear-gradient(135deg, rgba(138, 99, 210, 0.9) 0%, rgba(99, 210, 160, 0.8) 100%);
      padding: 40px 5%;
      color: var(--white);
      text-align: center;
    }

    .page-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .page-header p {
      font-size: 1.1rem;
      opacity: 0.9;
    }

    .prescription-history-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .prescription-count {
      background-color: var(--white);
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 30px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.05);
      text-align: center;
    }

    .prescription-count h2 {
      color: var(--primary-dark);
      font-size: 1.3rem;
    }

    .prescription-count .count {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      margin: 5px 0;
    }

    .prescription-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 25px;
    }

    .prescription-card {
      background-color: var(--white);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
    }

    .prescription-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }

    .prescription-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: var(--white);
      padding: 20px;
      position: relative;
    }

    .prescription-date {
      font-size: 0.9rem;
      opacity: 0.9;
      margin-bottom: 5px;
    }

    .prescription-id {
      font-size: 0.85rem;
      opacity: 0.8;
    }

    .prescription-body {
      padding: 20px;
    }

    .doctor-info {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .doctor-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--white);
      font-size: 1.2rem;
    }

    .doctor-details h3 {
      color: var(--primary-dark);
      font-size: 1.1rem;
      margin-bottom: 3px;
    }

    .doctor-details p {
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .prescription-details {
      margin-bottom: 20px;
    }

    .detail-item {
      margin-bottom: 10px;
      display: flex;
    }

    .detail-label {
      font-weight: 600;
      color: var(--primary-dark);
      min-width: 120px;
    }

    .detail-value {
      color: var(--text);
      flex: 1;
    }

    .truncate-text {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .prescription-actions {
      text-align: center;
      padding: 15px 20px;
      border-top: 1px solid rgba(0,0,0,0.05);
    }

    .btn {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      text-align: center;
      border: none;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .btn-primary {
      background-color: var(--primary);
      color: var(--white);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(138, 99, 210, 0.3);
    }

    .no-prescriptions {
      text-align: center;
      padding: 50px 20px;
      background-color: var(--white);
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .no-prescriptions i {
      font-size: 4rem;
      color: var(--primary-light);
      margin-bottom: 20px;
    }

    .no-prescriptions h2 {
      color: var(--primary-dark);
      margin-bottom: 15px;
    }

    .no-prescriptions p {
      color: var(--text-light);
      margin-bottom: 25px;
    }

    .back-to-dashboard {
      display: block;
      width: fit-content;
      margin: 30px auto;
      padding: 12px 25px;
      background-color: var(--primary);
      color: var(--white);
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .back-to-dashboard:hover {
      background-color: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(138, 99, 210, 0.3);
    }

    @media (max-width: 768px) {
      .prescription-cards {
        grid-template-columns: 1fr;
      }
      
      .nav-links {
        gap: 15px;
      }
      
      .page-header h1 {
        font-size: 2rem;
      }
    }

    @media (max-width: 576px) {
      nav {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
      }
      
      .prescription-card {
        width: 100%;
      }
      
      .detail-item {
        flex-direction: column;
      }
      
      .detail-label {
        margin-bottom: 5px;
      }
    }
  </style>
</head>
<body>

  <nav>
    <div class="logo">
      <i class="fas fa-spa"></i>
      <span>Maison Bloom</span>
    </div>
    <div class="nav-links">
      <a href="../home.php">Home</a>
      <a href="patient_dashboard.php">Dashboard</a>
      <a href="../booking.php">Book Now</a>
      <a href="../products.php">Products</a>
      <a href="patient_profile.php">My Profile</a>
      <a href="../logout.php">Logout</a>
    </div>
  </nav>

  <div class="page-header">
    <h1>Your Prescription History</h1>
    <p>All your past prescriptions in one place</p>
  </div>

  <div class="prescription-history-container">
    <div class="prescription-count">
      <h2>Total Prescriptions</h2>
      <div class="count"><?php echo $total_prescriptions; ?></div>
    </div>

    <?php if ($total_prescriptions > 0): ?>
      <div class="prescription-cards">
        <?php while ($prescription = $prescriptions_result->fetch_assoc()): ?>
          <div class="prescription-card">
            <div class="prescription-header">
              <div class="prescription-date"><?php echo htmlspecialchars($prescription['formatted_date']); ?></div>
              <div class="prescription-id">ID: #<?php echo htmlspecialchars($prescription['prescription_id']); ?></div>
            </div>
            
            <div class="prescription-body">
              <div class="doctor-info">
                <div class="doctor-avatar">
                  <i class="fas fa-user-md"></i>
                </div>
                <div class="doctor-details">
                  <h3><?php echo htmlspecialchars($prescription['doctor_name']); ?></h3>
                  <p><?php echo htmlspecialchars($prescription['specialization']); ?></p>
                </div>
              </div>
              
              <div class="prescription-details">
                <?php if (!empty($prescription['diagnosis'])): ?>
                  <div class="detail-item">
                    <span class="detail-label">Diagnosis:</span>
                    <span class="detail-value truncate-text"><?php echo htmlspecialchars($prescription['diagnosis']); ?></span>
                  </div>
                <?php endif; ?>
                
                <div class="detail-item">
                  <span class="detail-label">Medication:</span>
                  <span class="detail-value truncate-text"><?php echo htmlspecialchars($prescription['medication']); ?></span>
                </div>
                
                <div class="detail-item">
                  <span class="detail-label">Dosage:</span>
                  <span class="detail-value"><?php echo htmlspecialchars($prescription['dosage']); ?></span>
                </div>
                
                <?php if (!empty($prescription['appointment_date'])): ?>
                  <div class="detail-item">
                    <span class="detail-label">Appointment:</span>
                    <span class="detail-value"><?php echo date("M j, Y, g:i a", strtotime($prescription['appointment_date'])); ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="prescription-actions">
              <a href="view_prescription.php?prescription_id=<?php echo $prescription['prescription_id']; ?>" class="btn btn-primary">
                <i class="fas fa-file-prescription"></i> View Full Prescription
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="no-prescriptions">
        <i class="fas fa-file-medical"></i>
        <h2>No Prescriptions Yet</h2>
        <p>You haven't received any prescriptions yet. Book a consultation to get personalized treatment plans.</p>
        <a href="../booking.php" class="btn btn-primary">
          <i class="fas fa-calendar-alt"></i> Book a Consultation
        </a>
      </div>
    <?php endif; ?>
    
    <a href="patient_dashboard.php" class="back-to-dashboard">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

</body>
</html>