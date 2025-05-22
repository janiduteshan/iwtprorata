<?php
// Ensure session is started at the very beginning if not already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="nav">
    <div class="container">
        <!-- Logo -->
        <a href="index.php" class="logo-link">
            <!-- Assuming you have a logo image or text -->
            <img class="navbar-brand" src="assets/images/logo_placeholder.png" alt="LandSales Logo" style="height: 40px;">
            <!-- Or Text Logo: <span class="logo-text">LandSales</span> -->
        </a>
        <!-- Navigation -->
        <ul class="navbar" id="navbar">
            <li class="navbar-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a class="navbar-link" href="index.php">Home</a>
            </li>
            <li class="navbar-item <?php echo ($current_page == 'LandListings.php') ? 'active' : ''; ?>">
                <a class="navbar-link" href="LandListings.php">Land Listings</a>
            </li>
            <li class="navbar-item <?php echo ($current_page == 'AboutUs.php') ? 'active' : ''; ?>">
                <a class="navbar-link" href="AboutUs.php">About Us</a>
            </li>
            <li class="navbar-item <?php echo ($current_page == 'ContactUs.php') ? 'active' : ''; ?>">
                <a class="navbar-link" href="ContactUs.php">Contact Us</a>
            </li>
        </ul>
        <ul class="sign-btn">
            <?php
            if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true && isset($_SESSION['userID'])) {
                $username = $_SESSION['username'] ?? 'User';
                $user_type = $_SESSION['user_type'] ?? '';

                $dashboard_url = 'home.php'; // Default for 'user' or undefined
                $dashboard_page_name = 'home.php';

                switch ($user_type) {
                    case 'admin':
                        $dashboard_url = 'admin/dashboard.php';
                        $dashboard_page_name = 'dashboard.php'; // To check active state if user is in admin folder
                        break;
                    case 'buyer':
                        $dashboard_url = 'buyer_dashboard.php';
                        $dashboard_page_name = 'buyer_dashboard.php';
                        break;
                    case 'seller':
                        $dashboard_url = 'seller_dashboard.php';
                        $dashboard_page_name = 'seller_dashboard.php';
                        break;
                    case 'agent':
                        $dashboard_url = 'agent_dashboard.php';
                        $dashboard_page_name = 'agent_dashboard.php';
                        break;
                }
                
                // "List Your Property" link for sellers and agents
                if ($user_type == 'seller' || $user_type == 'agent') {
                    echo "<li class='navbar-item list-property-btn " . (($current_page == 'add_property.php') ? 'active' : '') . "'>";
                    echo "<a class='navbar-link btn btn-highlight' href='add_property.php'>List Your Property</a>";
                    echo "</li>";
                }
                ?>
                <div class="login-menu subnav">
                    <span class='navbar-link username-display'>
                        <?php echo htmlspecialchars($username); ?> <i class="ri-arrow-down-s-line"></i>
                    </span>
                    <ul class="subnav-content">
                        <li class="<?php echo ($current_page == $dashboard_page_name && strpos($_SERVER['REQUEST_URI'], $user_type.'_dashboard') !== false) ? 'active' : ''; echo ($current_page == 'dashboard.php' && $user_type == 'admin' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : ''; ?>">
                            <a href="<?php echo $dashboard_url; ?>">Dashboard</a></li>
                        <li class="<?php echo ($current_page == 'account-setting.php') ? 'active' : ''; ?>">
                            <a href="account-setting.php">Account Settings</a></li>
                        <li class="logout"><a href="logout.php">Log Out</a></li>
                    </ul>
                </div>

                <?php
            } else {
                // User is not logged in
                echo "<li class='navbar-item " . (($current_page == 'signin.php') ? 'active' : '') . "'>
                        <a class='navbar-link' href='signin.php'><i class='ri-user-line'></i> Login</a>
                      </li>";
                echo "<li class='navbar-item " . (($current_page == 'signup.php') ? 'active' : '') . "'>
                        <a class='navbar-link btn btn-signup' href='signup.php'>Sign Up</a>
                      </li>";
            }
            ?>
        </ul>
    </div>
</nav>