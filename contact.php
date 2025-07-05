<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body, html {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      background: url('images/helpline.jpg') no-repeat center center/cover;
    }

    .overlay {
      background-color: rgba(255, 255, 255, 0.9);
      min-height: 100vh;
      padding: 60px 20px;
      text-align: center;
    }

    h1 {
      color: #6a5acd;
      font-size: 3em;
      margin-bottom: 30px;
    }

    .contact-info {
      max-width: 700px;
      margin: auto;
      text-align: left;
      font-size: 1.1em;
    }

    .info-block {
      display: flex;
      align-items: center;
      margin: 20px 0;
    }

    .info-block i {
      font-size: 24px;
      color: #4b0082;
      width: 40px;
    }

    .info-block span {
      color: #333;
      margin-left: 10px;
    }
  </style>
</head>
<body>

  <div class="overlay">
    <h1>Contact Us</h1>

    <div class="contact-info">
      <div class="info-block">
        <i class="fas fa-envelope"></i>
        <span>support@maisonbloom.com</span>
      </div>

      <div class="info-block">
        <i class="fas fa-phone"></i>
        <span>+91 98765 43210</span>
      </div>

      <div class="info-block">
        <i class="fas fa-location-dot"></i>
        <span>Maison Bloom HQ, Bengaluru, India</span>
      </div>

      <div class="info-block">
        <i class="fas fa-clock"></i>
        <span>Mon – Sat: 9am – 7pm</span>
      </div>
    </div>
  </div>

</body>
</html>
