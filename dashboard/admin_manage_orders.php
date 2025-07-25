<?php
include "../db.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE orders SET status = '$status' WHERE order_id = $order_id");
    
    if ($conn->affected_rows > 0) {
        $message = "Order status updated successfully!";
    } else {
        $error = "Failed to update order status.";
    }
}

// Get all orders
$orders_query = "
    SELECT o.*, u.name as patient_name 
    FROM orders o
    JOIN users u ON o.patient_id = u.user_id
    ORDER BY o.order_date DESC
";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Orders - Maison Bloom</title>
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
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    
    .orders-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    .orders-table th {
      background-color: var(--lavender-dark);
      color: white;
      padding: 15px;
      text-align: left;
    }
    
    .orders-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .status-select {
      padding: 5px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }
    
    .status-pending { background-color: #FFF3CD; color: #856404; }
    .status-processing { background-color: #BEE5EB; color: #0C5460; }
    .status-shipped { background-color: #D1ECF1; color: #0C5460; }
    .status-delivered { background-color: #D4EDDA; color: #155724; }
    .status-cancelled { background-color: #F8D7DA; color: #721C24; }
    
    .status-badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-block;
    }
    
    .view-btn {
      background-color: var(--lavender-medium);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 0.85rem;
      transition: all 0.3s;
    }
    
    .view-btn:hover {
      background-color: var(--lavender-dark);
    }
    
    .update-btn {
      background-color: var(--purple-dark);
      color: white;
      border: none;
      padding: 5px 12px;
      border-radius: 20px;
      cursor: pointer;
      font-size: 0.85rem;
      transition: all 0.3s;
    }
    
    .update-btn:hover {
      opacity: 0.9;
    }
    
    .alert {
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    
    .alert-success {
      background-color: #D4EDDA;
      color: #155724;
    }
    
    .alert-error {
      background-color: #F8D7DA;
      color: #721C24;
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
    <div class="page-header">
      <h1>Manage Orders</h1>
    </div>
    
    <?php if (isset($message)): ?>
      <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <table class="orders-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($orders_result && $orders_result->num_rows > 0): ?>
          <?php while ($order = $orders_result->fetch_assoc()): ?>
            <tr>
              <td>#<?php echo $order['order_id']; ?></td>
              <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
              <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
              <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
              <td>
                <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                  <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                  <select name="status" class="status-select">
                    <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="processing" <?php if ($order['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                    <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="delivered" <?php if ($order['status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                  </select>
                  <button type="submit" name="update_status" class="update-btn">Update</button>
                </form>
              </td>
              <td>
                <a href="admin_order_details.php?order_id=<?php echo $order['order_id']; ?>" class="view-btn">
                  View Details
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center;">No orders found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>