<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle product delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE product_id = $product_id");
    header("Location: admin_view_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Products - Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f5ff;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      color: #4b0082;
      margin-bottom: 30px;
    }

    table {
      width: 95%;
      margin: auto;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ddd;
    }

    th {
      background-color: #b39ddb;
      color: white;
    }

    img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 5px;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
    }

    .edit-btn {
      background-color: #6a5acd;
    }

    .delete-btn {
      background-color: #d9534f;
    }
    body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #f8f5ff;
}

table {
  background: #fff;
  border-collapse: collapse;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

  </style>
</head>
<body>

  <h2>All Products</h2>

  <table>
    <tr>
      <th>ID</th>
      <th>Image</th>
      <th>Name</th>
      <th>Category</th>
      <th>Price</th>
      <th>Stock</th>
      <th>Actions</th>
    </tr>

    <?php
    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()) {
        echo "
        <tr>
          <td>{$row['product_id']}</td>
          <td><img src='../uploads/{$row['image']}'></td>
          <td>{$row['name']}</td>
          <td>{$row['category']}</td>
          <td>₹{$row['price']}</td>
          <td>{$row['stock_quantity']}</td>
          <td>
            <a href='admin_edit_product.php?id={$row['product_id']}' class='btn edit-btn'>Edit</a>
            <a href='?delete={$row['product_id']}' class='btn delete-btn' onclick=\"return confirm('Are you sure you want to delete this product?');\">Delete</a>
          </td>
        </tr>";
    }
    ?>
  </table>

</body>
</html>
