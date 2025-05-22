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
            <div class="add-form">
                <h2>Edit Property Type</h2>
                <?php
                if (isset($_GET['property_type_id']) && is_numeric($_GET['property_type_id'])) {
                    $property_type_id = (int)$_GET['property_type_id'];

                    // Retrieve the existing property type data from the database
                    $sql = "SELECT * FROM property_types WHERE property_type_id = ?";
                    $stmt = $conn->prepare($sql);
                    
                    if($stmt){
                        $stmt->bind_param("i", $property_type_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            // Display the update form with pre-filled values
                            ?>
                            <form action="property_type_process.php" method="POST">
                                <input type="hidden" name="action" value="edit_property_type">
                                <input type="hidden" name="property_type_id" value="<?php echo htmlspecialchars($row['property_type_id']); ?>">

                                <label for="pt_name">Property Type Name:</label>
                                <input type="text" id="pt_name" name="pt_name" value="<?php echo htmlspecialchars($row['name']); ?>" required>

                                <label for="pt_description">Description:</label>
                                <textarea name="pt_description" id="pt_description" cols="30" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>

                                <input type="submit" value="Update Type" name="editPropertyType">
                            </form>
                            <?php
                        } else {
                            echo "<p>Property Type not found.</p>";
                        }
                        $stmt->close();
                    } else {
                        echo "<p>Error preparing database query.</p>";
                    }
                } else {
                    echo "<p>No Property Type ID specified or ID is invalid.</p>";
                }
                ?>
            </div>
        </main>

    </div>
</body>

</html>