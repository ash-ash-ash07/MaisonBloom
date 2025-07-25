<?php
include "db.php";
session_start();

// Add to cart functionality
if(isset($_POST['add_to_cart'])) {
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

// Buy Now functionality (direct add to cart and redirect to checkout)
if (isset($_GET['buy_now'])) {
    $product_id = intval($_GET['product_id']);
    $quantity = intval($_GET['quantity']);
    
    // Get product details
    $product = $conn->query("SELECT * FROM products WHERE product_id = $product_id")->fetch_assoc();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }
        
        // Replace any existing quantity with the new one for Buy Now
        $_SESSION['cart'][$product_id] = array(
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        );
        
        header("Location: checkout.php");
        exit();
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f3efff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--lavender-light);
      margin: 0;
      padding: 0;
      color: #333;
    }

    nav {
      background-color: var(--white);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      box-shadow: var(--shadow);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: var(--lavender-dark);
      font-weight: 600;
      transition: var(--transition);
      padding: 8px 12px;
      border-radius: 20px;
    }

    nav a:hover {
      background-color: var(--lavender-light);
      color: var(--purple-dark);
    }

    .cart-icon {
      position: relative;
      display: inline-flex;
      align-items: center;
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
      font-weight: bold;
    }

    .page-title {
      text-align: center;
      color: var(--purple-dark);
      margin: 40px 0 30px;
      font-size: 2.5rem;
      font-weight: 600;
      position: relative;
      display: inline-block;
      width: 100%;
    }

    .page-title:after {
      content: '';
      display: block;
      width: 80px;
      height: 4px;
      background: var(--lavender-medium);
      margin: 15px auto;
      border-radius: 2px;
    }
    .product-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      padding: 30px 5%;
      max-width: 1400px;
      margin: 0 auto;
    }

    .product-card {
      background: var(--white);
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(107, 70, 193, 0.15);
    }

    .product-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 20px;
      transition: var(--transition);
    }

    .product-card:hover img {
      transform: scale(1.03);
    }

    .product-card h3 {
      font-size: 1.2rem;
      color: var(--purple-dark);
      margin: 10px 0;
      font-weight: 600;
    }

    .product-card .category {
      color: var(--lavender-dark);
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 15px;
      display: inline-block;
      padding: 4px 12px;
      background: var(--lavender-light);
      border-radius: 20px;
    }

    .product-price {
      font-size: 1.4rem;
      color: var(--lavender-dark);
      font-weight: 700;
      margin: 20px 0;
    }

    .quantity-control {
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 20px 0;
    }

    .quantity-btn {
      width: 32px;
      height: 32px;
      background: var(--lavender-medium);
      color: var(--purple-dark);
      border: 1px solid var(--lavender-medium);
      border-radius: 50%;
      font-size: 16px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .quantity-btn:hover {
      background: var(--purple-dark);
      color: white;
    }

    .quantity-input {
      width: 50px;
      text-align: center;
      margin: 0 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 8px;
      font-weight: 600;
    }

    .btn-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 20px;
    }

    .btn {
      padding: 12px 20px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-primary {
      background-color: var(--purple-dark);
      color: white;
    }

    .btn-primary:hover {
      background-color: #3a0068;
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
    }

    .btn-secondary {
      background-color: var(--lavender-medium);
      color: white;
    }

    .btn-secondary:hover {
      background-color: var(--lavender-dark);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(107, 70, 193, 0.3);
    }

    .view-cart-btn {
      display: block;
      width: 220px;
      margin: 40px auto;
      text-align: center;
      padding: 14px;
      font-size: 1rem;
    }

    @media (max-width: 768px) {
      .product-container {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        padding: 20px;
      }
      
      nav {
        flex-direction: column;
        padding: 15px;
      }
      
      nav > div {
        margin-top: 10px;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div><strong style="font-size: 1.2rem; color: var(--purple-dark);">🌸 Maison Bloom</strong></div>
    <div>
      <a href="home.php">Home</a>
      <a href="booking.php">Book Now</a>
      <a href="products.php">Products</a>
      <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'patient'): ?>
        <a href="../dashboard/patient_feedback.php">Feedback</a>
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
          <p class='category'>{$row['category']}</p>
          <div class='product-price'>₹{$row['price']}</div>
          
          <form method='POST' class='add-to-cart-form'>
            <div class='quantity-control'>
              <button type='button' class='quantity-btn minus'>-</button>
              <input type='number' name='quantity' class='quantity-input' value='1' min='1'>
              <button type='button' class='quantity-btn plus'>+</button>
            </div>
            <input type='hidden' name='product_id' value='{$row['product_id']}'>
            <div class='btn-group'>
              <button type='submit' name='add_to_cart' class='btn btn-secondary'>Add to Cart</button>
              <a href='products.php?buy_now=1&product_id={$row['product_id']}&quantity=1' class='btn btn-primary'>Buy Now</a>
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
        
        // Update the quantity in the Buy Now link
        const form = this.closest('form');
        const buyNowBtn = form.querySelector('.btn-group a[href*="buy_now"]');
        if (buyNowBtn) {
          const newHref = buyNowBtn.href.replace(/quantity=\d+/, quantity=${input.value});
          buyNowBtn.href = newHref;
        }
      });
    });

    // Update Buy Now link when quantity input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
      input.addEventListener('change', function() {
        const form = this.closest('form');
        const buyNowBtn = form.querySelector('.btn-group a[href*="buy_now"]');
        if (buyNowBtn) {
          const newHref = buyNowBtn.href.replace(/quantity=\d+/, quantity=${this.value});
          buyNowBtn.href = newHref;
        }
      });
    });
  </script>

</body>
</html>