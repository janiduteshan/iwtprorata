<?php
session_start();
include '../includes/config.php'; // Database Connection

// --- Helper function for redirecting with messages ---
function redirect_admin_prop($page, $message, $type = 'success') {
    header("Location: " . $page . "?message=" . urlencode($message) . "&type=" . $type);
    exit;
}

// --- Admin Access Control ---
if (!isset($_SESSION['userID']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    redirect_admin_prop('dashboard.php', 'Access denied. You must be an admin.', 'error');
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
define('PROPERTY_UPLOAD_DIR', '../assets/images/property_uploads/'); // Used by seller actions, ensure consistency or define globally

if (empty($action)) {
    redirect_admin_prop('properties.php', 'No action specified.', 'error');
}

// --- EDIT PROPERTY (ADMIN) ---
if ($action == 'edit_property_admin' && isset($_POST['editPropertyAdmin'])) {
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    // Admin can edit these fields:
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $location_text = trim(filter_input(INPUT_POST, 'location_text', FILTER_SANITIZE_STRING));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
    $size_value = filter_input(INPUT_POST, 'size_value', FILTER_VALIDATE_FLOAT);
    $size_unit = trim(filter_input(INPUT_POST, 'size_unit', FILTER_SANITIZE_STRING));
    $current_main_image_db = filter_input(INPUT_POST, 'current_main_image', FILTER_SANITIZE_STRING); // Path from DB

    if (!$property_id || empty($title) || empty($location_text) || $price === false || empty($property_type_id) || empty($status) || $size_value === false || empty($size_unit)) {
        redirect_admin_prop("property_edit.php?property_id=" . $property_id, "Please fill all required fields.", "error");
    }
    
    $allowed_statuses = ['available', 'sold', 'pending', 'removed'];
     if (!in_array($status, $allowed_statuses)) {
        redirect_admin_prop("property_edit.php?property_id=" . $property_id, "Invalid status selected.", "error");
    }

    $main_image_url_to_update = $current_main_image_db;

    // Handle new image upload
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $img_name = $_FILES['main_image']['name'];
        $img_tmp_name = $_FILES['main_image']['tmp_name'];
        $img_size = $_FILES['main_image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed_exts)) {
            if ($img_size <= 2000000) { // Max 2MB
                $unique_img_name = uniqid('prop_admin_', true) . '.' . $img_ext;
                $destination = PROPERTY_UPLOAD_DIR . $unique_img_name;
                if (move_uploaded_file($img_tmp_name, $destination)) {
                    $main_image_url_to_update = 'assets/images/property_uploads/' . $unique_img_name; // Relative path for DB
                    // Delete old image if it exists and is different
                    if (!empty($current_main_image_db) && file_exists(PROPERTY_UPLOAD_DIR . basename($current_main_image_db)) && $current_main_image_db != $main_image_url_to_update) {
                        unlink(PROPERTY_UPLOAD_DIR . basename($current_main_image_db));
                    }
                } else {
                     redirect_admin_prop("property_edit.php?property_id=" . $property_id, "Failed to move uploaded new image.", "error");
                }
            } else {
                redirect_admin_prop("property_edit.php?property_id=" . $property_id, "New image size exceeds 2MB limit.", "error");
            }
        } else {
            redirect_admin_prop("property_edit.php?property_id=" . $property_id, "Invalid new image format.", "error");
        }
    } elseif (isset($_FILES['main_image']) && $_FILES['main_image']['error'] != UPLOAD_ERR_NO_FILE) {
        redirect_admin_prop("property_edit.php?property_id=" . $property_id, "Error with image upload: " . $_FILES['main_image']['error'], "error");
    }

    $sql = "UPDATE properties SET title = ?, description = ?, location_text = ?, price = ?, property_type_id = ?, status = ?, main_image_url = ?, size_value = ?, size_unit = ?, updated_at = NOW() 
            WHERE property_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssdissdsi", $title, $description, $location_text, $price, $property_type_id, $status, $main_image_url_to_update, $size_value, $size_unit, $property_id);
        if ($stmt->execute()) {
            redirect_admin_prop('properties.php', 'Property updated successfully by admin!', 'success');
        } else {
            redirect_admin_prop("property_edit.php?property_id=" . $property_id, 'Error updating property: ' . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        redirect_admin_prop("property_edit.php?property_id=" . $property_id, 'Database error: ' . $conn->error, 'error');
    }

// --- DELETE PROPERTY (ADMIN) ---
} elseif ($action == 'delete_property_admin' && isset($_POST['property_id'])) {
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);

    if (!$property_id) {
        redirect_admin_prop('properties.php', 'Invalid Property ID for deletion.', 'error');
    }

    // Get main image URL for deletion from server
    $sql_img = "SELECT main_image_url FROM properties WHERE property_id = ?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $property_id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    $main_image_to_delete = null;
    if($result_img->num_rows > 0){
        $main_image_to_delete = $result_img->fetch_assoc()['main_image_url'];
    }
    $stmt_img->close();

    $conn->begin_transaction();
    try {
        // FKs should handle related tables (inquiries, saved_properties, property_images)
        $sql = "DELETE FROM properties WHERE property_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        
        $stmt->bind_param("i", $property_id);
        if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
        
        $stmt->close();

        // Delete main image file if it exists
        if (!empty($main_image_to_delete) && file_exists(PROPERTY_UPLOAD_DIR . basename($main_image_to_delete))) {
            if (!unlink(PROPERTY_UPLOAD_DIR . basename($main_image_to_delete))) {
                // Log error, but don't necessarily fail the whole transaction
                error_log("Admin delete: Failed to delete image file: " . PROPERTY_UPLOAD_DIR . basename($main_image_to_delete));
            }
        }
        $conn->commit();
        redirect_admin_prop('properties.php', 'Property deleted successfully by admin!', 'success');

    } catch (Exception $e) {
        $conn->rollback();
        redirect_admin_prop('properties.php', 'Failed to delete property: ' . $e->getMessage(), 'error');
    }

} else {
    redirect_admin_prop('properties.php', 'Invalid action or parameters.', 'error');
} elseif ($action == 'create_property_admin') {
    // --- CREATE PROPERTY (ADMIN) ---
    $_SESSION['form_data'] = $_POST; // Preserve form data on error

    $seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $location_text = trim(filter_input(INPUT_POST, 'location_text', FILTER_SANITIZE_STRING));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $size_value = filter_input(INPUT_POST, 'size_value', FILTER_VALIDATE_FLOAT);
    $size_unit = trim(filter_input(INPUT_POST, 'size_unit', FILTER_SANITIZE_STRING));
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);
    $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));

    // Validate required fields
    if (!$seller_id || empty($title) || empty($description) || empty($location_text) || $price === false || $size_value === false || empty($size_unit) || !$property_type_id || empty($status)) {
        redirect_admin_prop('property_add.php', 'Please fill all required fields.', 'error');
    }

    // Validate seller_id
    $sql_check_seller = "SELECT user_id FROM users WHERE user_id = ? AND user_type IN ('seller', 'agent', 'admin')"; // Admin can also be a seller
    $stmt_check_seller = $conn->prepare($sql_check_seller);
    $stmt_check_seller->bind_param("i", $seller_id);
    $stmt_check_seller->execute();
    $stmt_check_seller->store_result();
    if ($stmt_check_seller->num_rows == 0) {
        $stmt_check_seller->close();
        redirect_admin_prop('property_add.php', 'Invalid Seller ID or seller is not of type Seller or Agent.', 'error');
    }
    $stmt_check_seller->close();

    $allowed_statuses = ['available', 'sold', 'pending', 'draft', 'removed'];
    if (!in_array($status, $allowed_statuses)) {
        redirect_admin_prop('property_add.php', "Invalid status selected.", "error");
    }
    
    $allowed_size_units = ['acres', 'sq_ft', 'hectares', 'sq_m'];
    if (!in_array($size_unit, $allowed_size_units)) {
        redirect_admin_prop('property_add.php', "Invalid size unit selected.", "error");
    }


    $main_image_url_to_insert = null;
    // Handle image upload
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        if (!file_exists(PROPERTY_UPLOAD_DIR)) {
            mkdir(PROPERTY_UPLOAD_DIR, 0777, true);
        }
        $img_name = $_FILES['main_image']['name'];
        $img_tmp_name = $_FILES['main_image']['tmp_name'];
        $img_size = $_FILES['main_image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed_exts)) {
            if ($img_size <= 2000000) { // Max 2MB
                $unique_img_name = uniqid('prop_admin_new_', true) . '.' . $img_ext;
                $destination = PROPERTY_UPLOAD_DIR . $unique_img_name;
                if (move_uploaded_file($img_tmp_name, $destination)) {
                    $main_image_url_to_insert = 'assets/images/property_uploads/' . $unique_img_name; // Relative path for DB
                } else {
                    redirect_admin_prop('property_add.php', 'Failed to move uploaded image.', 'error');
                }
            } else {
                redirect_admin_prop('property_add.php', 'Image size exceeds 2MB limit.', 'error');
            }
        } else {
            redirect_admin_prop('property_add.php', 'Invalid image format. Allowed: JPG, JPEG, PNG, GIF.', 'error');
        }
    } elseif (isset($_FILES['main_image']) && $_FILES['main_image']['error'] != UPLOAD_ERR_NO_FILE) {
        redirect_admin_prop('property_add.php', 'Error with image upload: ' . $_FILES['main_image']['error'], 'error');
    }

    // Insert into database
    $sql_insert = "INSERT INTO properties (seller_id, title, description, location_text, price, size_value, size_unit, property_type_id, status, main_image_url, date_listed, updated_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert) {
        $stmt_insert->bind_param("isssdssdsss", $seller_id, $title, $description, $location_text, $price, $size_value, $size_unit, $property_type_id, $status, $main_image_url_to_insert);
        if ($stmt_insert->execute()) {
            unset($_SESSION['form_data']); // Clear form data on success
            redirect_admin_prop('properties.php', 'Property added successfully by admin!', 'success');
        } else {
            redirect_admin_prop('property_add.php', 'Error adding property: ' . $stmt_insert->error, 'error');
        }
        $stmt_insert->close();
    } else {
        redirect_admin_prop('property_add.php', 'Database error preparing to add property: ' . $conn->error, 'error');
    }

} else {
    redirect_admin_prop('properties.php', 'Invalid action specified.', 'error');
}

$conn->close();
?>