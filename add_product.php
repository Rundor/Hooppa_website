<?php
// Database connection
$user = 'root';
$pass = '';
$db = 'kids_toys_store';
$db = new mysqli('localhost', $user, $pass, $db) or die("Unable to connect");

// Only process if it's a POST request
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // Validate required fields
    $required = ['product_name', 'description', 'category', 'age_range', 
                'stock_quantity', 'price'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: Missing required field '$field'");
        }
    }

    // Process form data
    $product_name = $db->real_escape_string($_POST['product_name'] ?? '');
    $description = $db->real_escape_string($_POST['description'] ?? '');
    $category = $db->real_escape_string($_POST['category'] ?? '');
    $age_range = $db->real_escape_string($_POST['age_range'] ?? '');
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $admin_id = 1; // Default admin ID

    // Handle file upload
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        die("Error: No file uploaded or upload error occurred");
    }

    $target_dir = "assets/images/";
    $imageFileType = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Validate image
    $check = getimagesize($_FILES["product_image"]["tmp_name"]);
    if($check === false) {
        die("Error: Uploaded file is not an image");
    }

    // Check file size (5MB max)
    if ($_FILES["product_image"]["size"] > 5000000) {
        die("Error: Image file is too large (max 5MB)");
    }

    // Allow certain file formats
    if(!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        die("Error: Only JPG, JPEG, PNG & GIF files are allowed");
    }

    // Try to upload file
    if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
        die("Error: There was an error uploading your file");
    }

    // Insert into database
    $query = "INSERT INTO product (product_name, description, price, stock_quantity, category, age_range, admin_id) 
              VALUES ('$product_name', '$description', $price, $stock_quantity, '$category', '$age_range', $admin_id)";
    
    if ($db->query($query)) {
        $product_id = $db->insert_id;
        
        // Insert image info
        $image_query = "INSERT INTO product_images (product_id, file_name) VALUES ($product_id, '$filename')";
        $db->query($image_query);
        
        // Redirect with success
        header("Location: AdminMain.php?section=products&success=1");
        exit();
    } else {
        die("Database error: " . $db->error);
    }

    $db->close();
} else {
    // Not a POST request - redirect or show error
    header("Location: AdminMain.php?section=addProduct&error=invalid_request");
    exit();
}
?>