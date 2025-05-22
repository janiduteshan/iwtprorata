<?php
session_start();
include 'config.php'; // Database Connection

// --- Helper function for redirecting with messages ---
function redirect_with_message($url, $message, $type = 'success') {
    header("Location: " . $url . "?message=" . urlencode($message) . "&type=" . $type);
    exit;
}

// --- Access Control & Action Validation ---
if (!isset($_SESSION['userID'])) {
    redirect_with_message("../signin.php", "You must be logged in to perform this action.", "error");
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    redirect_with_message("../home.php", "Access denied. You are not authorized for this action.", "error");
}
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    redirect_with_message("../seller_dashboard.php", "No action specified.", "error");
}

$action = $_POST['action'] ?? $_GET['action'];
$seller_id = $_SESSION['userID'];

// Define upload directory - ensure this directory exists and is writable
define('UPLOAD_DIR', '../assets/images/property_uploads/');
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}


// --- Handle ADD PROPERTY action ---
if ($action == 'add_property' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize inputs
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $location_text = trim(filter_input(INPUT_POST, 'location_text', FILTER_SANITIZE_STRING));
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $size_value = filter_input(INPUT_POST, 'size_value', FILTER_VALIDATE_FLOAT);
    $size_unit = trim(filter_input(INPUT_POST, 'size_unit', FILTER_SANITIZE_STRING));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);

    // Basic Validation
    if (empty($title) || empty($description) || empty($location_text) || $size_value === false || empty($size_unit) || $price === false || empty($property_type_id)) {
        redirect_with_message("../add_property.php", "Please fill in all required fields correctly.", "error");
    }
    if ($price <= 0) {
         redirect_with_message("../add_property.php", "Price must be a positive value.", "error");
    }
    if ($size_value <= 0) {
         redirect_with_message("../add_property.php", "Size value must be a positive value.", "error");
    }

    // File Upload Handling for Main Image
    $main_image_url = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $img_name = $_FILES['main_image']['name'];
        $img_tmp_name = $_FILES['main_image']['tmp_name'];
        $img_size = $_FILES['main_image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed_exts)) {
            if ($img_size <= 2000000) { // Max 2MB
                $unique_img_name = uniqid('prop_', true) . '.' . $img_ext;
                $destination = UPLOAD_DIR . $unique_img_name;
                if (move_uploaded_file($img_tmp_name, $destination)) {
                    $main_image_url = 'assets/images/property_uploads/' . $unique_img_name; // Path to store in DB
                } else {
                    redirect_with_message("../add_property.php", "Failed to move uploaded image.", "error");
                }
            } else {
                redirect_with_message("../add_property.php", "Image size exceeds 2MB limit.", "error");
            }
        } else {
            redirect_with_message("../add_property.php", "Invalid image format. Allowed: JPG, JPEG, PNG, GIF.", "error");
        }
    } else if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] != UPLOAD_ERR_NO_FILE) {
        redirect_with_message("../add_property.php", "Error uploading image: " . $_FILES['main_image']['error'], "error");
    } else {
        // No main image uploaded, which is required for adding.
         redirect_with_message("../add_property.php", "Main image is required.", "error");
    }


    // Prepare SQL statement
    $sql_add = "INSERT INTO properties (seller_id, title, description, location_text, latitude, longitude, size_value, size_unit, price, property_type_id, main_image_url, status, date_listed, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW(), NOW(), NOW())";
    
    $stmt_add = $conn->prepare($sql_add);
    if ($stmt_add) {
        // Latitude and longitude can be null, handle this in bind_param if they are not provided or invalid
        // For simplicity, assuming they are either valid floats or null (already handled by FILTER_NULL_ON_FAILURE)
        $stmt_add->bind_param("isssdddsdiss", 
            $seller_id, $title, $description, $location_text, $latitude, $longitude, 
            $size_value, $size_unit, $price, $property_type_id, $main_image_url
        );

        if ($stmt_add->execute()) {
            redirect_with_message("../seller_dashboard.php", "Property listed successfully!", "success");
        } else {
            // Delete uploaded image if DB insert fails
            if ($main_image_url && file_exists(UPLOAD_DIR . basename($main_image_url))) {
                unlink(UPLOAD_DIR . basename($main_image_url));
            }
            redirect_with_message("../add_property.php", "Failed to list property: " . $stmt_add->error, "error");
        }
        $stmt_add->close();
    } else {
        // Delete uploaded image if DB prepare fails
        if ($main_image_url && file_exists(UPLOAD_DIR . basename($main_image_url))) {
            unlink(UPLOAD_DIR . basename($main_image_url));
        }
        redirect_with_message("../add_property.php", "Database error: " . $conn->error, "error");
    }
    $conn->close();
}
// --- End ADD PROPERTY action ---


