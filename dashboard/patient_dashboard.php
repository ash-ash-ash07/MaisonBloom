<?php
session_start();
include "../db.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Patient Dashboard - Maison Bloom</title>
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
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light);
      color: var(--text);
      line-height: 1.6;
    }

    nav {
      background-color: var(--white);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 5%;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08);
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
      gap: 25px;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--text);
      font-weight: 500;
      transition: all 0.3s ease;
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

    .banner {
      background: linear-gradient(135deg, rgba(138, 99, 210, 0.9) 0%, rgba(99, 210, 160, 0.8) 100%), 
                  url('../images/patient-banner.jpg');
      background-size: cover;
      background-position: center;
      height: 350px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--white);
      text-align: center;
      padding: 0 20px;
      position: relative;
      overflow: hidden;
    }

    .banner::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    }

    .banner h1 {
      font-size: 3rem;
      margin-bottom: 15px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
      position: relative;
      z-index: 1;
      animation: fadeInDown 1s ease;
    }

    .banner p {
      font-size: 1.2rem;
      max-width: 700px;
      opacity: 0.9;
      position: relative;
      z-index: 1;
      animation: fadeInUp 1s ease;
    }

    .welcome-badge {
      background-color: var(--white);
      color: var(--primary-dark);
      padding: 8px 20px;
      border-radius: 30px;
      font-weight: 600;
      margin-top: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      animation: bounce 2s infinite;
      position: relative;
      z-index: 1;
    }

    .section-title {
      text-align: center;
      margin: 60px 0 30px;
      position: relative;
    }

    .section-title h2 {
      font-size: 2.2rem;
      color: var(--primary-dark);
      display: inline-block;
      position: relative;
      padding-bottom: 15px;
    }
    .section-title h3 {
      text-align: center;
    }
    .section-title h4{
      text-align:center;
    }

    .section-title h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 2px;
    }

    .product-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
  padding: 30px 5%;
  max-width: 1400px;
  margin: 0 auto;
}

