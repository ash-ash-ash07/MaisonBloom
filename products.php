<?php
include "db.php";
session_start();

$res = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
  <title>All Products - Maison Bloom</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .product-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 30px;
    }
    .card {
      width: 240px;
      margin: 15px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      background: #fff;
    }
    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    .card .info {
      padding: 15px;
      text-align: center;
    }
    .btn {
      background-color: #b39ddb;
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      text-decoration: none;
      margin-top: 10px;
      display: inline-block;
    }
  </style>
</head>
<body>

  <h2 style="text-align:center; margin-top:20px;">All Products</h2>
  <div class="product-list">
    <?php
    while ($row = $res->fetch_assoc()) {
      echo "
        <div class='card'>
          <img src='uploads/{$row['image']}' alt='{$row['name']}'>
          <div class='info'>
            <h4>{$row['name']}</h4>
            <p>Category: {$row['category']}</p>
            <p>₹{$row['price']}</p>
          </div>
        </div>
      ";
    }
    ?>
  </div>

</body>
</html>
