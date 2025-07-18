<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    try {
        $image_name = basename($_FILES['image']['name']);
        $target_path = "../uploads/" . $image_name;
        
        // Validate image file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $detected_type = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($detected_type, $allowed_types)) {
            throw new Exception("Only JPG, PNG & GIF files are allowed.");
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $image_name);
            
            if ($stmt->execute()) {
                $success_message = "Product added successfully!";
            } else {
                throw new Exception("Error adding product to database.");
            }
        } else {
            throw new Exception("Error uploading image.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Product - Admin | Maison Bloom</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --lavender-light: #f9f7ff;
      --lavender-medium: #b39ddb;
      --lavender-dark: #6a5acd;
      --purple-dark: #4b0082;
      --white: #ffffff;
      --success: #4caf50;
      --error: #f44336;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-container {
      width: 100%;
      max-width: 700px;
      background: var(--white);
      padding: 40px;
      border-radius: 16px;
      box-shadow: var(--shadow);
      margin: 30px auto;
      position: relative;
      overflow: hidden;
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--purple-dark));
    }

    h2 {
      text-align: center;
      color: var(--purple-dark);
      margin-bottom: 30px;
      font-size: 2rem;
    }

    .form-group {
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--purple-dark);
    }

    input, select, textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease;
      background-color: rgba(179, 157, 219, 0.05);
    }

    input:focus, select:focus, textarea:focus {
      border-color: var(--lavender-medium);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.2);
      outline: none;
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    .file-input-container {
      position: relative;
      margin-bottom: 25px;
    }

    .file-input-label {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      border: 2px dashed var(--lavender-medium);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      background-color: rgba(179, 157, 219, 0.05);
    }

    .file-input-label:hover {
      border-color: var(--purple-dark);
      background-color: rgba(179, 157, 219, 0.1);
    }

    .file-input-label i {
      font-size: 2rem;
      color: var(--lavender-medium);
      margin-bottom: 10px;
    }

    .file-input {
      display: none;
    }

    .file-name {
      margin-top: 10px;
      font-size: 14px;
      color: #666;
      display: none;
    }

    button {
      width: 100%;
      padding: 15px;
      background-color: var(--lavender-medium);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    button:hover {
      background-color: var(--purple-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.3);
    }

    .message {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      text-align: center;
      font-weight: 500;
      display: none;
    }

    .success {
      background-color: rgba(76, 175, 80, 0.1);
      color: var(--success);
      border: 1px solid rgba(76, 175, 80, 0.3);
      display: block;
    }

    .error {
      background-color: rgba(244, 67, 54, 0.1);
      color: var(--error);
      border: 1px solid rgba(244, 67, 54, 0.3);
      display: block;
    }

    @media (max-width: 768px) {
      .form-container {
        padding: 30px 20px;
        margin: 20px;
      }
      
      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
    
    <?php if ($success_message): ?>
      <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
      <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" placeholder="Enter product name" required>
      </div>
      
      <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="">Select Category</option>
          <option value="skincare">Skincare</option>
          <option value="haircare">Haircare</option>
          <option value="body">Body Products</option>
        </select>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Enter product description" required></textarea>
      </div>

      <div class="form-group">
        <label for="price">Price (₹)</label>
        <input type="number" id="price" name="price" min="0" step="0.01" placeholder="Enter price" required>
      </div>

      <div class="form-group">
        <label for="stock">Stock Quantity</label>
        <input type="number" id="stock" name="stock" min="0" placeholder="Enter stock quantity" required>
      </div>

      <div class="form-group">
        <label>Product Image</label>
        <div class="file-input-container">
          <label for="image" class="file-input-label">
            <div>
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Click to upload product image</p>
              <span id="file-name" class="file-name"></span>
            </div>
          </label>
          <input type="file" id="image" name="image" class="file-input" accept="image/*" required>
        </div>
      </div>

      <button type="submit"><i class="fas fa-save"></i> Add Product</button>
    </form>
  </div>

  <script>
    // Show selected file name
    document.getElementById('image').addEventListener('change', function(e) {
      const fileName = e.target.files[0]?.name || 'No file selected';
      const fileNameElement = document.getElementById('file-name');
      fileNameElement.textContent = fileName;
      fileNameElement.style.display = 'block';
    });

    // Hide success message after 5 seconds
    setTimeout(() => {
      const successMsg = document.querySelector('.message.success');
      if (successMsg) successMsg.style.display = 'none';
    }, 5000);
  </script>

</body>
</html>