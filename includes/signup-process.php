<?php
session_start();
include 'config.php'; // Database Connection

if (isset($_POST['signup'])) {
    $fullName = $_POST["fullName"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $plain_password = $_POST["password"];
    $user_type = $_POST["user_type"];

    // Hash the password
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // Validate user_type
    $allowed_user_types = ['buyer', 'seller', 'agent'];
    if (!in_array($user_type, $allowed_user_types)) {
        echo "
            <script>
                alert('Invalid user role selected.');
                window.location.href = '../signup.php'; // Redirect back to the signup page
            </script>
        ";
        exit();
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (`username`, `password`, `full_name`, `email`, `user_type`, `reg_date`) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters: s = string
    $stmt->bind_param("sssss", $username, $hashed_password, $fullName, $email, $user_type);

    if ($stmt->execute()) {
        echo "
            <script>
                alert('Registration successful! Please sign in with your credentials.');
                window.location.href = '../signin.php'; // Redirect to the login page
            </script>
        ";
    } else {
        // Check for specific errors, e.g., duplicate username/email if you have unique constraints
        $error_message = "Registration Failed. Error: " . $stmt->error;
        if ($stmt->errno == 1062) { // Error number for duplicate entry
            $error_message = "Registration Failed. Username or Email already exists.";
        }
        echo "
            <script>
                alert('" . addslashes($error_message) . "');
                window.location.href = '../signup.php'; // Redirect back to the signup page
            </script>
        ";
    }
    $stmt->close();
    $conn->close();
    exit(); // Terminate the script
}
?>
