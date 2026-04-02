<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

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
?>
