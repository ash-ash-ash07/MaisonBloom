<?php
include "db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Order History - Maison Bloom</title>
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
      --shadow-sm: 0 2px 15px rgba(0,0,0,0.08);
      --shadow-md: 0 10px 30px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    
    body {
      background-color: #fafafa;
      color: var(--text);
      line-height: 1.6;
    }
    
    /* Navigation */
    nav {
      background-color: var(--white);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      box-shadow: var(--shadow-sm);
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
      align-items: center;
      gap: 25px;
    }
    
    .nav-links a {
      text-decoration: none;
      color: var(--text);
      font-weight: 500;
      transition: var(--transition);
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
    
    .cart-icon {
      position: relative;
      display: flex;
      align-items: center;
    }
    
    .cart-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background-color: var(--secondary);
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 600;
    }
    
    /* Main Content */
    .order-history-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 40px;
      background: var(--white);
      border-radius: 20px;
      box-shadow: var(--shadow-md);
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .page-header h1 {
      color: var(--primary-dark);
      font-size: 2rem;
    }
    
    .btn-view-more {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: var(--primary);
      color: white;
      border-radius: 30px;
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .btn-view-more:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(138, 99, 210, 0.3);
    }
    
    /* Order Table */
    .order-table {
      width: 100%;
      border-collapse: collapse;
      margin: 30px 0;
    }
    
    .order-table th {
      background-color: var(--primary);
      color: var(--white);
      padding: 15px;
      text-align: left;
      font-weight: 500;
    }
    
    .order-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .order-table tr:last-child td {
      border-bottom: none;
    }
    
    .order-table tr:hover {
      background-color: rgba(138, 99, 210, 0.03);
    }
    
    .order-status {
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      display: inline-block;
      text-transform: capitalize;
    }
    
    .status-pending { background-color: #FFF3CD; color: #856404; }
    .status-processing { background-color: #BEE5EB; color: #0C5460; }
    .status-shipped { background-color: #D1ECF1; color: #0C5460; }
    .status-delivered { background-color: #D4EDDA; color: #155724; }
    .status-cancelled { background-color: #F8D7DA; color: #721C24; }
    
    .view-order-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background-color: var(--primary-light);
      color: var(--primary-dark);
      padding: 8px 16px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 0.85rem;
      transition: var(--transition);
    }
    
    .view-order-btn:hover {
      background-color: var(--primary);
      color: white;
      transform: translateY(-2px);
    }
    
    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 40px;
      gap: 10px;
    }
    
    .pagination a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: var(--primary-light);
      color: var(--primary-dark);
      border-radius: 50%;
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .pagination a.active {
      background: var(--primary);
      color: white;
    }
    
    .pagination a:hover:not(.active) {
      background: var(--primary-light);
      transform: translateY(-2px);
    }
    
    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 0;
    }
    
    .empty-state i {
      font-size: 3rem;
      color: var(--primary-light);
      margin-bottom: 20px;
    }
    
    .empty-state p {
      color: var(--text-light);
      font-size: 1.1rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .order-history-container {
        padding: 25px;
        margin: 20px;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .order-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
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
      <a href="../booking.php">Book Now</a>
      <a href="../products.php">Products</a>
      <a href="../dashboard/patient_feedback.php">Feedback</a>
      <a href="../logout.php">Logout</a>
      <a href="../cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span class="cart-count"><?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>
        <?php endif; ?>
      </a>
    </div>
  </nav>

  <div class="order-history-container">
    <div class="page-header">
      <h1>Your Order History</h1>
      <a href="../dashboard/patient_dashboard.php" class="btn-view-more">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
    
    <table class="order-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Date</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Pagination
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM orders WHERE patient_id = $user_id";
        $count_result = $conn->query($count_query);
        $total_orders = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_orders / $per_page);
        
        // Get orders
        $orders_query = "
          SELECT * 
          FROM orders 
          WHERE patient_id = $user_id
          ORDER BY order_date DESC
          LIMIT $per_page OFFSET $offset
        ";
        $orders_result = $conn->query($orders_query);
        
        if ($orders_result && $orders_result->num_rows > 0) {
          while ($order = $orders_result->fetch_assoc()) {
            $status_class = 'status-' . $order['status'];
            $formatted_date = date("M j, Y", strtotime($order['order_date']));
            echo "<tr>
                    <td>#{$order['order_id']}</td>
                    <td>{$formatted_date}</td>
                    <td>₹{$order['total_amount']}</td>
                    <td><span class='order-status {$status_class}'>" . ucfirst($order['status']) . "</span></td>
                    <td><a href='order_details.php?order_id={$order['order_id']}' class='view-order-btn'><i class='fas fa-eye'></i> Details</a></td>
                  </tr>";
          }
        } else {
          echo "<tr>
                  <td colspan='5'>
                    <div class='empty-state'>
                      <i class='fas fa-box-open'></i>
                      <p>You haven't placed any orders yet</p>
                    </div>
                  </td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
    
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
      <?php endif; ?>
      
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
      
      <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</body>
</html>