<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  include '../includes/config.php'; // Database Connection
  include '../includes/header.php';
  session_start(); // Start the session
  $username = $_SESSION['username'];
  ?>
  <link rel="stylesheet" href="assets/admin-style.css">
</head>

<body>

  <div class="admin">
    <header class="admin__header">
      <a href="#" class="logo">
        <h1>Red Rooster Farm</h1>
      </a>
      <div class="toolbar">
      <h3> Hello, <?php echo $username; ?></h3>
        <a href="../logout.php" class="logout">
          Log Out
        </a>
      </div>
    </header>
        <nav class="admin__nav">
            <ul class="menu">
                <li class="menu__item">
                    <a class="menu__link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="menu__item">
                    <a class="menu__link" href="properties.php">Properties</a>
                </li>
                <li class="menu__item">
                    <a class="menu__link" href="property_types.php">Property Types</a>
                </li>
                <li class="menu__item">
                    <a class="menu__link" href="users.php">Users</a>
                </li>
                <li class="menu__item">
                    <a class="menu__link" href="../logout.php">Log out</a>
                </li>
            </ul>
        </nav>
        <main class="admin__main">
            <h2>Edit Property</h2>
            <?php
            if (isset($_GET['property_id']) && is_numeric($_GET['property_id'])) {
                $property_id = (int)$_GET['property_id'];

                $sql = "SELECT p.*, u.username AS seller_username 
                        FROM properties p 
                        JOIN users u ON p.seller_id = u.user_id 
                        WHERE p.property_id = ?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("i", $property_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $property = $result->fetch_assoc();
                        ?>
                        <form action="property_process.php" method="POST" enctype="multipart/form-data" class="property-form-admin">
                            <input type="hidden" name="action" value="edit_property_admin">
                            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property['property_id']); ?>">
                            <input type="hidden" name="current_main_image" value="<?php echo htmlspecialchars($property['main_image_url']); ?>">

                            <div class="form-group">
                                <label>Seller:</label>
                                <p><?php echo htmlspecialchars($property['seller_username']); ?> (User ID: <?php echo htmlspecialchars($property['seller_id']); ?>)</p>
                            </div>

                            <div class="form-group">
                                <label for="title">Property Title:</label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="location_text">Location:</label>
                                <input type="text" id="location_text" name="location_text" value="<?php echo htmlspecialchars($property['location_text']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price ($):</label>
                                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($property['price']); ?>" required>
                            </div>
                            
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

                            <div class="form-group">
                                <label for="property_type_id">Property Type:</label>
                                <select name="property_type_id" id="property_type_id" required>
                                    <?php
                                    $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
                                    $types_result = $conn->query($sql_types);
                                    if ($types_result && $types_result->num_rows > 0) {
                                        while ($type_row = $types_result->fetch_assoc()) {
                                            $selected = ($property['property_type_id'] == $type_row['property_type_id']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($type_row['property_type_id']) . "' $selected>" . htmlspecialchars($type_row['name']) . "</option>";
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
                                    <option value="removed" <?php echo ($property['status'] == 'removed') ? 'selected' : ''; ?>>Removed (by Admin)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="main_image">Main Image (Optional - leave blank to keep current):</label>
                                <?php if (!empty($property['main_image_url'])): ?>
                                    <p><img src="../<?php echo htmlspecialchars($property['main_image_url']); ?>" alt="Current Main Image" style="max-width: 200px; max-height:100px; display:block; margin-bottom:10px;"></p>
                                <?php endif; ?>
                                <input type="file" name="main_image" id="main_image" accept="image/jpeg, image/png, image/gif">
                            </div>

                            <input type="submit" value="Update Property" name="editPropertyAdmin" class="btn">
                        </form>
                        <?php
                    } else {
                        echo "<p>Property not found.</p>";
                    }
                    $stmt->close();
                } else {
                     echo "<p>Error preparing database query.</p>";
                }
            } else {
                echo "<p>No Property ID specified or ID is invalid.</p>";
            }
            ?>
        </main>
    </div>
    <style>
        .property-form-admin .form-group { margin-bottom: 15px; }
        .property-form-admin label { display: block; font-weight: bold; margin-bottom: 5px; }
        .property-form-admin input[type="text"],
        .property-form-admin input[type="number"],
        .property-form-admin textarea,
        .property-form-admin select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .property-form-admin textarea { resize: vertical; }
        .property-form-admin .btn { background-color: #007bff; color:white; padding: 10px 15px; border:none; border-radius: 4px; cursor:pointer; }
        .property-form-admin .btn:hover { background-color: #0056b3; }
    </style>
</body>

</html>