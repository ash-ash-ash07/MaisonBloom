<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Handle delete user
if (isset($_GET['delete'])) {
  $uid = intval($_GET['delete']);

  // Delete from doctor_profiles if doctor
  $conn->query("DELETE FROM doctor_profiles WHERE user_id = $uid");

  // Delete user
  $conn->query("DELETE FROM users WHERE user_id = $uid");

  echo "<script>alert('User deleted.'); window.location='admin_manage_users.php';</script>";
  exit;
}

// Fetch all users (patients + doctors)
$result = $conn->query("SELECT * FROM users ORDER BY role DESC, created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Users - Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f0ff;
      padding: 40px;
    }

    h2 {
      color: #4b0082;
      text-align: center;
      margin-bottom: 30px;
    }

    table {
      width: 95%;
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
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      color: white;
      text-decoration: none;
      font-size: 14px;
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

<h2>Manage Users</h2>

<table>
  <tr>
    <th>User ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Role</th>
    <th>Actions</th>
  </tr>

  <?php
  if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
  ?>
    <tr>
      <td><?php echo $row['user_id']; ?></td>
      <td><?php echo $row['name']; ?></td>
      <td><?php echo $row['email']; ?></td>
      <td><?php echo $row['phone_number']; ?></td>
      <td><?php echo ucfirst($row['role']); ?></td>
      <td>
        <a href="admin_edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn edit-btn">Edit</a>
        <a href="admin_manage_users.php?delete=<?php echo $row['user_id']; ?>"
           class="btn delete-btn"
           onclick="return confirm('Are you sure you want to delete this user?');">
           Delete
        </a>
      </td>
    </tr>
  <?php
    endwhile;
  else:
    echo "<tr><td colspan='6'>No users found.</td></tr>";
  endif;
  ?>
</table>

</body>
</html>
