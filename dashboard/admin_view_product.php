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
    header("Location: admin_view_product.php");
    exit;
}

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Products - Admin | Maison Bloom</title>
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
      --warning: #ff9800;
      --shadow: 0 4px 20px rgba(155, 120, 255, 0.15);
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--lavender-light);
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    h2 {
      color: var(--purple-dark);
      text-align: center;
      margin-bottom: 30px;
      font-size: 2rem;
      position: relative;
    }

    h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--purple-dark));
      border-radius: 3px;
    }

    .table-container {
      background: var(--white);
      border-radius: 16px;
      padding: 20px;
      box-shadow: var(--shadow);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0 auto;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid rgba(179, 157, 219, 0.2);
    }

    th {
      background-color: var(--purple-dark);
      color: var(--white);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 0.5px;
    }

    tr:hover {
      background-color: rgba(179, 157, 219, 0.05);
    }

    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid rgba(179, 157, 219, 0.3);
      transition: all 0.3s ease;
    }

    .product-image:hover {
      transform: scale(1.1);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.2);
    }

    .category-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .category-skincare {
      background-color: rgba(106, 90, 205, 0.1);
      color: var(--lavender-dark);
    }

    .category-haircare {
      background-color: rgba(255, 152, 0, 0.1);
      color: var(--warning);
    }

    .category-body {
      background-color: rgba(76, 175, 80, 0.1);
      color: var(--success);
    }

    .btn {
      padding: 8px 15px;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .edit-btn {
      background-color: rgba(106, 90, 205, 0.1);
      color: var(--lavender-dark);
    }

    .edit-btn:hover {
      background-color: rgba(106, 90, 205, 0.2);
    }

    .delete-btn {
      background-color: rgba(217, 83, 79, 0.1);
      color: var(--error);
    }

    .delete-btn:hover {
      background-color: rgba(217, 83, 79, 0.2);
    }

    .stock-status {
      font-weight: 600;
    }

    .in-stock {
      color: var(--success);
    }

    .low-stock {
      color: var(--warning);
    }

    .out-of-stock {
      color: var(--error);
    }

    .search-container {
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-box {
      flex: 1;
      min-width: 250px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 12px 15px 12px 40px;
      border: 1px solid rgba(179, 157, 219, 0.3);
      border-radius: 8px;
      font-size: 0.95rem;
      background-color: var(--white);
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      border-color: var(--lavender-medium);
      box-shadow: 0 0 0 3px rgba(179, 157, 219, 0.2);
      outline: none;
    }

    .search-box i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--lavender-medium);
    }

    .no-products {
      text-align: center;
      padding: 40px;
      color: #666;
    }

    .no-products i {
      font-size: 3rem;
      color: var(--lavender-medium);
      margin-bottom: 15px;
    }

    @media (max-width: 768px) {
      th, td {
        padding: 12px 8px;
      }
      
      .product-image {
        width: 60px;
        height: 60px;
      }
      
      .btn {
        padding: 6px 10px;
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h2><i class="fas fa-box-open"></i> Product Inventory</h2>
    
    <div class="search-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search products...">
      </div>
      <a href="admin_add_product.php" class="btn edit-btn">
        <i class="fas fa-plus"></i> Add New Product
      </a>
    </div>
    
    <div class="table-container">
      <table id="productsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
              $stock_class = '';
              if ($row['stock_quantity'] == 0) {
                $stock_class = 'out-of-stock';
              } elseif ($row['stock_quantity'] < 10) {
                $stock_class = 'low-stock';
              } else {
                $stock_class = 'in-stock';
              }
            ?>
              <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td>
                  <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                       alt="<?php echo htmlspecialchars($row['name']); ?>" 
                       class="product-image">
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                  <span class="category-badge category-<?php echo $row['category']; ?>">
                    <?php echo ucfirst($row['category']); ?>
                  </span>
                </td>
                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                <td class="stock-status <?php echo $stock_class; ?>">
                  <?php echo $row['stock_quantity']; ?>
                </td>
                <td>
                  <a href="admin_edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn edit-btn">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="?delete=<?php echo $row['product_id']; ?>" 
                     class="btn delete-btn"
                     onclick="return confirm('Are you sure you want to delete this product?');">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7">
                <div class="no-products">
                  <i class="fas fa-boxes"></i>
                  <h3>No products found</h3>
                  <p>There are currently no products in the inventory.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchValue = this.value.toLowerCase();
      const rows = document.querySelectorAll('#productsTable tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
      });
    });
  </script>

</body>
</html>