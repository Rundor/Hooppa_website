<?php include 'db.php'; ?>

<h1>Welcome to the Toy Store</h1>

<!-- Category list -->
<h3>Product Categories</h3>
<ul>
<?php
$cat_sql = "SELECT DISTINCT category FROM product";
$cat_result = mysqli_query($conn, $cat_sql);
while($row = mysqli_fetch_assoc($cat_result)) {
    echo "<li><a href='category.php?category=" . urlencode($row['category']) . "'>" . htmlspecialchars($row['category']) . "</a></li>";
}
?>
</ul>

<!-- Product list -->
<h2>All Products</h2>
<?php
$product_sql = "SELECT * FROM product";
$product_result = mysqli_query($conn, $product_sql);

while($row = mysqli_fetch_assoc($product_result)) {
    $product_id = $row['product_id'];

    // Get product image
    $image_sql = "SELECT file_name FROM product_images WHERE product_id = $product_id LIMIT 1";
    $image_result = mysqli_query($conn, $image_sql);
    $image_row = mysqli_fetch_assoc($image_result);
    $image_path = $image_row ? 'uploads/' . $image_row['file_name'] : 'default.jpg';

    echo "<div>";
    echo "<img src='$image_path' width='150'><br>";
    echo "<strong>" . htmlspecialchars($row['product_name']) . "</strong><br>";
    echo "Category: " . htmlspecialchars($row['category']) . "<br>";
    echo "Price: $" . $row['price'] . "<br>";
    echo "</div><hr>";
}
?>
