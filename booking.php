<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
  header("Location: login.php");
  exit;
}

$patient_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $slot_id = intval($_POST['slot_id']);
  // Check if slot is available
  $slot_check = $conn->query("SELECT * FROM consultation_slots WHERE slot_id = $slot_id AND status = 'available'");
  if ($slot_check && $slot_check->num_rows === 0) {
    echo "<script>alert('This slot is no longer available.'); window.location.href='booking.php';</script>";
    exit;
  }

  // Check if the patient already requested this slot
  $check = $conn->query("SELECT * FROM bookings WHERE patient_id = $patient_id AND slot_id = $slot_id");
  if ($check && $check->num_rows === 0) {
    // Insert with status 'pending'
    $conn->query("INSERT INTO bookings (slot_id, patient_id, status) VALUES ($slot_id, $patient_id, 'pending')");
    echo "<script>alert('Request sent to doctor!'); window.location.href='booking.php';</script>";
    exit;
  } else {
    echo "<script>alert('You have already requested this slot.'); window.location.href='booking.php';</script>";
    exit;
  }
}

$sql = "
  SELECT s.slot_id, s.date_time, d.doctor_id, u.name AS doctor_name, d.specialization
  FROM consultation_slots s
  JOIN doctor_profiles d ON s.doctor_id = d.doctor_id
  JOIN users u ON d.user_id = u.user_id
  WHERE s.status = 'available'
  ORDER BY s.date_time ASC
";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Book Appointment - Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --success: #4caf50;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    h2 {
      text-align: center;
      color: var(--purple-dark);
      margin-bottom: 40px;
      font-size: 2rem;
      position: relative;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--purple-dark));
      border-radius: 3px;
    }

    .slots-container {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .card {
      background: var(--white);
      padding: 25px;
      border-radius: 16px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border-left: 4px solid var(--lavender-dark);
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.5s ease forwards;
    }

    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }
    .card:nth-child(5) { animation-delay: 0.5s; }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(155, 120, 255, 0.2);
    }

    .card h3 {
      color: var(--purple-dark);
      margin: 0 0 10px;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card h3 i {
      color: var(--lavender-dark);
    }

    .card p {
      margin: 8px 0;
      color: #555;
    }

    .card p strong {
      color: var(--purple-dark);
    }

    .specialization {
      display: inline-block;
      padding: 4px 10px;
      background-color: rgba(179, 157, 219, 0.1);
      color: var(--lavender-dark);
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .btn {
      background-color: var(--lavender-dark);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      margin-top: 15px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .no-slots {
      text-align: center;
      padding: 40px;
      background: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow);
    }

    .no-slots i {
      font-size: 3rem;
      color: var(--lavender-medium);
      margin-bottom: 15px;
    }

    .no-slots h3 {
      color: var(--purple-dark);
      margin-bottom: 10px;
    }

    .no-slots p {
      color: #666;
      max-width: 500px;
      margin: 0 auto;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Date badge */
    .date-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: var(--lavender-dark);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .container {
        padding: 30px 15px;
      }
      
      h2 {
        font-size: 1.5rem;
      }
      
      .card {
        padding: 20px;
      }
      
      .date-badge {
        position: static;
        display: inline-block;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h2><i class="fas fa-calendar-check"></i> Book a Consultation</h2>

  <div class="slots-container">
    <?php if ($res && $res->num_rows > 0): ?>
      <?php while ($row = $res->fetch_assoc()): 
        $date_time = strtotime($row['date_time']);
        $day_name = date('D', $date_time);
        $date_formatted = date('M j', $date_time);
        $time_formatted = date('h:i A', $date_time);
      ?>
        <div class="card">
          <span class="date-badge">
            <i class="far fa-calendar-alt"></i> <?php echo $day_name . ', ' . $date_formatted; ?>
          </span>
          <h3><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($row['doctor_name']); ?></h3>
          <p><strong>Specialization:</strong> <span class="specialization"><?php echo htmlspecialchars($row['specialization']); ?></span></p>
          <p><strong>Time:</strong> <?php echo $time_formatted; ?></p>
          <form method="POST">
            <input type="hidden" name="slot_id" value="<?php echo $row['slot_id']; ?>">
            <button type="submit" class="btn">
              <i class="far fa-bookmark"></i> Book Now
            </button>
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="no-slots">
        <i class="far fa-calendar-times"></i>
        <h3>No Available Slots</h3>
        <p>There are currently no consultation slots available. Please check back later or contact our support team.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Add subtle animation to cards when they come into view
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    cards.forEach(card => {
      observer.observe(card);
      card.style.animationPlayState = 'paused';
    });
  });
</script>

</body>
</html>