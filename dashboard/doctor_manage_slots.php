<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  header("Location: login.php");
  exit;
}

$doctor_id = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['doctor_id'];

if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM consultation_slots WHERE slot_id = $id AND doctor_id = $doctor_id");
  header("Location: doctor_manage_slots.php");
  exit;
}

if (isset($_GET['approve'])) {
  $bid = intval($_GET['approve']);
  // Get the slot_id for this booking
  $slot_info = $conn->query("SELECT slot_id FROM bookings WHERE booking_id = $bid")->fetch_assoc();
  $slot_id = $slot_info['slot_id'];
  
  // Update booking status and mark slot as booked
  $conn->query("UPDATE bookings SET status = 'approved' WHERE booking_id = $bid");
  $conn->query("UPDATE consultation_slots SET status = 'booked' WHERE slot_id = $slot_id");
  
  // Reject all other pending requests for this slot
  $conn->query("UPDATE bookings SET status = 'rejected' WHERE slot_id = $slot_id AND status = 'pending'");
  
  header("Location: doctor_manage_slots.php");
  exit;
}

if (isset($_GET['reject'])) {
  $bid = intval($_GET['reject']);
  $conn->query("UPDATE bookings SET status = 'rejected' WHERE booking_id = $bid");
  header("Location: doctor_manage_slots.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Slots - Doctor | Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --success: #4caf50;
      --error: #f44336;
      --warning: #ff9800;
      --info: #2196f3;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    h2 {
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 2rem;
      position: relative;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--purple-dark));
      border-radius: 3px;
    }

    .table-container {
      background: var(--white);
      border-radius: 16px;
      padding: 20px;
      box-shadow: var(--shadow);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0 auto;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid rgba(179, 157, 219, 0.2);
    }

    th {
      background-color: var(--purple-dark);
      color: var(--white);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 0.5px;
      position: sticky;
      top: 0;
    }

    tr {
      transition: all 0.3s ease;
    }

    tr:hover {
      background-color: rgba(179, 157, 219, 0.05);
    }

    .status-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .status-available {
      background-color: rgba(76, 175, 80, 0.1);
      color: var(--success);
    }

    .status-booked {
      background-color: rgba(106, 90, 205, 0.1);
      color: var(--lavender-dark);
    }

    .status-pending {
      background-color: rgba(255, 152, 0, 0.1);
      color: var(--warning);
    }

    .btn {
      padding: 8px 15px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      margin: 3px;
    }

    .edit-btn {
      background-color: rgba(106, 90, 205, 0.1);
      color: var(--lavender-dark);
    }

    .edit-btn:hover {
      background-color: rgba(106, 90, 205, 0.2);
    }

    .delete-btn {
      background-color: rgba(217, 83, 79, 0.1);
      color: var(--error);
    }

    .delete-btn:hover {
      background-color: rgba(217, 83, 79, 0.2);
    }

    .approve-btn {
      background-color: rgba(76, 175, 80, 0.1);
      color: var(--success);
    }

    .approve-btn:hover {
      background-color: rgba(76, 175, 80, 0.2);
    }

    .reject-btn {
      background-color: rgba(255, 152, 0, 0.1);
      color: var(--warning);
    }

    .reject-btn:hover {
      background-color: rgba(255, 152, 0, 0.2);
    }

    .booking-box {
      background: rgba(179, 157, 219, 0.05);
      margin: 8px 0;
      padding: 12px;
      border-radius: 8px;
      border-left: 3px solid var(--lavender-medium);
      transition: all 0.3s ease;
    }

    .booking-box:hover {
      background: rgba(179, 157, 219, 0.1);
      transform: translateX(5px);
    }

    .booking-box strong {
      color: var(--purple-dark);
    }

    .no-requests {
      color: #888;
      font-style: italic;
    }

    .action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
    }

    .slot-date {
      font-weight: 600;
      color: var(--purple-dark);
    }

    .slot-time {
      color: #666;
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    tr {
      animation: fadeIn 0.5s ease forwards;
    }

    tr:nth-child(1) { animation-delay: 0.1s; }
    tr:nth-child(2) { animation-delay: 0.2s; }
    tr:nth-child(3) { animation-delay: 0.3s; }
    tr:nth-child(4) { animation-delay: 0.4s; }
    tr:nth-child(5) { animation-delay: 0.5s; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      
      h2 {
        font-size: 1.5rem;
      }
      
      th, td {
        padding: 12px 8px;
        font-size: 0.9rem;
      }
      
      .btn {
        padding: 6px 10px;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h2><i class="fas fa-calendar-alt"></i> Your Consultation Slots</h2>
  
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date & Time</th>
          <th>Status</th>
          <th>Actions</th>
          <th>Patient Requests</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $res = $conn->query("SELECT * FROM consultation_slots WHERE doctor_id = $doctor_id ORDER BY date_time ASC");
        
        while ($row = $res->fetch_assoc()): 
          $date_time = strtotime($row['date_time']);
          $day_name = date('D', $date_time);
          $date_formatted = date('M j, Y', $date_time);
          $time_formatted = date('h:i A', $date_time);
          $status_class = 'status-' . $row['status'];
        ?>
          <tr>
            <td><?php echo $row['slot_id']; ?></td>
            <td>
              <div class="slot-date"><?php echo $day_name . ', ' . $date_formatted; ?></div>
              <div class="slot-time"><?php echo $time_formatted; ?></div>
            </td>
            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
            <td class="action-buttons">
              <a class="btn edit-btn" href="doctor_edit_slot.php?id=<?php echo $row['slot_id']; ?>">
                <i class="fas fa-edit"></i> Edit
              </a>
              <a class="btn delete-btn" href="?delete=<?php echo $row['slot_id']; ?>" onclick="return confirm('Are you sure you want to delete this slot?');">
                <i class="fas fa-trash-alt"></i> Delete
              </a>
            </td>
            <td>
              <?php
              $slot_id = $row['slot_id'];
              $requests = $conn->query("SELECT b.*, u.name FROM bookings b JOIN users u ON b.patient_id = u.user_id WHERE b.slot_id = $slot_id ORDER BY b.created_at DESC");
              
              if ($requests && $requests->num_rows > 0) {
                while ($req = $requests->fetch_assoc()) {
                  $status_class = 'status-' . $req['status'];
                  echo "<div class='booking-box'>
                          <strong><i class='fas fa-user'></i> {$req['name']}</strong>
                          <span class='status-badge $status_class'>" . ucfirst($req['status']) . "</span>";
                  
                  if ($req['status'] === 'pending' && $row['status'] === 'available') {
                    echo "<div class='action-buttons' style='margin-top:8px;'>
                            <a class='btn approve-btn' href='?approve={$req['booking_id']}'>
                              <i class='fas fa-check'></i> Approve
                            </a>
                            <a class='btn reject-btn' href='?reject={$req['booking_id']}'>
                              <i class='fas fa-times'></i> Reject
                            </a>
                          </div>";
                  }
                  echo "</div>";
                }
              } else {
                echo "<div class='no-requests'><i class='fas fa-info-circle'></i> No requests yet</div>";
              }
              ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Add hover effects and confirmation for delete actions
  document.addEventListener('DOMContentLoaded', function() {
    // Add animation to table rows as they come into view
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
      observer.observe(row);
      row.style.animationPlayState = 'paused';
    });
  });
</script>

</body>
</html>