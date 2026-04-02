<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "kids_toys_store");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $checkoutType = $_POST['checkout_type'] ?? 'cart';

    // Get the ordered items
    if ($checkoutType === 'direct' && isset($_SESSION['direct_purchase'])) {
        $items = [$_SESSION['direct_purchase']];
    } elseif ($checkoutType === 'cart' && isset($_SESSION['cart'])) {
        $items = $_SESSION['cart'];
    } else {
        echo "<script>alert('No items in cart.'); window.location.href='index.php';</script>";
        exit;
    }

    // ✅ Update stock in the database
    foreach ($items as $item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];

        $updateStockSQL = "UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?";
        $stmt = $conn->prepare($updateStockSQL);
        $stmt->bind_param("iii", $quantity, $productId, $quantity);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ Save purchase to cookies (past purchases history)
    $currentOrder = [
        'items' => $items,
        'timestamp' => time()
    ];

    $history = [];
    if (isset($_COOKIE['past_purchases'])) {
        $existing = json_decode($_COOKIE['past_purchases'], true);
        if (is_array($existing)) {
            $history = $existing;
        }
    }

    $history[] = $currentOrder;
    $history = array_slice($history, -10);
    setcookie('past_purchases', json_encode($history), time() + (30 * 24 * 60 * 60), "/");

    // ✅ Clear cart session
    unset($_SESSION['cart']);
    unset($_SESSION['direct_purchase']);

    // ✅ Redirect
    echo "<script>
        alert('🎉 Thank you! Your order has been placed.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

header("Location: index.php");
exit;
?>
