<?php
session_start();
include 'includes/config.php'; // Database Connection
include 'includes/header.php'; // Includes HTML head, title, CSS links

// --- Access Control ---
// Check if user is logged in
if (!isset($_SESSION['userID'])) { // Assuming 'userID' is set upon login
    header("Location: signin.php?error=not_logged_in");
    exit;
}

// Check if user is a 'buyer'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    // Redirect to home or show an access denied message
    // For now, redirect to home
    header("Location: home.php?error=access_denied");
    exit;
}

// Set page title (can be done in header.php if logic is more complex)
echo "<script>document.title = 'Buyer Dashboard - " . htmlspecialchars($_SESSION['username']) . "';</script>";

?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Buyer Dashboard</h1>
        <p class="text-gray-600 mb-8">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>

        <?php
        // Display messages for save/unsave property from toggle_saved_property.php
        if (isset($_GET['save_status_msg'])) {
            $message_type_class = ($_GET['save_status_type'] ?? 'info') == 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
            echo "<div class='p-4 mb-6 text-sm border rounded-lg " . $message_type_class . "'>" . htmlspecialchars(urldecode($_GET['save_status_msg'])) . "</div>";
        }
        ?>

        <!-- My Saved Properties Section -->
        <section class="saved-properties-section mb-12 p-6 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700 border-b pb-3">My Saved Properties</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                if (isset($conn) && isset($_SESSION['userID'])) {
                    $buyer_id = $_SESSION['userID'];
                    $sql_saved = "SELECT p.property_id, p.title, p.location_text, p.price, p.main_image_url, p.size_value, p.size_unit 
                                  FROM properties p 
                                  JOIN saved_properties sp ON p.property_id = sp.property_id 
                                  WHERE sp.user_id = ? 
                                  ORDER BY sp.date_saved DESC";
                    
                    $stmt_saved = $conn->prepare($sql_saved);
                    if ($stmt_saved) {
                        $stmt_saved->bind_param("i", $buyer_id);
                        $stmt_saved->execute();
                        $result_saved = $stmt_saved->get_result();

                        if ($result_saved->num_rows > 0) {
                            while ($prop = $result_saved->fetch_assoc()) {
                                echo "<div class='bg-white rounded-lg shadow-md overflow-hidden flex flex-col transition-transform duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1'>";
                                // Main content link
                                echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "' class='block flex-grow flex flex-col'>";
                                $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png';
                                echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='w-full h-40 object-cover'>"; // Adjusted height for dashboard
                                echo "<div class='p-4 flex-grow flex flex-col'>"; // Adjusted padding
                                echo "<h3 class='text-md font-semibold text-gray-800 mb-1'>" . htmlspecialchars($prop['title']) . "</h3>";
                                echo "<p class='text-xs text-gray-600 mb-1 flex items-center'><i class='ri-map-pin-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['location_text']) . "</p>";
                                if (!empty($prop['size_value']) && !empty($prop['size_unit'])) { 
                                    echo "<p class='text-xs text-gray-600 mb-2 flex items-center'><i class='ri-fullscreen-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                                }
                                echo "<p class='text-lg font-bold text-green-600 mt-auto'>$" . number_format($prop['price'], 2) . "</p>";
                                echo "</div>"; 
                                echo "</a>"; 

                                echo "<div class='p-3 border-t border-gray-200'>";
                                echo "<a href='includes/toggle_saved_property.php?property_id=" . htmlspecialchars($prop['property_id']) . "' class='w-full text-center px-3 py-2 text-xs font-medium rounded-md shadow-sm text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 flex items-center justify-center'><i class='ri-delete-bin-line mr-1'></i> Remove</a>";
                                echo "</div>";
                                echo "</div>"; 
                            }
                        } else {
                            echo "<p class='col-span-full text-center text-gray-500 py-8'>You haven't saved any properties yet.</p>";
                        }
                        $stmt_saved->close();
                    } else {
                        echo "<p class='col-span-full text-center text-red-500 py-8'>Error preparing to fetch saved properties.</p>";
                    }
                } else {
                    echo "<p class='col-span-full text-center text-red-500 py-8'>Could not fetch saved properties. Please ensure you are logged in.</p>";
                }
                ?>
            </div>
        </section>

        <!-- My Inquiries & Messages Section -->
        <section class="inquiries-section p-6 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700 border-b pb-3">My Inquiries & Messages</h2>
            <div class="space-y-4">
                <?php
                if (isset($conn) && isset($_SESSION['userID'])) {
                    $buyer_id = $_SESSION['userID'];
                    $sql_inquiries = "SELECT i.inquiry_id, i.property_id, i.message, i.inquiry_date, i.status, p.title AS property_title 
                                      FROM inquiries i
                                      JOIN properties p ON i.property_id = p.property_id
                                      WHERE i.buyer_id = ? 
                                      ORDER BY i.inquiry_date DESC";
                    
                    $stmt_inquiries = $conn->prepare($sql_inquiries);
                    if ($stmt_inquiries) {
                        $stmt_inquiries->bind_param("i", $buyer_id);
                        $stmt_inquiries->execute();
                        $result_inquiries = $stmt_inquiries->get_result();

                        if ($result_inquiries->num_rows > 0) {
                            while ($inq = $result_inquiries->fetch_assoc()) {
                                $status_color = 'text-gray-600';
                                if ($inq['status'] == 'new') $status_color = 'text-blue-600';
                                if ($inq['status'] == 'read') $status_color = 'text-green-600';
                                if ($inq['status'] == 'replied') $status_color = 'text-purple-600';

                                echo "<div class='p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200'>";
                                echo "<h4 class='text-md font-semibold text-gray-800'><a href='PropertyDetail.php?property_id=" . htmlspecialchars($inq['property_id']) . "' class='hover:text-green-600'>" . htmlspecialchars($inq['property_title']) . "</a></h4>";
                                echo "<p class='text-sm text-gray-600 mt-1 italic'>Your message: \"" . htmlspecialchars(substr($inq['message'], 0, 100)) . (strlen($inq['message']) > 100 ? "..." : "") . "\"</p>";
                                echo "<p class='text-xs text-gray-500 mt-2'>Date: " . date("F j, Y, g:i a", strtotime($inq['inquiry_date'])) . "</p>";
                                echo "<p class='text-sm font-medium mt-1 " . $status_color . "'>Status: " . htmlspecialchars(ucfirst($inq['status'])) . "</p>";
                                echo "</div>"; 
                            }
                        } else {
                            echo "<p class='text-center text-gray-500 py-8'>You haven't made any inquiries yet.</p>";
                        }
                        $stmt_inquiries->close();
                    } else {
                        echo "<p class='text-center text-red-500 py-8'>Error preparing to fetch your inquiries.</p>";
                    }
                } else {
                    echo "<p class='text-center text-red-500 py-8'>Could not fetch inquiries. Please ensure you are logged in.</p>";
                }
                ?>
            </div>
        </section>

    </div> <!-- /.container -->

    <?php
    // include 'includes/footer.php'; // If you have a common footer
    ?>
</body>
</html>
