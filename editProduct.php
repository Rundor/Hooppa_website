<?php
header('Content-Type: application/json');

// Database connection
$user = 'root';
$pass = '';
$db = 'kids_toys_store';
$db = new mysqli('localhost', $user, $pass, $db);

if ($db->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Check if product ID is provided
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit();
}

$product_id = (int)$_POST['product_id'];

try {
    // Get form data with proper validation
    $product_name = trim($db->real_escape_string($_POST['product_name'] ?? ''));
    $description = trim($db->real_escape_string($_POST['description'] ?? ''));
    $category = trim($db->real_escape_string($_POST['category'] ?? ''));
    $age_range = trim($db->real_escape_string($_POST['age_range'] ?? ''));
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    // Validate required fields
    if (empty($product_name)) {
        throw new Exception('Product name is required');
    }
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0');
    }

    // Update product in database
    $update_query = "UPDATE product SET 
    product_name = ?, 
    description = ?, 
    price = ?, 
    stock_quantity = ?, 
    category = ?, 
    age_range = ?, 
    updated_at = NOW()
    WHERE product_id = ?";

// description = $description,
   $stmt = $db->prepare($update_query);
if (!$stmt) {
    throw new Exception('Prepare failed: ' . $db->error);
}

$stmt->bind_param("ssdissi", 
    $product_name,      // s
    $description,       // s
    $price,             // d
    $stock_quantity,    // i
    $category,          // s
    $age_range,         // s
    $product_id         // i
);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $image_response = '';
    // Handle file upload if a new image was provided
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "assets/images/";
        $imageFileType = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $filename;
        
        // Validate image
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if($check === false) {
            throw new Exception('File is not an image');
        }
        
        // Check file size (5MB max)
        if ($_FILES["product_image"]["size"] > 5000000) {
            throw new Exception('File is too large (max 5MB)');
        }
        
        // Allow certain file formats
        if(!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed');
        }
        
        // Try to upload file
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            // Update image in database
            $image_query = "UPDATE product_images SET file_name = ? WHERE product_id = ?";
            $img_stmt = $db->prepare($image_query);
            $img_stmt->bind_param("si", $filename, $product_id);
            $img_stmt->execute();
            $img_stmt->close();
            
            $image_response = $filename;
        } else {
            throw new Exception('Error uploading file');
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Product updated successfully',
        'product' => [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'description' => $description,
            'price' => $price,
            'stock_quantity' => $stock_quantity,
            'category' => $category,
            'age_range' => $age_range,
            'file_name' => $image_response ?: null
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $db->close();
}
?>