<?php
session_start();
include 'includes/config.php'; // Database Connection
include 'includes/header.php'; // Includes HTML head, title, CSS links

// --- Access Control ---
if (!isset($_SESSION['userID'])) {
    header("Location: signin.php?error=not_logged_in");
    exit;
}
// Crucially, check for 'agent' user_type
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: home.php?error=access_denied_agent");
    exit;
}

echo "<script>document.title = 'Agent Dashboard - " . htmlspecialchars($_SESSION['username']) . "';</script>";
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container agent-dashboard-page">
        <h1>Agent Dashboard</h1>
        <p>Welcome, Agent <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) && $_GET['type'] == 'error' ? 'error-message' : 'success-message';
            echo "<div class='" . $message_type . "'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>";
        }
        ?>

        <!-- My Property Listings Section (as Agent/Seller) -->
        <section class="dashboard-section my-listings-section">
            <h2>My Property Listings</h2>
            <a href="add_property.php" class="btn btn-primary" style="margin-bottom: 15px; display: inline-block;">+ List a New Property</a>
            <div class="table-responsive">
                <table class="table listings-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date Listed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($conn) && isset($_SESSION['userID'])) {
                            $agent_id_as_seller = $_SESSION['userID']; // Agent is the seller in this context
                            $sql_listings = "SELECT property_id, title, location_text, price, status, date_listed 
                                             FROM properties 
                                             WHERE seller_id = ? 
                                             ORDER BY date_listed DESC";
                            
                            $stmt_listings = $conn->prepare($sql_listings);
                            if ($stmt_listings) {
                                $stmt_listings->bind_param("i", $agent_id_as_seller);
                                $stmt_listings->execute();
                                $result_listings = $stmt_listings->get_result();

                                if ($result_listings->num_rows > 0) {
                                    while ($prop = $result_listings->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($prop['title']) . "</td>";
                                        echo "<td>" . htmlspecialchars($prop['location_text']) . "</td>";
                                        echo "<td>$" . number_format($prop['price'], 2) . "</td>";
                                        echo "<td>" . htmlspecialchars(ucfirst($prop['status'])) . "</td>";
                                        echo "<td>" . date("Y-m-d", strtotime($prop['date_listed'])) . "</td>";
                                        echo "<td class='actions'>";
                                        echo "<a href='edit_property.php?property_id=" . $prop['property_id'] . "' class='btn-edit'>Edit</a> ";
                                        // Form for toggling status
                                        echo "<form action='includes/property_actions.php' method='POST' style='display:inline;'>";
                                        echo "<input type='hidden' name='property_id' value='" . $prop['property_id'] . "'>";
                                        echo "<input type='hidden' name='current_status' value='" . $prop['status'] . "'>";
                                        $button_text = ($prop['status'] == 'available') ? 'Mark Sold' : 'Mark Available';
                                        $button_class = ($prop['status'] == 'available') ? 'btn-status' : 'btn-status btn-available';
                                        echo "<button type='submit' name='action' value='toggle_status' class='" . $button_class . "'>" . $button_text . "</button>";
                                        echo "</form>";
                                        // Optional Delete Button (ensure property_actions.php handles this securely)
                                        echo "<form action='includes/property_actions.php' method='POST' style='display:inline; margin-left: 5px;' onsubmit='return confirm(\"Are you sure you want to delete this property? This action cannot be undone.\");'>";
                                        echo "<input type='hidden' name='property_id' value='" . $prop['property_id'] . "'>";
                                        echo "<button type='submit' name='action' value='delete_property' class='btn-delete'>Delete</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'><p class='no-results'>You have not listed any properties yet.</p></td></tr>";
                                }
                                $stmt_listings->close();
                            } else {
                                echo "<tr><td colspan='6'><p class='no-results'>Error preparing to fetch your properties.</p></td></tr>";
                            }
                        } else {
                             echo "<tr><td colspan='6'><p class='no-results'>Could not fetch properties. Please ensure you are logged in.</p></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Property Inquiries Section (for listings managed by Agent) -->
        <section class="dashboard-section inquiries-received-section">
            <h2>Property Inquiries Received</h2>
            <div class="inquiries-list">
                <?php
                if (isset($conn) && isset($_SESSION['userID'])) {
                    $agent_id_as_seller = $_SESSION['userID'];
                    $sql_inquiries = "SELECT i.inquiry_id, i.property_id, i.message, i.inquiry_date, i.status, 
                                             p.title AS property_title, 
                                             COALESCE(u.full_name, 'Guest User') AS buyer_name, 
                                             COALESCE(u.email, 'N/A') AS buyer_email
                                      FROM inquiries i
                                      JOIN properties p ON i.property_id = p.property_id
                                      LEFT JOIN users u ON i.buyer_id = u.user_id 
                                      WHERE i.seller_id = ? -- Agent is the seller of these properties
                                      ORDER BY i.inquiry_date DESC";
                    
                    $stmt_inquiries = $conn->prepare($sql_inquiries);
                    if ($stmt_inquiries) {
                        $stmt_inquiries->bind_param("i", $agent_id_as_seller);
                        $stmt_inquiries->execute();
                        $result_inquiries = $stmt_inquiries->get_result();

                        if ($result_inquiries->num_rows > 0) {
                            while ($inq = $result_inquiries->fetch_assoc()) {
                                echo "<div class='inquiry-item'>";
                                echo "<h4>Inquiry for: <a href='PropertyDetail.php?property_id=" . htmlspecialchars($inq['property_id']) . "'>" . htmlspecialchars($inq['property_title']) . "</a></h4>";
                                echo "<p><strong>From:</strong> " . htmlspecialchars($inq['buyer_name']) . " (" . htmlspecialchars($inq['buyer_email']) . ")</p>";
                                echo "<p class='message-snippet'>Message: \"" . htmlspecialchars(substr($inq['message'], 0, 150)) . (strlen($inq['message']) > 150 ? "..." : "") . "\"</p>";
                                echo "<p class='inquiry-date'>Received: " . date("F j, Y, g:i a", strtotime($inq['inquiry_date'])) . "</p>";
                                echo "<p class='inquiry-status'>Status: " . htmlspecialchars(ucfirst($inq['status'])) . "</p>";
                                // Optional: Action to mark as read/responded, or reply link
                                // echo "<a href='manage_inquiry.php?inquiry_id=" . $inq['inquiry_id'] . "' class='btn-manage-inquiry'>Manage</a>";
                                echo "</div>"; // end inquiry-item
                            }
                        } else {
                            echo "<p class='no-results'>You have not received any inquiries for your listings yet.</p>";
                        }
                        $stmt_inquiries->close();
                    } else {
                        echo "<p class='no-results'>Error preparing to fetch inquiries.</p>";
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
