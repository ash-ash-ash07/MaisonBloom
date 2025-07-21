<?php
include "db.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: patient_dashboard.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Get order details
$order = $conn->query("
    SELECT * FROM orders 
    WHERE order_id = $order_id AND patient_id = $user_id
")->fetch_assoc();

if (!$order) {
    header("Location: patient_dashboard.php");
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
    .order-details-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 40px;
      background: var(--white);
      border-radius: 20px;
      box-shadow: var(--shadow-md);
    }
    
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .order-header h2 {
      color: var(--primary-dark);
      font-size: 1.8rem;
    }
    
    .order-header p {
      color: var(--text-light);
    }
    
    .order-status {
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      text-transform: capitalize;
    }
    
    .status-pending { background-color: #FFF3CD; color: #856404; }
    .status-processing { background-color: #BEE5EB; color: #0C5460; }
    .status-shipped { background-color: #D1ECF1; color: #0C5460; }
    .status-delivered { background-color: #D4EDDA; color: #155724; }
    .status-cancelled { background-color: #F8D7DA; color: #721C24; }
    
    /* Order Items */
    .order-items {
      margin: 40px 0;
    }
    
    .order-items h3 {
      color: var(--primary-dark);
      margin-bottom: 20px;
      font-size: 1.3rem;
    }
    
    .order-item {
      display: flex;
      align-items: center;
      padding: 20px;
      margin-bottom: 15px;
      background: var(--light);
      border-radius: 12px;
      transition: var(--transition);
    }
    
    .order-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(138, 99, 210, 0.1);
    }
    
    .order-item-image {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .order-item-details {
      flex: 1;
    }
    
    .order-item-name {
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 5px;
      font-size: 1.1rem;
    }
    
    .order-item-price {
      color: var(--text-light);
    }
    
    .order-item-total {
      font-weight: 600;
      color: var(--primary-dark);
    }
    
    /* Order Summary */
    .order-summary {
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.1);
    }
    
    .order-summary h3 {
      color: var(--primary-dark);
      margin-bottom: 20px;
      font-size: 1.3rem;
    }
    
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    
    .total-row {
      font-weight: 600;
      font-size: 1.2rem;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid rgba(0,0,0,0.1);
      color: var(--primary-dark);
    }
    
    /* Shipping Info */
    .shipping-info {
      margin-top: 40px;
      padding: 25px;
      background: var(--light);
      border-radius: 12px;
    }
    
    .shipping-info h3 {
      color: var(--primary-dark);
      margin-bottom: 15px;
      font-size: 1.3rem;
    }
    
    .shipping-info p {
      margin-bottom: 10px;
    }
    
    /* Buttons */
    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 30px;
      padding: 12px 24px;
      background: var(--primary);
      color: white;
      border-radius: 30px;
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
      border: none;
      cursor: pointer;
    }
    
    .btn-back:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(138, 99, 210, 0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .order-details-container {
        padding: 25px;
        margin: 20px;
      }
      
      .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .order-item-image {
        width: 100%;
        height: auto;
        max-height: 200px;
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

  <div class="order-details-container">
    <div class="order-header">
      <div>
        <h2>Order #<?php echo $order['order_id']; ?></h2>
        <p>Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
      </div>
      <div>
        <span class="order-status status-<?php echo $order['status']; ?>">
          <?php echo ucfirst($order['status']); ?>
        </span>
      </div>
    </div>
    
    <div class="order-items">
      <h3>Order Items</h3>
      <?php while($item = $items->fetch_assoc()): ?>
        <div class="order-item">
          <img src="../uploads/<?php echo $item['image']; ?>" class="order-item-image" alt="<?php echo $item['name']; ?>">
          <div class="order-item-details">
            <div class="order-item-name"><?php echo $item['name']; ?></div>
            <div class="order-item-price">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></div>
          </div>
          <div class="order-item-total">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
        </div>
      <?php endwhile; ?>
    </div>
    
    <div class="order-summary">
      <h3>Order Summary</h3>
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
    
    <div class="shipping-info">
      <h3>Shipping Information</h3>
      <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
      <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    </div>
    
    <a href="patient_dashboard.php" class="btn-back">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

</body>
</html>