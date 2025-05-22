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
        <section class="filters-section">
            <form action="LandListings.php" method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" name="location" id="location" placeholder="City, area..." value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="property_type">Property Type:</label>
                        <select name="property_type" id="property_type">
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="min_size">Min Size (acres):</label>
                        <input type="number" name="min_size" id="min_size" placeholder="e.g., 1" value="<?php echo htmlspecialchars($_GET['min_size'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="max_size">Max Size (acres):</label>
                        <input type="number" name="max_size" id="max_size" placeholder="e.g., 100" value="<?php echo htmlspecialchars($_GET['max_size'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="min_price">Min Price ($):</label>
                        <input type="number" name="min_price" id="min_price" placeholder="e.g., 10000" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="max_price">Max Price ($):</label>
                        <input type="number" name="max_price" id="max_price" placeholder="e.g., 500000" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_by">Sort By:</label>
                        <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                            <option value="date_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'date_desc') ? 'selected' : ''; ?>>Latest</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="size_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'size_asc') ? 'selected' : ''; ?>>Size: Small to Large</option>
                            <option value="size_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'size_desc') ? 'selected' : ''; ?>>Size: Large to Small</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn filter-btn">Apply Filters</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Property Listings -->
        <section class="property-listings-section">
            <div class="listings-grid">
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

                    // Pagination (simple limit for now, full pagination for later)
                    // $base_sql .= " LIMIT 20"; 

                    $stmt = $conn->prepare($base_sql);

                    if ($stmt) {
                        if (!empty($types) && count($params) > 0) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($prop = $result->fetch_assoc()) {
                                echo "<div class='listing-card'>";
                                echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "'>";
                                $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png';
                                echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='listing-image'>";
                                echo "<div class='listing-details'>";
                                echo "<h3>" . htmlspecialchars($prop['title']) . "</h3>";
                                echo "<p class='type'>" . htmlspecialchars($prop['property_type_name']) . "</p>";
                                echo "<p class='location'>" . htmlspecialchars($prop['location_text']) . "</p>";
                                echo "<p class='price'>$" . number_format($prop['price'], 2) . "</p>";
                                echo "<p class='size'>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                                echo "</div>"; // end listing-details
                                // Save/Unsave button for logged-in buyers
                                if (isset($_SESSION['userID']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer') {
                                    $current_user_id_for_save = $_SESSION['userID'];
                                    $property_id_for_save = $prop['property_id'];
                                    $is_currently_saved_list = false;
                                    
                                    // This check inside a loop is not optimal for performance on large lists.
                                    // A better approach would be to fetch all saved property IDs for the user once outside the loop.
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
                                    
                                    $save_button_text_list = $is_currently_saved_list ? "<i class='ri-heart-fill'></i> Unsave" : "<i class='ri-heart-line'></i> Save";
                                    $save_button_class_list = $is_currently_saved_list ? "btn btn-secondary btn-sm btn-save-toggle" : "btn btn-primary btn-sm btn-save-toggle";
                                    echo "<div class='listing-actions' style='padding: 0 15px 15px;'>";
                                    echo "<a href='includes/toggle_saved_property.php?property_id=" . $property_id_for_save . "' class='" . $save_button_class_list . "'>" . $save_button_text_list . "</a>";
                                    echo "</div>";
                                }
                                echo "</a>"; // This <a> tag wraps the card content for navigation
                                echo "</div>"; // end listing-card
                            }
                        } else {
                            echo "<p class='no-results'>No properties found matching your criteria. Try broadening your search.</p>";
                        }
                        $stmt->close();
                    } else {
                        echo "<p class='no-results'>Error preparing your search query. Please try again later.</p>";
                        // Log error: $conn->error;
                    }
                } else {
                    echo "<p class='no-results'>Database connection error. Cannot load properties.</p>";
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
