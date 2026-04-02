<?php
session_start();
require_once 'cart_logic.php';
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kids_toys_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
  $product_id = intval($_POST['product_id']);
  $quantity = intval($_POST['quantity']);

  $sql = "SELECT * FROM product WHERE product_id = $product_id";
  $result = $conn->query($sql);
  $product = $result->fetch_assoc();

  if ($product) {
    $item = [
      'id' => $product['product_id'],
      'name' => $product['product_name'],
      'price' => $product['price'],
      'quantity' => $quantity
    ];

    $found = false;
    foreach ($_SESSION['cart'] as &$cartItem) {
      if ($cartItem['id'] == $item['id']) {
        $cartItem['quantity'] += $item['quantity'];
        $found = true;
        break;
      }
    }
    unset($cartItem);

    if (!$found) {
      $_SESSION['cart'][] = $item;
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
  $removeIndex = $_POST['remove_index'];
  if (isset($_SESSION['cart'][$removeIndex])) {
    unset($_SESSION['cart'][$removeIndex]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
}

$sql = 
"SELECT 
p.product_id, 
p.product_name, 
p.description, 
p.price, 
p.stock_quantity, 
p.category, 
p.age_range, 
p.admin_id, 
p.created_at, 
p.updated_at, 
CONCAT('assets/images/', pi.file_name) AS image_url
FROM product p
LEFT JOIN (
SELECT product_id, file_name 
FROM product_images 
GROUP BY product_id
) pi ON p.product_id = pi.product_id
WHERE p.category = 'Kid’s Electronics'";

$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dolls - Hooppa</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css">
  <style>
    #side-cart {
      position: fixed;
      top: 0;
      right: 0;
      width: 300px;
      height: 100%;
      background: #fff;
      box-shadow: -2px 0 5px rgba(0,0,0,0.3);
      z-index: 9999;
      padding: 1em;
      display: none;
    }
  </style>
</head>
<body>
      <!-- Header -->
    <?php include 'header.php'; ?>
  
  <main>
    <div class="container" style="margin-bottom: 25ex;">
      <h1>Electronics</h1>
    <div class="products-row">
     <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $productNameSlug = strtolower(str_replace(' ', '', $row['product_name']));
             

              // Define custom link overrides
              if ($row['product_name'] == "Kid's Digital Camera") {
                  $productLink = "product-kid'sdigitalCamera.php";
              } elseif ($row['product_name'] == 'Electronic Piano') {
                  $productLink = 'product-kid’selectronicpiano.php';
              } else {
                $productLink = 'product-' . $productNameSlug . '.php';

              }
              
              echo '<div class="product-card">';
              echo '<a href="' . $productLink . '" class="product-link">';
              //
              if ($row['image_url']) {
                echo "<td class='editable file-uploadT'><img src='" . $row['image_url'] . "' alt='" . $row['product_name'] . "'class='small-image'  /></td>";
            } else {
                echo "<td>No image available</td>";
            }
             //
              echo '</a>';
              echo '<h3><a href="' . $productLink . '" class="product-link">' . $row['product_name'] . '</a></h3>';
              echo '<p><a href="' . $productLink . '" class="product-link"><span class="icon-saudi_riyal"></span> ' . $row['price'] . '</a></p>';
              echo '<form method="POST" action="">
                        <input type="hidden" name="product_id" value="' . $row['product_id'] . '">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                      </form>';
              echo '</div>';
          }
      } else {
          echo '<p>No products available in this category.</p>';
      }
      $conn->close();
      ?>
      </div>
    </div>
  </main>
  
  <!-- Side Cart -->
 <div id="side-cart" style="position: fixed; top: 0; right: 0; width: 300px; height: 100%; background: white; box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5); padding: 20px; display: none; z-index: 1000; overflow-y: auto;">
  <div class="side-cart-header">
    <h3>Shopping Cart</h3>
    <form method="post" style="display:inline; float:right;">
      <button id="close-cart" type="button" style="background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
    </form>
  </div>
 <div id="cart-items">
    <?php
    $total = 0;
    if (!empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $index => $item) {
        $lineTotal = $item['price'] * $item['quantity'];
        $total += $lineTotal;
        echo "<div class='cart-item'>";
        echo "<span>{$item['name']} x{$item['quantity']} - " . number_format($lineTotal, 2) . " SAR</span>";
        echo "<form method='post' style='display:inline; margin-left:10px;'>";
        echo "<input type='hidden' name='remove_index' value='{$index}'>";
        echo "<button type='submit' name='remove_item' style='border:none; background:none; color:red; font-size:18px; cursor:pointer;'>×</button>";
        echo "</form>";
        echo "</div>";
      }
    } else {
      echo "<p>Your cart is empty.</p>";
    }
    ?>
  </div>
  <div class="side-cart-footer">
    <p>Total: <span id="cart-total"><?php echo number_format($total, 2); ?></span> SAR</p>
    <button class="btn checkout-btn" onclick="window.location.href='checkout.php'">Checkout</button>
  </div>
</div>
  
  <footer>
    <div class="footer-container">
      <img src="assets/images/HOOPPA_label" alt="Hooppa logo" />
      <p class="copyright">&copy; 2025 HOOPPA. All rights reserved.</p>
    </div>
  </footer>
  <script>
document.addEventListener('DOMContentLoaded', function () {
  const cartIcon = document.getElementById('cart-icon');
  const sideCart = document.getElementById('side-cart');
  const closeCartBtn = document.getElementById('close-cart');

  if (cartIcon && sideCart && closeCartBtn) {
    cartIcon.addEventListener('click', () => {
      sideCart.style.display = 'block';
    });

    closeCartBtn.addEventListener('click', () => {
      sideCart.style.display = 'none';
    });
  }
});
  </script>
</body>
</html>