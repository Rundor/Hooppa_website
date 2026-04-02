<?php
session_start();

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
    $inCartQty = 0;
    foreach ($_SESSION['cart'] as $item) {
      if ($item['id'] == $product_id) {
        $inCartQty = $item['quantity'];
        break;
      }
    }

    if (($inCartQty + $quantity) > $product['stock_quantity']) {
      echo "<script>alert('Not enough stock. Only {$product['stock_quantity']} units available.'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
      exit();
    }

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

$sql = "SELECT p.product_id, p.product_name, p.price, pi.file_name 
        FROM product p
        LEFT JOIN (
          SELECT product_id, MIN(file_name) as file_name 
          FROM product_images 
          GROUP BY product_id
        ) pi ON p.product_id = pi.product_id
        WHERE p.category IN ('Plush Toys', 'Train Sets')";
$result = $conn->query($sql);


// Remove item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
  $removeIndex = $_POST['remove_index'];
  if (isset($_SESSION['cart'][$removeIndex])) {
    unset($_SESSION['cart'][$removeIndex]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOOPPA | Home Page</title>
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    
    <!-- Riyal Symbol -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css">

    <style>
        /* Adjust product layout and image styles */
        
        @media (max-width: 768px) {
            .col4 img {
                width: 120px;
            }   
        }
        
        @font-face {
           font-family: 'FontsFree-Net-Marykate-Regular';
           src: url('assets/fonts/FontsFree-Net-Marykate-Regular.woff2') format('woff2'),
           url('assets/fonts/FontsFree-Net-Marykate-Regular.woff') format('woff');
           font-weight: Regular;
        }
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

<body class="body">
    
    <!-- Header -->
    <?php include 'header.php'; ?>
    
    <main>
        
        <!-- Explore section -->
        <div class="container-homepage">

            <div style=" padding-top: 60px; text-align: center;">
                <p style="color: white; font-size: medium; white-space: nowrap;"> Give Your Child</p> <p><span style="font-family: 'FontsFree-Net-Marykate-Regular',sans-serif; font-size: xxx-large; color: white;">A <span style="color: #f0cd4b;">HOOPPA</span> JOY!</span></p>
            </div>
            <div style="flex: 1; padding-top: 8px;  text-align: center;">
               <a href="#Products" class="explore-btn">Explore</a>
            </div> 
            <div>
                <img src="assets/images/redbg.png" alt="pictue" width="1200px" style="position: absolute; left: 34ex; top: 20ex; object-fit: cover;">
                <img src="assets/images/pictureShape.png" alt="picture" width="770px" style="position: absolute; left: 4ex; top: 30ex; object-fit: cover;"> 
            </div>
         </div>
        
        <!-- ads -->
        <div id="ads" class="adsContainer">
            <div class="ad-slider">
                <div class="ad-track">
                    <img src="assets/images/ad1.png" alt="Ad 1" />
                    <img src="assets/images/ad2.png" alt="Ad 2" />
                    <img src="assets/images/ad3.png" alt="Ad 3" />
  </div>
            </div>
        </div>
        
        <!-- Products Section -->
        <div id="Products" class="productsContainer">
            <h2>New Arrivals</h2> 

            <div class="products-row">
              <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productNameSlug = strtolower(str_replace(' ', '', $row['product_name']));

        // Define custom link overrides
        if ($row['product_name'] == 'Toy Train Set') {
            $productLink = 'product-toyTrain.php';
        } elseif ($row['product_name'] == 'Stuffed Animal Elephant') {
            $productLink = 'product-stuffedElephant.php';
        } else {
            $productLink = 'product-' . $productNameSlug . '.php';
        }

        echo '<div class="product-card">';
        echo '<a href="' . $productLink . '" class="product-link">';
        if (!empty($row['file_name'])) {
            echo '<img src="assets/images/' . $row['file_name'] . '" alt="' . htmlspecialchars($row['product_name']) . '" class="small-image" />';
        } else {
            echo '<img src="assets/images/default-placeholder.png" alt="No image available" class="small-image" />';
        }
        echo '</a>';
        echo '<h3><a href="' . $productLink . '" class="product-link">' . htmlspecialchars($row['product_name']) . '</a></h3>';
        echo '<p><a href="' . $productLink . '" class="product-link"><span class="icon-saudi_riyal"></span> ' . number_format($row['price'], 2) . '</a></p>';
        echo '<button class="add-to-cart" data-product="' . htmlspecialchars($row['product_name']) . '" data-price="' . $row['price'] . '">Add to Cart</button>';
        echo '</div>';
    }
} else {
    echo '<p>No products available in this category.</p>';
}
$conn->close();
?>

              
            </div>
          
<a href="All%20products.php" class="view-all-btn">View All →
</a>
        </div>
        
    </main>
   <?php
if (isset($_COOKIE['past_purchases'])) {
    $orders = json_decode($_COOKIE['past_purchases'], true);

    if (!empty($orders)) {
        echo "<section class='purchase-history'>";
        echo "<h2>Your Past Purchases</h2>";

        foreach (array_reverse($orders) as $orderIndex => $order) {
            $timestamp = isset($order['timestamp']) ? date("Y-m-d H:i", $order['timestamp']) : "Unknown Time";
            $items = $order['items'];
            $orderTotal = 0;

            echo "<details>";
            echo "<summary>Order #" . ($orderIndex + 1) . " – $timestamp</summary>";
            echo "<ul>";

            foreach ($items as $item) {
                $name = htmlspecialchars($item['name']);
                $qty = intval($item['quantity']);
                $price = floatval($item['price']);
                $lineTotal = $price * $qty;
                $orderTotal += $lineTotal;
                echo "<li>$name x$qty – " . number_format($lineTotal, 2) . " SAR</li>";
            }

            echo "</ul>";
            echo "<p><strong>Order Total: " . number_format($orderTotal, 2) . " SAR</strong></p>";
            echo "</details>";
        }

        echo "</section>";
    }
}
?>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <img src="assets/images/HOOPPA_label" alt="Hooppa logo">
            <p class="copyright">&copy; 2025 HOOPPA. All rights reserved.</p>
        </div>
    </footer>
    
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
    
    

<!-- cart script-->
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

    <!-- Header Section -->
    <script>
    fetch('header.html?timestamp=' + new Date().getTime()) // Cache busting query parameter
  .then(response => response.text())
  .then(html => {
    document.getElementById('header').innerHTML =html;
  })
  .catch(error => {
    console.error('There was a problem loading the header:', error);
    document.getElementById('header').textContent = 'Error loading header';
  });
  </script>
    
    <script>
  const track = document.querySelector('.ad-track');
  const ads = document.querySelectorAll('.ad-track img');
  let index = 0;

  setInterval(() => {
    index = (index + 1) % ads.length;
    track.style.transform = `translateX(-${index * 100}%)`;
  }, 3000); // changes ad every 3 seconds
</script>
    

        
</body> 
</html>
