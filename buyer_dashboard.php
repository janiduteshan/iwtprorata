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

    <div class="container page-container buyer-dashboard-page">
        <h1>Buyer Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>

        <?php
        // Display messages for save/unsave property from toggle_saved_property.php
        if (isset($_GET['save_status_msg'])) {
            $message_class = ($_GET['save_status_type'] ?? 'info') == 'error' ? 'error-message' : 'success-message';
            echo "<div class='" . $message_class . "' style='margin-bottom: 15px;'>" . htmlspecialchars(urldecode($_GET['save_status_msg'])) . "</div>";
        }
        ?>

        <!-- My Saved Properties Section -->
        <section class="dashboard-section saved-properties-section">
            <h2>My Saved Properties</h2>
            <div class="listings-grid">
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
                                echo "<div class='listing-card'>";
                                echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "'>";
                                $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png';
                                echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='listing-image'>";
                                echo "<div class='listing-details'>";
                                echo "<h3>" . htmlspecialchars($prop['title']) . "</h3>";
                                echo "<p class='location'>" . htmlspecialchars($prop['location_text']) . "</p>";
                                echo "<p class='price'>$" . number_format($prop['price'], 2) . "</p>";
                                // echo "<p class='size'>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                                echo "</div>"; // end listing-details
                                // Add Remove from Saved button
                                echo "<div class='listing-actions' style='padding: 0 15px 15px;'>";
                                echo "<a href='includes/toggle_saved_property.php?property_id=" . htmlspecialchars($prop['property_id']) . "' class='btn btn-danger btn-sm btn-remove-saved'><i class='ri-delete-bin-line'></i> Remove</a>";
                                echo "</div>";
                                echo "</a>"; // This a tag wraps the main content for navigation to detail page
                                echo "</div>"; // end listing-card
                            }
                        } else {
                            echo "<p class='no-results'>You haven't saved any properties yet.</p>";
                        }
                        $stmt_saved->close();
                    } else {
                        echo "<p class='no-results'>Error preparing to fetch saved properties.</p>";
                        // Log error: $conn->error;
                    }
                } else {
                    echo "<p class='no-results'>Could not fetch saved properties. Please ensure you are logged in.</p>";
                }
                ?>
            </div>
        </section>

        <!-- My Inquiries & Messages Section -->
        <section class="dashboard-section inquiries-section">
            <h2>My Inquiries & Messages</h2>
            <div class="inquiries-list">
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
                                echo "<div class='inquiry-item'>";
                                echo "<h4><a href='PropertyDetail.php?property_id=" . htmlspecialchars($inq['property_id']) . "'>" . htmlspecialchars($inq['property_title']) . "</a></h4>";
                                echo "<p class='message-snippet'>Your message: \"" . htmlspecialchars(substr($inq['message'], 0, 100)) . (strlen($inq['message']) > 100 ? "..." : "") . "\"</p>";
                                echo "<p class='inquiry-date'>Date: " . date("F j, Y, g:i a", strtotime($inq['inquiry_date'])) . "</p>";
                                echo "<p class='inquiry-status'>Status: " . htmlspecialchars(ucfirst($inq['status'])) . "</p>";
                                // Optional: Link to view full inquiry/conversation later
                                // echo "<a href='view_inquiry.php?inquiry_id=" . $inq['inquiry_id'] . "' class='btn-view-inquiry'>View Details</a>";
                                echo "</div>"; // end inquiry-item
                            }
                        } else {
                            echo "<p class='no-results'>You haven't made any inquiries yet.</p>";
                        }
                        $stmt_inquiries->close();
                    } else {
                        echo "<p class='no-results'>Error preparing to fetch your inquiries.</p>";
                        // Log error: $conn->error;
                    }
                } else {
                    echo "<p class='no-results'>Could not fetch inquiries. Please ensure you are logged in.</p>";
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
