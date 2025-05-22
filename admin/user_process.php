<?php
session_start();
include '../includes/config.php'; // Database Connection

// --- Helper function for redirecting with messages ---
function redirect_admin_user_page($message, $type = 'success') {
    header("Location: users.php?message=" . urlencode($message) . "&type=" . $type);
    exit;
}

// --- Admin Access Control ---
if (!isset($_SESSION['userID']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    redirect_admin_user_page('Access denied. You must be an admin.', 'error');
}

$action = $_POST['action'] ?? '';

if ($action == 'update_user_role') {
    $user_id_to_update = filter_input(INPUT_POST, 'user_id_to_update', FILTER_VALIDATE_INT);
    $new_user_type = trim(filter_input(INPUT_POST, 'new_user_type', FILTER_SANITIZE_STRING));
    $current_admin_id = $_SESSION['userID'];

    if (!$user_id_to_update || empty($new_user_type)) {
        redirect_admin_user_page('Invalid user ID or new role specified.', 'error');
    }

    $allowed_roles = ['buyer', 'seller', 'agent', 'admin', 'user']; // 'user' as a fallback/generic
    if (!in_array($new_user_type, $allowed_roles)) {
        redirect_admin_user_page('Invalid role selected.', 'error');
    }

    // Prevent admin from changing their own role to a non-admin role if they are the only admin
    if ($user_id_to_update == $current_admin_id && $new_user_type !== 'admin') {
        // Check if there are other admins
        $sql_check_admins = "SELECT COUNT(*) AS admin_count FROM users WHERE user_type = 'admin' AND user_id != ?";
        $stmt_check = $conn->prepare($sql_check_admins);
        $stmt_check->bind_param("i", $current_admin_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($result_check['admin_count'] == 0) {
            redirect_admin_user_page('Cannot change your own role. You are the only admin.', 'error');
        }
    }
    
    // Also, an admin cannot change their own role if the select was disabled, this is a server-side check.
    // (The form select is disabled for current admin, so this POST value might not be sent or might be empty.
    // However, if it was maliciously enabled, this server check is important)
     if ($user_id_to_update == $current_admin_id && $new_user_type !== 'admin' && !isset($_POST['new_user_type_enabled_for_self'])) {
        // This case means the select was likely disabled and value not POSTed, or user is trying to demote self.
        // If new_user_type was not submitted because it was disabled, it would be empty.
        // If it was submitted and is not 'admin', it's an attempt to demote self.
        // The previous check for "only admin" is more specific. This is a general "don't change your own role from admin".
        // It's safer to prevent an admin from changing their own role at all via this form.
        // They can create another admin user, log in as that user, then change the original admin's role.
         redirect_admin_user_page('Admins cannot change their own role using this form.', 'error');
    }


    $sql_update_role = "UPDATE users SET user_type = ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($sql_update_role);
    if ($stmt_update) {
        $stmt_update->bind_param("si", $new_user_type, $user_id_to_update);
        if ($stmt_update->execute()) {
            redirect_admin_user_page('User role updated successfully!', 'success');
        } else {
            redirect_admin_user_page('Error updating user role: ' . $stmt_update->error, 'error');
        }
        $stmt_update->close();
    } else {
        redirect_admin_user_page('Database error: ' . $conn->error, 'error');
    }

} else {
    redirect_admin_user_page('Invalid action.', 'error');
}

$conn->close();
?>