// --- Handle EDIT PROPERTY action ---
elseif ($action == 'edit_property' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    if (!$property_id) {
        redirect_with_message("../seller_dashboard.php", "Invalid Property ID.", "error");
    }

    // Verify ownership (essential!)
    $sql_check_owner = "SELECT main_image_url FROM properties WHERE property_id = ? AND seller_id = ?";
    $stmt_check = $conn->prepare($sql_check_owner);
    $stmt_check->bind_param("ii", $property_id, $seller_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows !== 1) {
        redirect_with_message("../seller_dashboard.php", "Property not found or you do not have permission to edit it.", "error");
    }
    $current_property_data = $result_check->fetch_assoc();
    $current_main_image_path_db = $current_property_data['main_image_url']; // Relative path from DB
    $stmt_check->close();

    // Retrieve and sanitize inputs (similar to add_property)
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $location_text = trim(filter_input(INPUT_POST, 'location_text', FILTER_SANITIZE_STRING));
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    $size_value = filter_input(INPUT_POST, 'size_value', FILTER_VALIDATE_FLOAT);
    $size_unit = trim(filter_input(INPUT_POST, 'size_unit', FILTER_SANITIZE_STRING));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $current_main_image_form = filter_input(INPUT_POST, 'current_main_image', FILTER_SANITIZE_STRING);


    // Basic Validation
    if (empty($title) || empty($description) || empty($location_text) || $size_value === false || empty($size_unit) || $price === false || empty($property_type_id) || empty($status)) {
        redirect_with_message("../edit_property.php?property_id=" . $property_id, "Please fill in all required fields correctly.", "error");
    }
     if ($price <= 0) {
         redirect_with_message("../edit_property.php?property_id=" . $property_id, "Price must be a positive value.", "error");
    }
    if ($size_value <= 0) {
         redirect_with_message("../edit_property.php?property_id=" . $property_id, "Size value must be a positive value.", "error");
    }
    $allowed_statuses = ['available', 'sold', 'pending'];
    if (!in_array($status, $allowed_statuses)) {
        redirect_with_message("../edit_property.php?property_id=" . $property_id, "Invalid status selected.", "error");
    }


    // File Upload Handling for Main Image (if a new one is provided)
    $main_image_url_to_update = $current_main_image_path_db; // Keep old image by default

    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $img_name = $_FILES['main_image']['name'];
        $img_tmp_name = $_FILES['main_image']['tmp_name'];
        $img_size = $_FILES['main_image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed_exts)) {
            if ($img_size <= 2000000) { // Max 2MB
                $unique_img_name = uniqid('prop_', true) . '.' . $img_ext;
                $destination = UPLOAD_DIR . $unique_img_name;
                if (move_uploaded_file($img_tmp_name, $destination)) {
                    $main_image_url_to_update = 'assets/images/property_uploads/' . $unique_img_name;
                    // Delete old image if it exists and is different from the new one
                    if (!empty($current_main_image_path_db) && file_exists(UPLOAD_DIR . basename($current_main_image_path_db)) && $current_main_image_path_db != $main_image_url_to_update) {
                        unlink(UPLOAD_DIR . basename($current_main_image_path_db));
                    }
                } else {
                    redirect_with_message("../edit_property.php?property_id=" . $property_id, "Failed to move uploaded new image.", "error");
                }
            } else {
                redirect_with_message("../edit_property.php?property_id=" . $property_id, "New image size exceeds 2MB limit.", "error");
            }
        } else {
            redirect_with_message("../edit_property.php?property_id=" . $property_id, "Invalid new image format. Allowed: JPG, JPEG, PNG, GIF.", "error");
        }
    } elseif (isset($_FILES['main_image']) && $_FILES['main_image']['error'] != UPLOAD_ERR_NO_FILE) {
        // An error occurred with the upload, other than just no file being submitted
        redirect_with_message("../edit_property.php?property_id=" . $property_id, "Error with new image upload: " . $_FILES['main_image']['error'], "error");
    }

    // Prepare SQL statement for update
    $sql_update = "UPDATE properties SET 
                    title = ?, description = ?, location_text = ?, latitude = ?, longitude = ?, 
                    size_value = ?, size_unit = ?, price = ?, property_type_id = ?, 
                    main_image_url = ?, status = ?, updated_at = NOW()
                   WHERE property_id = ? AND seller_id = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param("sssdddsdisssii", 
            $title, $description, $location_text, $latitude, $longitude, 
            $size_value, $size_unit, $price, $property_type_id, 
            $main_image_url_to_update, $status, $property_id, $seller_id
        );

        if ($stmt_update->execute()) {
            redirect_with_message("../seller_dashboard.php", "Property updated successfully!", "success");
        } else {
            // If DB update fails but a new image was uploaded, attempt to delete the new image.
            if ($main_image_url_to_update != $current_main_image_path_db && file_exists(UPLOAD_DIR . basename($main_image_url_to_update))) {
                 unlink(UPLOAD_DIR . basename($main_image_url_to_update));
            }
            redirect_with_message("../edit_property.php?property_id=" . $property_id, "Failed to update property: " . $stmt_update->error, "error");
        }
        $stmt_update->close();
    } else {
        if ($main_image_url_to_update != $current_main_image_path_db && file_exists(UPLOAD_DIR . basename($main_image_url_to_update))) {
            unlink(UPLOAD_DIR . basename($main_image_url_to_update));
        }
        redirect_with_message("../edit_property.php?property_id=" . $property_id, "Database error on update: " . $conn->error, "error");
    }
    $conn->close();
}
// --- End EDIT PROPERTY action ---


