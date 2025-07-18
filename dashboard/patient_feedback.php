<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rating = intval($_POST['rating']);
  $message = $conn->real_escape_string($_POST['message']);
  $patient_id = $_SESSION['user_id'];

  $conn->query("INSERT INTO feedbacks (patient_id, rating, message) VALUES ($patient_id, $rating, '$message')");
  echo "<script>alert('Thank you for your feedback!'); window.location='patient_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Submit Feedback - Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --success: #4caf50;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--lavender-light);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-image: url('../images/homebg3.jpeg');
      background-size: cover;
      background-position: center;
      background-blend-mode: overlay;
      background-color: rgba(249, 247, 255, 0.9);
      animation: fadeIn 3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .feedback-container {
      width: 100%;
      max-width: 600px;
      padding: 40px;
      margin: 20px;
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: var(--shadow);
      position: relative;
      overflow: hidden;
      transform: translateY(0);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feedback-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(106, 90, 205, 0.3);
    }

    .feedback-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--lavender-dark));
    }

    .feedback-header {
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .feedback-header h2 {
      color: var(--purple-dark);
      font-size: 2rem;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .feedback-header p {
      color: var(--lavender-dark);
      font-size: 1rem;
    }

    .rating-container {
      margin-bottom: 25px;
      text-align: center;
    }

    .rating-container label {
      display: block;
      margin-bottom: 15px;
      font-weight: 600;
      color: var(--purple-dark);
    }

    .rating-stars {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .rating-stars input {
      display: none;
    }

    .rating-stars label {
      font-size: 2rem;
      color: #ddd;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .rating-stars label:hover,
    .rating-stars input:checked ~ label {
      color: #ffc107;
      transform: scale(1.1);
    }

    .rating-stars input:checked ~ label {
      text-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: var(--purple-dark);
    }

    .form-group textarea {
      width: 100%;
      padding: 15px;
      border: 2px solid #e0d6ff;
      border-radius: 10px;
      font-size: 1rem;
      color: var(--purple-dark);
      background: rgba(249, 247, 255, 0.7);
      transition: all 0.3s ease;
      min-height: 150px;
      resize: vertical;
    }

    .form-group textarea:focus {
      outline: none;
      border-color: var(--lavender-dark);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.3);
      background: var(--white);
    }

    .submit-btn {
      width: 100%;
      background-color: var(--lavender-dark);
      color: white;
      padding: 15px;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .submit-btn:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    /* Floating animation for decorative elements */
    .floating {
      position: absolute;
      opacity: 0.1;
      z-index: -1;
      font-size: 24px;
    }

    .floating-1 {
      top: 20%;
      left: 10%;
      animation: float 8s ease-in-out infinite;
    }

    .floating-2 {
      bottom: 15%;
      right: 10%;
      animation: float 10s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(5deg); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .feedback-container {
        padding: 30px 20px;
      }
      
      .feedback-header h2 {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      body {
        background-image: url('images/homebg2.jpeg');
      }
      
      .rating-stars label {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="floating floating-1">🌸</div>
  <div class="floating floating-2">🌿</div>

  <div class="feedback-container">
    <div class="feedback-header">
      <h2><i class="fas fa-comment-medical"></i> Share Your Feedback</h2>
      <p>Your opinion helps us improve our services</p>
    </div>

    <form method="POST">
      <div class="rating-container">
        <label>How would you rate your experience?</label>
        <div class="rating-stars">
          <input type="radio" id="star5" name="rating" value="5" required>
          <label for="star5">★</label>
          <input type="radio" id="star4" name="rating" value="4">
          <label for="star4">★</label>
          <input type="radio" id="star3" name="rating" value="3">
          <label for="star3">★</label>
          <input type="radio" id="star2" name="rating" value="2">
          <label for="star2">★</label>
          <input type="radio" id="star1" name="rating" value="1">
          <label for="star1">★</label>
        </div>
      </div>

      <div class="form-group">
        <label for="message">Your Feedback:</label>
        <textarea id="message" name="message" placeholder="Please share your experience with us..." required></textarea>
      </div>

      <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> Submit Feedback
      </button>
    </form>
  </div>

  <script>
    // Add animation to stars on hover
    const stars = document.querySelectorAll('.rating-stars label');
    stars.forEach(star => {
      star.addEventListener('mouseover', () => {
        const index = Array.from(stars).indexOf(star);
        stars.forEach((s, i) => {
          if (i <= index) {
            s.style.transform = 'scale(1.1)';
            s.style.color = '#ffc107';
          }
        });
      });

      star.addEventListener('mouseout', () => {
        const checkedStar = document.querySelector('.rating-stars input:checked');
        if (checkedStar) {
          const checkedIndex = Array.from(document.querySelectorAll('.rating-stars input')).indexOf(checkedStar);
          stars.forEach((s, i) => {
            s.style.color = i <= checkedIndex ? '#ffc107' : '#ddd';
            s.style.transform = i <= checkedIndex ? 'scale(1.1)' : 'scale(1)';
          });
        } else {
          stars.forEach(s => {
            s.style.color = '#ddd';
            s.style.transform = 'scale(1)';
          });
        }
      });
    });

    // Add animation when a star is clicked
    document.querySelectorAll('.rating-stars input').forEach(input => {
      input.addEventListener('change', () => {
        const index = Array.from(document.querySelectorAll('.rating-stars input')).indexOf(input);
        stars.forEach((s, i) => {
          s.style.color = i <= index ? '#ffc107' : '#ddd';
          s.style.transform = i <= index ? 'scale(1.1)' : 'scale(1)';
          if (i <= index) {
            s.style.animation = 'pulse 0.5s ease';
          }
        });
      });
    });
  </script>
</body>
</html>