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

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
  $removeIndex = $_POST['remove_index'];
  if (isset($_SESSION['cart'][$removeIndex])) {
    unset($_SESSION['cart'][$removeIndex]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
  }
}
// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $index = $_POST['item_index'];
    $newQty = max(1, intval($_POST['new_quantity'])); // Prevent quantity less than 1
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] = $newQty;
    }
}

// Handle clear cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_cart'])) {
  unset($_SESSION['cart']);
  header("Location: checkout.php");
  exit();
}

// Handle direct "Buy Now" product
$isDirect = isset($_GET['type']) && $_GET['type'] === 'direct';
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout | HOOPPA</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css" />
  <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
  <style>
    h1 { text-align: center; margin-bottom: 30px; }
    .checkout-summary { border-bottom: 1px solid #ccc; padding-bottom: 20px; margin-bottom: 20px; }
    .checkout-summary ul { list-style: none; padding: 0; }
    .checkout-summary li { padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
    .total { font-weight: bold; text-align: right; font-size: 1.2em; margin-top: 10px; }
    .checkout-form { display: flex; flex-direction: column; gap: 15px; }
    .checkout-form input, .checkout-form textarea { padding: 10px; font-size: 1em; border: 1px solid #ccc; border-radius: 5px; }
    .checkout-form button { background-color: #222; color: #fff; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-size: 1em; transition: background-color 0.3s ease; }
    .checkout-form button:hover { background-color: #444; }
    .clear-btn { background-color: #999; color: white; margin-top: 10px; border: none; border-radius: 10ex; padding: 1ex 2ex; cursor: pointer; }
    .clear-btn:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-1px); }
    .clear-btn:active { transform: translateY(0); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .delete-btn { background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; }
  </style>
</head>
<body>
      <!-- Header -->
    <?php include 'header.php'; ?>

  <main>
    <div class="container">
      <h1>Checkout</h1>

      <div class="checkout-summary">
        <h2>Your Order</h2>
        <ul>
          <?php
          if ($isDirect && isset($_SESSION['direct_purchase'])) {
              $item = $_SESSION['direct_purchase'];
              $lineTotal = $item['price'] * $item['quantity'];
              $total += $lineTotal;
              echo "<li><span>{$item['name']} x{$item['quantity']}</span><span>" . number_format($lineTotal, 2) . " SAR</span></li>";
          } elseif (!empty($_SESSION['cart'])) {
              foreach ($_SESSION['cart'] as $index => $item) {
                  $lineTotal = $item['price'] * $item['quantity'];
                  $total += $lineTotal;
echo "<li>";
echo "<form method='post' style='display:flex; align-items:center; gap:10px;'>";
echo "<input type='hidden' name='item_index' value='{$index}'>";
echo "<span>{$item['name']} x</span>";
echo "<input type='number' value='{$item['quantity']}' min='1' style='width:60px;' 
      onchange=\"updateQuantity({$index}, this.value, this.parentElement.nextElementSibling)\">";
echo "<button type='submit' name='remove_item' class='delete-btn'>Delete</button>";
echo "</form>";
echo "<span>" . number_format($lineTotal, 2) . " SAR</span>";
echo "</li>";


              }
          } else {
              echo "<li>Your cart is empty.</li>";
          }
          ?>
        </ul>
        <p class="total">Total: <span id="grand-total"><?php echo number_format($total, 2); ?></span> SAR</p>
        <?php if (!$isDirect && !empty($_SESSION['cart'])): ?>
          <form method="post">
            <button class="clear-btn" type="submit" name="clear_cart">Delete All</button>
          </form>
        <?php endif; ?>
      </div>

<form class="checkout-form" method="post" action="buy.php">
  <h2>Customer Details</h2>
  <input type="text" name="fullname" placeholder="Full Name" required />
  <input type="email" name="email" placeholder="Email Address" required />
  <input type="text" name="address" placeholder="Shipping Address" required />
  <textarea name="notes" placeholder="Additional Notes (optional)" rows="3"></textarea>
  <input type="hidden" name="checkout_type" value="<?php echo $isDirect ? 'direct' : 'cart'; ?>" />
  <button type="submit" class="buy-button">Place Order</button>
</form>

    </div>
  </main>

  <footer>
    <div class="footer-container">
      <img src="assets/images/HOOPPA_label" alt="Hooppa logo" />
      <p class="copyright">&copy; 2025 HOOPPA. All rights reserved.</p>
    </div>
  </footer>
  <script>
function updateQuantity(index, quantity, totalElement) {
  fetch('update_quantity.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `index=${index}&quantity=${quantity}`
  })
  .then(response => response.text())
  .then(data => {
    if (totalElement) {
      totalElement.textContent = `${data} SAR`;
    }

    // Now recalculate the grand total
    recalculateGrandTotal();
  })
  .catch(err => {
    console.error('Quantity update failed:', err);
  });
}

function recalculateGrandTotal() {
  let itemSpans = document.querySelectorAll('li > span:last-child');
  let total = 0;
  itemSpans.forEach(span => {
    let match = span.textContent.match(/([\d.]+)/);
    if (match) {
      total += parseFloat(match[1]);
    }
  });
  document.getElementById('grand-total').textContent = total.toFixed(2);
}

</script>

</body>
</html>
