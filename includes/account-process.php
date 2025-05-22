<?php
session_start(); // Start the session
include 'config.php'; // Database Connection

// Check if the form is submitted
if (isset($_POST['update'])) {
    // Retrieve form data
    $username = $_POST['username'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone = $_POST['phone'];

    // Update the user profile in the database
    $userId = $_SESSION['userID']; // Assuming you have a user_id stored in the session
    $sql = "UPDATE users SET username = '$username', full_name = '$fullName', email = '$email', address = '$address', city = '$city', phone = '$phone' WHERE user_id = $userId";

    // Execute the SQL query and check for success
    if ($conn->query($sql) === TRUE) {
        // Update successful, update the session variables
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['email'] = $email;
        $_SESSION['address'] = $address;
        $_SESSION['city'] = $city;
        $_SESSION['phone'] = $phone;

        // Redirect to a success page or display a success message
        echo "
                    <script>
                        alert('Update Successfully');
                        window.location.href = '../index.php'; 
                    </script>
                ";
    } else {
        // Error occurred while updating, handle the error accordingly
        echo "Error updating profile: " . $conn->error;
    }
} elseif (isset($_POST['action']) && $_POST['action'] == 'delete_account') {
    if (!isset($_POST['userID']) || !isset($_SESSION['userID']) || $_POST['userID'] != $_SESSION['userID']) {
        // User ID mismatch or not provided, potential tampering
        session_destroy(); // Log out for security
        header("Location: ../signin.php?error=auth_error");
        exit;
    }

    $userIdToDelete = (int)$_SESSION['userID'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Update user's status to 'deleted' and clear personal data
        $updateUserSql = "UPDATE users SET 
                            status = 'deleted', 
                            username = CONCAT('deleted_user_', user_id), 
                            email = CONCAT('deleted_', user_id, '@deleted.com'), 
                            full_name = '[DELETED]', 
                            address = NULL, 
                            city = NULL, 
                            phone = NULL,
                            password = '' 
                          WHERE user_id = ?";
        $stmtUser = $conn->prepare($updateUserSql);
        if (!$stmtUser) {
            throw new Exception("Error preparing user update: " . $conn->error);
        }
        $stmtUser->bind_param("i", $userIdToDelete);
        if (!$stmtUser->execute()) {
            throw new Exception("Error deactivating user account: " . $stmtUser->error);
        }
        $stmtUser->close();

        // 2. Update status of properties owned by the user to 'removed'
        // This is relevant if the user is a seller or admin who might own properties.
        $updatePropertiesSql = "UPDATE properties SET status = 'removed' WHERE seller_id = ?";
        $stmtProps = $conn->prepare($updatePropertiesSql);
        if (!$stmtProps) {
            throw new Exception("Error preparing property update: " . $conn->error);
        }
        $stmtProps->bind_param("i", $userIdToDelete);
        if (!$stmtProps->execute()) {
            // If this fails, we might still want to deactivate the user, so log and continue or rollback.
            // For now, let's make it critical for consistency.
            throw new Exception("Error updating user properties: " . $stmtProps->error);
        }
        $stmtProps->close();
        
        // If all operations were successful, commit the transaction
        $conn->commit();

        // Destroy the session and log out
        session_destroy();

        // Redirect to homepage with a success message
        // Using a GET parameter for the message for simplicity here.
        // A more robust solution might use session flash messages set *before* destroying the session.
        header("Location: ../home.php?message=account_deleted_success");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        // Log the error internally if possible
        // Redirect with a generic error message
        // Destroy session as a precaution if something sensitive failed.
        session_destroy();
        header("Location: ../home.php?message=account_deleted_error&err_detail=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    // No action specified or unknown action
    header("Location: ../index.php");
    exit;
}

$conn->close(); // Close the connection at the end if not already closed
?>