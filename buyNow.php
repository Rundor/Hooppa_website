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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $sql = "SELECT * FROM product WHERE product_id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    if ($product) {
        $_SESSION['direct_purchase'] = [
            'id' => $product['product_id'],
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
        header("Location: checkout.php?type=direct");
        exit();
    } else {
        echo "Product not found.";
    }
}
$conn->close();
?>
