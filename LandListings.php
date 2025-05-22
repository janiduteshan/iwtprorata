<?php
session_start();
include 'includes/config.php'; // Database Connection
include 'includes/header.php'; // Includes HTML head, title, CSS links
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container">
        <h1>Available Land Properties</h1>

        <?php
        // Display messages for save/unsave property from toggle_saved_property.php
        if (isset($_GET['save_status_msg'])) {
            $message_class = ($_GET['save_status_type'] ?? 'info') == 'error' ? 'error-message' : 'success-message';
            echo "<div class='" . $message_class . "' style='margin-bottom: 15px;'>" . htmlspecialchars(urldecode($_GET['save_status_msg'])) . "</div>";
        }
        ?>

        <!-- Search and Filter Form -->
        <section class="filters-section bg-gray-100 p-6 rounded-lg shadow-lg mb-8">
            <form action="LandListings.php" method="GET" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location:</label>
                        <input type="text" name="location" id="location" placeholder="City, area..." value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="property_type" class="block text-sm font-medium text-gray-700 mb-1">Property Type:</label>
                        <select name="property_type" id="property_type"
                                class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="">Any Type</option>
                            <?php
                            if (isset($conn)) {
                                $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
                                $types_result = $conn->query($sql_types);
                                if ($types_result && $types_result->num_rows > 0) {
                                    while ($type_row = $types_result->fetch_assoc()) {
                                        $selected = (isset($_GET['property_type']) && $_GET['property_type'] == $type_row['property_type_id']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($type_row['property_type_id']) . "' $selected>" . htmlspecialchars($type_row['name']) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="min_size" class="block text-sm font-medium text-gray-700 mb-1">Min Size (acres):</label>
                        <input type="number" name="min_size" id="min_size" placeholder="e.g., 1" value="<?php echo htmlspecialchars($_GET['min_size'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="max_size" class="block text-sm font-medium text-gray-700 mb-1">Max Size (acres):</label>
                        <input type="number" name="max_size" id="max_size" placeholder="e.g., 100" value="<?php echo htmlspecialchars($_GET['max_size'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Min Price ($):</label>
                        <input type="number" name="min_price" id="min_price" placeholder="e.g., 10000" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Max Price ($):</label>
                        <input type="number" name="max_price" id="max_price" placeholder="e.g., 500000" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>"
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort By:</label>
                        <select name="sort_by" id="sort_by" onchange="this.form.submit()"
                                class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                            <option value="date_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'date_desc') ? 'selected' : ''; ?>>Latest</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="size_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'size_asc') ? 'selected' : ''; ?>>Size: Small to Large</option>
                            <option value="size_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'size_desc') ? 'selected' : ''; ?>>Size: Large to Small</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Property Listings -->
        <section class="property-listings-section py-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                if (isset($conn)) {
                    $base_sql = "SELECT p.property_id, p.title, p.location_text, p.price, p.size_value, p.size_unit, p.main_image_url, pt.name as property_type_name 
                                 FROM properties p 
                                 JOIN property_types pt ON p.property_type_id = pt.property_type_id 
                                 WHERE p.status = 'available'";
                    
                    $conditions = [];
                    $params = [];
                    $types = ""; // For bind_param type string

                    // Location filter
                    if (!empty($_GET['location'])) {
                        $conditions[] = "(p.location_text LIKE ? OR p.title LIKE ?)";
                        $location_param = "%" . $_GET['location'] . "%";
                        $params[] = $location_param;
                        $params[] = $location_param;
                        $types .= "ss";
                    }

                    // Property Type filter
                    if (!empty($_GET['property_type'])) {
                        $conditions[] = "p.property_type_id = ?";
                        $params[] = $_GET['property_type'];
                        $types .= "i";
                    }

                    // Min Size filter
                    if (!empty($_GET['min_size'])) {
                        $conditions[] = "p.size_value >= ?";
                        $params[] = $_GET['min_size'];
                        $types .= "d"; // Assuming size_value is decimal/double
                    }
                    // Max Size filter
                    if (!empty($_GET['max_size'])) {
                        $conditions[] = "p.size_value <= ?";
                        $params[] = $_GET['max_size'];
                        $types .= "d";
                    }

                    // Min Price filter
                    if (!empty($_GET['min_price'])) {
                        $conditions[] = "p.price >= ?";
                        $params[] = $_GET['min_price'];
                        $types .= "d"; // Assuming price is decimal/double
                    }
                    // Max Price filter
                    if (!empty($_GET['max_price'])) {
                        $conditions[] = "p.price <= ?";
                        $params[] = $_GET['max_price'];
                        $types .= "d";
                    }

                    if (count($conditions) > 0) {
                        $base_sql .= " AND " . implode(" AND ", $conditions);
                    }

                    // Sorting logic
                    $sort_by = $_GET['sort_by'] ?? 'date_desc';
                    $order_by_sql = " ORDER BY ";
                    switch ($sort_by) {
                        case 'price_asc': $order_by_sql .= "p.price ASC"; break;
                        case 'price_desc': $order_by_sql .= "p.price DESC"; break;
                        case 'size_asc': $order_by_sql .= "p.size_value ASC"; break;
                        case 'size_desc': $order_by_sql .= "p.size_value DESC"; break;
                        case 'date_desc':
                        default: $order_by_sql .= "p.date_listed DESC"; break;
                    }
                    $base_sql .= $order_by_sql;

                    $stmt = $conn->prepare($base_sql);

                    if ($stmt) {
                        if (!empty($types) && count($params) > 0) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($prop = $result->fetch_assoc()) {
                                echo "<div class='bg-white rounded-lg shadow-lg overflow-hidden flex flex-col transition-transform duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1'>";
                                // Main content link
                                echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "' class='block flex-grow flex flex-col'>";
                                $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png';
                                echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='w-full h-48 object-cover'>";
                                echo "<div class='p-5 flex-grow flex flex-col'>"; // p-5 for padding
                                echo "<h3 class='text-lg font-semibold text-gray-800 mb-1'>" . htmlspecialchars($prop['title']) . "</h3>";
                                echo "<p class='text-xs text-gray-500 mb-2'>" . htmlspecialchars($prop['property_type_name']) . "</p>";
                                echo "<p class='text-sm text-gray-600 mb-1 flex items-center'><i class='ri-map-pin-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['location_text']) . "</p>";
                                echo "<p class='text-sm text-gray-600 mb-3 flex items-center'><i class='ri-fullscreen-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                                echo "<p class='text-xl font-bold text-green-600 mt-auto'>$" . number_format($prop['price'], 2) . "</p>";
                                echo "</div>"; // end p-5
                                echo "</a>"; // End main content link

                                // Save/Unsave button for logged-in buyers - Placed outside the main <a> for independent action
                                if (isset($_SESSION['userID']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer') {
                                    $current_user_id_for_save = $_SESSION['userID'];
                                    $property_id_for_save = $prop['property_id'];
                                    $is_currently_saved_list = false;
                                    
                                    $stmt_check_save_list = $conn->prepare("SELECT saved_id FROM saved_properties WHERE user_id = ? AND property_id = ?");
                                    if($stmt_check_save_list) {
                                        $stmt_check_save_list->bind_param("ii", $current_user_id_for_save, $property_id_for_save);
                                        $stmt_check_save_list->execute();
                                        $result_check_save_list = $stmt_check_save_list->get_result();
                                        if ($result_check_save_list->num_rows > 0) {
                                            $is_currently_saved_list = true;
                                        }
                                        $stmt_check_save_list->close();
                                    }
                                    
                                    $save_button_text_list = $is_currently_saved_list ? "<i class='ri-heart-fill mr-1'></i>Unsave" : "<i class='ri-heart-line mr-1'></i>Save";
                                    $save_button_class_list = $is_currently_saved_list ? 
                                        "w-full text-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400" : 
                                        "w-full text-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400";
                                    
                                    echo "<div class='p-4 border-t border-gray-200'>"; // Separator for action button
                                    echo "<a href='includes/toggle_saved_property.php?property_id=" . $property_id_for_save . "' class='" . $save_button_class_list . " flex items-center justify-center'>" . $save_button_text_list . "</a>";
                                    echo "</div>";
                                }
                                echo "</div>"; // end card
                            }
                        } else {
                            echo "<p class='col-span-full text-center text-gray-500 py-10'>No properties found matching your criteria. Try broadening your search.</p>";
                        }
                        $stmt->close();
                    } else {
                        echo "<p class='col-span-full text-center text-red-500 py-10'>Error preparing your search query. Please try again later.</p>";
                        // Log error: $conn->error;
                    }
                } else {
                    echo "<p class='col-span-full text-center text-red-500 py-10'>Database connection error. Cannot load properties.</p>";
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
