<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Dashboard - Maison Bloom</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f3efff;
    }

    nav {
      background-color: #fff;
      display: flex;
      justify-content: space-between;
      padding: 15px 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #6a5acd;
      font-weight: bold;
    }

    .banner {
      background-image: url('../images/patient-banner.jpg');
      background-size: cover;
      background-position: center;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    .banner h1 {
      font-size: 3em;
    }

    .content {
      padding: 40px;
      text-align: center;
    }

    .card {
      display: inline-block;
      width: 250px;
      margin: 15px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      background: #fff;
    }

    .card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .card .info {
      padding: 15px;
    }

    .btn {
      display: inline-block;
      background: #b39ddb;
      color: white;
      padding: 10px 20px;
      border-radius: 20px;
      text-decoration: none;
      margin-top: 15px;
    
    }
    .how-it-works {
  background: #eae2ff;
  padding: 50px 20px;
  margin-top: 40px;
}

.how-it-works h2 {
  color: #4b0082;
  font-size: 30px;
  margin-bottom: 30px;
  text-align: center;
}

.section-title {
  font-size: 22px;
  text-align: center;
  color: #6a5acd;
  margin: 40px 0 20px 10px;
}

.steps {
  display: flex;
  flex-wrap: wrap;
  gap: 25px;
  justify-content: center;
}

.step {
  background: #fff;
  border-radius: 12px;
  width: 260px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  text-align: center;
  opacity: 0;
  transform: translateY(30px);
  animation: fadeInUp 1s forwards;
}

.step h4 {
  color: #9575cd;
  margin: 10px 0;
  font-size: 18px;
}

.step p {
  font-size: 14px;
  color: #444;
  line-height: 1.5;
}

.step img.icon {
  width: 50px;
  height: 50px;
  margin-bottom: 10px;
}

.btn {
  background-color: #b39ddb;
  color: white;
  padding: 10px 24px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s;
}

.btn:hover {
  background-color: #9575cd;
}

/* Animation */
@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step:nth-child(1) { animation-delay: 0.1s; }
.step:nth-child(2) { animation-delay: 0.3s; }
.step:nth-child(3) { animation-delay: 0.5s; }
.step:nth-child(4) { animation-delay: 0.7s; }
.step:nth-child(5) { animation-delay: 0.9s; }
.step:nth-child(6) { animation-delay: 1.1s; }
.step:nth-child(7) { animation-delay: 1.3s; }
.product-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
  padding: 30px;
}

.product-card {
  width: 220px;
  background: #fff;
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: scale(1.07);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.product-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 10px;
}

.product-card h3 {
  font-size: 16px;
  color: #4b0082;
  margin: 8px 0;
  height: 45px; /* Ensures equal height for product names */
  overflow: hidden;
}

.product-card p {
  color: #444;
  font-weight: bold;
  margin: 5px 0 15px;
}

.buy-btn {
  display: inline-block;
  padding: 10px 16px;
  background-color: #b39ddb;
  color: white;
  font-weight: bold;
  text-decoration: none;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.buy-btn:hover {
  background-color: #9575cd;
}
.feedback-btn {
  background-color: #b39ddb;
  color: white;
  padding: 12px 24px;
  font-weight: bold;
  text-decoration: none;
  border-radius: 10px;
  transition: background-color 0.3s ease;
}

.feedback-btn:hover {
  background-color: #9575cd;
}


  </style>
</head>
<body>

  <nav>
    <div><strong> 🌸 Maison Bloom</strong></div>
    <div>
      <a href="../home.php">Home</a>
      <a href="../booking.php">Book Now</a>
      <a href="../products.php">Products</a>
      <a href="../logout.php">Logout</a>
    </div>
  </nav>

  <div class="banner">
    <h1>Welcome, <?php echo $_SESSION['name']; ?>! to Maison Bloom</h1>
  </div>

  <h2 style="text-align:center; color: #4b0082;">Recommended Products</h2>

<div class="product-container">
    <?php
    include "../db.php";
    $res = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 5");
    while ($row = $res->fetch_assoc()) {
      echo "
        <div class='product-card'>
          <img src='../uploads/{$row['image']}' alt='{$row['name']}'>
        
            <h3>{$row['name']}</h3>
            <p>₹{$row['price']}</p>
            <a href='../products.php' class='btn'>Buy Now</a>
          </div>
        
      ";
    }
    ?>
    <br><a href="../products.php" class="btn">View More</a>
  </div>
<div class="how-it-works">
  <h2>How Maison Bloom Works</h2>

  <!-- Make Bookings Section -->
  <h3 class="section-title">Make Bookings</h3>
  <div class="steps">
    <div class="step animated">
      <img src="../images/booking.png" class="icon" alt="Book Icon">
      <h4>Step 1</h4>
      <p>Book a consultation with a certified dermatologist that fits your schedule.</p>
    </div>
    <div class="step animated">
      <img src="../images/upload.png" class="icon" alt="Upload Icon">
      <h4>Step 2</h4>
      <p>Upload photos and describe your skin or hair concern before the appointment.</p>
    </div>
    <div class="step animated">
      <img src="../images/consultation.png" class="icon" alt="Consult Icon">
      <h4>Step 3</h4>
      <p>Join your consultation via video or chat and get expert diagnosis and treatment.</p>
    </div>
    <div class="step animated">
      <img src="../images/prescription.png" class="icon" alt="Prescription Icon">
      <h4>Step 4</h4>
      <p>Receive a digital prescription and suggested care routine.</p>
    </div>
  </div>

  <div style="text-align:center; margin-top: 20px;">
    <a href="../booking.php" class="btn">Book Now</a>
  </div>

  <!-- Buy Products Section -->
  <h3 class="section-title">Buy Products</h3>
  <div class="steps">
    <div class="step animated">
      <img src="../images/browse.png" class="icon" alt="Browse Icon">
      <h4>Step 1</h4>
      <p>Browse dermatologist-recommended skincare, haircare, and body products.</p>
    </div>
    <div class="step animated">
      <img src="../images/cart.png" class="icon" alt="Cart Icon">
      <h4>Step 2</h4>
      <p>Add items to your cart and proceed to secure checkout.</p>
    </div>
    <div class="step animated">
      <img src="../images/delivery.png" class="icon" alt="Delivery Icon">
      <h4>Step 3</h4>
      <p>Complete payment and receive your products at your doorstep.</p>
    </div>
  </div>

  <div style="text-align:center; margin-top: 20px;">
    <a href="../products.php" class="btn">Shop Products</a>
  </div>
</div>
<div style="text-align: center; margin-top: 30px;">
  <a href="patient_feedback.php" class="feedback-btn">Give Feedback</a>
</div>

</body>
</html>
