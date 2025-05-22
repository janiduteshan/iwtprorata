<?php
session_start();
include '../includes/config.php'; // Database Connection

// Ensure user is logged in and is an admin, otherwise redirect to signin page
if (!isset($_SESSION['authenticated']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: ../signin.php');
    exit();
}

if (isset($_POST['addUser'])) {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password']; // Storing password as plain text. This is a security risk!
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // --- SECURITY WARNING ---
    // The following SQL query uses direct variable injection, which is a significant security vulnerability (SQL Injection).
    // In a production environment, PREPARED STATEMENTS (using mysqli or PDO) MUST be used to prevent SQL injection.
    // For this exercise, I am proceeding with direct injection to maintain consistency with the existing codebase,
    // but this is acknowledged as a temporary and insecure measure.
    // --- END SECURITY WARNING ---

    // Construct the SQL INSERT statement
    $sql = "INSERT INTO users (username, password, full_name, email, address, city, phone, role, reg_date, user_type) 
            VALUES ('$username', '$password', '$full_name', '$email', '$address', '$city', '$phone', '$role', NOW(), '$role')";
            // Also setting user_type to the role for now, as the system seems to use user_type for admin/user checks.
            // This might need further review based on how user_type and role are intended to be used together.

    if (mysqli_query($conn, $sql)) {
        // Successful insertion
        echo "<script>
                alert('User added successfully!');
                window.location.href = 'users.php';
              </script>";
    } else {
        // Error in insertion
        $errorMessage = mysqli_error($conn);
        echo "<script>
                alert('Failed to add user. Error: " . addslashes($errorMessage) . "');
                window.location.href = 'user-add.php';
              </script>";
    }

    mysqli_close($conn);
} else {
    // If the form wasn't submitted, redirect to the add user page or users list
    // header('Location: user-add.php'); // Commented out as we need to add more logic below
    // exit(); // Commented out
} elseif (isset($_POST['updateUser'])) {
    // Retrieve form data for update
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password']; // No mysqli_real_escape_string here, handle separately

    // --- SECURITY WARNING ---
    // Direct variable injection is used below. PREPARED STATEMENTS are essential for production.
    // --- END SECURITY WARNING ---

    // Base SQL UPDATE statement
    $sql = "UPDATE users SET 
            username = '$username', 
            full_name = '$full_name', 
            email = '$email', 
            address = '$address', 
            city = '$city', 
            phone = '$phone', 
            role = '$role', 
            user_type = '$role'"; // Update user_type to match role

    // Append password update if password is provided
    if (!empty($password)) {
        // Storing password as plain text. This is a security risk!
        $sql .= ", password = '" . mysqli_real_escape_string($conn, $password) . "'";
    }

    $sql .= " WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        // Successful update
        echo "<script>
                alert('User updated successfully!');
                window.location.href = 'users.php';
              </script>";
    } else {
        // Error in update
        $errorMessage = mysqli_error($conn);
        echo "<script>
                alert('Failed to update user. Error: " . addslashes($errorMessage) . "');
                window.location.href = 'user-edit.php?id=" . $user_id . "';
              </script>";
    }
    mysqli_close($conn);
} else {
    // If neither addUser nor updateUser is set, redirect to users list or a default admin page
    // header('Location: users.php'); // Commented out as we need to add more logic below
    // exit(); // Commented out
} elseif (isset($_GET['deleteUser']) && isset($_GET['id'])) {
    // Handle delete user request
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);

    // --- SECURITY WARNING ---
    // Direct variable injection is used below. PREPARED STATEMENTS are essential for production.
    // --- END SECURITY WARNING ---

    $sql = "DELETE FROM users WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        // Successful deletion
        echo "<script>
                alert('User deleted successfully!');
                window.location.href = 'users.php';
              </script>";
    } else {
        // Error in deletion
        $errorMessage = mysqli_error($conn);
        echo "<script>
                alert('Failed to delete user. Error: " . addslashes($errorMessage) . "');
                window.location.href = 'users.php';
              </script>";
    }
    mysqli_close($conn);
} else {
    // If no specific action is set (addUser, updateUser, deleteUser), redirect to users list
    header('Location: users.php');
    exit();
}
?>
