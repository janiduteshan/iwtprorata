<?php
session_start();
include '../includes/config.php'; // Database Connection

// Role check: Ensure user is logged in and is a 'seller'
if (!isset($_SESSION['authenticated']) || $_SESSION['role'] !== 'seller') {
    // Optionally, set a message for the user
    // $_SESSION['message'] = "Access Denied. You must be logged in as a seller to view this page.";
    
    // Redirect to signin page or home page
    header('Location: ../signin.php'); 
    exit();
}

$seller_username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Red Rooster Farm</title>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="assets/seller-style.css">
    <!-- Removed inline styles to rely on seller-style.css -->
</head>

<body>
    <header class="seller-header">
        <div class="logo">
            <h1>Seller Dashboard - Red Rooster Farm</h1>
        </div>
        <div class="toolbar">
            <h3>Hello, <?php echo htmlspecialchars($seller_username); ?></h3>
            <a href="../logout.php" class="logout">Log Out</a>
        </div>
    </header>

    <div class="seller-container">
        <nav class="seller-nav">
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li> <!-- Added class="active" for current page -->
                <li><a href="products.php">Manage Products</a></li>
                <li><a href="product-add.php">Add New Product</a></li>
                <li><a href="../logout.php">Log out</a></li> <!-- Added Log Out to nav for consistency -->
            </ul>
        </nav>

        <main class="seller-main">
            <h2>Welcome to the Seller Dashboard</h2>
            <p>This is your area to manage your products and sales.</p>
            <!-- Content will go here -->
        </main>
    </div>

</body>
</html>
