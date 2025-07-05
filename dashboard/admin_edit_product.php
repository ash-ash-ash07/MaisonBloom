<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $category = $_POST['category'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];

  if (!empty($_FILES['image']['name'])) {
    $image = basename($_FILES['image']['name']);
    $target = "../uploads/" . $image;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $conn->query("UPDATE products SET name='$name', category='$category', description='$description', price='$price', stock_quantity='$stock', image='$image' WHERE product_id=$id");
  } else {
    $conn->query("UPDATE products SET name='$name', category='$category', description='$description', price='$price', stock_quantity='$stock' WHERE product_id=$id");
  }

 header("Location: admin_edit_product.php?id=$id&success=updated");
exit;

}


$res = $conn->query("SELECT * FROM products WHERE product_id = $id");
$product = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
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

    .preview-img {
      text-align: center;
      margin-top: -10px;
    }

    .preview-img img {
      width: 100px;
      border-radius: 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
    
  <div class="form-container">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" value="<?php echo $product['name']; ?>" required>

      <select name="category" required>
        <option value="skincare" <?php if($product['category']=="skincare") echo "selected"; ?>>Skincare</option>
        <option value="haircare" <?php if($product['category']=="haircare") echo "selected"; ?>>Haircare</option>
        <option value="body" <?php if($product['category']=="body") echo "selected"; ?>>Body Products</option>
      </select>

      <textarea name="description" required><?php echo $product['description']; ?></textarea>

      <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
      <input type="number" name="stock" value="<?php echo $product['stock_quantity']; ?>" required>

      <div class="preview-img">
        <p>Current Image:</p>
        <img src="../uploads/<?php echo $product['image']; ?>" alt="Current Image">
      </div>

      <input type="file" name="image" accept="image/*"><br><br>
      <button type="submit">Update Product</button>
    </form>
  </div>

</body>
</html>
