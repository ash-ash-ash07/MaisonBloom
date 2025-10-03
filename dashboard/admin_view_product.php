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
      padding: 25px;
      box-shadow: var(--shadow);
      overflow-x: auto;
      border: 1px solid rgba(179, 157, 219, 0.1);
      position: relative;
    }

    .table-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--lavender-medium), var(--lavender-dark));
      border-radius: 16px 16px 0 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 0 auto;
    }

    th, td {
      padding: 16px;
      text-align: left;
      border-bottom: 1px solid rgba(179, 157, 219, 0.15);
    }

    th {
      background-color: var(--purple-dark);
      color: var(--white);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      position: sticky;
      top: 0;
    }

    tr {
      transition: all 0.3s ease;
    }

    tr:hover {
      background-color: rgba(179, 157, 219, 0.08);
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(155, 120, 255, 0.1);
    }

    .product-image {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid rgba(179, 157, 219, 0.2);
      transition: all 0.3s ease;
      background: var(--lavender-light);
    }

    .product-image:hover {
      transform: scale(1.08);
      border-color: var(--lavender-medium);
      box-shadow: 0 5px 15px rgba(106, 90, 205, 0.2);
    }

    .category-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .category-skincare {
      background: linear-gradient(135deg, rgba(106, 90, 205, 0.15), rgba(179, 157, 219, 0.1));
      color: var(--lavender-dark);
      border: 1px solid rgba(106, 90, 205, 0.2);
    }

    .category-haircare {
      background: linear-gradient(135deg, rgba(255, 152, 0, 0.15), rgba(255, 193, 7, 0.1));
      color: var(--warning);
      border: 1px solid rgba(255, 152, 0, 0.2);
    }

    .category-body {
      background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(129, 199, 132, 0.1));
      color: var(--success);
      border: 1px solid rgba(76, 175, 80, 0.2);
    }

    .btn {
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: none;
      cursor: pointer;
    }

    .edit-btn {
      background: linear-gradient(135deg, var(--lavender-medium), var(--lavender-dark));
      color: var(--white);
      box-shadow: 0 2px 8px rgba(106, 90, 205, 0.2);
    }

    .edit-btn:hover {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(106, 90, 205, 0.3);
    }

    .delete-btn {
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      color: var(--white);
      box-shadow: 0 2px 8px rgba(244, 67, 54, 0.2);
    }

    .delete-btn:hover {
      background: linear-gradient(135deg, #ee5a52, #d32f2f);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
    }

    .add-btn {
      background: linear-gradient(135deg, var(--lavender-dark), var(--purple-dark));
      color: var(--white);
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(106, 90, 205, 0.3);
    }

    .add-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(106, 90, 205, 0.4);
    }

    .stock-status {
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      display: inline-block;
    }

    .in-stock {
      background: rgba(76, 175, 80, 0.1);
      color: var(--success);
      border: 1px solid rgba(76, 175, 80, 0.2);
    }

    .low-stock {
      background: rgba(255, 152, 0, 0.1);
      color: var(--warning);
      border: 1px solid rgba(255, 152, 0, 0.2);
    }

    .out-of-stock {
      background: rgba(244, 67, 54, 0.1);
      color: var(--error);
      border: 1px solid rgba(244, 67, 54, 0.2);
    }

    .search-container {
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 25px;
      flex-wrap: wrap;
    }

    .search-box {
      flex: 1;
      min-width: 300px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 14px 20px 14px 45px;
      border: 2px solid rgba(179, 157, 219, 0.3);
      border-radius: 12px;
      font-size: 0.95rem;
      background-color: var(--white);
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(155, 120, 255, 0.1);
    }

    .search-box input:focus {
      border-color: var(--lavender-medium);
      box-shadow: 0 4px 16px rgba(179, 157, 219, 0.2);
      outline: none;
    }

    .search-box i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--lavender-medium);
      font-size: 1.1rem;
    }

    .action-buttons {
      display: flex;
      gap: 12px;
    }

    .no-products {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }

    .no-products i {
      font-size: 4rem;
      color: var(--lavender-medium);
      margin-bottom: 20px;
      opacity: 0.7;
    }

    .no-products h3 {
      color: var(--purple-dark);
      margin-bottom: 10px;
      font-size: 1.5rem;
    }

    .back-nav {
      position: absolute;
      top: 25px;
      left: 25px;
      z-index: 1000;
    }

    .back-btn {
      text-decoration: none;
      color: var(--purple-dark);
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 500;
      background: rgba(255, 255, 255, 0.95);
      padding: 10px 18px;
      border-radius: 30px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border: 1px solid rgba(179, 157, 219, 0.2);
    }

    .back-btn:hover {
      background: var(--white);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(155, 120, 255, 0.2);
      color: var(--purple-dark);
    }

    /* Animation for table rows */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    tbody tr {
      animation: fadeInUp 0.5s ease forwards;
    }

    tbody tr:nth-child(1) { animation-delay: 0.1s; }
    tbody tr:nth-child(2) { animation-delay: 0.2s; }
    tbody tr:nth-child(3) { animation-delay: 0.3s; }
    tbody tr:nth-child(4) { animation-delay: 0.4s; }
    tbody tr:nth-child(5) { animation-delay: 0.5s; }

    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }
      
      .search-container {
        gap: 15px;
      }
      
      .search-box {
        min-width: 100%;
      }
      
      th, td {
        padding: 12px 8px;
      }
      
      .product-image {
        width: 50px;
        height: 50px;
      }
      
      .action-buttons {
        flex-direction: column;
        gap: 8px;
      }
      
      .back-nav {
        position: relative;
        top: 0;
        left: 0;
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>

  <!-- Back Navigation -->
  <div class="back-nav">
    <a href="admin_dashboard.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="container">
    <h2><i class="fas fa-box-open"></i> Product Inventory</h2>
    
    <div class="search-container">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search products by name, category, or price...">
      </div>
      <a href="admin_add_product.php" class="btn add-btn">
        <i class="fas fa-plus-circle"></i> Add New Product
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
                <td><strong>#<?php echo $row['product_id']; ?></strong></td>
                <td>
                  <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                       alt="<?php echo htmlspecialchars($row['name']); ?>" 
                       class="product-image"
                       onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHZpZXdCb3g9IjAgMCA3MCA3MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjcwIiBoZWlnaHQ9IjcwIiByeD0iMTAiIGZpbGw9IiNGOUY3RkYiLz4KPHBhdGggZD0iTTM1IDQwQzM4LjMxMzcgNDAgNDEgMzcuMzEzNyA0MSAzNEM0MSAzMC42ODYzIDM4LjMxMzcgMjggMzUgMjhDMzEuNjg2MyAyOCAyOSAzMC42ODYzIDI5IDM0QzI5IDM3LjMxMzcgMzEuNjg2MyA0MCAzNSA0MFoiIGZpbGw9IiNCMzlEREIiLz4KPHBhdGggZD0iTTQ1IDQ3VjQ1QzQ1IDQxLjEzNDEgNDEuODY1OSAzOCAzOCAzOEgzMkMyOC4xMzQxIDM4IDI1IDQxLjEzNDEgMjUgNDVWNDciIGZpbGw9IiNCMzlEREIiLz4KPC9zdmc+'">
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                  <?php if(strlen($row['description']) > 0): ?>
                    <br><small style="color: #666; font-size: 0.85rem;"><?php echo substr(htmlspecialchars($row['description']), 0, 50); ?>...</small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="category-badge category-<?php echo $row['category']; ?>">
                    <i class="fas fa-tag"></i> <?php echo ucfirst($row['category']); ?>
                  </span>
                </td>
                <td><strong>₹<?php echo number_format($row['price'], 2); ?></strong></td>
                <td>
                  <span class="stock-status <?php echo $stock_class; ?>">
                    <?php echo $row['stock_quantity']; ?> units
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <a href="admin_edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn edit-btn">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?delete=<?php echo $row['product_id']; ?>" 
                       class="btn delete-btn"
                       onclick="return confirm('Are you sure you want to delete <?php echo addslashes($row['name']); ?>? This action cannot be undone.');">
                      <i class="fas fa-trash-alt"></i> Delete
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7">
                <div class="no-products">
                  <i class="fas fa-boxes"></i>
                  <h3>No Products Found</h3>
                  <p>Start by adding your first product to the inventory.</p>
                  <a href="admin_add_product.php" class="btn add-btn" style="margin-top: 20px;">
                    <i class="fas fa-plus-circle"></i> Add Your First Product
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Enhanced search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchValue = this.value.toLowerCase();
      const rows = document.querySelectorAll('#productsTable tbody tr');
      let hasResults = false;
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchValue)) {
          row.style.display = '';
          hasResults = true;
        } else {
          row.style.display = 'none';
        }
      });
      
      // Show no results message if needed
      const noProductsRow = document.querySelector('.no-products');
      if (noProductsRow) {
        noProductsRow.style.display = hasResults ? 'none' : '';
      }
    });

    // Add animation on scroll
    document.addEventListener('DOMContentLoaded', function() {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
          }
        });
      }, { threshold: 0.1 });

      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
        observer.observe(row);
      });
    });
  </script>

</body>
</html>