<?php
session_start();
include 'includes/config.php'; // Database Connection
include 'includes/header.php'; // Includes HTML head, title, CSS links

// --- Property ID Retrieval ---
if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    // Redirect to listings page or show an error
    // For now, simple error and exit
    // echo "<div class='container'><p class='error-message'>Invalid Property ID specified.</p></div>";
    // include 'includes/footer.php'; // If you have a footer
    // exit;
    // More user-friendly: redirect
    header("Location: LandListings.php?error=invalid_property_id");
    exit;
}
$property_id = (int)$_GET['property_id'];

// --- Fetch Property Details ---
$property = null;
$seller = null;
$property_type_name = '';

if (isset($conn) && $property_id > 0) {
    $sql = "SELECT p.*, pt.name AS property_type_name, u.full_name AS seller_full_name, u.email AS seller_email, u.phone AS seller_phone, u.user_id AS seller_user_id
            FROM properties p
            JOIN property_types pt ON p.property_type_id = pt.property_type_id
            LEFT JOIN users u ON p.seller_id = u.user_id
            WHERE p.property_id = ? AND p.status = 'available'"; // Only show available properties on detail page for general users

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $property = $result->fetch_assoc();
            $property_type_name = $property['property_type_name']; // Already fetched
            // Seller details are part of $property array with alias, e.g., $property['seller_full_name']
        }
        $stmt->close();
    } else {
        // Error in preparing statement
        // Log this error $conn->error
        $property = null; // Ensure property remains null
    }
}

// Update page title dynamically if property is found
if ($property && !empty($property['title'])) {
    echo "<script>document.title = '" . htmlspecialchars($property['title']) . " - Land Listings';</script>";
}

