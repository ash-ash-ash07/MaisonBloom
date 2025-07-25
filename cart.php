<?php
include "db.php";
session_start();

// Redirect if not logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    header("Location: cart.php");
    exit;
}

// Update quantities
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        
        if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } elseif ($quantity <= 0 && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit;
}

// Proceed to checkout
if (isset($_POST['checkout'])) {
    header("Location: checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Your Cart - Maison Bloom</title>
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

    .cart-container {
      display: flex;
      gap: 30px;
    }

    .cart-items {
      flex: 2;
      background: var(--white);
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow);
    }

    .cart-summary {
      flex: 1;
      background: var(--white);
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow);
      height: fit-content;
    }

    .cart-table {
      width: 100%;
      border-collapse: collapse;
    }

    .cart-table th {
      text-align: left;
      padding: 12px;
      border-bottom: 2px solid var(--lavender-light);
      color: var(--purple-dark);
    }

    .cart-table td {
      padding: 15px 12px;
      border-bottom: 1px solid var(--lavender-light);
    }

    .product-cell {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }

    .quantity-input {
      width: 50px;
      text-align: center;
      padding: 5px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .remove-btn {
      color: #ff6b6b;
      text-decoration: none;
      font-size: 14px;
    }

    .remove-btn:hover {
      text-decoration: underline;
    }

    .summary-title {
      color: var(--purple-dark);
      border-bottom: 2px solid var(--lavender-light);
      padding-bottom: 10px;
      margin-bottom: 20px;
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
      padding: 12px 24px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s;
      text-align: center;
      border: none;
      cursor: pointer;
    }

    .btn-primary {
      background-color: var(--lavender-dark);
      color: white;
      width: 100%;
      margin-top: 20px;
    }

    .btn-secondary {
      background-color: var(--lavender-medium);
      color: white;
      width: 100%;
      margin-top: 10px;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
    }

    .empty-cart {
      text-align: center;
      padding: 50px;
      color: #666;
    }

    .empty-cart a {
      display: inline-block;
      margin-top: 20px;
    }
    .button-container {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.btn-primary, .btn-secondary {
  flex: 1;
  padding: 12px 24px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s;
  text-align: center;
  border: none;
  cursor: pointer;
  margin-top: 0;
}

.btn-primary {
  background-color: var(--lavender-dark);
  color: white;
}

.btn-secondary {
  background-color: var(--lavender-medium);
  color: white;
}

.btn-primary:hover, .btn-secondary:hover {
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
      <a href="patient_feedback.php">Feedback</a>
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
    <h1 class="page-title">Your Shopping Cart</h1>

    <?php if(empty($_SESSION['cart'])): ?>
      <div class="empty-cart">
        <h2>Your cart is empty</h2>
        <p>Browse our products to find something you'll love!</p>
        <a href="products.php" class="btn btn-primary">Shop Now</a>
      </div>
    <?php else: ?>
      <form method="POST">
        <div class="cart-container">
          <div class="cart-items">
            <table class="cart-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $total = 0;
                foreach($_SESSION['cart'] as $product_id => $item):
                  $subtotal = $item['price'] * $item['quantity'];
                  $total += $subtotal;
                ?>
                  <tr>
                    <td>
                      <div class="product-cell">
                        <img src="uploads/<?php echo $item['image']; ?>" class="product-image" alt="<?php echo $item['name']; ?>">
                        <div><?php echo $item['name']; ?></div>
                      </div>
                    </td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                      <input type="number" name="quantities[<?php echo $product_id; ?>]" class="quantity-input" 
                             value="<?php echo $item['quantity']; ?>" min="1">
                    </td>
                    <td>₹<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                      <a href="?remove=<?php echo $product_id; ?>" class="remove-btn">Remove</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <button type="submit" name="update_cart" class="btn btn-secondary" style="margin-top: 20px;">Update Cart</button>
          </div>

          <div class="cart-summary">
            <h3 class="summary-title">Order Summary</h3>
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
            <div class="button-container">
  <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout</button>
  <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
</div>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>

</body>
</html>