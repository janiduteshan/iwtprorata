<nav id="nav">
    <div class="container">
        <!-- Logo -->
        <a href="index.php" class="">
            <img class="navbar-brand"  alt="">
        </a>
        <!-- Navigation -->
        <ul class="navbar" id="navbar">
            <li class="navbar-item">
                <a class="navbar-link" href="index.php">Home</a>
            </li>
            <li class="navbar-item">
                <a class="navbar-link" href="shop.php">Shop</a>
            </li>
            <li class="navbar-item">
                <a class="navbar-link" href="new-arrivals.php">New Arrivals</a>
            </li>
          
            </li>
            <li class="navbar-item">
                <a class="navbar-link" href="about.php">About Us</a>
            </li>
            <li class="navbar-item">
                <a class="navbar-link" href="contact.php">Contact Us</a>
            </li>
        </ul>
        <ul class="sign-btn">
            <?php
            // Ensure session is started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
                $username = $_SESSION['username'];
                $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';

                $dashboard_url = 'home.php'; // Default
                switch ($user_type) {
                    case 'admin': $dashboard_url = 'admin/dashboard.php'; break;
                    case 'buyer': $dashboard_url = 'buyer_dashboard.php'; break;
                    case 'seller': $dashboard_url = 'seller_dashboard.php'; break;
                    case 'agent': $dashboard_url = 'agent_dashboard.php'; break;
                    // Default 'user' or empty user_type will go to home.php as defined by $dashboard_url initial value
                }
                ?>
                <li class='navbar-item'>
                    <a class='navbar-link' href='cart.php'><i class='ri-shopping-cart-2-line'></i></a>
                </li>
                <li class='navbar-item'>
                    <a class='navbar-link' href='#'><i class='ri-search-line'></i></a>
                </li>
                <div class="login-menu subnav">
                    <span class='navbar-link'>
                        <?php echo htmlspecialchars($username); ?> <i class="ri-arrow-down-s-line"></i>
                    </span>
                    <ul class="subnav-content">
                        <li><a href="<?php echo $dashboard_url; ?>">Dashboard</a></li>
                        <li><a href="account-setting.php">Account Setting</a></li>
                        <!-- <li><a href="my-orders.php">My Order</a></li> -->
                        <li class="logout"><a href="logout.php"> Log Out</a></li>
                    </ul>
                </div>

                <?php
            } else {
                // User is not logged in
                echo "<li class='navbar-item'>
                        <a class='navbar-link' href='signin.php'><i class='ri-user-line'></i> Login</a>
                      </li>
                      <li class='navbar-item'>
                        <a class='navbar-link' href='signup.php'>Sign Up</a>
                      </li>
                      <li class='navbar-item'>
                        <a class='navbar-link' href='cart.php'><i class='ri-shopping-cart-2-line'></i></a>
                      </li>
                      <li class='navbar-item'>
                        <a class='navbar-link' href='#'><i class='ri-search-line'></i></a>
                      </li>";
            }
            ?>
        </ul>
    </div>
</nav>