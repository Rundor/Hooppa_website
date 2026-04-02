<?php
header('Content-Type: application/json');

// Database connection
$user = 'root';
$pass = '';
$dbname = 'kids_toys_store';

try {
    $db = new mysqli('localhost', $user, $pass, $dbname);
    
    if ($db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate product ID
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        throw new Exception('Invalid product ID');
    }

    $product_id = (int)$_POST['product_id'];

    // Start transaction
    $db->begin_transaction();

    try {
        // First delete the image references
        $image_query = "SELECT file_name FROM product_images WHERE product_id = ?";
        $stmt = $db->prepare($image_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Delete physical image files
        while ($row = $result->fetch_assoc()) {
            if ($row['file_name'] && file_exists("assets/images/" . $row['file_name'])) {
                unlink("assets/images/" . $row['file_name']);
            }
        }
        $stmt->close();

        // Delete from product_images table
        $delete_images = "DELETE FROM product_images WHERE product_id = ?";
        $stmt = $db->prepare($delete_images);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        // Delete from product table
        $delete_product = "DELETE FROM product WHERE product_id = ?";
        $stmt = $db->prepare($delete_product);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('No product found with that ID');
        }
        
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($db)) $db->close();
}
?>