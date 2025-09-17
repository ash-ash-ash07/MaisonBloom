<?php
include "db.php";
error_log(print_r($_GET, true));
session_start();

// Redirect if not logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: products.php");
    exit;
}

// Handle direct "Buy Now" from product page
if (isset($_GET['product_id']) && isset($_GET['quantity'])) {
    $product_id = intval($_GET['product_id']);
    $quantity = intval($_GET['quantity']);
    
    // Get product details
    $product = $conn->query("SELECT * FROM products WHERE product_id = $product_id")->fetch_assoc();
    
    if ($product) {
        $_SESSION['cart'] = array(
            $product_id => array(
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            )
        );
    }
}

// Calculate total
$total = 0;
foreach($_SESSION['cart'] as $product_id => $item) {
    $total += $item['price'] * $item['quantity'];
}

// Store order details in session for payment processing
$_SESSION['order_details'] = [
    'total_amount' => $total * 100, // Convert to paise (Razorpay requirement)
    'currency' => 'INR',
    'products' => $_SESSION['cart']
];

// Process checkout after successful payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['razorpay_payment_id'])) {
    // This will be handled by the payment verification callback
    // The actual order creation happens in verify_payment.php
}

// Razorpay configuration (replace with your actual keys)
define('RAZORPAY_KEY_ID', 'rzp_test_RI9FQgK2b4MTMp');
define('RAZORPAY_KEY_SECRET', 'p65o1EuP7YEI8gNi9bBG4h4Y');
?>

<!DOCTYPE html>
<html>
<head>
  <title>Checkout - Maison Bloom</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
    }

    .page-title {
      color: var(--purple-dark);
      text-align: center;
      margin-bottom: 30px;
    }

    .checkout-container {
      display: flex;
      gap: 30px;
    }

    .checkout-form {
      flex: 2;
      background: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow);
    }

    .order-summary {
      flex: 1;
      background: var(--white);
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--shadow);
      height: fit-content;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--purple-dark);
      font-weight: 500;
    }

    .form-group input, .form-group select, .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
    }

    .form-row {
      display: flex;
      gap: 20px;
    }

    .form-row .form-group {
      flex: 1;
    }

    .summary-title {
      color: var(--purple-dark);
      border-bottom: 2px solid var(--lavender-light);
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--lavender-light);
    }

    .order-item img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 15px;
    }

    .order-item-details {
      flex: 1;
    }

    .order-item-name {
      font-weight: bold;
      color: var(--purple-dark);
    }

    .order-item-price {
      color: #666;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }

    .total-row {
      font-weight: bold;
      font-size: 18px;
      margin-top: 20px;
      padding-top: 15px;
      border-top: 2px solid var(--lavender-light);
    }

    .btn {
      display: inline-block;
      padding: 14px 28px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s;
      text-align: center;
      border: none;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
    }

    .btn-primary {
      background-color: var(--lavender-dark);
      color: white;
      margin-top: 20px;
    }

    .btn-primary:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
    }
    
    .razorpay-payment-button {
      background-color: var(--lavender-dark);
      color: white;
      padding: 14px 28px;
      border-radius: 25px;
      font-weight: bold;
      border: none;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
      margin-top: 20px;
    }
    
    .razorpay-payment-button:hover {
      background-color: var(--purple-dark);
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
      <a href="cart.php" style="position: relative;">
        🛒
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span style="position: absolute; top: -10px; right: -10px; background: var(--purple-dark); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
            <?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
          </span>
        <?php endif; ?>
      </a>
    </div>
  </nav>

  <div class="container">
    <h1 class="page-title">Checkout</h1>

    <form id="checkout-form" method="POST" action="verify_payment.php">
      <div class="checkout-container">
        <div class="checkout-form">
          <h2>Shipping Information</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="city">City</label>
              <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
              <label for="state">State</label>
              <input type="text" id="state" name="state" required>
            </div>
            <div class="form-group">
              <label for="zip">ZIP Code</label>
              <input type="text" id="zip" name="zip" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
          </div>
          
          <div class="form-group">
            <label for="notes">Order Notes (Optional)</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
          </div>
        </div>

        <div class="order-summary">
          <h3 class="summary-title">Your Order</h3>
          
          <?php foreach($_SESSION['cart'] as $product_id => $item): ?>
            <div class="order-item">
              <img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
              <div class="order-item-details">
                <div class="order-item-name"><?php echo $item['name']; ?></div>
                <div class="order-item-price">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></div>
              </div>
              <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
          <?php endforeach; ?>
          
          <div class="summary-row">
            <span>Subtotal:</span>
            <span>₹<?php echo number_format($total, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping:</span>
            <span>Free</span>
          </div>
          <div class="summary-row total-row">
            <span>Total:</span>
            <span>₹<?php echo number_format($total, 2); ?></span>
          </div>
          
          <input type="hidden" name="amount" value="<?php echo $total * 100; ?>">
          <input type="hidden" name="currency" value="INR">
          
          <button type="button" id="rzp-button" class="btn btn-primary">Pay with Razorpay</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    var options = {
      "key": "<?php echo RAZORPAY_KEY_ID; ?>",
      "amount": "<?php echo $total * 100; ?>", // Amount is in currency subunits (paise)
      "currency": "INR",
      "name": "Maison Bloom",
      "description": "Product Purchase",
      "image": "https://example.com/your_logo.jpg", // Add your logo URL
      "handler": function (response){
        // After payment is successful, submit the form to verify_payment.php
        document.getElementById('checkout-form').submit();
      },
      "prefill": {
        "name": document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value,
        "email": "<?php echo $_SESSION['email'] ?? ''; ?>",
        "contact": document.getElementById('phone').value
      },
      "notes": {
        "address": document.getElementById('address').value
      },
      "theme": {
        "color": "#6a5acd"
      }
    };
    
    var rzp = new Razorpay(options);
    
    document.getElementById('rzp-button').onclick = function(e){
      // Validate form before opening Razorpay
      if (document.getElementById('first_name').value && 
          document.getElementById('last_name').value && 
          document.getElementById('address').value && 
          document.getElementById('city').value && 
          document.getElementById('state').value && 
          document.getElementById('zip').value && 
          document.getElementById('phone').value) {
        rzp.open();
        e.preventDefault();
      } else {
        alert('Please fill all the required fields before proceeding to payment.');
      }
    }
  </script>

</body>
</html>