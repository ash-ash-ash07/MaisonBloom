<?php
include "db.php";
session_start();

// Add to cart functionality
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Get product details
    $product = $conn->query("SELECT * FROM products WHERE product_id = $product_id")->fetch_assoc();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = array(
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            );
        }
        
        echo "<script>alert('Product added to cart!');</script>";
    }
}

// Get all products
$res = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
  <title>All Products - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
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

    .cart-icon {
      position: relative;
    }

    .cart-count {
      position: absolute;
      top: -10px;
      right: -10px;
      background: var(--purple-dark);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
    }

    .page-title {
      text-align: center;
      color: var(--purple-dark);
      margin: 30px 0;
      font-size: 32px;
    }

    .product-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      padding: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .product-card {
      width: 280px;
      background: var(--white);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .product-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .product-card h3 {
      font-size: 18px;
      color: var(--purple-dark);
      margin: 10px 0;
      height: 50px;
      overflow: hidden;
    }

    .product-card p {
      color: #555;
      font-weight: bold;
      margin: 10px 0;
    }

    .product-price {
      font-size: 20px;
      color: var(--lavender-dark);
      font-weight: bold;
      margin: 15px 0;
    }

    .quantity-control {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 15px 0;
    }

    .quantity-btn {
      width: 30px;
      height: 30px;
      background: var(--lavender-medium);
      color: white;
      border: none;
      border-radius: 50%;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .quantity-input {
      width: 40px;
      text-align: center;
      margin: 0 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 5px;
    }

    .btn-group {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    .btn {
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
    }

    .btn-primary {
      background-color: var(--lavender-dark);
      color: white;
    }

    .btn-secondary {
      background-color: var(--lavender-medium);
      color: white;
    }

    .btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
    }

    .view-cart-btn {
      display: block;
      width: 200px;
      margin: 30px auto;
      text-align: center;
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
      <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'patient'): ?>
        <a href="patient_feedback.php">Feedback</a>
      <?php endif; ?>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
      <a href="cart.php" class="cart-icon">
        🛒
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span class="cart-count"><?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>
        <?php endif; ?>
      </a>
    </div>
  </nav>

  <h1 class="page-title">Our Premium Products</h1>

  <div class="product-container">
    <?php
    while ($row = $res->fetch_assoc()) {
      echo "
        <div class='product-card'>
          <img src='uploads/{$row['image']}' alt='{$row['name']}'>
          <h3>{$row['name']}</h3>
          <p>{$row['category']}</p>
          <div class='product-price'>₹{$row['price']}</div>
          
          <form method='POST'>
            <div class='quantity-control'>
              <button type='button' class='quantity-btn minus'>-</button>
              <input type='number' name='quantity' class='quantity-input' value='1' min='1'>
              <button type='button' class='quantity-btn plus'>+</button>
            </div>
            <input type='hidden' name='product_id' value='{$row['product_id']}'>
            <div class='btn-group'>
              <button type='submit' name='add_to_cart' class='btn btn-secondary'>Add to Cart</button>
              <a href='checkout.php?product_id={$row['product_id']}&quantity=1' class='btn btn-primary'>Buy Now</a>
            </div>
          </form>
        </div>
      ";
    }
    ?>
  </div>

  <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
    <a href="cart.php" class="btn btn-primary view-cart-btn">View Cart (<?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>)</a>
  <?php endif; ?>

  <script>
    // Quantity control buttons
    document.querySelectorAll('.quantity-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        let value = parseInt(input.value);
        
        if (this.classList.contains('minus')) {
          if (value > 1) {
            input.value = value - 1;
          }
        } else {
          input.value = value + 1;
        }
      });
    });
  </script>

</body>
</html>