<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maison Bloom - Home</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

  <link rel="stylesheet" href="css/style.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body, html {
      height: 100%;
      font-family: 'Segoe UI', sans-serif;
    }

    nav {
      background-color: rgba(255, 255, 255, 0.95);
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    nav .nav-container {
      max-width: 1100px;
      margin: auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
    }

    nav .logo {
      font-size: 24px;
      color: #6a5acd;
      font-weight: bold;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
    }

    nav ul li a {
      text-decoration: none;
      color: #4b0082;
      font-weight: bold;
      padding: 8px 12px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    nav ul li a:hover {
      background-color: #e1d5fa;
    }

    .slideshow {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .slideshow img {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      animation: slideShow 15s infinite;
    }

    .slideshow img:nth-child(1) {
      animation-delay: 0s;
    }

    .slideshow img:nth-child(2) {
      animation-delay: 5s;
    }

    .slideshow img:nth-child(3) {
      animation-delay: 10s;
    }

    @keyframes slideShow {
      0% { opacity: 0; }
      10% { opacity: 1; }
      30% { opacity: 1; }
      40% { opacity: 0; }
      100% { opacity: 0; }
    }

    .overlay {
      
      min-height: 100vh;
      padding-top: 100px;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .welcome-container {
      max-width: 1200px;
      margin: auto;
      padding: 40px;
      background: rgba(230, 230, 250, 0.8);
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(75, 0, 130, 0.1);
    }
    .container {
      max-width: 1200px;
      margin: auto;
      background: rgba(230, 230, 250, 0.8);
      padding: 40px 20px;
      text-align: center;
    }

    h1 {
      font-size: 3em;
      color: #6a5acd;
      margin-bottom: 10px;
    }

    p {
      font-size: 1.2em;
      color: #333;
    }
     .product-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-top: 40px;
    }

    .card {
      width: 220px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
      text-align: center;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: scale(1.03);
    }

    .card img {
      width: 100%;
      height: 170px;
      object-fit: cover;
    }

    .card h4 {
      color: #4b0082;
      margin: 10px 0 5px;
    }

    .card p {
      color: #666;
      margin-bottom: 15px;
    }

    .btn-container {
      margin-top: 30px;
    }

    .btn {
      background-color: #b39ddb;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 30px;
      text-decoration: none;
      font-weight: bold;
      margin: 10px;
      display: inline-block;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background-color: #9575cd;
    }
    
.testimonial-section {
  max-width: 800px;
  margin: 60px auto;
  text-align: center;
}
.testimonial-card {
  background:rgba(230, 230, 250, 0.8);
  color: #black;
  padding: 50px;
  border-radius: 15px;
  max-width: 500px;
  margin: auto;
  position: relative;
}
.stars {
  font-size: 20px;
  color: gold;
  margin-bottom: 10px;
}
.testimonial-card h4 {
  color: #black;
  margin-bottom: 10px;
}
.testimonial-card p {
  font-style: italic;
  font-size: 14px;
}
.patient-info {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 15px;
}
.patient-info img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}
.patient-info span {
  font-weight: bold;
  color: #black;
}


  </style>
</head>
<body>

  <!-- Navigation -->
  <nav>
    <div class="nav-container">
      <div class="logo">🌸 Maison Bloom</div>
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="login.php">Login</a></li>
      </ul>
    </div>
  </nav>

  <!-- Background Slideshow -->
  <div class="slideshow">
    <img src="images/skincare1.jpg" alt="Skincare 1">
    <img src="images/skincare2.jpg" alt="Skincare 2">
    <img src="images/skincare3.jpg" alt="Skincare 3">
  </div>

  <!-- Welcome Message -->
  <div class="overlay">
    <div class="welcome-container">
      <h1>Welcome to Maison Bloom</h1>
      <p>Your trusted dermatology & skincare platform. Book online consultations, upload images, and shop dermatologist-recommended products—all in one place.</p>
      <div class="btn-container">
        <a href="register.php" class="btn">Get Started</a>
        <a href="login.php" class="btn">Login</a>
      </div>
    </div>
  </div>
  <div class="container">
    
    <h2 style="color:#4b0082; margin-top: 50px;">Recently Added Products</h2>

    <div class="product-list">
      <?php
      $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
      ?>
        <div class="card">
          <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
          <h4><?php echo $row['name']; ?></h4>
          <p>₹<?php echo $row['price']; ?></p>
        </div>
      <?php
        endwhile;
      else:
        echo "<p>No products available.</p>";
      endif;
      ?>
    </div>

    <a href="login.php" class="btn">Explore More</a>
  </div>
  <div class="swiper mySwiper testimonial-section">
  <div class="swiper-wrapper">
    <?php
    include "db.php";
    $feedbacks = $conn->query("
      SELECT f.*, u.name, u.role 
      FROM feedbacks f
      JOIN users u ON f.patient_id = u.user_id
      ORDER BY f.created_at DESC
      LIMIT 10
    ");

    while ($row = $feedbacks->fetch_assoc()):
      $stars = str_repeat("★", $row['rating']) . str_repeat("☆", 5 - $row['rating']);
    ?>
    <div class="swiper-slide">
      <div class="testimonial-card">
        <div class="stars"><?php echo $stars; ?></div>
        <h4>Great service!</h4>
        <p><?php echo $row['message']; ?></p>
        <div class="patient-info">
          <img src="images/user.png" alt="avatar">
          <span><?php echo $row['name']; ?> <small>Patient</small></span>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>
<script>
var swiper = new Swiper(".mySwiper", {
  loop: true,
  slidesPerView: 1,
  spaceBetween: 30,
  centeredSlides: true,
  autoplay: {
    delay: 4000,
    disableOnInteraction: false,
  }
});
</script>


</body>
</html>