?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container property-detail-page">
        <?php if ($property): ?>
            <!-- Dynamically set page title via JavaScript or pass to header.php -->
            <h1><?php echo htmlspecialchars($property['title']); ?></h1>

            <!-- Image Gallery -->
            <section class="image-gallery-section">
                <div class="main-image">
                    <?php 
                    $main_image_path = !empty($property['main_image_url']) ? htmlspecialchars($property['main_image_url']) : 'assets/images/placeholder_property_large.png';
                    ?>
                    <img src="<?php echo $main_image_path; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" style="width:100%; max-width:700px; height:auto; border-radius:8px;">
                </div>
                <div class="additional-images">
                    <h4>Additional Images:</h4>
                    <?php
                    $sql_images = "SELECT image_url, caption FROM property_images WHERE property_id = ? ORDER BY is_primary_image DESC, image_id ASC";
                    $stmt_images = $conn->prepare($sql_images);
                    if($stmt_images){
                        $stmt_images->bind_param("i", $property_id);
                        $stmt_images->execute();
                        $images_result = $stmt_images->get_result();
                        if ($images_result->num_rows > 0) {
                            echo "<div class='thumbnail-grid'>";
                            while($img_row = $images_result->fetch_assoc()){
                                if ($img_row['image_url'] != $property['main_image_url']) { // Avoid duplicating main image if listed again
                                    echo "<div class='thumbnail-item'>";
                                    echo "<img src='" . htmlspecialchars($img_row['image_url']) . "' alt='" . htmlspecialchars($img_row['caption'] ?: $property['title']) . "' style='width:100px; height:auto; margin:5px; border-radius:4px; cursor:pointer;' onclick='updateMainImage(\"" . htmlspecialchars($img_row['image_url']) . "\")'>";
                                    echo "</div>";
                                }
                            }
                            echo "</div>";
                        } else {
                            echo "<p>No additional images available.</p>";
                        }
                        $stmt_images->close();
                    }
                    ?>
                </div>
            </section>
            <script>
                function updateMainImage(newImageUrl) {
                    document.querySelector('.main-image img').src = newImageUrl;
                }
            </script>

            <!-- Property Description -->
            <section class="property-description-section">
                <h2>Property Description</h2>
                <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
            </section>

            <!-- Key Details Section -->
            <section class="key-details-section">
                <h2>Key Details</h2>
                <ul>
                    <li><strong>Price:</strong> $<?php echo number_format($property['price'], 2); ?></li>
                    <li><strong>Location:</strong> <?php echo htmlspecialchars($property['location_text']); ?></li>
                    <li><strong>Size:</strong> <?php echo htmlspecialchars($property['size_value']); ?> <?php echo htmlspecialchars($property['size_unit']); ?></li>
                    <li><strong>Property Type:</strong> <?php echo htmlspecialchars($property_type_name); ?></li>
                    <li><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($property['status'])); ?></li>
                    <li><strong>Date Listed:</strong> <?php echo date("F j, Y", strtotime($property['date_listed'])); ?></li>
                </ul>
            </section>

            <!-- Map Location -->
            <section class="map-location-section">
                <h2>Map Location</h2>
                <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                    <p>Coordinates: <?php echo htmlspecialchars($property['latitude']); ?>, <?php echo htmlspecialchars($property['longitude']); ?></p>
                    <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($property['latitude']); ?>,<?php echo htmlspecialchars($property['longitude']); ?>" target="_blank" class="btn">View on Google Maps</a>
                    <!-- Basic embedded map as an iframe -->
                    <div style="margin-top: 15px;">
                         <iframe width="100%" height="400" frameborder="0" style="border:0"
                            src="https://maps.google.com/maps?q=<?php echo htmlspecialchars($property['latitude']); ?>,<?php echo htmlspecialchars($property['longitude']); ?>&hl=es;z=14&amp;output=embed" allowfullscreen>
                         </iframe>
                    </div>
                <?php else: ?>
                    <p>Map location data is not available for this property.</p>
                <?php endif; ?>
            </section>

            <!-- Seller Information -->
            <section class="seller-info-section">
                <h2>Seller Information</h2>
                <?php if (!empty($property['seller_full_name'])): ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($property['seller_full_name']); ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($property['seller_email']); ?>"><?php echo htmlspecialchars($property['seller_email']); ?></a></p>
                    <p><strong>Phone:</strong> <?php echo !empty($property['seller_phone']) ? htmlspecialchars($property['seller_phone']) : 'Not available'; ?></p>
                <?php else: ?>
                    <p>Seller information is not available.</p>
                <?php endif; ?>
            </section>

            <!-- Inquiry Form Section -->
            <section class="inquiry-form-section">
                <h2>Contact Seller / Make an Inquiry</h2>
                <?php
                // Display success/error messages for inquiry submission
                if (isset($_GET['inquiry_status'])) {
                    if ($_GET['inquiry_status'] == 'success') {
                        echo "<p class='success-message'>Your inquiry has been submitted successfully!</p>";
                    } elseif ($_GET['inquiry_status'] == 'error') {
                        echo "<p class='error-message'>There was an error submitting your inquiry. Please try again. " . htmlspecialchars($_GET['msg'] ?? '') . "</p>";
                    }
                }

                // Display messages for save/unsave property
                if (isset($_GET['save_status_msg'])) {
                    $message_class = ($_GET['save_status_type'] ?? 'info') == 'error' ? 'error-message' : 'success-message';
                    echo "<p class='" . $message_class . "'>" . htmlspecialchars(urldecode($_GET['save_status_msg'])) . "</p>";
                }


                // Save/Unsave Property Button for Buyers
                if (isset($_SESSION['userID']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'buyer') {
                    $current_user_id = $_SESSION['userID'];
                    $is_currently_saved = false;
                    $stmt_check_save = $conn->prepare("SELECT saved_id FROM saved_properties WHERE user_id = ? AND property_id = ?");
                    if($stmt_check_save){
                        $stmt_check_save->bind_param("ii", $current_user_id, $property_id);
                        $stmt_check_save->execute();
                        $result_check_save = $stmt_check_save->get_result();
                        if ($result_check_save->num_rows > 0) {
                            $is_currently_saved = true;
                        }
                        $stmt_check_save->close();
                    }
                    
                    $save_button_text = $is_currently_saved ? "Unsave Property <i class='ri-heart-fill'></i>" : "Save Property <i class='ri-heart-line'></i>";
                    $save_button_class = $is_currently_saved ? "btn btn-secondary" : "btn btn-primary"; // Or custom classes
                    echo "<div class='property-actions' style='margin-bottom: 20px;'>";
                    echo "<a href='includes/toggle_saved_property.php?property_id=" . $property['property_id'] . "' class='" . $save_button_class . "'>" . $save_button_text . "</a>";
                    echo "</div>";
                }
                
                $can_submit_inquiry = true;
                if (isset($_SESSION['userID']) && $_SESSION['userID'] == $property['seller_id']) {
                    echo "<p>This is your listing. You cannot send an inquiry to yourself.</p>";
                    $can_submit_inquiry = false;
                }

                if ($can_submit_inquiry) :
                ?>
                <form action="includes/submit_inquiry.php" method="POST" class="inquiry-form">
                    <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                    <input type="hidden" name="seller_id" value="<?php echo $property['seller_id']; ?>">
                    
                    <div class="form-group">
                        <label for="inq_name">Your Name:</label>
                        <input type="text" name="inq_name" id="inq_name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_email">Your Email:</label>
                        <input type="email" name="inq_email" id="inq_email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="inq_phone">Your Phone (Optional):</label>
                        <input type="tel" name="inq_phone" id="inq_phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="inq_message">Message:</label>
                        <textarea name="inq_message" id="inq_message" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="submit_inquiry" class="btn">Send Inquiry</button>
                </form>
                <?php endif; ?>
            </section>

        <?php else: ?>
            <p class="error-message">Property not found or is no longer available. Please <a href="LandListings.php">return to listings</a>.</p>
        <?php endif; ?>
    </div> <!-- /.container -->

    <?php
    // include 'includes/footer.php'; // If you have a common footer
    ?>
</body>
</html>
