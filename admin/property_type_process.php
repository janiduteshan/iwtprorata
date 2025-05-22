<?php
session_start();
include '../includes/config.php'; // Database Connection

// --- Helper function for redirecting with messages ---
function redirect_admin($page, $message, $type = 'success') {
    header("Location: " . $page . "?message=" . urlencode($message) . "&type=" . $type);
    exit;
}

// --- Admin Access Control ---
if (!isset($_SESSION['userID']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    redirect_admin('dashboard.php', 'Access denied. You must be an admin.', 'error'); // Or redirect to login
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($action)) {
    redirect_admin('property_types.php', 'No action specified.', 'error');
}

// --- ADD PROPERTY TYPE ---
if ($action == 'add_property_type' && isset($_POST['addPropertyType'])) {
    $pt_name = trim(filter_input(INPUT_POST, 'pt_name', FILTER_SANITIZE_STRING));
    $pt_description = trim(filter_input(INPUT_POST, 'pt_description', FILTER_SANITIZE_STRING));

    if (empty($pt_name)) {
        redirect_admin('property_types.php', 'Property Type Name is required.', 'error');
    }

    $sql = "INSERT INTO property_types (name, description, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $pt_name, $pt_description);
        if ($stmt->execute()) {
            redirect_admin('property_types.php', 'Property Type added successfully!', 'success');
        } else {
            redirect_admin('property_types.php', 'Error adding property type: ' . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        redirect_admin('property_types.php', 'Database error: ' . $conn->error, 'error');
    }

// --- EDIT PROPERTY TYPE ---
} elseif ($action == 'edit_property_type' && isset($_POST['editPropertyType'])) {
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);
    $pt_name = trim(filter_input(INPUT_POST, 'pt_name', FILTER_SANITIZE_STRING));
    $pt_description = trim(filter_input(INPUT_POST, 'pt_description', FILTER_SANITIZE_STRING));

    if (empty($property_type_id) || empty($pt_name)) {
        redirect_admin('property_types.php', 'Property Type ID or Name is missing.', 'error');
    }

    $sql = "UPDATE property_types SET name = ?, description = ?, updated_at = NOW() WHERE property_type_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssi", $pt_name, $pt_description, $property_type_id);
        if ($stmt->execute()) {
            redirect_admin('property_types.php', 'Property Type updated successfully!', 'success');
        } else {
            redirect_admin('property_types.php', 'Error updating property type: ' . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        redirect_admin('property_types.php', 'Database error: ' . $conn->error, 'error');
    }

// --- DELETE PROPERTY TYPE ---
} elseif ($action == 'delete_property_type' && isset($_POST['property_type_id'])) { // Changed to POST for consistency
    $property_type_id = filter_input(INPUT_POST, 'property_type_id', FILTER_VALIDATE_INT);

    if (empty($property_type_id)) {
        redirect_admin('property_types.php', 'Property Type ID is missing.', 'error');
    }

    // Optional: Check if any properties are using this type before deleting
    $sql_check = "SELECT COUNT(*) AS count FROM properties WHERE property_type_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $property_type_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if ($result_check['count'] > 0) {
        redirect_admin('property_types.php', 'Cannot delete. This property type is currently assigned to ' . $result_check['count'] . ' properties.', 'error');
    }


    $sql = "DELETE FROM property_types WHERE property_type_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $property_type_id);
        if ($stmt->execute()) {
            redirect_admin('property_types.php', 'Property Type deleted successfully!', 'success');
        } else {
            redirect_admin('property_types.php', 'Error deleting property type: ' . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        redirect_admin('property_types.php', 'Database error: ' . $conn->error, 'error');
    }

} else {
    redirect_admin('property_types.php', 'Invalid action or missing parameters.', 'error');
}

$conn->close();
?>
