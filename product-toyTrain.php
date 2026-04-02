<?php
session_start();
require_once 'cart_logic.php';
// DATABASE CONNECTION
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kids_toys_store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get product details from database
$product_id = 7; 

// Handle Add to Cart POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    // Fetch product info from DB
    $sql = "SELECT * FROM product WHERE product_id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

if ($product) {
    // Check if requested quantity is available
    if ($quantity > $product['stock_quantity']) {
        $_SESSION['error'] = "Requested quantity exceeds available stock.";
    } else {
        $item = [
            'id' => $product['product_id'],
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];

        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $item['id']) {
                $newQuantity = $cartItem['quantity'] + $item['quantity'];
                if ($newQuantity > $product['stock_quantity']) {
                    $_SESSION['error'] = "Adding more than available stock.";
                    $found = true; // Skip adding
                } else {
                    $cartItem['quantity'] = $newQuantity;
                    $found = true;
                }
                break;
            }
        }
        unset($cartItem);

        if (!$found) {
            $_SESSION['cart'][] = $item;
        }
    }
}}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $removeIndex = $_POST['remove_index'];
    if (isset($_SESSION['cart'][$removeIndex])) {
        unset($_SESSION['cart'][$removeIndex]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
    }
}



// Fetch product info
$sql = "SELECT * FROM product WHERE product_id = $product_id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

// Fetch specifications for this product
$specifications = [];
$spec_sql = "SELECT specifications FROM product WHERE product_id = $product_id";
$spec_result = $conn->query($spec_sql);
if ($spec_result && $spec_result->num_rows > 0) {
  while($row = $spec_result->fetch_assoc()) {
      $lines = explode("\n", $row['specifications']);
      foreach ($lines as $line) {
          $trimmed = trim($line);
          if (!empty($trimmed)) {
              $specifications[] = $trimmed;
          }
      }
  }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo $product['product_name']; ?> - Hoopa</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Teachers:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@emran-alhaddad/saudi-riyal-font/index.css"/>
  <style>
  #side-cart {
    position: fixed;
    top: 0;
    right: 0;
    width: 300px;
    height: 100%;
    background: white;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
    padding: 20px;
    display: none; /* initially hidden */
    z-index: 1000; /* so it appears above everything else */
    overflow-y: auto;
  }

  .close-cart {
    background: none;
    border: none;
    font-size: 30px;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 10px;
  }

  .cart-item {
    margin-bottom: 10px;
  }
</style>
</head>
<body>
    <!-- Header -->
    <?php include 'header.php'; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
  <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

  <main class="product-page">
    <section id="dreamhouse" class="product-section active">
        <div class="product-overview">
            <div class="product-images-container">
                <div class="product-images">
                <img src="assets/images/product-train.png" alt="train 1" class="active" />
                <img src="assets/images/product-train2.png" alt="train 2" class="active" />
                </div>
                <div class="image-buttons">
                    <button id="prev-button"><i class="fas fa-chevron-left"></i></button>
                    <button id="next-button"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>

            <aside class="product-info">
                <h1 class="product-title">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                    <div class="star-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <i class="far fa-star"></i>
                    </div>
                </h1>
                <p class="short-description">
    <?php echo htmlspecialchars($product['description']); ?>
</p>

                
<div id="spec-popup" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 15px; width: 250px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1001;">
    <h4>Specifications</h4>
    <ul style="margin: 0; padding-left: 20px;">
        <?php
        if (!empty($specifications)) {
            foreach ($specifications as $spec) {
                echo "<li>" . htmlspecialchars(trim($spec)) . "</li>";
            }
        } else {
            echo "<li>No specifications available.</li>";
        }
        ?>
    </ul>
</div>

                <p class="product-price icon-saudi_riyal">SAR <?php echo number_format($product['price'], 2); ?></p>
                <p class="product-stock">In Stock: <?php echo $product['stock_quantity']; ?> units</p>
                <p class="product-category">Category: <?php echo htmlspecialchars($product['category']); ?></p>
                <p class="product-age">Age Group: <?php echo htmlspecialchars($product['age_range']); ?></p>

<!-- Shared Quantity Input (outside forms) -->
<label for="quantity-train">Quantity:</label>
<input type="number" id="quantity-train" name="quantity" min="1" max="<?php echo $product['stock_quantity']; ?>" value="1" />

<!-- Add to Cart Form -->
<form method="POST" action="">
  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
  <input type="hidden" id="cart-quantity" name="quantity" value="1">
  <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
</form>

<!-- Buy Now Form -->
<form method="POST" action="buyNow.php">
  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
  <input type="hidden" id="buy-now-quantity" name="quantity" value="1">
  <button type="submit" class="btn buy-now">Buy Now</button>
</form>



            </aside>
        </div>

        <section class="product-details">
        <h2>Description 
  <i id="desc-help-icon" class="fa fa-info-circle" style="cursor: pointer; margin-left: 8px;"></i>
</h2>

        <p>
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            


        </section>
    </section>

    <section class="product-comments">
        <h2>Customer Reviews</h2>
        

        <div class="comments-display">
            <p><strong>Aisha:</strong> My daughter loves this Barbie set! Great quality and very colorful.</p>
            <p><strong>Omar:</strong> Perfect gift for birthdays. The elevator feature is a nice touch!</p>
        </div>
    </section>
