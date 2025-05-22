<?php
session_start(); // Start the session
include '../includes/config.php'; // Database Connection

// Role check: Ensure user is logged in and is a 'seller'
if (!isset($_SESSION['authenticated']) || $_SESSION['role'] !== 'seller') {
    // Optionally, set a message for the user if needed
    // $_SESSION['message'] = "Access Denied. You must be logged in as a seller to perform this action.";
    header('Location: ../signin.php'); 
    exit();
}

// --- SECURITY NOTE ---
// The entire file uses direct variable injection into SQL queries.
// This is a major security vulnerability (SQL Injection).
// In a real-world application, PREPARED STATEMENTS (mysqli or PDO) MUST be used.
// For this exercise, I am maintaining consistency with the existing codebase's practices.
// --- END SECURITY NOTE ---

if (isset($_POST['addItem'])) {
    // Retrieve form data
    // Retrieve form data and sanitize
    $productName = mysqli_real_escape_string($conn, $_POST["pname"]);
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    $quantity = mysqli_real_escape_string($conn, $_POST["qty"]);
    $productCode = mysqli_real_escape_string($conn, $_POST["pCode"]);
    $brand = mysqli_real_escape_string($conn, $_POST["brand"]);
    $category = mysqli_real_escape_string($conn, $_POST["category"]);

    $img = $_FILES['img']['name']; // Original filename, handle with care
    $imgTmp = $_FILES['img']['tmp_name'];

    // Set the target directory for uploading the brand logo
    $targetDirectory = "../assets/images/uploads/";

    // Generate a unique filename for the brand logo
    $productImgName = uniqid() . '_' . $img;

    // Set the target path for the brand logo
    $targetFilePath = $targetDirectory . $productImgName;

    // Move the uploaded brand logo to the target directory
    if (move_uploaded_file($imgTmp, $targetFilePath)) {

        $sql = "INSERT INTO product (product_name, product_price, product_image, product_code, qty, brand, category,created_at) 
                VALUES ('$productName', '$price', '$productImgName', '$productCode', '$quantity', '$brand', '$category', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "
            <script>
                alert('Product Added successfully.');
                window.location.href = 'products.php'; 
            </script>
        ";
        } else {
            echo "
            <script>
                alert('Error Adding Product');
                window.location.href = 'products.php'; 
            </script>
        ";
        }
    }
} elseif (isset($_POST['editItem'])) {
    // Retrieve the form data
    // Retrieve the form data and sanitize
    $productID = mysqli_real_escape_string($conn, $_POST["productID"]);
    $productName = mysqli_real_escape_string($conn, $_POST["pname"]);
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    $quantity = mysqli_real_escape_string($conn, $_POST["qty"]);
    $productCode = mysqli_real_escape_string($conn, $_POST["pCode"]);
    $brand = mysqli_real_escape_string($conn, $_POST["brand"]);
    $category = mysqli_real_escape_string($conn, $_POST["category"]);

    $img = $_FILES['img']['name']; // Original filename, handle with care
    $imgTmp = $_FILES['img']['tmp_name'];

    // Set the target directory for uploading the brand logo
    $targetDirectory = "../assets/images/uploads/";

    // Generate a unique filename for the brand logo
    $productImgName = uniqid() . '_' . $img;

    // Set the target path for the brand logo
    $targetFilePath = $targetDirectory . $productImgName;

    // Check if a new product image was uploaded
    if ($img != '') {
        if (move_uploaded_file($imgTmp, $targetFilePath)) {

            $sql = "UPDATE `product` 
            SET `product_name`='$productName',
                `product_price`='$price',
                `product_code`='$productCode',
                `product_image`='$productImgName',
                `qty`='$quantity',
                `brand`='$brand',
                `category`='$category',
                `updated_at`=NOW() 
            WHERE `product_id`='$productID'";
     
        }
    } else {
        // Update the product data in the database without changing the image
        $sql = "UPDATE `product` 
        SET `product_name`='$productName',
            `product_price`='$price',
            `product_code`='$productCode',
            `qty`='$quantity',
            `brand`='$brand',
            `category`='$category',
            `updated_at`=NOW() 
        WHERE `product_id`='$productID'";
    }

    if ($conn->query($sql) === TRUE) {
        echo "
            <script>
                alert('Product updated successfully.');
                window.location.href = 'products.php'; 
            </script>
        ";
    } else {
        echo "
            <script>
                alert('Error updating Product');
                window.location.href = 'products.php'; 
            </script>
        ";
    }

} elseif (isset($_GET['deleteProduct']) && isset($_GET['product_id'])) { // Changed to check for deleteProduct=true
    $productid = mysqli_real_escape_string($conn, $_GET['product_id']); // Sanitize product_id

    // --- Additional Security: Verify this product belongs to the logged-in seller if such a link exists ---
    // For example: $seller_id = $_SESSION['user_id']; (if user_id is the seller's ID)
    // $checkSql = "SELECT * FROM product WHERE product_id = '$productid' AND seller_id_column = '$seller_id'";
    // ... execute $checkSql and if num_rows == 0, then deny deletion.
    // This step is skipped as the DB schema for product-seller linkage is not defined in the current context.
    // --- End Additional Security Note ---

    // Perform the delete operation
    $sql = "DELETE FROM product WHERE product_id = '$productid'"; // Use quotes for string comparison
    if ($conn->query($sql) === TRUE) {
        echo "
            <script>
                alert('Product deleted successfully!');
                window.location.href = 'products.php'; 
            </script>
        ";
    } else {
        echo "
        <script>
            alert('Error deleting Product');
            window.location.href = 'products.php'; 
        </script>
    ";
    }
} else {
    // If no valid action is specified, redirect to products page
    echo "
        <script>
            alert('Invalid operation.');
            window.location.href = 'products.php'; 
        </script>
    ";
}

mysqli_close($conn); // Close the database connection at the end
?>