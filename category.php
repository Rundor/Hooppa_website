<?php
include 'db.php';
$category = $_GET['category'];

$stmt = $conn->prepare("SELECT * FROM product WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Products in Category: " . htmlspecialchars($category) . "</h2>";

while($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];

    $img_sql = "SELECT file_name FROM product_images WHERE product_id = ? LIMIT 1";
    $img_stmt = $conn->prepare($img_sql);
    $img_stmt->bind_param("i", $product_id);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();
    $img = $img_result->fetch_assoc();
    $img_path = $img ? 'uploads/' . $img['file_name'] : 'default.jpg';

    echo "<div>";
    echo "<img src='$img_path' width='150'><br>";
    echo "<strong>" . htmlspecialchars($row['product_name']) . "</strong><br>";
    echo "Price: $" . $row['price'] . "<br>";
    echo "</div><hr>";
}
?>
