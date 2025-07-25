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

$photo = !empty($profile['profile_photo']) ? "../uploads/doctors/" . $profile['profile_photo'] : "../images/default.png";

// Get upcoming appointments count with error handling
$appointments_count = 0;
git status
git add .
git commit -m "WIP: save current dashboard changes"
git pull
$count_query = $conn->query("
    SELECT COUNT(*) as count 
    FROM bookings b
    JOIN consultation_slots c ON b.slot_id = c.slot_id
    WHERE c.doctor_id = {$profile['doctor_id']}
    AND b.status = 'approved'
    AND c.date_time > NOW()
")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Doctor Dashboard - Maison Bloom</title>
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
  </style>
</head>
<body>

<div class="dashboard-container">
  <!-- Sidebar -->
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
        <a href="#" class="nav-link">
          <i class="fas fa-users"></i>
          <span>Patient Records</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="#" class="nav-link">
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

  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <h1 class="animate__animated animate__fadeIn">Doctor Dashboard</h1>
      <div class="date-display animate__animated animate__fadeIn">
        <i class="far fa-calendar-alt"></i>
        <span><?php echo date('l, F j, Y'); ?></span>
      </div>
    </div>

    <div class="stats-cards">
      <div class="stat-card animate__animated animate__fadeInLeft">
        <i class="fas fa-calendar-check"></i>
        <div class="stat-title">Upcoming Appointments</div>
        <div class="stat-value"><?php echo $appointments_count; ?></div>
        <div class="stat-change">+2 from yesterday</div>
      </div>
      <div class="stat-card animate__animated animate__fadeInLeft animate__delay-1s">
        <i class="fas fa-user-clock"></i>
        <div class="stat-title">Pending Approvals</div>
        <div class="stat-value">3</div>
        <div class="stat-change">-1 from yesterday</div>
      </div>
      <div class="stat-card animate__animated animate__fadeInLeft animate__delay-2s">
        <i class="fas fa-file-medical"></i>
        <div class="stat-title">Prescriptions Issued</div>
        <div class="stat-value"><?php echo $prescriptions_count; ?></div>
        <div class="stat-change">+5 this week</div>
      </div>
    </div>

    <div class="quick-actions">
      <a href="doctor_add_slot.php" class="action-btn animate__animated animate__fadeIn">
        <i class="fas fa-plus-circle"></i>
        <span class="action-title">Add Slot</span>
      </a>
      <a href="doctor_manage_slots.php" class="action-btn animate__animated animate__fadeIn animate__delay-1s">
        <i class="fas fa-calendar-alt"></i>
        <span class="action-title">Manage Slots</span>
      </a>
      <a href="doctor_patient_records.php" class="action-btn animate__animated animate__fadeIn animate__delay-2s">
        <i class="fas fa-users"></i>
        <span class="action-title">Patient Records</span>
      </a>
      <a href="doctor _issue_prescription.php" class="action-btn animate__animated animate__fadeIn animate__delay-3s">
        <i class="fas fa-file-prescription"></i>
        <span class="action-title">Issue Prescription</span>
      </a>
    </div>

    <div class="upcoming-appointments animate__animated animate__fadeIn">
      <h2 class="section-title">
        <i class="fas fa-calendar-day"></i>
        <span>Upcoming Appointments</span>
      </h2>
      
      <table class="appointments-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Appointment Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $appointments = $conn->query("
            SELECT b.booking_id, u.name as patient_name, c.date_time, b.status 
            FROM bookings b
            JOIN consultation_slots c ON b.slot_id = c.slot_id
            JOIN users u ON b.patient_id = u.user_id
            WHERE c.doctor_id = (SELECT doctor_id FROM doctor_profiles WHERE user_id = $user_id)
            AND c.date_time > NOW()
            ORDER BY c.date_time ASC
            LIMIT 5
          ");
          
          if ($appointments && $appointments->num_rows > 0) {
            while ($row = $appointments->fetch_assoc()) {
              $date = date('M j, Y', strtotime($row['date_time']));
              $time = date('h:i A', strtotime($row['date_time']));
              echo "
                <tr>
                  <td class='patient-name'>{$row['patient_name']}</td>
                  <td>
                    <div class='appointment-time'>
                      <span class='appointment-date'>{$date}</span>
                      <span class='appointment-hour'>{$time}</span>
                    </div>
                  </td>
                  <td><span class='status-badge {$row['status']}'>" . ucfirst($row['status']) . "</span></td>
                  <td>
                    <i class='fas fa-eye action-icon' title='View Details'></i>
                    <i class='fas fa-video action-icon' title='Start Consultation'></i>
                    <i class='fas fa-file-prescription action-icon' title='Issue Prescription'></i>
                  </td>
                </tr>
              ";
            }
          } else {
            echo "
              <tr>
                <td colspan='4' style='text-align: center; padding: 30px; color: var(--text-light);'>
                  No upcoming appointments found
                </td>
              </tr>
            ";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  // Add scroll effect to navbar
  window.addEventListener('scroll', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.scrollY > 10) {
      sidebar.classList.add('scrolled');
    } else {
      sidebar.classList.remove('scrolled');
    }
  });

  // Add animation to stat cards on scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate__fadeIn');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.stat-card, .action-btn, .upcoming-appointments').forEach(el => {
    observer.observe(el);
  });
</script>

</body>
</html>