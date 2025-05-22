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
} elseif ($action == 'create_user') {
    // Retrieve and sanitize form data
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password = $_POST['password']; // Will be hashed, not sanitized as string
    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
    $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING));
    $city = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
    $user_type = trim(filter_input(INPUT_POST, 'user_type', FILTER_SANITIZE_STRING));

    // Validate required fields
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = 'Username, email, and password are required.';
        header("Location: user_add.php");
        exit;
    }

    if (!$email) {
        $_SESSION['error_message'] = 'Invalid email format.';
        header("Location: user_add.php");
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error_message'] = 'Password must be at least 6 characters long.';
        header("Location: user_add.php");
        exit;
    }

    // Check for unique username
    $sql_check_username = "SELECT user_id FROM users WHERE username = ?";
    $stmt_check_username = $conn->prepare($sql_check_username);
    $stmt_check_username->bind_param("s", $username);
    $stmt_check_username->execute();
    $stmt_check_username->store_result();
    if ($stmt_check_username->num_rows > 0) {
        $_SESSION['error_message'] = 'Username already exists. Please choose a different one.';
        $stmt_check_username->close();
        header("Location: user_add.php");
        exit;
    }
    $stmt_check_username->close();

    // Check for unique email
    $sql_check_email = "SELECT user_id FROM users WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    if ($stmt_check_email->num_rows > 0) {
        $_SESSION['error_message'] = 'Email already registered. Please use a different one.';
        $stmt_check_email->close();
        header("Location: user_add.php");
        exit;
    }
    $stmt_check_email->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL for insertion
    $sql_insert_user = "INSERT INTO users (username, email, password, full_name, address, city, phone, user_type, reg_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($sql_insert_user);
    if ($stmt_insert) {
        $stmt_insert->bind_param("ssssssss", $username, $email, $hashed_password, $full_name, $address, $city, $phone, $user_type);
        if ($stmt_insert->execute()) {
            redirect_admin_user_page('User created successfully!', 'success');
        } else {
            $_SESSION['error_message'] = 'Error creating user: ' . $stmt_insert->error;
            header("Location: user_add.php");
            exit;
        }
        $stmt_insert->close();
    } else {
        $_SESSION['error_message'] = 'Database error preparing to create user: ' . $conn->error;
        header("Location: user_add.php");
        exit;
    }

} else {
    redirect_admin_user_page('Invalid action specified.', 'error');
} elseif ($action == 'delete_user') {
    $user_id_to_delete = filter_input(INPUT_POST, 'user_id_to_delete', FILTER_VALIDATE_INT);
    $current_admin_id = $_SESSION['userID'];

    if (!$user_id_to_delete) {
        redirect_admin_user_page('Invalid user ID for deletion.', 'error');
    }

    if ($user_id_to_delete == $current_admin_id) {
        redirect_admin_user_page('You cannot delete your own account.', 'error');
    }

    // Attempt to delete the user
    $sql_delete_user = "DELETE FROM users WHERE user_id = ?";
    $stmt_delete = $conn->prepare($sql_delete_user);
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $user_id_to_delete);
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                redirect_admin_user_page('User deleted successfully!', 'success');
            } else {
                redirect_admin_user_page('User not found or already deleted.', 'error');
            }
        } else {
            // Check for foreign key constraint violation
            if ($conn->errno == 1451) { // Error code for foreign key constraint
                 redirect_admin_user_page('Cannot delete this user. They may have associated properties or other records. Please reassign or delete those first. Error: ' . $stmt_delete->error, 'error');
            } else {
                redirect_admin_user_page('Error deleting user: ' . $stmt_delete->error, 'error');
            }
        }
        $stmt_delete->close();
    } else {
        redirect_admin_user_page('Database error preparing to delete user: ' . $conn->error, 'error');
    }

} else {
    redirect_admin_user_page('Invalid action.', 'error');
}

$conn->close();
?>
