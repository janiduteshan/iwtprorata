<?php
session_start();
include 'config.php'; // Database Connection

// --- Helper function for redirecting ---
function redirect_back($default_page = '../index.php', $message = '', $type = 'info') {
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? $default_page;
    // Append message and type to the redirect URL if they exist
    if (!empty($message)) {
        $redirect_url .= (strpos($redirect_url, '?') === false ? '?' : '&') . "save_status_msg=" . urlencode($message) . "&save_status_type=" . $type;
    }
    header("Location: " . $redirect_url);
    exit;
}

// --- Access Control & Input Validation ---
if (!isset($_SESSION['userID']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    // If not a buyer or not logged in, redirect with an error or to login page
    // For simplicity, redirecting to signin if not logged in, or home if not buyer
    $redirect_page = isset($_SESSION['userID']) ? '../home.php' : '../signin.php';
    $error_message = isset($_SESSION['userID']) ? 'Only buyers can save properties.' : 'You need to be logged in to save properties.';
    redirect_back($redirect_page, $error_message, 'error');
}

if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    redirect_back('../LandListings.php', 'Invalid property specified.', 'error');
}

$property_id = (int)$_GET['property_id'];
$user_id = (int)$_SESSION['userID'];

if ($property_id <= 0) {
    redirect_back('../LandListings.php', 'Invalid property ID.', 'error');
}

// Check if the property exists (optional but good practice)
$stmt_check_prop = $conn->prepare("SELECT property_id FROM properties WHERE property_id = ?");
$stmt_check_prop->bind_param("i", $property_id);
$stmt_check_prop->execute();
$result_check_prop = $stmt_check_prop->get_result();
if ($result_check_prop->num_rows === 0) {
    $stmt_check_prop->close();
    redirect_back('../LandListings.php', 'Property not found.', 'error');
}
$stmt_check_prop->close();


// --- Check if property is already saved by this user ---
$stmt_check = $conn->prepare("SELECT saved_id FROM saved_properties WHERE user_id = ? AND property_id = ?");
if (!$stmt_check) {
    redirect_back(null, 'Database error (check saved): ' . $conn->error, 'error');
}
$stmt_check->bind_param("ii", $user_id, $property_id);
$stmt_check->execute();
$result = $stmt_check->get_result();
$is_saved = ($result->num_rows > 0);
$stmt_check->close();

// --- Perform Save or Unsave Action ---
if ($is_saved) {
    // Property is saved, so delete it (unsave)
    $stmt_delete = $conn->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
    if (!$stmt_delete) {
        redirect_back(null, 'Database error (delete saved): ' . $conn->error, 'error');
    }
    $stmt_delete->bind_param("ii", $user_id, $property_id);
    if ($stmt_delete->execute()) {
        $message = "Property unsaved successfully.";
        $type = "success";
    } else {
        $message = "Failed to unsave property: " . $stmt_delete->error;
        $type = "error";
    }
    $stmt_delete->close();
} else {
    // Property is not saved, so insert it (save)
    $stmt_insert = $conn->prepare("INSERT INTO saved_properties (user_id, property_id, date_saved) VALUES (?, ?, NOW())");
    if (!$stmt_insert) {
        redirect_back(null, 'Database error (insert saved): ' . $conn->error, 'error');
    }
    $stmt_insert->bind_param("ii", $user_id, $property_id);
    if ($stmt_insert->execute()) {
        $message = "Property saved successfully!";
        $type = "success";
    } else {
        $message = "Failed to save property: " . $stmt_insert->error;
        $type = "error";
    }
    $stmt_insert->close();
}

$conn->close();
redirect_back(null, $message, $type);
?>
