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

$sql = "SELECT p.product_id, p.product_name, p.price, p.stock_quantity, pi.file_name 
        FROM product p
        LEFT JOIN (
          SELECT product_id, MIN(file_name) as file_name 
          FROM product_images 
          GROUP BY product_id
        ) pi ON p.product_id = pi.product_id
        WHERE p.category = 'Dolls'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOOPPA | Categories</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">
      
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    <!-- Riyal Symbol -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css">
</head>
<body class="body">
    
  <!-- Header -->
 <?php include 'header.php'; ?>

    
    <main>
        <div class="container">
            <p><br></p>

            <h1>🧩 Categories</h1>
            <div class="categories">
                <div class="row">
                    <div class="col3"><a href="dolls.php"><img src="assets/images/doll.png" alt="Dolls"><h6>Dolls</h6><p class="category-age">Ages 4-8</p></a></div>
                    <div class="col3"><a href="legoSet.php"><img src="assets/images/Lego.png" alt="Lego"><h6>Lego Set</h6><p class="category-age">Ages 6-12</p></a></div>
                    <div class="col3"><a href="remoteControlToys.php"><img src="assets/images/remotecontrol.png" alt="Remote Control Toys"><h6>Remote Control Toys</h6><p class="category-age">Ages 3-5</p></a></div>
                    <div class="col3"><a href="electronics.php"><img src="assets/images/kidselectronic.png" alt="Kids Electronics"><h6>Kids Electronics</h6><p class="category-age">Ages 6-12</p></a></div>
                    <div class="col3"><a href="sportsEquipment.php"><img src="assets/images/sports.png" alt="Sport Equipment"><h6>Sport Equipment</h6><p class="category-age">Ages 6+</p></a></div>
                    <div class="col3"><a href="brainTeaser.php"><img src="assets/images/brainteaser.png" alt="Brain Teaser"><h6>Brain Teaser</h6><p class="category-age">Ages 3-5</p></a></div>
                    <div class="col3"><a href="trainSets.php"><img src="assets/images/train.png" alt="Train Sets"><h6>Train Sets</h6><p class="category-age">Ages 4-6</p></a></div>
                    <div class="col3"><a href="plushToys.php"><img src="assets/images/plush-toy.png" alt="Plush Toys"><h6>Plush Toys</h6><p class="category-age">Ages 2-5</p></a></div>
                    <div class="col3"><a href="outdoorToys.php"><img src="assets/images/outdoors.png" alt="Outdoor Toys"><h6>Outdoor Toys</h6><p class="category-age">Ages 5+</p></a></div>
                </div>
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
    
    <!-- FOOTER -->
    <footer>
        <div class="footer-container">
            <img src="assets/images/HOOPPA_label.png" alt="Hooppa logo">
            <p class="copyright">&copy; 2025 HOOPPA. All rights reserved.</p>
        </div>
    </footer>
    
    
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
</body>
</html>