</main>


  <!-- Side Cart -->
  <div id="side-cart">
    <div class="side-cart-header">
      <h3>Shopping Cart</h3>
      <button id="close-cart" class="close-cart">&times;</button>
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
      <span id="cart-total"><?php echo number_format($total, 2); ?></span>
      <button class="btn checkout-btn" onclick="window.location.href='checkout.php'">Checkout</button>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <img src="assets/images/HOOPPA_label" alt="Hooppa logo" />
      <p class="copyright">&copy; 2025 HOOPPA. All rights reserved.</p>
    </div>
  </footer>

  <!-- Java Script -->
  <script>
document.addEventListener('DOMContentLoaded', function() {
  const cartIcon = document.getElementById('cart-icon');
  const closeCart = document.getElementById('close-cart');
  const sideCart = document.getElementById('side-cart');
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  const cartItems = document.getElementById('cart-items');
  const cartTotal = document.getElementById('cart-total');
  const helpIcon = document.getElementById('desc-help-icon');
  const specPopup = document.getElementById('spec-popup');
  const quantityInput = document.getElementById('quantity-train');
  const cartQuantity = document.getElementById('cart-quantity');
  const buyNowQuantity = document.getElementById('buy-now-quantity');

  // Show side cart
  if (cartIcon) {
    cartIcon.addEventListener('click', () => {
      sideCart.style.display = 'block';
    });
  }

  // Close side cart
  if (closeCart) {
    closeCart.addEventListener('click', () => {
      sideCart.style.display = 'none';
    });
  }

  // Handle visual cart update
  addToCartButtons.forEach(button => {
    button.addEventListener('click', () => {
      const productName = button.getAttribute('data-product');
      const productPrice = parseFloat(button.getAttribute('data-price'));
      const quantityToAdd = parseInt(quantityInput.value);

      // Check if item exists
      let existingItem = Array.from(cartItems.children).find(item =>
        item.getAttribute('data-product') === productName
      );

      if (existingItem) {
        let currentQuantity = parseInt(existingItem.getAttribute('data-quantity'));
        let newQuantity = currentQuantity + quantityToAdd;
        existingItem.setAttribute('data-quantity', newQuantity);
        existingItem.querySelector('.item-text').textContent = `${productName} x${newQuantity} - ${(productPrice * newQuantity).toFixed(2)} SAR`;
      } else {
        const cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.setAttribute('data-product', productName);
        cartItem.setAttribute('data-quantity', quantityToAdd);

        const itemText = document.createElement('span');
        itemText.classList.add('item-text');
        itemText.textContent = `${productName} x${quantityToAdd} - ${(productPrice * quantityToAdd).toFixed(2)} SAR`;

        const removeBtn = document.createElement('button');
        removeBtn.textContent = '×';
        removeBtn.classList.add('remove-btn');
        removeBtn.style.marginLeft = '10px';
        removeBtn.style.color = 'red';
        removeBtn.style.border = 'none';
        removeBtn.style.background = 'none';
        removeBtn.style.fontSize = '18px';
        removeBtn.style.cursor = 'pointer';

        removeBtn.addEventListener('click', () => {
          const qty = parseInt(cartItem.getAttribute('data-quantity'));
          let total = parseFloat(cartTotal.textContent);
          total -= qty * productPrice;
          cartTotal.textContent = total.toFixed(2);
          cartItem.remove();
        });

        cartItem.appendChild(itemText);
        cartItem.appendChild(removeBtn);
        cartItems.appendChild(cartItem);
      }

      // Update total
      let total = parseFloat(cartTotal.textContent);
      total += productPrice * quantityToAdd;
      cartTotal.textContent = total.toFixed(2);
    });
  });

  // Buy Now alert
  window.buyNow = function(productName, price) {
    alert(`Buying: ${productName} for ${price} SAR`);
  };

  // Quantity sync
  if (quantityInput && cartQuantity && buyNowQuantity) {
    quantityInput.addEventListener('input', function () {
      cartQuantity.value = this.value;
      buyNowQuantity.value = this.value;
    });
  }

  // Show/Hide specification popup
  if (helpIcon && specPopup) {
    helpIcon.addEventListener('click', function () {
      specPopup.style.display = specPopup.style.display === 'none' ? 'block' : 'none';

      const rect = helpIcon.getBoundingClientRect();
      specPopup.style.top = (rect.bottom + window.scrollY + 5) + 'px';
      specPopup.style.left = (rect.left + window.scrollX) + 'px';
    });

    document.addEventListener('click', function (event) {
      if (!specPopup.contains(event.target) && !helpIcon.contains(event.target)) {
        specPopup.style.display = 'none';
      }
    });
  }

  // Image switching
  const images = document.querySelectorAll(".product-images img");
  let currentImageIndex = 0;
  const totalImages = images.length;

  function showImage(index) {
    images.forEach((img, i) => {
      img.classList.remove("active");
      if (i === index) {
        img.classList.add("active");
      }
    });
  }

  function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % totalImages;
    showImage(currentImageIndex);
  }

  function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
    showImage(currentImageIndex);
  }

  showImage(currentImageIndex);

  const nextBtn = document.getElementById("next-button");
  const prevBtn = document.getElementById("prev-button");

  if (nextBtn) nextBtn.addEventListener("click", nextImage);
  if (prevBtn) prevBtn.addEventListener("click", prevImage);
});
</script>



</body>
</html>
