<?php
include "../db.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: admin_manage_orders.php");
    exit;
}

$order_id = intval($_GET['order_id']);

// Get order details
$order = $conn->query("
    SELECT o.*, u.name as patient_name, u.email as patient_email
    FROM orders o
    JOIN users u ON o.patient_id = u.user_id
    WHERE o.order_id = $order_id
")->fetch_assoc();

if (!$order) {
    header("Location: admin_manage_orders.php");
    exit;
}

// Get order items
$items = $conn->query("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Order Details - Maison Bloom</title>
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
    }
    
    /* Navigation Bar Styles */
    nav {
      background-color: var(--lavender-dark);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .nav-brand {
      color: white;
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
    }
    
    .nav-links {
      display: flex;
      gap: 20px;
    }
    
    .nav-links a {
      color: white;
      text-decoration: none;
      font-size: 1rem;
      padding: 8px 15px;
      border-radius: 20px;
      transition: all 0.3s ease;
    }
    
    .nav-links a:hover {
      background-color: var(--purple-dark);
    }
    
    .nav-links a:last-child {
      background-color: var(--purple-dark);
    }
    
    .nav-links a:last-child:hover {
      background-color: #3a0066;
    }
    
    .container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
    }
    
    .order-details-card {
      background: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow);
    }
    
    .order-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    
    .status-pending { background-color: #FFF3CD; color: #856404; }
    .status-processing { background-color: #BEE5EB; color: #0C5460; }
    .status-shipped { background-color: #D1ECF1; color: #0C5460; }
    .status-delivered { background-color: #D4EDDA; color: #155724; }
    .status-cancelled { background-color: #F8D7DA; color: #721C24; }
    
    .order-section {
      margin-bottom: 30px;
    }
    
    .order-section h3 {
      color: var(--purple-dark);
      margin-bottom: 15px;
    }
    
    .order-items {
      margin: 20px 0;
    }
    
    .order-item {
      display: flex;
      align-items: center;
      padding: 15px;
      margin-bottom: 15px;
      background: var(--lavender-light);
      border-radius: 12px;
    }
    
    .order-item-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 20px;
    }
    
    .order-item-details {
      flex: 1;
    }
    
    .order-item-name {
      font-weight: 600;
      color: var(--purple-dark);
      margin-bottom: 5px;
    }
    
    .order-item-price {
      color: #666;
    }
    
    .order-summary {
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    
    .total-row {
      font-weight: 600;
      font-size: 1.1rem;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .customer-info, .shipping-info {
      margin-top: 30px;
      padding: 20px;
      background: var(--lavender-light);
      border-radius: 12px;
    }
    
    .info-row {
      margin-bottom: 10px;
    }
    
    .info-label {
      font-weight: 600;
      color: var(--purple-dark);
      display: inline-block;
      width: 150px;
    }
    
    .btn-back {
      display: inline-block;
      margin-top: 30px;
      padding: 10px 20px;
      background: var(--lavender-dark);
      color: white;
      border-radius: 30px;
      text-decoration: none;
      transition: all 0.3s;
    }
    
    .btn-back:hover {
      background: var(--purple-dark);
    }
    
    .status-form {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 20px;
    }
    
    .status-select {
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }
    
    .update-btn {
      background-color: var(--purple-dark);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .update-btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>

  <nav>
    <div class="nav-brand">Maison Bloom Admin</div>
    <div class="nav-links">
      <a href="../home.php">Home</a>
      <a href="admin_add_product.php">Add Product</a>
      <a href="admin_view_product.php">View Products</a>
      <a href="admin_manage_users.php">Manage Users</a>
      <a href="admin_manage_orders.php">Manage Orders</a>
      <a href="../logout.php">Logout</a>
    </div>
  </nav>

  <div class="container">
    <div class="order-details-card">
      <div class="order-header">
        <div>
          <h2>Order #<?php echo $order['order_id']; ?></h2>
          <p>Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
        </div>
        <div>
          <span class="status-badge status-<?php echo $order['status']; ?>">
            <?php echo ucfirst($order['status']); ?>
          </span>
        </div>
      </div>
      
      <form method="POST" class="status-form">
        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
        <select name="status" class="status-select">
          <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
          <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>Processing</option>
          <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
          <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
          <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
        </select>
        <button type="submit" name="update_status" class="update-btn">Update Status</button>
      </form>
      
      <div class="order-section">
        <h3>Customer Information</h3>
        <div class="customer-info">
          <div class="info-row">
            <span class="info-label">Name:</span>
            <span><?php echo htmlspecialchars($order['patient_name']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?php echo htmlspecialchars($order['patient_email']); ?></span>
          </div>
        </div>
      </div>
      
      <div class="order-section">
        <h3>Order Items</h3>
        <div class="order-items">
          <?php while($item = $items->fetch_assoc()): ?>
            <div class="order-item">
              <img src="../uploads/<?php echo $item['image']; ?>" class="order-item-image" alt="<?php echo $item['name']; ?>">
              <div class="order-item-details">
                <div class="order-item-name"><?php echo $item['name']; ?></div>
                <div class="order-item-price">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></div>
              </div>
              <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
      
      <div class="order-section">
        <h3>Order Summary</h3>
        <div class="order-summary">
          <div class="summary-row">
            <span>Subtotal:</span>
            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping:</span>
            <span>Free</span>
          </div>
          <div class="summary-row total-row">
            <span>Total:</span>
            <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
          </div>
        </div>
      </div>
      
      <div class="order-section">
        <h3>Shipping Information</h3>
        <div class="shipping-info">
          <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
          <div class="info-row" style="margin-top: 15px;">
            <span class="info-label">Payment Method:</span>
            <span><?php echo htmlspecialchars($order['payment_method']); ?></span>
          </div>
        </div>
      </div>
      
      <a href="admin_manage_orders.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Orders
      </a>
    </div>
  </div>

</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> 874631f00b810eb307a48fed5c6d77be4d6f6c6a
