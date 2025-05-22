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
            <h2>Property Types</h2>
            <table id="customers">
                <tr>
                    <th>Type ID</th>
                    <th>Type Name</th>
                    <th>Description</th>
                    <th>Created Date</th>
                    <th>Updated Date</th>
                    <th>Action</th>
                </tr>
                
                    <?php
                    // The table 'category' was renamed to 'property_types' and 'category_id' to 'property_type_id' in the SQL schema update
                    $sql = "SELECT property_type_id, name, description, created_at, updated_at FROM property_types ORDER BY property_type_id DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($row['property_type_id']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['created_at'] ? date("Y-m-d H:i:s", strtotime($row['created_at'])) : 'N/A'); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['updated_at'] ? date("Y-m-d H:i:s", strtotime($row['updated_at'])) : 'N/A'); ?>
                            </td>
                            <td>
                                <a href="property_type_edit.php?property_type_id=<?php echo $row['property_type_id']; ?>" class="edit-badge"
                                    title="Edit"><i class="ri-pencil-fill"></i></a>
                                <!-- Delete form -->
                                <form action="property_type_process.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this property type? This might affect existing properties.');">
                                    <input type="hidden" name="property_type_id" value="<?php echo $row['property_type_id']; ?>">
                                    <input type="hidden" name="action" value="delete_property_type">
                                    <button type="submit" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>No property types found.</td></tr>";
                    }
                    ?>
                

            </table>

            <div class="add-form">
                <h2>Add New Property Type</h2>
            <form action="property_type_process.php" method="POST">
                <input type="hidden" name="action" value="add_property_type">
                <label for="pt_name">Property Type Name:</label>
                <input type="text" id="pt_name" name="pt_name" placeholder="Enter Property Type Name" required>

                <label for="pt_description">Description:</label>
                <textarea name="pt_description" id="pt_description" cols="30" rows="3" placeholder="Enter Description"></textarea>

                <input type="submit" value="Add Type" name="addPropertyType">
            </form>
            </div>
        </main>

    </div>
</body>

</html>