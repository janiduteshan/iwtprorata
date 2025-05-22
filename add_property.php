<?php
session_start();
include 'includes/config.php'; // Database Connection
include 'includes/header.php'; // Includes HTML head, title, CSS links

// --- Access Control ---
if (!isset($_SESSION['userID'])) {
    header("Location: signin.php?error=not_logged_in");
    exit;
}
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: home.php?error=access_denied_seller_page");
    exit;
}

echo "<script>document.title = 'Add New Property - Seller Dashboard';</script>";
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container add-property-page">
        <h1>Add New Property</h1>
        <p>Fill in the details below to list your property.</p>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) && $_GET['type'] == 'error' ? 'error-message' : 'success-message';
            echo "<div class='" . $message_type . "'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>";
        }
        ?>

        <form action="includes/property_actions.php" method="POST" enctype="multipart/form-data" class="property-form">
            <input type="hidden" name="action" value="add_property">

            <div class="form-group">
                <label for="title">Property Title:</label>
                <input type="text" name="title" id="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="6" required></textarea>
            </div>

            <div class="form-group">
                <label for="location_text">Location (Address/Area):</label>
                <input type="text" name="location_text" id="location_text" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude (Optional):</label>
                    <input type="text" name="latitude" id="latitude" placeholder="e.g., 40.7128">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude (Optional):</label>
                    <input type="text" name="longitude" id="longitude" placeholder="e.g., -74.0060">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="size_value">Size Value:</label>
                    <input type="number" name="size_value" id="size_value" step="any" required>
                </div>
                <div class="form-group">
                    <label for="size_unit">Size Unit:</label>
                    <select name="size_unit" id="size_unit" required>
                        <option value="acres">Acres</option>
                        <option value="sq_ft">Square Feet</option>
                        <option value="hectares">Hectares</option>
                        <option value="sq_m">Square Meters</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" name="price" id="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="property_type_id">Property Type:</label>
                <select name="property_type_id" id="property_type_id" required>
                    <option value="" disabled selected>Select Property Type</option>
                    <?php
                    if (isset($conn)) {
                        $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
                        $types_result = $conn->query($sql_types);
                        if ($types_result && $types_result->num_rows > 0) {
                            while ($type_row = $types_result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($type_row['property_type_id']) . "'>" . htmlspecialchars($type_row['name']) . "</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="main_image">Main Image:</label>
                <input type="file" name="main_image" id="main_image" accept="image/jpeg, image/png, image/gif" required>
                <small>Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
            </div>
            
            <!-- Placeholder for Additional Images - to be implemented if time allows
            <div class="form-group">
                <label for="additional_images">Additional Images (Optional):</label>
                <input type="file" name="additional_images[]" id="additional_images" multiple accept="image/jpeg, image/png, image/gif">
                <small>You can select multiple images. Max size per image: 2MB.</small>
            </div>
            -->

            <div class="form-group">
                <button type="submit" class="btn btn-submit">List Property</button>
            </div>
        </form>
    </div><!-- /.container -->

    <?php
    // include 'includes/footer.php';
    ?>
</body>
</html>
