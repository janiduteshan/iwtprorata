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

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Agent Dashboard</h1>
        <p class="text-gray-600 mb-8">Welcome, Agent <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            $message_type_class = isset($_GET['type']) && $_GET['type'] == 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
            echo "<div class='p-4 mb-6 text-sm border rounded-lg " . $message_type_class . "'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>";
        }
        ?>

        <!-- My Property Listings Section (as Agent/Seller) -->
        <section class="my-listings-section mb-12 p-6 bg-white rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-6 border-b pb-3">
                <h2 class="text-2xl font-semibold text-gray-700">My Property Listings</h2>
                <a href="add_property.php" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md shadow-sm">+ List New Property</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Listed</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
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
                                        echo "<tr class='hover:bg-gray-50'>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($prop['title']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($prop['location_text']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>$" . number_format($prop['price'], 2) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars(ucfirst($prop['status'])) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . date("Y-m-d", strtotime($prop['date_listed'])) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 flex items-center'>";
                                        echo "<a href='edit_property.php?property_id=" . $prop['property_id'] . "' class='text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded-md bg-indigo-100 hover:bg-indigo-200 text-xs'>Edit</a>";
                                        
                                        echo "<form action='includes/property_actions.php' method='POST' class='inline-block'>";
                                        echo "<input type='hidden' name='property_id' value='" . $prop['property_id'] . "'>";
                                        echo "<input type='hidden' name='current_status' value='" . $prop['status'] . "'>";
                                        $button_text = ($prop['status'] == 'available') ? 'Mark Sold' : 'Mark Available';
                                        $button_base_class = "px-3 py-1 rounded-md text-xs text-white focus:outline-none focus:ring-2 focus:ring-offset-2";
                                        $button_color_class = ($prop['status'] == 'available') ? 'bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-400' : 'bg-green-500 hover:bg-green-600 focus:ring-green-400';
                                        echo "<button type='submit' name='action' value='toggle_status' class='" . $button_base_class . " " . $button_color_class . "'>" . $button_text . "</button>";
                                        echo "</form>";
                                        
                                        echo "<form action='includes/property_actions.php' method='POST' class='inline-block' onsubmit='return confirm(\"Are you sure you want to delete this property? This action cannot be undone.\");'>";
                                        echo "<input type='hidden' name='property_id' value='" . $prop['property_id'] . "'>";
                                        echo "<button type='submit' name='action' value='delete_property' class='text-red-600 hover:text-red-900 px-3 py-1 rounded-md bg-red-100 hover:bg-red-200 text-xs'>Delete</button>";
                                        echo "</form>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center'>You have not listed any properties yet.</td></tr>";
                                }
                                $stmt_listings->close();
                            } else {
                                echo "<tr><td colspan='6' class='px-6 py-4 whitespace-nowrap text-sm text-red-500 text-center'>Error preparing to fetch your properties.</td></tr>";
                            }
                        } else {
                             echo "<tr><td colspan='6' class='px-6 py-4 whitespace-nowrap text-sm text-red-500 text-center'>Could not fetch properties. Please ensure you are logged in.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Property Inquiries Section (for listings managed by Agent) -->
        <section class="inquiries-received-section p-6 bg-white rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mb-6 text-gray-700 border-b pb-3">Property Inquiries Received</h2>
            <div class="space-y-4">
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
                                $status_color = 'text-gray-600';
                                if ($inq['status'] == 'new') $status_color = 'text-blue-600';
                                if ($inq['status'] == 'read') $status_color = 'text-green-600';
                                if ($inq['status'] == 'replied') $status_color = 'text-purple-600';

                                echo "<div class='p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200'>";
                                echo "<h4 class='text-md font-semibold text-gray-800'>Inquiry for: <a href='PropertyDetail.php?property_id=" . htmlspecialchars($inq['property_id']) . "' class='text-green-600 hover:text-green-700'>" . htmlspecialchars($inq['property_title']) . "</a></h4>";
                                echo "<p class='text-sm text-gray-600 mt-1'><strong>From:</strong> " . htmlspecialchars($inq['buyer_name']) . " (" . htmlspecialchars($inq['buyer_email']) . ")</p>";
                                echo "<p class='text-sm text-gray-600 mt-1 italic'>Message: \"" . htmlspecialchars(substr($inq['message'], 0, 150)) . (strlen($inq['message']) > 150 ? "..." : "") . "\"</p>";
                                echo "<p class='text-xs text-gray-500 mt-2'>Received: " . date("F j, Y, g:i a", strtotime($inq['inquiry_date'])) . "</p>";
                                echo "<p class='text-sm font-medium mt-1 " . $status_color . "'>Status: " . htmlspecialchars(ucfirst($inq['status'])) . "</p>";
                                echo "</div>"; 
                            }
                        } else {
                            echo "<p class='text-center text-gray-500 py-8'>You have not received any inquiries for your listings yet.</p>";
                        }
                        $stmt_inquiries->close();
                    } else {
                        echo "<p class='text-center text-red-500 py-8'>Error preparing to fetch inquiries.</p>";
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
