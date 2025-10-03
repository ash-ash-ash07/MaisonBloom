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

// Get recent activity data
$recent_activities = [];
$activity_query = "
    (SELECT 'user_registered' as type, username as title, created_at as timestamp, CONCAT('New user registered: ', username) as description FROM users ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'product_added' as type, name as title, created_at as timestamp, CONCAT('New product added: ', name) as description FROM products ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'appointment_booked' as type, CONCAT('Appointment for ', patient_name) as title, appointment_date as timestamp, CONCAT('New appointment booked for ', patient_name) as description FROM appointments ORDER BY appointment_date DESC LIMIT 3)
    UNION
    (SELECT 'order_placed' as type, CONCAT('Order #', id) as title, order_date as timestamp, CONCAT('New order placed: #', id) as description FROM orders ORDER BY order_date DESC LIMIT 3)
    ORDER BY timestamp DESC LIMIT 10
";

$activity_result = mysqli_query($conn, $activity_query);
if ($activity_result && mysqli_num_rows($activity_result) > 0) {
    while ($row = mysqli_fetch_assoc($activity_result)) {
        $recent_activities[] = $row;
    }
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
      --danger: #f44336;
      --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      color: #333;
      transition: var(--transition);
      overflow-x: hidden;
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
      transition: var(--transition);
    }

    .nav-brand {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--purple-dark);
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
    }

    .nav-brand::before {
      content: "🌸";
      animation: rotate 10s linear infinite;
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
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    nav a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(179, 157, 219, 0.2), transparent);
      transition: left 0.5s;
    }

    nav a:hover::before {
      left: 100%;
    }

    nav a:hover {
      color: var(--purple-dark);
      background-color: rgba(179, 157, 219, 0.1);
      transform: translateY(-2px);
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
      position: relative;
    }

    .dashboard-header::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, var(--lavender-dark), var(--purple-dark));
      border-radius: 3px;
    }

    .dashboard-header h2 {
      color: var(--purple-dark);
      font-size: 2.2rem;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(106, 90, 205, 0.1);
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
      border-radius: 16px;
      padding: 25px;
      box-shadow: var(--shadow);
      text-align: center;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      cursor: pointer;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--lavender-dark), var(--purple-dark));
    }

    .stat-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.05) 0%, rgba(106, 90, 205, 0.05) 100%);
      opacity: 0;
      transition: var(--transition);
    }

    .stat-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(155, 120, 255, 0.25);
    }

    .stat-card:hover::after {
      opacity: 1;
    }

    .stat-value {
      font-size: 2.8rem;
      font-weight: bold;
      margin: 10px 0;
      position: relative;
      z-index: 1;
      transition: var(--transition);
    }

    .stat-card:hover .stat-value {
      transform: scale(1.05);
    }

    .stat-label {
      color: var(--lavender-dark);
      font-size: 0.9rem;
      position: relative;
      z-index: 1;
      font-weight: 600;
    }

    .products-stat { 
      color: var(--purple-dark); 
      text-shadow: 0 2px 4px rgba(75, 0, 130, 0.2);
    }
    .users-stat { 
      color: var(--info); 
      text-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);
    }
    .appointments-stat { 
      color: var(--success); 
      text-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
    }
    .orders-stat { 
      color: var(--warning); 
      text-shadow: 0 2px 4px rgba(255, 152, 0, 0.2);
    }

    /* Main Cards */
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }

    .card {
      background: var(--white);
      border-radius: 20px;
      padding: 30px;
      box-shadow: var(--shadow);
      transition: var(--transition);
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
      cursor: pointer;
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

    .card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(106, 90, 205, 0.1) 100%);
      opacity: 0;
      transition: var(--transition);
    }

    .card:hover::after {
      opacity: 1;
    }

    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }

    .card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 15px 35px rgba(155, 120, 255, 0.25);
    }

    .card-icon {
      font-size: 3rem;
      color: var(--lavender-dark);
      margin-bottom: 20px;
      transition: var(--transition);
      position: relative;
      z-index: 1;
    }

    .card:hover .card-icon {
      transform: scale(1.2) rotate(5deg);
      color: var(--purple-dark);
    }

    .card h3 {
      color: var(--purple-dark);
      margin-bottom: 15px;
      font-size: 1.4rem;
      position: relative;
      z-index: 1;
    }

    .card p {
      color: #666;
      line-height: 1.6;
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }

    .btn {
      display: inline-block;
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: white;
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      border: 2px solid transparent;
      margin-top: auto;
      width: fit-content;
      position: relative;
      z-index: 1;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(106, 90, 205, 0.4);
    }

    /* Recent Activity */
    .activity-container {
      background: var(--white);
      border-radius: 20px;
      padding: 30px;
      box-shadow: var(--shadow);
      margin-top: 40px;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .activity-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--lavender-dark), var(--purple-dark));
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
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .refresh-btn {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 20px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 5px;
      font-weight: 600;
      position: relative;
      overflow: hidden;
    }

    .refresh-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .refresh-btn:hover::before {
      left: 100%;
    }

    .refresh-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .activity-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .activity-item {
      display: flex;
      align-items: center;
      padding: 18px 0;
      border-bottom: 1px solid rgba(179, 157, 219, 0.2);
      animation: fadeIn 0.5s ease;
      transition: var(--transition);
      border-radius: 8px;
      padding-left: 10px;
      padding-right: 10px;
    }

    .activity-item:hover {
      background-color: rgba(179, 157, 219, 0.05);
      transform: translateX(5px);
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-icon {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: white;
      transition: var(--transition);
    }

    .activity-item:hover .activity-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .activity-content {
      flex: 1;
    }

    .activity-title {
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--purple-dark);
    }

    .activity-time {
      color: #888;
      font-size: 0.8rem;
    }

    .empty-activity {
      text-align: center;
      padding: 30px;
      color: #888;
      font-style: italic;
    }

    /* Floating elements for visual interest */
    .floating-element {
      position: absolute;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.1), rgba(106, 90, 205, 0.1));
      z-index: 0;
      animation: float 15s infinite ease-in-out;
    }

    .floating-element:nth-child(1) {
      top: 10%;
      left: 5%;
      width: 80px;
      height: 80px;
      animation-delay: 0s;
    }

    .floating-element:nth-child(2) {
      top: 60%;
      right: 5%;
      width: 120px;
      height: 120px;
      animation-delay: 5s;
    }

    .floating-element:nth-child(3) {
      bottom: 20%;
      left: 10%;
      width: 60px;
      height: 60px;
      animation-delay: 10s;
    }

    /* Dark mode toggle */
    .dark-mode-toggle {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(106, 90, 205, 0.4);
      z-index: 99;
      transition: var(--transition);
      border: none;
      font-size: 1.2rem;
    }

    .dark-mode-toggle:hover {
      transform: scale(1.1) rotate(15deg);
      box-shadow: 0 8px 25px rgba(106, 90, 205, 0.5);
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
    body.dark-mode .activity-title,
    body.dark-mode .stat-label {
      color: #b8b8b8;
    }

    body.dark-mode .nav-brand,
    body.dark-mode nav a,
    body.dark-mode .card h3,
    body.dark-mode .activity-header h3 {
      color: #b39ddb;
    }

    body.dark-mode .empty-activity {
      color: #888;
    }

    body.dark-mode .floating-element {
      background: linear-gradient(135deg, rgba(179, 157, 219, 0.05), rgba(106, 90, 205, 0.05));
    }

    /* Animations */
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

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      33% { transform: translateY(-20px) rotate(120deg); }
      66% { transform: translateY(10px) rotate(240deg); }
    }

    /* Loading animation */
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
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
        flex-wrap: wrap;
      }
      
      .card-container,
      .stats-container {
        grid-template-columns: 1fr;
      }
      
      .floating-element {
        display: none;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Background Elements -->
  <div class="floating-element"></div>
  <div class="floating-element"></div>
  <div class="floating-element"></div>

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
        <div class="stat-value orders-stat"><?php echo $order_count; ?></div>
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
    </div>

    <!-- Recent Activity Section -->
    <div class="activity-container">
      <div class="activity-header">
        <h3><i class="fas fa-history"></i> Recent Activity</h3>
        <button class="refresh-btn" id="refreshActivity">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>
      <ul class="activity-list" id="activityList">
        <?php if (empty($recent_activities)): ?>
          <li class="empty-activity">No recent activity to display</li>
        <?php else: ?>
          <?php foreach ($recent_activities as $activity): ?>
            <li class="activity-item">
              <div class="activity-icon">
                <?php 
                  switch($activity['type']) {
                    case 'user_registered':
                      echo '<i class="fas fa-user-plus"></i>';
                      break;
                    case 'product_added':
                      echo '<i class="fas fa-shopping-cart"></i>';
                      break;
                    case 'appointment_booked':
                      echo '<i class="fas fa-calendar-check"></i>';
                      break;
                    case 'order_placed':
                      echo '<i class="fas fa-box"></i>';
                      break;
                    default:
                      echo '<i class="fas fa-bell"></i>';
                  }
                ?>
              </div>
              <div class="activity-content">
                <div class="activity-title"><?php echo $activity['description']; ?></div>
                <div class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
 
  <!-- Dark Mode Toggle -->
  <button class="dark-mode-toggle" id="darkModeToggle">
    <i class="fas fa-moon"></i>
  </button>

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

    // Refresh activity functionality
    const refreshBtn = document.getElementById('refreshActivity');
    const activityList = document.getElementById('activityList');
    
    refreshBtn.addEventListener('click', function() {
      // Show loading state
      const originalContent = refreshBtn.innerHTML;
      refreshBtn.innerHTML = '<div class="loading"></div>';
      refreshBtn.disabled = true;
      
      // Fetch updated activity data
      fetch('get_recent_activity.php')
        .then(response => response.json())
        .then(data => {
          // Clear current activity list
          activityList.innerHTML = '';
          
          if (data.length === 0) {
            activityList.innerHTML = '<li class="empty-activity">No recent activity to display</li>';
          } else {
            // Add new activity items
            data.forEach(activity => {
              const listItem = document.createElement('li');
              listItem.className = 'activity-item';
              
              // Determine icon based on activity type
              let iconClass = 'fas fa-bell';
              switch(activity.type) {
                case 'user_registered':
                  iconClass = 'fas fa-user-plus';
                  break;
                case 'product_added':
                  iconClass = 'fas fa-shopping-cart';
                  break;
                case 'appointment_booked':
                  iconClass = 'fas fa-calendar-check';
                  break;
                case 'order_placed':
                  iconClass = 'fas fa-box';
                  break;
              }
              
              listItem.innerHTML = `
                <div class="activity-icon">
                  <i class="${iconClass}"></i>
                </div>
                <div class="activity-content">
                  <div class="activity-title">${activity.description}</div>
                  <div class="activity-time">${activity.formatted_time}</div>
                </div>
              `;
              
              activityList.appendChild(listItem);
            });
          }
          
          // Restore button state
          refreshBtn.innerHTML = originalContent;
          refreshBtn.disabled = false;
        })
        .catch(error => {
          console.error('Error fetching activity data:', error);
          refreshBtn.innerHTML = originalContent;
          refreshBtn.disabled = false;
        });
    });

    // Auto-refresh activity every 30 seconds
    setInterval(() => {
      if (!refreshBtn.disabled) {
        refreshBtn.click();
      }
    }, 30000);

    // Add hover effects to stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
      });
    });
  </script>

</body>
</html>