.product-card {
  width: 280px;
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.product-image {
  width: 100%;
  height: 200px;
  position: relative;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.product-tag {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: var(--secondary);
  color: var(--white);
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.product-info {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.product-info h3 {
  font-size: 1.1rem;
  color: var(--primary-dark);
  margin-bottom: 10px;
  min-height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.price {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--primary);
  margin: 10px 0;
  text-align: center;
}

.btn-group {
  display: flex;
  gap: 10px;
  margin-top: auto;
  padding-top: 15px;
}

.btn-group form {
  flex: 1;
}

.btn-group a {
  flex: 1;
}

.btn {
  display: inline-block;
  padding: 10px;
  border-radius: 30px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  text-align: center;
  width: 100%;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-primary {
  background-color: var(--primary);
  color: var(--white);
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(138, 99, 210, 0.3);
}

.btn-secondary {
  background-color: var(--accent);
  color: var(--white);
}

.btn-secondary:hover {
  background-color: #4dbb8c;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(99, 210, 160, 0.3);
}

.btn-view-more {
  display: block;
  width: fit-content;
  margin: 30px auto;
  padding: 12px 30px;
  background-color: var(--primary);
  color: var(--white);
  border-radius: 30px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-view-more:hover {
  background-color: var(--primary-dark);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(138, 99, 210, 0.3);
}
    .how-it-works {
      background: linear-gradient(135deg, var(--light) 0%, var(--white) 100%);
      padding: 80px 5%;
      margin-top: 60px;
      position: relative;
      overflow: hidden;
    }

    .how-it-works::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('../images/pattern.png');
      opacity: 0.03;
      z-index: 0;
    }

    .how-it-works h2 {
      position: relative;
      z-index: 1;
    }

    .steps-container {
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .steps {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin-top: 40px;
    }

    .step {
      background: var(--white);
      border-radius: 16px;
      width: 280px;
      padding: 30px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.05);
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .step:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .step::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
    }

    .step-number {
      width: 40px;
      height: 40px;
      background-color: var(--primary);
      color: var(--white);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      margin: 0 auto 20px;
      position: relative;
    }

    .step-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 20px;
    }

    .step h4 {
      color: var(--primary-dark);
      margin-bottom: 15px;
      font-size: 1.2rem;
    }

    .step p {
      color: var(--text-light);
      font-size: 0.95rem;
    }

    .cta-container {
      text-align: center;
      margin-top: 50px;
      position: relative;
      z-index: 1;
    }

    .booking-section {
      max-width: 1000px;
      margin: 80px auto;
      padding: 40px;
      background-color: var(--white);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }

    .booking-section::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(138, 99, 210, 0.1) 0%, rgba(138, 99, 210, 0) 70%);
    }

    .booking-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }

    .booking-table th {
      background-color: var(--primary);
      color: var(--white);
      padding: 15px;
      text-align: left;
      font-weight: 500;
    }

    .booking-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .booking-table tr:last-child td {
      border-bottom: none;
    }

    .booking-table tr:hover {
      background-color: rgba(138, 99, 210, 0.03);
    }

    .status {
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      display: inline-block;
    }

    .status.pending {
      background-color: #FFF3CD;
      color: #856404;
    }

    .status.approved {
      background-color: #D4EDDA;
      color: #155724;
    }

    .status.rejected {
      background-color: #F8D7DA;
      color: #721C24;
    }

    .feedback-section {
      text-align: center;
      margin: 80px 0;
    }

    .feedback-btn {
      display: inline-block;
      padding: 15px 40px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: var(--white);
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(138, 99, 210, 0.3);
      position: relative;
      overflow: hidden;
    }

    .feedback-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(138, 99, 210, 0.4);
    }

    .feedback-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: 0.5s;
    }

    .feedback-btn:hover::before {
      left: 100%;
    }

    /* Animations */
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-10px);
      }
      60% {
        transform: translateY(-5px);
      }
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .banner h1 {
        font-size: 2.5rem;
      }
      
      .nav-links {
        gap: 15px;
      }
    }

    @media (max-width: 768px) {
      nav {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
      }
      
      .banner {
        height: 300px;
      }
      
      .banner h1 {
        font-size: 2rem;
      }
      
      .product-card {
        width: 100%;
        max-width: 350px;
      }
      
      .steps {
        flex-direction: column;
        align-items: center;
      }
      
      .step {
        width: 100%;
        max-width: 350px;
      }
      
      .booking-section {
        padding: 20px;
      }
    }

    @media (max-width: 576px) {
      .banner h1 {
        font-size: 1.8rem;
      }
      
      .banner p {
        font-size: 1rem;
      }
      
      .section-title h2 {
        font-size: 1.8rem;
      }
      
      .btn-group {
        flex-direction: column;
      }
    }
    .order-history-section {
      max-width: 1000px;
      margin: 80px auto;
      padding: 40px;
      background-color: var(--white);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }
    
    .order-history-section::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(138, 99, 210, 0.1) 0%, rgba(138, 99, 210, 0) 70%);
    }
    
    .order-history-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    
    .order-history-table th {
      background-color: var(--primary);
      color: var(--white);
      padding: 15px;
      text-align: left;
      font-weight: 500;
    }
    
    .order-history-table td {
      padding: 15px;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .order-history-table tr:last-child td {
      border-bottom: none;
    }
    
    .order-history-table tr:hover {
      background-color: rgba(138, 99, 210, 0.03);
    }
    
    .order-status {
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      display: inline-block;
    }
    
    .status-pending {
      background-color: #FFF3CD;
      color: #856404;
    }
    
    .status-processing {
      background-color: #BEE5EB;
      color: #0C5460;
    }
    
    .status-shipped {
      background-color: #D1ECF1;
      color: #0C5460;
    }
    
    .status-delivered {
      background-color: #D4EDDA;
      color: #155724;
    }
    
    .status-cancelled {
      background-color: #F8D7DA;
      color: #721C24;
    }
    
    .view-order-btn {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      padding: 5px 12px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }
    
    .view-order-btn:hover {
      background-color: var(--primary);
      color: white;
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
    </div>
  </nav>

  <div class="banner">
    <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
    <p>Your journey to healthy skin and hair starts here</p>
    <div class="welcome-badge">
      <i class="fas fa-heart"></i> Premium Member
    </div>
  </div>

  <div class="section-title">
    <h2>Recommended For You</h2>
  </div>

    <div class="product-container">
    <?php
    include "../db.php";
    $res = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 4");
    while ($row = $res->fetch_assoc()) {
      echo "
        <div class='product-card'>
          <div class='product-image'>
            <img src='../uploads/{$row['image']}' alt='{$row['name']}'>
            <div class='product-tag'>NEW</div>
          </div>
          <div class='product-info'>
            <h3>{$row['name']}</h3>
            <div class='price'>₹{$row['price']}</div>
            <div class='btn-group'>
              <a href='../checkout.php?product_id={$row['product_id']}&quantity=1' class='btn btn-secondary'>
                <i class='fas fa-bolt'></i> Buy Now
              </a>
            </div>
          </div>
        </div>
      ";
    }
    ?>
    <a href="../products.php" class="btn-view-more">
      <i class="fas fa-arrow-right"></i> View All Products
    </a>
</div>

  <div class="how-it-works">
    <div class="steps-container">
      <h2 class="section-title">
        <h2>How Maison Bloom Works</h2>
      </h2>

      <h3 class="section-title"> Make Bookings </h3>
    
      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <i class="fas fa-calendar-check step-icon"></i>
          <h4>Book Consultation</h4>
          <p>Schedule with a certified dermatologist at your convenience.</p>
        </div>
        <div class="step">
          <div class="step-number">2</div>
          <i class="fas fa-cloud-upload-alt step-icon"></i>
          <h4>Upload Details</h4>
          <p>Share photos and describe your skin or hair concerns.</p>
        </div>
        <div class="step">
          <div class="step-number">3</div>
          <i class="fas fa-video step-icon"></i>
          <h4>Video Consultation</h4>
          <p>Get expert diagnosis and treatment plan.</p>
        </div>
        <div class="step">
          <div class="step-number">4</div>
          <i class="fas fa-file-prescription step-icon"></i>
          <h4>Receive Prescription</h4>
          <p>Digital prescription and personalized care routine.</p>
        </div>
      </div>

      <div class="cta-container">
        <a href="../booking.php" class="btn-view-more">
          <i class="fas fa-calendar-alt"></i> Book Appointment Now
        </a>
      </div>

      <h3 class="section-title">Buy Products</h3>
      </h3>
      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <i class="fas fa-search step-icon"></i>
          <h4>Browse Products</h4>
          <p>Discover dermatologist-recommended skincare solutions.</p>
        </div>
        <div class="step">
          <div class="step-number">2</div>
          <i class="fas fa-shopping-cart step-icon"></i>
          <h4>Add to Cart</h4>
          <p>Select your products and proceed to checkout.</p>
        </div>
        <div class="step">
          <div class="step-number">3</div>
          <i class="fas fa-truck step-icon"></i>
          <h4>Fast Delivery</h4>
          <p>Receive your products at your doorstep.</p>
        </div>
      </div>

      <div class="cta-container">
        <a href="../products.php" class="btn-view-more">
          <i class="fas fa-shopping-bag"></i> Explore Our Products
        </a>
      </div>
    </div>
  </div>

  <div class="booking-section">
    <h2 class="section-title">
      <h2>Your Appointment History</h2>
    </h2>
    <table class="booking-table">
      <thead>
        <tr>
          <th>Doctor</th>
          <th>Date & Time</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $query="
          SELECT b.*, c.date_time, u.name AS doctor_name 
          FROM bookings b
          JOIN consultation_slots c ON b.slot_id = c.slot_id
          JOIN doctor_profiles d ON c.doctor_id = d.doctor_id
          JOIN users u ON d.user_id = u.user_id
          WHERE b.patient_id = $user_id
          ORDER BY b.created_at DESC
        ";
        $res = $conn->query($query);
        if ($res && $res->num_rows > 0){
          while ($row = $res->fetch_assoc()){
            $status_class = $row['status'];
            $formatted_date = date("D, M j, Y - h:i A", strtotime($row['date_time']));
            echo "<tr>
                    <td><i class='fas fa-user-md'></i> {$row['doctor_name']}</td>
                    <td><i class='far fa-clock'></i> $formatted_date</td>
                    <td><span class='status $status_class'>" . ucfirst($row['status']) . "</span></td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='3' style='text-align: center;'>No bookings yet. <a href='../booking.php' style='color: var(--primary);'>Book your first appointment</a></td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <div class="feedback-section">
    <h2 class="section-title">
      <h2>Share Your Experience</h2>
    </h2>
    <p style="max-width: 600px; margin: 0 auto 30px; color: var(--text-light);">
      Your feedback helps us improve our services and helps other patients make informed decisions.
    </p>
    <a href="patient_feedback.php" class="feedback-btn">
      <i class="fas fa-comment-alt"></i> Give Feedback
    </a>
  </div>
  <div class="order-history-section">
    <h2 class="section-title">
      <h2>Your Order History</h2>
    </h2>
    <table class="order-history-table">
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
        $orders_query = "
          SELECT o.* 
          FROM orders o
          WHERE o.patient_id = $user_id
          ORDER BY o.order_date DESC
          LIMIT 5
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
                    <td><a href='../order_details.php?order_id={$order['order_id']}' class='view-order-btn'>View Details</a></td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='5' style='text-align: center;'>No orders yet. <a href='../products.php' style='color: var(--primary);'>Start shopping</a></td></tr>";
        }
        ?>
      </tbody>
    </table>
    <div style="text-align: center; margin-top: 20px;">
      <a href="../order_history.php" class="btn-view-more">
        <i class="fas fa-history"></i> View Full Order History
      </a>
    </div>
  </div>
  
<?php
include('../db.php'); // Adjust path if needed

$patient_id = $_SESSION['patient_id'] ?? $_SESSION['user_id'] ?? null;
if (!$patient_id) {
    echo "Error: Patient not logged in.";
    exit;
}

$query = "SELECT p.content, u.name AS doctor_name, p.issued_at
          FROM prescriptions p
          JOIN doctor_profiles dp ON p.doctor_id = dp.doctor_id
          JOIN users u ON dp.user_id = u.user_id
          WHERE p.patient_id = $patient_id
          ORDER BY p.issued_at DESC";

$result = mysqli_query($conn, $query);
?>

 <!-- ✅ now HTML starts outside PHP -->
<div class="container" style="margin-top: 40px;">
    <h3 style="color: #7b4397;">Your Prescriptions</h3>
    <table style="width: 100%; border-collapse: collapse; background-color: #f5f0fa; color: #4a148c; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #a084ca; color: white;">
                <th style="padding: 12px;">Doctor</th>
                <th style="padding: 12px;">Prescription</th>
                <th style="padding: 12px;">Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td style="padding: 10px;"><?= htmlspecialchars($row['doctor_name']) ?></td>
                <td style="padding: 10px;"><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                <td style="padding: 10px;"><?= date('M d, Y', strtotime($row['issued_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3" style="padding: 15px; text-align: center;">No prescriptions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<a href="prescriptions.php?patient_id=<?= $_SESSION['user_id'] ?>" class="btn">View My Prescriptions</a>

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