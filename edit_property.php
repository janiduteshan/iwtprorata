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

// --- Property ID and Ownership Check ---
if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    header("Location: seller_dashboard.php?message=" . urlencode("Invalid Property ID.") . "&type=error");
    exit;
}
$property_id = (int)$_GET['property_id'];
$seller_id = $_SESSION['userID'];

$property = null;
if (isset($conn)) {
    $sql_prop = "SELECT * FROM properties WHERE property_id = ? AND seller_id = ?";
    $stmt_prop = $conn->prepare($sql_prop);
    if ($stmt_prop) {
        $stmt_prop->bind_param("ii", $property_id, $seller_id);
        $stmt_prop->execute();
        $result_prop = $stmt_prop->get_result();
        if ($result_prop->num_rows === 1) {
            $property = $result_prop->fetch_assoc();
        } else {
            // Property not found or doesn't belong to the seller
            header("Location: seller_dashboard.php?message=" . urlencode("Property not found or access denied.") . "&type=error");
            exit;
        }
        $stmt_prop->close();
    } else {
        header("Location: seller_dashboard.php?message=" . urlencode("Database error.") . "&type=error");
        exit;
    }
} else {
    header("Location: seller_dashboard.php?message=" . urlencode("Database connection error.") . "&type=error");
    exit;
}

echo "<script>document.title = 'Edit Property: " . htmlspecialchars($property['title']) . " - Seller Dashboard';</script>";
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container edit-property-page">
        <h1>Edit Property: <?php echo htmlspecialchars($property['title']); ?></h1>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) && $_GET['type'] == 'error' ? 'error-message' : 'success-message';
            echo "<div class='" . $message_type . "'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>";
        }
        ?>

        <form action="includes/property_actions.php" method="POST" enctype="multipart/form-data" class="property-form">
            <input type="hidden" name="action" value="edit_property">
            <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
            <input type="hidden" name="current_main_image" value="<?php echo htmlspecialchars($property['main_image_url']); ?>">


            <div class="form-group">
                <label for="title">Property Title:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="6" required><?php echo htmlspecialchars($property['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="location_text">Location (Address/Area):</label>
                <input type="text" name="location_text" id="location_text" value="<?php echo htmlspecialchars($property['location_text']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude (Optional):</label>
                    <input type="text" name="latitude" id="latitude" placeholder="e.g., 40.7128" value="<?php echo htmlspecialchars($property['latitude']); ?>">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude (Optional):</label>
                    <input type="text" name="longitude" id="longitude" placeholder="e.g., -74.0060" value="<?php echo htmlspecialchars($property['longitude']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="size_value">Size Value:</label>
                    <input type="number" name="size_value" id="size_value" step="any" value="<?php echo htmlspecialchars($property['size_value']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="size_unit">Size Unit:</label>
                    <select name="size_unit" id="size_unit" required>
                        <option value="acres" <?php echo ($property['size_unit'] == 'acres') ? 'selected' : ''; ?>>Acres</option>
                        <option value="sq_ft" <?php echo ($property['size_unit'] == 'sq_ft') ? 'selected' : ''; ?>>Square Feet</option>
                        <option value="hectares" <?php echo ($property['size_unit'] == 'hectares') ? 'selected' : ''; ?>>Hectares</option>
                        <option value="sq_m" <?php echo ($property['size_unit'] == 'sq_m') ? 'selected' : ''; ?>>Square Meters</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" name="price" id="price" step="0.01" value="<?php echo htmlspecialchars($property['price']); ?>" required>
            </div>

            <div class="form-group">
                <label for="property_type_id">Property Type:</label>
                <select name="property_type_id" id="property_type_id" required>
                    <option value="" disabled>Select Property Type</option>
                    <?php
                    if (isset($conn)) {
                        $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
                        $types_result = $conn->query($sql_types);
                        if ($types_result && $types_result->num_rows > 0) {
                            while ($type_row = $types_result->fetch_assoc()) {
                                $selected = ($property['property_type_id'] == $type_row['property_type_id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($type_row['property_type_id']) . "' $selected>" . htmlspecialchars($type_row['name']) . "</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status:</label>
                 <select name="status" id="status" required>
                    <option value="available" <?php echo ($property['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo ($property['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                    <option value="pending" <?php echo ($property['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>

            <div class="form-group">
                <label for="main_image">Main Image (Optional - leave blank to keep current):</label>
                <?php if (!empty($property['main_image_url'])): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($property['main_image_url']); ?>" alt="Current Main Image" style="max-width: 200px; max-height: 100px; display:block; margin-bottom:10px;"></p>
                <?php endif; ?>
                <input type="file" name="main_image" id="main_image" accept="image/jpeg, image/png, image/gif">
                <small>Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-submit">Update Property</button>
                <a href="seller_dashboard.php" class="btn btn-cancel" style="margin-left:10px;">Cancel</a>
            </div>
        </form>
    </div><!-- /.container -->

    <?php
    // include 'includes/footer.php';
    ?>
</body>
</html>
<style>
/* Using styles from add_property.php if they are similar, or define new ones */
.page-container { padding-top: 20px; padding-bottom: 20px; }
.edit-property-page h1 { margin-bottom: 10px; font-size: 2.2em; color: #333; }
.edit-property-page > p { margin-bottom: 20px; font-size: 1.1em; }

.property-form { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.property-form .form-group { margin-bottom: 20px; }
.property-form .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
.property-form .form-group input[type="text"],
.property-form .form-group input[type="number"],
.property-form .form-group textarea,
.property-form .form-group select,
.property-form .form-group input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1em;
    transition: border-color 0.2s;
}
.property-form .form-group input:focus,
.property-form .form-group textarea:focus,
.property-form .form-group select:focus {
    border-color: #007bff;
    outline: none;
}
.property-form .form-group textarea { resize: vertical; min-height: 100px; }
.property-form .form-group small { display: block; margin-top: 5px; color: #777; font-size: 0.9em; }

.property-form .form-row { display: flex; flex-wrap: wrap; gap: 20px; }
.property-form .form-row .form-group { flex: 1 1 calc(50% - 10px); } /* Adjust basis for spacing */

.property-form .btn-submit { background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; transition: background-color 0.2s; }
.property-form .btn-submit:hover { background-color: #218838; }
.property-form .btn-cancel { background-color: #6c757d; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; text-decoration:none; display:inline-block;}
.property-form .btn-cancel:hover { background-color: #5a6268; }


.success-message { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; }
.error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px; }

@media (max-width: 600px) {
    .property-form .form-row .form-group {
        flex-basis: 100%;
    }
}
</style>
