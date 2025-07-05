<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    .slideshow {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      z-index: -1;
    }

    .slideshow img {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      animation: fadeSlide 18s infinite;
    }

    .slideshow img:nth-child(1) { animation-delay: 0s; }
    .slideshow img:nth-child(2) { animation-delay: 6s; }
    .slideshow img:nth-child(3) { animation-delay: 12s; }

    @keyframes fadeSlide {
      0%, 100% { opacity: 0; }
      10%, 30% { opacity: 1; }
      40% { opacity: 0; }
    }

    .overlay {
      background-color: rgba(255, 255, 255, 0.85);
      min-height: 100vh;
      padding: 60px 30px;
      text-align: center;
    }

    h1 {
      font-size: 3em;
      color: #6a5acd;
    }

    .content {
      max-width: 800px;
      margin: 30px auto;
      color: #333;
      font-size: 1.1em;
      line-height: 1.8;
    }
  </style>
</head>
<body>

  <div class="slideshow">
    <img src="images/about1.jpg">
    <img src="images/about2.jpg">
    <img src="images/about3.jpg">
  </div>

  <div class="overlay">
    <h1>About Maison Bloom</h1>
    <div class="content">
      <p><strong>Maison Bloom</strong> is a trusted platform connecting patients to certified dermatologists with a click. We bring together virtual consultations and curated skincare products under one roof.</p>

      <p>We believe skincare should be accessible, expert-driven, and personalized. With our secure telemedicine tools, you can book appointments, upload your skin concerns, and receive treatment from anywhere.</p>

      <p>Our product shop is carefully crafted with dermatology-approved skincare, haircare, and body care products. Whether you're managing acne, eczema, or looking for nourishing skin maintenance — we're here to help you bloom.</p>
    </div>
  </div>

</body>
</html>
