<?php
header('Content-Type: application/json');

// Database connection
$user = 'root';
$pass = '';
$db = 'kids_toys_store';
$db = new mysqli('localhost', $user, $pass, $db) or die("Unable to connect");

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch product data
$product_query = "SELECT p.*, pi.file_name 
                 FROM product p 
                 LEFT JOIN product_images pi ON p.product_id = pi.product_id 
                 WHERE p.product_id = $product_id 
                 LIMIT 1";
$product_result = $db->query($product_query);

if ($product_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit();
}

$product = $product_result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'product' => $product
]);

$db->close();
?>