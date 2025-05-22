<?php
session_start();
include 'config.php'; // Database Connection

if (isset($_POST['submit_inquiry'])) {
    // Retrieve form data
    $property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
    $seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);
    $inq_name = trim(filter_input(INPUT_POST, 'inq_name', FILTER_SANITIZE_STRING));
    $inq_email = trim(filter_input(INPUT_POST, 'inq_email', FILTER_VALIDATE_EMAIL));
    $inq_phone = trim(filter_input(INPUT_POST, 'inq_phone', FILTER_SANITIZE_STRING)); // Basic sanitization
    $inq_message = trim(filter_input(INPUT_POST, 'inq_message', FILTER_SANITIZE_STRING)); // Basic sanitization

    $buyer_id = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : null;

    // Basic Validation
    if (empty($property_id) || empty($seller_id) || empty($inq_name) || empty($inq_email) || empty($inq_message)) {
        // Handle validation error - redirect back with an error message
        header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=error&msg=" . urlencode("Please fill in all required fields."));
        exit;
    }

    if ($inq_email === false) {
        header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=error&msg=" . urlencode("Invalid email format."));
        exit;
    }
    
    // Prevent seller from inquiring about their own property (double check, although form might be hidden)
    if ($buyer_id !== null && $buyer_id == $seller_id) {
         header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=error&msg=" . urlencode("You cannot send an inquiry for your own listing."));
        exit;
    }


    // Prepare SQL statement to insert inquiry
    $sql = "INSERT INTO inquiries (property_id, buyer_id, seller_id, message, inquiry_date, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), 'new', NOW(), NOW())";
            // Note: submit_inquiry.php doesn't know the 'agent_id' for the property, so it's not included here.
            // The inquiries table schema has agent_id as nullable.

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        // For buyer_id, if it's null, we want to insert SQL NULL, not 0.
        // The 'i' type hint works for null integer values in mysqli bind_param.
        $stmt->bind_param("iiis", $property_id, $buyer_id, $seller_id, $inq_message);

        if ($stmt->execute()) {
            // Success
            header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=success");
            exit;
        } else {
            // Execution error
            header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=error&msg=" . urlencode("Database error: " . $stmt->error));
            exit;
        }
        $stmt->close();
    } else {
        // Statement preparation error
        header("Location: ../PropertyDetail.php?property_id=" . $property_id . "&inquiry_status=error&msg=" . urlencode("Database error: " . $conn->error));
        exit;
    }
    $conn->close();

} else {
    // Not a POST request or form not submitted correctly
    header("Location: ../LandListings.php?error=invalid_access");
    exit;
}
?>
