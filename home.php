<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maison Bloom - Home</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body, html {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    /* Navigation */
    nav {
      background-color: rgba(255, 255, 255, 0.98);
      position: fixed;
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1000;
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    nav.scrolled {
      background-color: rgba(255, 255, 255, 0.95);
      box-shadow: 0 5px 20px rgba(106, 90, 205, 0.2);
    }

    .nav-container {
      max-width: 1200px;
      margin: auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
    }

    .logo {
      font-size: 28px;
      color: var(--purple-dark);
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo i {
      color: var(--lavender-dark);
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 25px;
    }

    nav ul li a {
      text-decoration: none;
      color: var(--purple-dark);
      font-weight: 500;
      padding: 8px 15px;
      border-radius: 30px;
      transition: all 0.3s ease;
      position: relative;
    }

    nav ul li a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: var(--lavender-dark);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    nav ul li a:hover::after {
      width: 70%;
    }

    nav ul li a:hover {
      color: var(--lavender-dark);
    }

    .nav-btn {
      background: var(--lavender-dark);
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .nav-btn:hover {
      background: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    /* Hero Section */
    .hero {
      height: 100vh;
      position: relative;
      overflow: hidden;
    }

    .hero-video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -1;
    }

    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(75, 0, 130, 0.2);
      z-index: 0;
    }

    .hero-content {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
      position: relative;
      z-index: 1;
      color: white;
    }

    .hero-content h1 {
      font-size: 4rem;
      margin-bottom: 20px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
      animation: fadeInUp 1s ease;
    }

    .hero-content p {
      font-size: 1.3rem;
      max-width: 700px;
      margin-bottom: 30px;
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
      animation: fadeInUp 1s ease 0.2s forwards;
      opacity: 0;
    }

    .hero-btns {
      display: flex;
      gap: 20px;
      animation: fadeInUp 1s ease 0.4s forwards;
      opacity: 0;
    }

    .btn {
      padding: 15px 30px;
      border-radius: 30px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
    }

    .btn-primary {
      background: var(--lavender-dark);
      color: white;
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .btn-primary:hover {
      background: var(--purple-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(106, 90, 205, 0.4);
    }

    .btn-secondary {
      background: transparent;
      color: white;
      border: 2px solid white;
    }

    .btn-secondary:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-3px);
    }

    /* Features Section */
    .section {
      padding: 100px 20px;
      position: relative;
    }

    .section-title {
      text-align: center;
      margin-bottom: 60px;
    }

    .section-title h2 {
      font-size: 2.5rem;
      color: var(--purple-dark);
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }

    .section-title h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--lavender-dark);
      border-radius: 2px;
    }

    .section-title p {
      color: #666;
      font-size: 1.1rem;
      max-width: 700px;
      margin: 0 auto;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .feature-card {
      background: var(--white);
      border-radius: 15px;
      padding: 40px 30px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border: 1px solid rgba(179, 157, 219, 0.2);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(155, 120, 255, 0.2);
    }

    .feature-icon {
      font-size: 3rem;
      color: var(--lavender-dark);
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1);
      color: var(--purple-dark);
    }

    .feature-card h3 {
      color: var(--purple-dark);
      margin-bottom: 15px;
      font-size: 1.4rem;
    }

    .feature-card p {
      color: #666;
      line-height: 1.6;
    }

    /* Products Section */
    .products-section {
      background: var(--lavender-light);
    }

    .products-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .product-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }

    .product-card {
      background: var(--white);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      position: relative;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(155, 120, 255, 0.25);
    }

    .product-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: var(--lavender-dark);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      z-index: 2;
    }

    .product-img-container {
      height: 200px;
      overflow: hidden;
      position: relative;
    }

    .product-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .product-card:hover .product-img {
      transform: scale(1.1);
    }

    .product-info {
      padding: 20px;
    }

    .product-info h4 {
      color: var(--purple-dark);
      margin-bottom: 10px;
      font-size: 1.1rem;
    }

    .product-price {
      color: var(--lavender-dark);
      font-weight: 600;
      font-size: 1.2rem;
      margin-bottom: 15px;
    }

    .product-btn {
      display: inline-block;
      width: 100%;
      padding: 10px;
      background: var(--lavender-medium);
      color: white;
      text-align: center;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .product-btn:hover {
      background: var(--lavender-dark);
    }

    .section-btn {
      text-align: center;
      margin-top: 50px;
    }

    /* Testimonials */
    .testimonial-section {
      background: var(--white);
      padding: 100px 20px;
    }

    .testimonial-container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .testimonial-card {
      background: var(--white);
      border-radius: 15px;
      padding: 40px;
      box-shadow: var(--shadow);
      margin: 20px;
      position: relative;
      border: 1px solid rgba(179, 157, 219, 0.2);
    }

    .testimonial-card::before {
      content: '"';
      position: absolute;
      top: 20px;
      left: 20px;
      font-size: 5rem;
      color: rgba(179, 157, 219, 0.2);
      font-family: serif;
      line-height: 1;
    }

    .stars {
      color: gold;
      font-size: 1.2rem;
      margin-bottom: 15px;
    }

    .testimonial-text {
      font-style: italic;
      color: #555;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .patient-info {
      display: flex;
      align-items: center;
    }

    .patient-img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 15px;
    }

    .patient-name {
      font-weight: 600;
      color: var(--purple-dark);
    }

    .patient-role {
      color: #666;
      font-size: 0.9rem;
    }

    /* Newsletter */
    .newsletter-section {
      background: var(--lavender-dark);
      color: white;
      padding: 80px 20px;
      text-align: center;
    }

    .newsletter-container {
      max-width: 600px;
      margin: 0 auto;
    }

    .newsletter-section h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }

    .newsletter-section p {
      margin-bottom: 30px;
      opacity: 0.9;
    }

    .newsletter-form {
      display: flex;
      max-width: 500px;
      margin: 0 auto;
    }

    .newsletter-input {
      flex: 1;
      padding: 15px 20px;
      border: none;
      border-radius: 30px 0 0 30px;
      font-size: 1rem;
    }

    .newsletter-input:focus {
      outline: none;
    }

    .newsletter-btn {
      background: var(--purple-dark);
      color: white;
      border: none;
      padding: 0 30px;
      border-radius: 0 30px 30px 0;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .newsletter-btn:hover {
      background: #3a0068;
    }

    /* Footer */
    footer {
      background: var(--purple-dark);
      color: white;
      padding: 60px 20px 30px;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
    }

    .footer-col h3 {
      font-size: 1.3rem;
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }

    .footer-col h3::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 2px;
      background: var(--lavender-medium);
    }

    .footer-col p {
      opacity: 0.8;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .social-links {
      display: flex;
      gap: 15px;
    }

    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      color: white;
      transition: all 0.3s ease;
    }

    .social-links a:hover {
      background: var(--lavender-medium);
      transform: translateY(-3px);
    }

    .footer-links {
      list-style: none;
    }

    .footer-links li {
      margin-bottom: 10px;
    }

    .footer-links a {
      color: white;
      opacity: 0.8;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .footer-links a:hover {
      opacity: 1;
      padding-left: 5px;
    }

    .copyright {
      text-align: center;
      margin-top: 50px;
      padding-top: 20px;
      border-top: 1px solid rgba(255,255,255,0.1);
      opacity: 0.7;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    .floating {
      animation: float 4s ease-in-out infinite;
    }

    /* Back to Top Button */
    .back-to-top {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      background: var(--lavender-dark);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 999;
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .back-to-top.active {
      opacity: 1;
      visibility: visible;
    }

    .back-to-top:hover {
      background: var(--purple-dark);
      transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-content h1 {
        font-size: 2.5rem;
      }
      
      .hero-content p {
        font-size: 1.1rem;
      }
      
      .hero-btns {
        flex-direction: column;
        gap: 15px;
      }
      
      .newsletter-form {
        flex-direction: column;
      }
      
      .newsletter-input {
        border-radius: 30px;
        margin-bottom: 10px;
      }
      
      .newsletter-btn {
        border-radius: 30px;
        padding: 15px;
      }
      
      nav ul {
        display: none;
      }
    }
    /* Hero Slideshow */
.hero-slideshow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
}

.hero-slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  opacity: 0;
  transition: opacity 1.5s ease-in-out;
}

.hero-slide.active {
  opacity: 1;
}
  </style>
</head>
<body>

  <!-- Navigation -->
  <nav id="navbar">
    <div class="nav-container">
      <div class="logo">
        <i class="fas fa-spa"></i>
        <span>Maison Bloom</span>
      </div>
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#products">Products</a></li>
        <li><a href="#testimonials">Testimonials</a></li>
        <li><a href="login.php" class="nav-btn">Login</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
<section class="hero" id="home">
  <div class="hero-slideshow">
    <div class="hero-slide active" style="background-image: url('images/skincare1.jpg');"></div>
    <div class="hero-slide" style="background-image: url('images/homebg1.jpeg');"></div>
    <div class="hero-slide" style="background-image: url('images/homebg4.jpg');"></div>
    <div class="hero-slide" style="background-image: url('images/homebg5.jpg');"></div>
  </div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <h1>Beautiful Skin Starts Here</h1>
    <p>Expert dermatology care from the comfort of your home. Book consultations, get prescriptions, and shop premium skincare products.</p>
    <div class="hero-btns">
      <a href="register.php" class="btn btn-primary">Get Started</a>
      <a href="#features" class="btn btn-secondary">Learn More</a>
    </div>
  </div>
</section>

  <!-- Features Section -->
  <section class="section" id="features">
    <div class="section-title">
      <h2>Why Choose Maison Bloom</h2>
      <p>We combine medical expertise with personalized care to give you the best skin health experience</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <h3>Easy Booking</h3>
        <p>Schedule appointments with top dermatologists in just a few clicks, available 24/7 to fit your busy life.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-user-md"></i>
        </div>
        <h3>Expert Dermatologists</h3>
        <p>Consult with board-certified specialists who provide personalized treatment plans for your skin concerns.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">
          <i class="fas fa-pills"></i>
        </div>
        <h3>Curated Products</h3>
        <p>Shop our collection of dermatologist-recommended skincare products with proven results.</p>
      </div>
    </div>
  </section>

  <!-- Products Section -->
  <section class="section products-section" id="products">
    <div class="products-container">
      <div class="section-title">
        <h2>Featured Products</h2>
        <p>Dermatologist-approved skincare solutions for all your needs</p>
      </div>
      <div class="product-list">
        <?php
        $query = "SELECT * FROM products ORDER BY created_at DESC LIMIT 4";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <div class="product-card">
            <div class="product-badge">New</div>
            <div class="product-img-container">
              <img src="uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-img">
            </div>
            <div class="product-info">
              <h4><?php echo $row['name']; ?></h4>
              <div class="product-price">₹<?php echo $row['price']; ?></div>
              <a href="login.php" class="product-btn">View Product</a>
            </div>
          </div>
        <?php
          endwhile;
        else:
          echo "<p>No products available.</p>";
        endif;
        ?>
      </div>
      <div class="section-btn">
        <a href="login.php" class="btn btn-primary">View All Products</a>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section class="testimonial-section" id="testimonials">
    <div class="testimonial-container">
      <div class="section-title">
        <h2>What Our Patients Say</h2>
        <p>Hear from people who have experienced our services</p>
      </div>
      <div class="swiper mySwiper">
        <div class="swiper-wrapper">
          <?php
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
              <p class="testimonial-text"><?php echo $row['message']; ?></p>
              <div class="patient-info">
                <img src="images/user.png" alt="avatar" class="patient-img">
                <div>
                  <div class="patient-name"><?php echo $row['name']; ?></div>
                  <div class="patient-role">Patient</div>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>

  <!-- Newsletter Section -->
  <section class="newsletter-section">
    <div class="newsletter-container">
      <h2>Stay Updated</h2>
      <p>Subscribe to our newsletter for skincare tips, special offers, and the latest products</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Your email address" class="newsletter-input" required>
        <button type="submit" class="newsletter-btn">Subscribe</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-col">
        <h3>Maison Bloom</h3>
        <p>Your trusted dermatology and skincare platform providing expert consultations and premium products.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h3>Quick Links</h3>
        <ul class="footer-links">
          <li><a href="#home">Home</a></li>
          <li><a href="#features">Services</a></li>
          <li><a href="#products">Products</a></li>
          <li><a href="#testimonials">Testimonials</a></li>
          <li><a href="about.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Contact Us</h3>
        <ul class="footer-links">
          <li><i class="fas fa-map-marker-alt"></i> 123 Skin Care St, Beauty City</li>
          <li><i class="fas fa-phone"></i> +1 234 567 890</li>
          <li><i class="fas fa-envelope"></i> info@maisonbloom.com</li>
        </ul>
      </div>
    </div>
    <div class="copyright">
      &copy; <?php echo date("Y"); ?> Maison Bloom. All rights reserved.
    </div>
  </footer>

  <!-- Back to Top Button -->
  <div class="back-to-top">
    <i class="fas fa-arrow-up"></i>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
  <script>
    // Hero Image Slideshow
const heroSlides = document.querySelectorAll('.hero-slide');
let currentSlide = 0;

function showNextSlide() {
  heroSlides[currentSlide].classList.remove('active');
  currentSlide = (currentSlide + 1) % heroSlides.length;
  heroSlides[currentSlide].classList.add('active');
}

// Change slide every 5 seconds
setInterval(showNextSlide, 5000);

// Initialize first slide
heroSlides[0].classList.add('active');
    // Initialize Swiper
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
        }
      }
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.getElementById('navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Back to top button
    const backToTopButton = document.querySelector('.back-to-top');
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.add('active');
      } else {
        backToTopButton.classList.remove('active');
      }
    });

    backToTopButton.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });
  </script>
</body>
</html>