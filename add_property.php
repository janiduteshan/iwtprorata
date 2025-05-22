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

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Add New Property</h1>
        <p class="text-gray-600 mb-8">Fill in the details below to list your property.</p>

        <!-- Display Success/Error Messages -->
        <?php
        if (isset($_GET['message'])) {
            $message_type = isset($_GET['type']) && $_GET['type'] == 'error' ? 'text-red-700 bg-red-100 border-red-300' : 'text-green-700 bg-green-100 border-green-300';
            echo "<div class='p-4 mb-6 text-sm border rounded-lg " . $message_type . "'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>";
        }
        ?>

        <form action="includes/property_actions.php" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-8 rounded-lg shadow-lg">
            <input type="hidden" name="action" value="add_property">

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Property Title:</label>
                <input type="text" name="title" id="title" required
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                <textarea name="description" id="description" rows="5" required
                          class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
            </div>

            <div>
                <label for="location_text" class="block text-sm font-medium text-gray-700 mb-1">Location (Address/Area):</label>
                <input type="text" name="location_text" id="location_text" required
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude (Optional):</label>
                    <input type="text" name="latitude" id="latitude" placeholder="e.g., 40.7128"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude (Optional):</label>
                    <input type="text" name="longitude" id="longitude" placeholder="e.g., -74.0060"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="size_value" class="block text-sm font-medium text-gray-700 mb-1">Size Value:</label>
                    <input type="number" name="size_value" id="size_value" step="any" required
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div>
                    <label for="size_unit" class="block text-sm font-medium text-gray-700 mb-1">Size Unit:</label>
                    <select name="size_unit" id="size_unit" required
                            class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                        <option value="acres">Acres</option>
                        <option value="sq_ft">Square Feet</option>
                        <option value="hectares">Hectares</option>
                        <option value="sq_m">Square Meters</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($):</label>
                <input type="number" name="price" id="price" step="0.01" required
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
            </div>

            <div>
                <label for="property_type_id" class="block text-sm font-medium text-gray-700 mb-1">Property Type:</label>
                <select name="property_type_id" id="property_type_id" required
                        class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
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

            <div>
                <label for="main_image" class="block text-sm font-medium text-gray-700 mb-1">Main Image:</label>
                <input type="file" name="main_image" id="main_image" accept="image/jpeg, image/png, image/gif" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <small class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, GIF. Max size: 2MB.</small>
            </div>
            
            <!-- Placeholder for Additional Images - to be implemented if time allows
            <div>
                <label for="additional_images" class="block text-sm font-medium text-gray-700 mb-1">Additional Images (Optional):</label>
                <input type="file" name="additional_images[]" id="additional_images" multiple accept="image/jpeg, image/png, image/gif"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <small class="mt-1 text-xs text-gray-500">You can select multiple images. Max size per image: 2MB.</small>
            </div>
            -->

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    List Property
                </button>
            </div>
        </form>
    </div><!-- /.container -->

    <?php
    // include 'includes/footer.php';
    ?>
</body>
</html>
