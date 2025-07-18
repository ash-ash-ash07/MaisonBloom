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
  <title>Manage Users - Admin | Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --lavender-light: #f9f7ff;
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
      text-align: center;
      margin-bottom: 30px;
      font-size: 2rem;
      position: relative;
      display: inline-block;
      width: 100%;
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
    }

    tr:hover {
      background-color: rgba(179, 157, 219, 0.05);
    }

    .role-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .role-doctor {
      background-color: rgba(33, 150, 243, 0.1);
      color: var(--info);
    }

    .role-patient {
      background-color: rgba(76, 175, 80, 0.1);
      color: var(--success);
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

    .search-container {
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-box {
      flex: 1;
      min-width: 250px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 12px 15px 12px 40px;
      border: 1px solid rgba(179, 157, 219, 0.3);
      border-radius: 8px;
      font-size: 0.95rem;
      background-color: var(--white);
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      border-color: var(--lavender-medium);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.2);
      outline: none;
    }

    .search-box i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--lavender-medium);
    }

    .no-users {
      text-align: center;
      padding: 40px;
      color: #666;
    }

    .no-users i {
      font-size: 3rem;
      color: var(--lavender-medium);
      margin-bottom: 15px;
    }

    @media (max-width: 768px) {
      th, td {
        padding: 12px 8px;
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
    <h2><i class="fas fa-users-cog"></i> Manage Users</h2>
    
    <div class="search-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search users...">
      </div>
    </div>
    
    <div class="table-container">
      <table id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                <td>
                  <span class="role-badge role-<?php echo $row['role']; ?>">
                    <i class="fas fa-<?php echo $row['role'] === 'doctor' ? 'user-md' : 'user'; ?>"></i>
                    <?php echo ucfirst($row['role']); ?>
                  </span>
                </td>
                <td>
                  <a href="admin_edit_user.php?id=<?php echo $row['user_id']; ?>" class="btn edit-btn">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="admin_manage_users.php?delete=<?php echo $row['user_id']; ?>"
                     class="btn delete-btn"
                     onclick="return confirm('Are you sure you want to delete this user?');">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">
                <div class="no-users">
                  <i class="fas fa-user-slash"></i>
                  <h3>No users found</h3>
                  <p>There are currently no users registered in the system.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchValue = this.value.toLowerCase();
      const rows = document.querySelectorAll('#usersTable tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
      });
    });
  </script>

</body>
</html>