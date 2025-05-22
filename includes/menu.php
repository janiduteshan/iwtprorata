<?php
// Ensure session is started at the very beginning if not already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="nav" class="bg-white shadow-md py-2">
    <div class="container mx-auto flex flex-wrap items-center justify-between">
        <!-- Logo -->
        <a href="index.php" class="flex items-center">
            <img class="h-10" src="assets/images/logo_placeholder.png" alt="LandSales Logo">
            <!-- Or Text Logo: <span class="text-xl font-bold text-green-600">LandSales</span> -->
        </a>
        
        <!-- Mobile Menu Toggle Button (hidden on larger screens) -->
        <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <!-- Icon when menu is closed. -->
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
            <!-- Icon when menu is open. -->
            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Navigation Links -->
        <ul class="hidden md:flex items-center space-x-1 md:space-x-4 order-3 md:order-2 w-full md:w-auto" id="navbar-menu">
            <li class="<?php echo ($current_page == 'index.php') ? 'border-b-2 border-green-500' : ''; ?>">
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50" href="index.php">Home</a>
            </li>
            <li class="<?php echo ($current_page == 'LandListings.php') ? 'border-b-2 border-green-500' : ''; ?>">
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50" href="LandListings.php">Land Listings</a>
            </li>
            <li class="<?php echo ($current_page == 'AboutUs.php') ? 'border-b-2 border-green-500' : ''; ?>">
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50" href="AboutUs.php">About Us</a>
            </li>
            <li class="<?php echo ($current_page == 'ContactUs.php') ? 'border-b-2 border-green-500' : ''; ?>">
                <a class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50" href="ContactUs.php">Contact Us</a>
            </li>
        </ul>

        <!-- User/Login Buttons -->
        <ul class="hidden md:flex items-center space-x-2 order-2 md:order-3" id="user-menu">
            <?php
            if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true && isset($_SESSION['userID'])) {
                $username = $_SESSION['username'] ?? 'User';
                $user_type = $_SESSION['user_type'] ?? '';

                $dashboard_url = 'home.php'; 
                $dashboard_page_name = 'home.php';

                switch ($user_type) {
                    case 'admin':
                        $dashboard_url = 'admin/dashboard.php';
                        $dashboard_page_name = 'dashboard.php';
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
                
                if ($user_type == 'seller' || $user_type == 'agent') {
                    echo "<li class='" . (($current_page == 'add_property.php') ? ' ' : '') . "'>"; // Removed active class, button style is enough
                    echo "<a class='px-4 py-2 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-md shadow-sm' href='add_property.php'>List Property</a>";
                    echo "</li>";
                }
                ?>
                <li class="relative">
                    <button id="user-dropdown-button" class='px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 flex items-center'>
                        <?php echo htmlspecialchars($username); ?> <i class="ri-arrow-down-s-line ml-1"></i>
                    </button>
                    <ul id="user-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                        <li class="<?php echo ($current_page == $dashboard_page_name && strpos($_SERVER['REQUEST_URI'], $user_type.'_dashboard') !== false) ? 'bg-gray-100' : ''; echo ($current_page == 'dashboard.php' && $user_type == 'admin' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'bg-gray-100' : ''; ?>">
                            <a href="<?php echo $dashboard_url; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-green-600">Dashboard</a></li>
                        <li class="<?php echo ($current_page == 'account-setting.php') ? 'bg-gray-100' : ''; ?>">
                            <a href="account-setting.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-green-600">Account Settings</a></li>
                        <li><a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-green-600">Log Out</a></li>
                    </ul>
                </li>

                <?php
            } else {
                // User is not logged in
                echo "<li class='" . (($current_page == 'signin.php') ? '' : '') . "'>
                        <a class='px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 flex items-center' href='signin.php'><i class='ri-user-line mr-1'></i> Login</a>
                      </li>";
                echo "<li class='" . (($current_page == 'signup.php') ? '' : '') . "'>
                        <a class='px-4 py-2 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md shadow-sm' href='signup.php'>Sign Up</a>
                      </li>";
            }
            ?>
        </ul>
        <!-- Mobile Menu (initially hidden, shown based on JS) -->
        <div class="md:hidden w-full order-last" id="mobile-menu">
            <ul class="flex flex-col mt-4 space-y-1">
                 <li class="<?php echo ($current_page == 'index.php') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600'; ?>">
                    <a class="block px-3 py-2 rounded-md text-base font-medium" href="index.php">Home</a>
                </li>
                <li class="<?php echo ($current_page == 'LandListings.php') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600'; ?>">
                    <a class="block px-3 py-2 rounded-md text-base font-medium" href="LandListings.php">Land Listings</a>
                </li>
                <li class="<?php echo ($current_page == 'AboutUs.php') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600'; ?>">
                    <a class="block px-3 py-2 rounded-md text-base font-medium" href="AboutUs.php">About Us</a>
                </li>
                <li class="<?php echo ($current_page == 'ContactUs.php') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600'; ?>">
                    <a class="block px-3 py-2 rounded-md text-base font-medium" href="ContactUs.php">Contact Us</a>
                </li>
                <!-- Mobile User/Login Buttons -->
                <?php
                if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true && isset($_SESSION['userID'])) {
                    if ($user_type == 'seller' || $user_type == 'agent') {
                        echo "<li class='pt-2 " . (($current_page == 'add_property.php') ? '' : '') . "'>";
                        echo "<a class='block w-full px-3 py-2 text-center text-base font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-md shadow-sm' href='add_property.php'>List Property</a>";
                        echo "</li>";
                    }
                    echo "<li class='pt-2 border-t border-gray-200 mt-2'><span class='block px-3 py-2 text-base font-medium text-gray-500'>" . htmlspecialchars($username) . "</span></li>";
                    echo "<li class='" . ($current_page == $dashboard_page_name ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600') . "'><a href='" . $dashboard_url . "' class='block px-3 py-2 rounded-md text-base font-medium'>Dashboard</a></li>";
                    echo "<li class='" . ($current_page == 'account-setting.php' ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600') . "'><a href='account-setting.php' class='block px-3 py-2 rounded-md text-base font-medium'>Account Settings</a></li>";
                    echo "<li><a href='logout.php' class='block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-green-600'>Log Out</a></li>";
                } else {
                    echo "<li class='pt-2 border-t border-gray-200 mt-2 " . (($current_page == 'signin.php') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-green-600') . "'>
                            <a class='block px-3 py-2 rounded-md text-base font-medium' href='signin.php'><i class='ri-user-line mr-1'></i> Login</a>
                          </li>";
                    echo "<li class='" . (($current_page == 'signup.php') ? '' : '') . "'>
                            <a class='block w-full mt-1 px-3 py-2 text-center text-base font-medium text-white bg-green-500 hover:bg-green-600 rounded-md shadow-sm' href='signup.php'>Sign Up</a>
                          </li>";
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
<script>
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const navbarMenu = document.getElementById('navbar-menu'); // Main nav links for desktop
    const userMenuDesktop = document.getElementById('user-menu'); // User links for desktop
    const mobileMenu = document.getElementById('mobile-menu'); // Container for mobile links

    mobileMenuButton.addEventListener('click', () => {
        const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true' || false;
        mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
        mobileMenu.classList.toggle('hidden');
        // Toggle icons
        mobileMenuButton.querySelectorAll('svg').forEach(icon => icon.classList.toggle('hidden'));

        // Ensure desktop menus are hidden when mobile menu is triggered to open
        if (!isExpanded) { // if menu is about to open
            navbarMenu.classList.add('hidden');
            userMenuDesktop.classList.add('hidden');
        } else { // if menu is about to close, restore desktop view based on screen size
             if (window.innerWidth >= 768) { // md breakpoint
                navbarMenu.classList.remove('hidden');
                userMenuDesktop.classList.remove('hidden');
             }
        }
    });

    // User dropdown toggle
    const userDropdownButton = document.getElementById('user-dropdown-button');
    const userDropdownMenu = document.getElementById('user-dropdown-menu');

    if (userDropdownButton) {
        userDropdownButton.addEventListener('click', (event) => {
            userDropdownMenu.classList.toggle('hidden');
            event.stopPropagation(); // Prevent click from bubbling to document
        });
    }
    // Close dropdown if clicked outside
    document.addEventListener('click', (event) => {
        if (userDropdownButton && userDropdownMenu && !userDropdownMenu.classList.contains('hidden')) {
            if (!userDropdownButton.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                userDropdownMenu.classList.add('hidden');
            }
        }
    });
    
    // Hide mobile menu and restore desktop menus on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) { // Tailwind's 'md' breakpoint
            mobileMenu.classList.add('hidden');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
            mobileMenuButton.querySelectorAll('svg').forEach(icon => {
                if (icon.classList.contains('block')) icon.classList.remove('hidden'); // Show hamburger
                else icon.classList.add('hidden'); // Hide close
            });
            navbarMenu.classList.remove('hidden'); // Show desktop main nav
            userMenuDesktop.classList.remove('hidden'); // Show desktop user nav
        } else {
            // If mobile menu was not explicitly closed before resize and is not currently expanded by button click
            if(mobileMenuButton.getAttribute('aria-expanded') !== 'true'){
                navbarMenu.classList.add('hidden');
                userMenuDesktop.classList.add('hidden');
            }
        }
    });
</script>