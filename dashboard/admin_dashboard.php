<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - Maison Bloom</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f7ff;
    }

    nav {
      background-color: #fff;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #6a5acd;
      font-weight: bold;
    }

    .container {
      padding: 40px;
      text-align: center;
    }

    h2 {
      color: #4b0082;
    }

    .card-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-top: 30px;
    }

    .card {
      width: 250px;
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: scale(1.02);
    }

    .card h3 {
      color: #6a5acd;
      margin-bottom: 10px;
    }

    .btn {
      display: inline-block;
      background-color: #b39ddb;
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      margin-top: 10px;
    }

    .btn:hover {
      background-color: #9575cd;
    }
  </style>
</head>
<body>

  <!-- Navigation Bar -->
  <nav>
    <div><strong>Maison Bloom Admin</strong></div>
    <div>
      <a href="../home.php">Home</a>
      <a href="admin_add_product.php">Add Product</a>
      <a href="admin_view_product.php">View Products</a>
      <a href="../logout.php">Logout</a>
    </div>
  </nav>

  <!-- Dashboard Cards -->
  <div class="container">
    <h2>Welcome, Admin!</h2>

    <div class="card-container">

      <div class="card">
        <h3>Add New Product</h3>
        <p>Add skincare, haircare or body products.</p>
        <a href="admin_add_product.php" class="btn">Add Product</a>
      </div>

      <div class="card">
        <h3>View Products</h3>
        <p>Manage and update existing product catalog.</p>
        <a href="admin_view_product.php" class="btn">View All</a>
      </div>

      <div class="card">
        <h3>Manage Users</h3>
        <p>View or update doctors and patients.</p>
        <a href="admin_manage_users.php" class="btn" style="margin: 20px; display: inline-block;">Manage
</a>

      </div>

      <div class="card">
        <h3>Consultation Reports</h3>
        <p>Access appointments and prescription history.</p>
        <a href="#" class="btn">View Reports</a>
      </div>

    </div>
  </div>

</body>
</html>
