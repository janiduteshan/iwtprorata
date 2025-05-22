<?php
    session_start();
    include 'config.php'; // Database Connection

    if(isset($_POST['login'])) {
        $email = $_POST['email'];
        $password  = $_POST['psw']; // In a real application, verify hashed password!
        
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, email, address, city, phone, user_type FROM users WHERE email = ?");
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password using password_verify()
            if (password_verify($password, $row['password'])) {
                $_SESSION['userID'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['user_type'] = $row['user_type']; // Standardized to user_type
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['address'] = $row['address'];
                $_SESSION['city'] = $row['city'];
                $_SESSION['phone'] = $row['phone'];
                $_SESSION['authenticated'] = true;

                $redirect_url = '../home.php'; // Default redirect
                $user_role = $row['user_type'];

                if ($user_role == 'admin') {
                    $redirect_url = '../admin/dashboard.php';
                } elseif ($user_role == 'buyer') {
                    $redirect_url = '../buyer_dashboard.php';
                } elseif ($user_role == 'seller') {
                    $redirect_url = '../seller_dashboard.php';
                } elseif ($user_role == 'agent') {
                    $redirect_url = '../agent_dashboard.php';
                } elseif ($user_role == 'user' && empty($user_role)) { // Fallback for old 'user' or empty roles
                     $redirect_url = '../home.php';
                }

                echo "
                    <script>
                        alert('Login Successfully');
                        window.location.href = '$redirect_url'; 
                    </script>
                ";
            } else {
                // Password mismatch
                echo "
                    <script>
                        alert('Invalid email or password.');
                        window.location.href = '../signin.php'; 
                    </script>
                ";
            }
        } else {
            // User not found
            echo "
                <script>
                    alert('Invalid email or password.');
                    window.location.href = '../signin.php'; 
                </script>
            ";
        }
        $stmt->close();
        $conn->close();
        exit();
    }


?>