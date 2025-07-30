<?php
include "db.php";
session_start();

// Redirect if not logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: products.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$patient_id = $_SESSION['user_id'];

// Get order details
  $order = $conn->query("
    SELECT o.*, u.name as patient_name 
    FROM orders o
    JOIN users u ON o.patient_id = u.user_id
    WHERE o.order_id = $order_id AND o.patient_id = $patient_id
")->fetch_assoc();

if (!$order) {
    header("Location: products.php");
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
  <title>Order Confirmation - Maison Bloom</title>
  <style>
    :root {
      --lavender-light: #f3efff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--lavender-light);
      margin: 0;
      padding: 0;
    }

    nav {
      background-color: var(--white);
      display: flex;
      justify-content: space-between;
      padding: 15px 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: var(--lavender-dark);
      font-weight: bold;
    }

    .container {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
    }

    .confirmation-card {
      background: var(--white);
      border-radius: 12px;
      padding: 40px;
      box-shadow: var(--shadow);
      text-align: center;
    }

    .confirmation-icon {
      font-size: 60px;
      color: #4CAF50;
      margin-bottom: 20px;
    }

    .confirmation-title {
      color: var(--purple-dark);
      font-size: 28px;
      margin-bottom: 15px;
    }

    .confirmation-text {
      color: #555;
      margin-bottom: 30px;
      font-size: 18px;
    }

    .order-details {
      text-align: left;
      margin: 30px 0;
      padding: 20px;
      background: var(--lavender-light);
      border-radius: 8px;
    }

    .order-details h3 {
      color: var(--purple-dark);
      margin-top: 0;
    }

    .detail-row {
      display: flex;
      margin-bottom: 10px;
    }

    .detail-label {
      font-weight: bold;
      width: 150px;
      color: var(--purple-dark);
    }

    .order-items {
      margin: 30px 0;
    }

    .order-item {
      display: flex;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid var(--lavender-light);
    }

    .order-item img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 20px;
    }

    .item-details {
      flex: 1;
    }

    .item-name {
      font-weight: bold;
      color: var(--purple-dark);
    }

    .item-price {
      color: #666;
    }

    .order-total {
      text-align: right;
      font-size: 18px;
      font-weight: bold;
      margin-top: 20px;
      color: var(--purple-dark);
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      background-color: var(--lavender-dark);
      color: white;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s;
      margin-top: 20px;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <nav>
    <div><strong>🌸 Maison Bloom</strong></div>
    <div>
      <a href="home.php">Home</a>
      <a href="booking.php">Book Now</a>
      <a href="products.php">Products</a>
      <a href="patient/patient_feedback.php">Feedback</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="container">
    <div class="confirmation-card">
      <div class="confirmation-icon">✓</div>
      <h1 class="confirmation-title">Thank You for Your Order!</h1>
      <p class="confirmation-text">Your order has been placed successfully. We'll notify you when your items are on their way.</p>
      
      <div class="order-details">
        <h3>Order Details</h3>
        <div class="detail-row">
          <span class="detail-label">Order Number:</span>
          <span>#<?php echo $order['order_id']; ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date:</span>
          <span><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Status:</span>
          <span><?php echo ucfirst($order['status']); ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Amount:</span>
          <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
      </div>
      
      <div class="order-items">
        <h3>Order Items</h3>
        <?php while($item = $items->fetch_assoc()): ?>
          <div class="order-item">
            <img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
            <div class="item-details">
              <div class="item-name"><?php echo $item['name']; ?></div>
              <div class="item-price">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></div>
            </div>
            <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
          </div>
        <?php endwhile; ?>
        <div class="order-total">
          Total: ₹<?php echo number_format($order['total_amount'], 2); ?>
        </div>
      </div>
      <div class="shipping-info">
    <h3>Shipping Information</h3>
    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
    <p>Payment Method: <?php echo htmlspecialchars($order['payment_method']); ?></p>
</div>
      
      <a href="products.php" class="btn">Continue Shopping</a>
    </div>
  </div>

</body>
</html>