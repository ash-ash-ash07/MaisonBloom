<?php
session_start();
include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  $image_name = basename($_FILES['image']['name']);
  $target_path = "../uploads/" . $image_name;
  move_uploaded_file($_FILES['image']['tmp_name'], $target_path);

  $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $image_name);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Product</title>
  <style>
    body {
      background: #f3efff;
      font-family: 'Segoe UI', sans-serif;
      padding: 40px;
    }

    .form-container {
      max-width: 600px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #4b0082;
      margin-bottom: 25px;
    }

    input, select, textarea {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      margin-bottom: 20px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 15px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #b39ddb;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
    }

    button:hover {
      background-color: #9575cd;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2>Add New Product</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Product Name" required>
      
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="skincare">Skincare</option>
        <option value="haircare">Haircare</option>
        <option value="body">Body Products</option>
      </select>

      <textarea name="description" placeholder="Product Description" required></textarea>

      <input type="number" name="price" placeholder="Price (₹)" required>
      <input type="number" name="stock" placeholder="Stock Quantity" required>

      <input type="file" name="image" accept="image/*" required>

      <button type="submit">Add Product</button>
    </form>
  </div>

</body>
</html>
