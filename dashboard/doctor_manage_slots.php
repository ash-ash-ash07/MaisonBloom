<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit;
}

// Get doctor_id
$doctor = $conn->query("SELECT doctor_id FROM doctor_profiles WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM consultation_slots WHERE slot_id = $id AND doctor_id = $doctor_id");
    header("Location: doctor_manage_slots.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Consultation Slots</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f0ff;
      padding: 50px;
    }

    h2 {
      color: #4b0082;
      text-align: center;
      margin-bottom: 30px;
    }

    table {
      width: 90%;
      margin: auto;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ddd;
    }

    th {
      background-color: #b39ddb;
      color: white;
    }

    .btn {
      padding: 6px 14px;
      border: none;
      border-radius: 6px;
      color: white;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
    }

    .edit-btn {
      background-color: #6a5acd;
    }

    .delete-btn {
      background-color: #d9534f;
    }
  </style>
</head>
<body>

  <h2>Your Consultation Slots</h2>

  <table>
    <tr>
      <th>ID</th>
      <th>Date & Time</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>

    <?php
    $result = $conn->query("SELECT * FROM consultation_slots WHERE doctor_id = $doctor_id ORDER BY date_time ASC");
    if ($result && $result->num_rows > 0):
      while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <td><?php echo $row['slot_id']; ?></td>
        <td><?php echo date('D, M j, Y - h:i A', strtotime($row['date_time'])); ?></td>
        <td><?php echo ucfirst($row['status']); ?></td>
        <td>
          <a class="btn edit-btn" href="doctor_edit_slot.php?id=<?php echo $row['slot_id']; ?>">Edit</a>
          <a class="btn delete-btn" href="doctor_manage_slots.php?delete=<?php echo $row['slot_id']; ?>"
             onclick="return confirm('Are you sure you want to delete this slot?');">
             Delete
          </a>
        </td>
      </tr>
    <?php
      endwhile;
    else:
      echo "<tr><td colspan='4'>No slots added yet.</td></tr>";
    endif;
    ?>
  </table>

</body>
</html>
