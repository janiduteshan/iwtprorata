<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    include '../includes/config.php'; // Database Connection
    include '../includes/header.php'; // Includes session_start() and other headers
    if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'admin') {
        header("Location: ../login.php"); // Redirect if not admin
        exit();
    }
    $admin_username = $_SESSION['username'];

    // Fetch property types for the dropdown
    $property_types = [];
    $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
    $result_types = $conn->query($sql_types);
    if ($result_types && $result_types->num_rows > 0) {
        while ($row_type = $result_types->fetch_assoc()) {
            $property_types[] = $row_type;
        }
    }
    ?>
    <link rel="stylesheet" href="assets/admin-style.css">
    <title>Add New Property (Admin)</title>
</head>
<body>
    <div class="admin">
        <header class="admin__header">
            <a href="dashboard.php" class="logo">
                <h1>Red Rooster Farm</h1>
            </a>
            <div class="toolbar">
                <h3>Hello, <?php echo htmlspecialchars($admin_username); ?></h3>
                <a href="../logout.php" class="logout">Log Out</a>
            </div>
        </header>

        <nav class="admin__nav">
            <ul class="menu">
                <li class="menu__item"><a class="menu__link" href="dashboard.php">Dashboard</a></li>
                <li class="menu__item"><a class="menu__link" href="properties.php">Properties</a></li>
                <li class="menu__item"><a class="menu__link" href="property_types.php">Property Types</a></li>
                <li class="menu__item"><a class="menu__link" href="users.php">Users</a></li>
                <li class="menu__item"><a class="menu__link" href="../logout.php">Log out</a></li>
            </ul>
        </nav>

        <main class="admin__main">
            <h2>Add New Property</h2>

            <?php
            if (isset($_GET['message'])) {
                $type = $_GET['type'] ?? 'info';
                $color = ($type == 'error') ? 'red' : 'green';
                echo '<p style="color:' . $color . ';">' . htmlspecialchars($_GET['message']) . '</p>';
            }
            if (isset($_SESSION['form_data'])) {
                $form_data = $_SESSION['form_data'];
                unset($_SESSION['form_data']);
            }
            ?>

            <form action="property_process.php" method="POST" enctype="multipart/form-data" class="form-add-property">
                <input type="hidden" name="action" value="create_property_admin">

                <div class="form-group">
                    <label for="seller_id">Seller ID:</label>
                    <input type="number" id="seller_id" name="seller_id" value="<?php echo htmlspecialchars($form_data['seller_id'] ?? ''); ?>" required>
                    <small>Enter the User ID of the property seller.</small>
                </div>

                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="location_text">Location:</label>
                    <input type="text" id="location_text" name="location_text" value="<?php echo htmlspecialchars($form_data['location_text'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($form_data['price'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="size_value">Size Value:</label>
                    <input type="number" id="size_value" name="size_value" step="any" value="<?php echo htmlspecialchars($form_data['size_value'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="size_unit">Size Unit:</label>
                    <select id="size_unit" name="size_unit" required>
                        <option value="acres" <?php echo (isset($form_data['size_unit']) && $form_data['size_unit'] == 'acres') ? 'selected' : ''; ?>>Acres</option>
                        <option value="sq_ft" <?php echo (isset($form_data['size_unit']) && $form_data['size_unit'] == 'sq_ft') ? 'selected' : ''; ?>>Square Feet</option>
                        <option value="hectares" <?php echo (isset($form_data['size_unit']) && $form_data['size_unit'] == 'hectares') ? 'selected' : ''; ?>>Hectares</option>
                        <option value="sq_m" <?php echo (isset($form_data['size_unit']) && $form_data['size_unit'] == 'sq_m') ? 'selected' : ''; ?>>Square Meters</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="property_type_id">Property Type:</label>
                    <select id="property_type_id" name="property_type_id" required>
                        <option value="">Select Property Type</option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo $type['property_type_id']; ?>" <?php echo (isset($form_data['property_type_id']) && $form_data['property_type_id'] == $type['property_type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="available" <?php echo (isset($form_data['status']) && $form_data['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="pending" <?php echo (isset($form_data['status']) && $form_data['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="sold" <?php echo (isset($form_data['status']) && $form_data['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                        <option value="draft" <?php echo (isset($form_data['status']) && $form_data['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="main_image">Main Image (Optional):</label>
                    <input type="file" id="main_image" name="main_image" accept="image/jpeg, image/png, image/gif">
                </div>

                <button type="submit" class="btn-submit">Add Property</button>
            </form>
        </main>
    </div>
</body>
</html>
