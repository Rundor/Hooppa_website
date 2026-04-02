<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index'];
    $newQty = max(1, intval($_POST['quantity'])); // Ensure minimum 1

    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] = $newQty;
        $updatedItem = $_SESSION['cart'][$index];
        $lineTotal = $updatedItem['price'] * $updatedItem['quantity'];
        echo number_format($lineTotal, 2); // Return new line total
        exit;
    }
}
http_response_code(400);
echo "Invalid request";