// --- Handle TOGGLE STATUS action ---
elseif ($action == 'toggle_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    $current_status = trim(filter_input(INPUT_POST, 'current_status', FILTER_SANITIZE_STRING));

    if (!$property_id || empty($current_status)) {
        redirect_with_message("../seller_dashboard.php", "Invalid property data for status toggle.", "error");
    }

    // Verify ownership (essential!)
    $sql_check_owner = "SELECT status FROM properties WHERE property_id = ? AND seller_id = ?";
    $stmt_check = $conn->prepare($sql_check_owner);
    $stmt_check->bind_param("ii", $property_id, $seller_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows !== 1) {
        redirect_with_message("../seller_dashboard.php", "Property not found or you do not have permission to change its status.", "error");
    }
    $property_data = $result_check->fetch_assoc();
    // Ensure the current_status from form matches the one in DB for consistency, though DB one is more authoritative
    if ($property_data['status'] !== $current_status) {
         redirect_with_message("../seller_dashboard.php", "Property status has changed since you loaded the page. Please refresh.", "error");
    }
    $stmt_check->close();

    // Determine new status
    $new_status = ($current_status == 'available') ? 'sold' : 'available';
    // Could also add 'pending' logic if needed, e.g. available -> pending -> sold

    // Prepare SQL statement for update
    $sql_toggle = "UPDATE properties SET status = ?, updated_at = NOW() WHERE property_id = ? AND seller_id = ?";
    $stmt_toggle = $conn->prepare($sql_toggle);
    if ($stmt_toggle) {
        $stmt_toggle->bind_param("sii", $new_status, $property_id, $seller_id);
        if ($stmt_toggle->execute()) {
            redirect_with_message("../seller_dashboard.php", "Property status updated to '" . ucfirst($new_status) . "' successfully!", "success");
        } else {
            redirect_with_message("../seller_dashboard.php", "Failed to update property status: " . $stmt_toggle->error, "error");
        }
        $stmt_toggle->close();
    } else {
        redirect_with_message("../seller_dashboard.php", "Database error on status update: " . $conn->error, "error");
    }
    $conn->close();
}
// --- End TOGGLE STATUS action ---


// --- Handle DELETE PROPERTY action ---
elseif ($action == 'delete_property' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);

    if (!$property_id) {
        redirect_with_message("../seller_dashboard.php", "Invalid Property ID for deletion.", "error");
    }

    // Verify ownership and get main image URL for deletion
    $sql_check_owner = "SELECT main_image_url FROM properties WHERE property_id = ? AND seller_id = ?";
    $stmt_check = $conn->prepare($sql_check_owner);
    $stmt_check->bind_param("ii", $property_id, $seller_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows !== 1) {
        redirect_with_message("../seller_dashboard.php", "Property not found or you do not have permission to delete it.", "error");
    }
    $property_data = $result_check->fetch_assoc();
    $main_image_to_delete_db_path = $property_data['main_image_url'];
    $stmt_check->close();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Note: Foreign key constraints for `property_images`, `saved_properties` are ON DELETE CASCADE.
        // For `inquiries`, it's ON DELETE SET NULL. So, these will be handled by the DB.

        // Delete the property record from the database
        $sql_delete = "DELETE FROM properties WHERE property_id = ? AND seller_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $property_id, $seller_id);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Failed to delete property from database: " . $stmt_delete->error);
        }
        $stmt_delete->close();

        // If DB deletion is successful, delete the main image file
        if (!empty($main_image_to_delete_db_path)) {
            $full_image_path = UPLOAD_DIR . basename($main_image_to_delete_db_path);
            if (file_exists($full_image_path)) {
                if (!unlink($full_image_path)) {
                    // Log this error, but don't necessarily fail the whole operation if DB record is gone.
                    // Or, decide to roll back if file deletion is critical. For now, log and proceed.
                    error_log("Failed to delete image file: " . $full_image_path);
                }
            } else {
                 error_log("Image file not found for deletion: " . $full_image_path);
            }
        }
        
        $conn->commit();
        redirect_with_message("../seller_dashboard.php", "Property deleted successfully!", "success");

    } catch (Exception $e) {
        $conn->rollback();
        redirect_with_message("../seller_dashboard.php", "Failed to delete property: " . $e->getMessage(), "error");
    }
    $conn->close();
}
// --- End DELETE PROPERTY action ---


else {
    redirect_with_message("../seller_dashboard.php", "Invalid action or request method.", "error");
}

?>
