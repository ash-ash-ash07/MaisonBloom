<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get counts for dashboard cards
$product_count = 0;
$user_count = 0;
$appointment_count = 0;

$product_query = "SELECT COUNT(*) as count FROM products";
$user_query = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$appointment_query = "SELECT COUNT(*) as count FROM appointments";

$product_result = mysqli_query($conn, $product_query);
$user_result = mysqli_query($conn, $user_query);
$appointment_result = mysqli_query($conn, $appointment_query);

if ($product_result) {
    $product_count = mysqli_fetch_assoc($product_result)['count'];
}
if ($user_result) {
    $user_count = mysqli_fetch_assoc($user_result)['count'];
}
if ($appointment_result) {
    $appointment_count = mysqli_fetch_assoc($appointment_result)['count'];
}
$order_count = 0;
$order_query = "SELECT COUNT(*) as count FROM orders";
$order_result = mysqli_query($conn, $order_query);
if ($order_result) {
    $order_count = mysqli_fetch_assoc($order_result)['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - Maison Bloom</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
      --success: #4caf50;
      --warning: #ff9800;
      --info: #2196f3;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      color: #333;
    }

    nav {
      background-color: var(--white);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav-brand {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--purple-dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-brand::before {
      content: "🌸";
    }

    .nav-links {
      display: flex;
      gap: 20px;
    }

    nav a {
      text-decoration: none;
      color: var(--lavender-dark);
      font-weight: 600;
      padding: 8px 12px;
      border-radius: 8px;
      transition: all 0.3s ease;
      position: relative;
    }

    nav a:hover {
      color: var(--purple-dark);
      background-color: rgba(179, 157, 219, 0.1);
    }

    nav a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: var(--lavender-dark);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    nav a:hover::after {
      width: 100%;
    }

    .container {
      padding: 40px 5%;
      max-width: 1400px;
      margin: 0 auto;
    }

    .dashboard-header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeIn 0.8s ease;
    }

    .dashboard-header h2 {
      color: var(--purple-dark);
      font-size: 2.2rem;
      margin-bottom: 10px;
    }

    .dashboard-header p {
      color: var(--lavender-dark);
      font-size: 1.1rem;
      max-width: 700px;
      margin: 0 auto;
    }

    /* Stats Overview */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: var(--white);
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow);
      text-align: center;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: bold;
      margin: 10px 0;
    }

    .stat-label {
      color: var(--lavender-dark);
      font-size: 0.9rem;
    }

    .products-stat { color: var(--purple-dark); }
    .users-stat { color: var(--info); }
    .appointments-stat { color: var(--success); }

    /* Main Cards */
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }

    .card {
      background: var(--white);
      border-radius: 16px;
      padding: 30px;
      box-shadow: var(--shadow);
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      border: 1px solid rgba(179, 157, 219, 0.2);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.6s ease forwards;
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--lavender-dark), var(--purple-dark));
    }

    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(155, 120, 255, 0.2);
    }

    .card-icon {
      font-size: 2.5rem;
      color: var(--lavender-dark);
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }

    .card:hover .card-icon {
      transform: scale(1.1);
    }

    .card h3 {
      color: var(--purple-dark);
      margin-bottom: 15px;
      font-size: 1.4rem;
    }

    .card p {
      color: #666;
      line-height: 1.6;
      margin-bottom: 25px;
    }

    .btn {
      display: inline-block;
      background-color: var(--lavender-dark);
      color: white;
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      border: 2px solid transparent;
      margin-top: auto;
      width: fit-content;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    /* Recent Activity */
    .activity-container {
      background: var(--white);
      border-radius: 16px;
      padding: 30px;
      box-shadow: var(--shadow);
      margin-top: 40px;
    }

    .activity-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .activity-header h3 {
      color: var(--purple-dark);
      margin: 0;
    }

    .activity-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .activity-item {
      display: flex;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid rgba(179, 157, 219, 0.2);
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: rgba(179, 157, 219, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--lavender-dark);
    }

    .activity-content {
      flex: 1;
    }

    .activity-title {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .activity-time {
      color: #888;
      font-size: 0.8rem;
    }

    /* Dark mode toggle */
    .dark-mode-toggle {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--purple-dark);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(106, 90, 205, 0.3);
      z-index: 99;
      transition: all 0.3s ease;
    }

    .dark-mode-toggle:hover {
      transform: scale(1.1);
    }

    /* Dark mode styles */
    body.dark-mode {
      background-color: #1a1a2e;
      color: #e6e6e6;
    }

    body.dark-mode nav {
      background-color: #16213e;
    }

    body.dark-mode .card,
    body.dark-mode .stat-card,
    body.dark-mode .activity-container {
      background-color: #16213e;
      color: #e6e6e6;
    }

    body.dark-mode .card p,
    body.dark-mode .activity-title {
      color: #b8b8b8;
    }

    body.dark-mode .nav-brand,
    body.dark-mode nav a,
    body.dark-mode .card h3,
    body.dark-mode .activity-header h3 {
      color: #b39ddb;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
      nav {
        flex-direction: column;
        gap: 15px;
      }
      
      .nav-links {
        width: 100%;
        justify-content: space-around;
      }
      
      .card-container,
      .stats-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <!-- Navigation Bar -->
  <nav>
    <div class="nav-brand">Maison Bloom Admin</div>
    <div class="nav-links">
      <a href="../home.php">Home</a>
      <a href="admin_add_product.php">Add Product</a>
      <a href="admin_view_product.php">View Products</a>
      <a href="admin_manage_users.php">Manage Users</a>
      <a href="../logout.php">Logout</a>
    </div>
  </nav>

  <!-- Dashboard Content -->
  <div class="container">
    <div class="dashboard-header">
      <h2>Welcome, Admin!</h2>
      <p>Manage your Maison Bloom store with these powerful tools</p>
    </div>

    <!-- Stats Overview -->
    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-value products-stat"><?php echo $product_count; ?></div>
        <div class="stat-label">Total Products</div>
      </div>
      <div class="stat-card">
        <div class="stat-value users-stat"><?php echo $user_count; ?></div>
        <div class="stat-label">Registered Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-value appointments-stat"><?php echo $appointment_count; ?></div>
        <div class="stat-label">Appointments</div>
      </div>
      <div class="stat-card">
        <div class="stat-value" style="color: var(--purple-dark);"><?php echo $order_count; ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
    </div>

    <div class="card-container">
      <div class="card">
        <div class="card-icon"><i class="fas fa-plus"></i></div>
        <h3>Add New Product</h3>
        <p>Add skincare, haircare or body products to your catalog.</p>
        <a href="admin_add_product.php" class="btn">Add Product</a>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-eye"></i></div>
        <h3>View Products</h3>
        <p>Manage and update your existing product inventory.</p>
        <a href="admin_view_product.php" class="btn">View All</a>
      </div>

       <div class="card">
        <div class="card-icon"><i class="fas fa-shopping-bag"></i></div>
        <h3>Manage Orders</h3>
        <p>View and update customer orders and shipping status.</p>
        <a href="admin_manage_orders.php" class="btn">Manage Orders</a>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-users"></i></div>
        <h3>Manage Users</h3>
        <p>View or update doctors and patients accounts.</p>
        <a href="admin_manage_users.php" class="btn">Manage Users</a>
      </div>

      <div class="card">
        <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
        <h3>Consultation Reports</h3>
        <p>Access appointments and prescription history.</p>
        <a href="#" class="btn">View Reports</a>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="activity-container">
      <div class="activity-header">
        <h3><i class="fas fa-history"></i> Recent Activity</h3>
      </div>
      <ul class="activity-list">
        <li class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-user-plus"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">New user registered: John Doe</div>
            <div class="activity-time">2 hours ago</div>
          </div>
        </li>
        <li class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">New product added: Lavender Shampoo</div>
            <div class="activity-time">5 hours ago</div>
          </div>
        </li>
        <li class="activity-item">
          <div class="activity-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">New appointment booked</div>
            <div class="activity-time">Yesterday</div>
          </div>
        </li>
      </ul>
    </div>
  </div>

  <!-- Dark Mode Toggle -->
  <div class="dark-mode-toggle" id="darkModeToggle">
    <i class="fas fa-moon"></i>
  </div>

  <script>
    // Dark mode toggle functionality
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check for saved user preference
    if (localStorage.getItem('darkMode') === 'enabled') {
      body.classList.add('dark-mode');
      darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    
    darkModeToggle.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
        localStorage.setItem('darkMode', 'disabled');
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }
    });

    // Simulate dynamic content loading
    setTimeout(() => {
      const activityList = document.querySelector('.activity-list');
      if (activityList) {
        const newActivity = document.createElement('li');
        newActivity.className = 'activity-item';
        newActivity.innerHTML = `
          <div class="activity-icon">
            <i class="fas fa-bell"></i>
          </div>
          <div class="activity-content">
            <div class="activity-title">System notification: Weekly backup completed</div>
            <div class="activity-time">Just now</div>
          </div>
        `;
        activityList.insertBefore(newActivity, activityList.firstChild);
      }
    }, 3000);
  </script>

</body>
</html>