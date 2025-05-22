<?php
session_start();
// include 'config.php'; // Only needed if saving to DB as an alternative

// Define a recipient email address
define('CONTACT_FORM_RECIPIENT_EMAIL', 'admin@onlinelandsales.com'); // Replace with actual admin email

if (isset($_POST['submit_contact_form'])) {
    // Retrieve and sanitize form data
    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    $message_body = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($subject) || empty($message_body)) {
        header("Location: ../ContactUs.php?status=error&msg=" . urlencode("Please fill in all required fields."));
        exit;
    }

    if ($email === false) {
        header("Location: ../ContactUs.php?status=error&msg=" . urlencode("Invalid email format."));
        exit;
    }

    // Prepare email
    $to = CONTACT_FORM_RECIPIENT_EMAIL;
    $email_subject = "Contact Form Submission: " . $subject;
    
    $email_headers = "From: " . $full_name . " <" . $email . ">\r\n";
    $email_headers .= "Reply-To: " . $email . "\r\n";
    $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion();

    $email_message_body = "You have received a new message from your website contact form.\n\n";
    $email_message_body .= "Here are the details:\n";
    $email_message_body .= "Name: " . $full_name . "\n";
    $email_message_body .= "Email: " . $email . "\n";
    $email_message_body .= "Subject: " . $subject . "\n";
    $email_message_body .= "Message:\n" . $message_body . "\n";

    // Attempt to send the email
    // Note: The mail() function's success is highly dependent on server configuration.
    // It might not work in some local development environments without a configured mail server (e.g., Sendmail, Postfix).
    if (mail($to, $email_subject, $email_message_body, $email_headers)) {
        header("Location: ../ContactUs.php?status=success");
        exit;
    } else {
        // Email sending failed
        // As an alternative or fallback, you might log this to a file or a database table.
        // For now, just show a generic error.
        // error_log("Mail sending failed for contact form submission from: " . $email); // Example logging
        header("Location: ../ContactUs.php?status=error&msg=" . urlencode("Sorry, we could not send your message at this time. Please try again later or contact us directly via phone."));
        exit;
    }

} else {
    // Not a POST request or form not submitted correctly
    header("Location: ../ContactUs.php?status=error&msg=" . urlencode("Invalid form submission."));
    exit;
}
?>
