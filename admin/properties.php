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
            <h2>Manage Properties</h2>
            <!-- <a href="property_add.php" class="btn">Add Property</a> --> <!-- Admin typically doesn't add, but manages -->
            <table id="customers">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Date Listed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.property_id, p.title, pt.name AS property_type_name, p.location_text, p.price, 
                                   u.username AS seller_username, p.status, p.date_listed
                            FROM properties p
                            JOIN users u ON p.seller_id = u.user_id
                            JOIN property_types pt ON p.property_type_id = pt.property_type_id
                            ORDER BY p.date_listed DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['property_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['property_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['location_text']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['seller_username']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($row['date_listed']))); ?></td>
                            <td>
                                <a href="property_edit.php?property_id=<?php echo $row['property_id']; ?>" class="edit-badge"
                                    title="Edit"><i class="ri-pencil-fill"></i></a>
                                <!-- Delete form -->
                                <form action="property_process.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this property?');">
                                    <input type="hidden" name="property_id" value="<?php echo $row['property_id']; ?>">
                                    <input type="hidden" name="action" value="delete_property_admin">
                                    <button type="submit" class="del-badge" title="Delete"><i class="ri-delete-bin-7-fill"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php
                        }
                    } else {
                        echo "<tr><td colspan='9'>No properties found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>

    </div>
</body>

</html>