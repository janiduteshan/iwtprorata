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
            <h2>Registered Users</h2>
            <a href="user_add.php" class="btn-add-new">Create New User</a>
            <br>
            <table id="customers">
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Full Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Phone</th>
                    <th>User Type</th>
                    <th>Reg Date</th>
                    <th>Action</th>
                </tr>
                
                    <?php
                    // Ensure $conn is available from config.php
                    if (!isset($conn)) {
                        // This should not happen if includes are correct
                        echo "<tr><td colspan='9'>Database connection error.</td></tr>";
                    } else {
                        $current_admin_id = $_SESSION['userID'] ?? 0; // Get current admin's ID
                        $sql = "SELECT user_id, username, email, full_name, address, city, phone, user_type, reg_date FROM users ORDER BY user_id ASC";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['address'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['city'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                <td>
                                    <form action="user_process.php" method="POST" class="user-role-form">
                                        <input type="hidden" name="action" value="update_user_role">
                                        <input type="hidden" name="user_id_to_update" value="<?php echo $row['user_id']; ?>">
                                        <select name="new_user_type" <?php if ($row['user_id'] == $current_admin_id) echo 'disabled'; ?>>
                                            <option value="buyer" <?php if ($row['user_type'] == 'buyer') echo 'selected'; ?>>Buyer</option>
                                            <option value="seller" <?php if ($row['user_type'] == 'seller') echo 'selected'; ?>>Seller</option>
                                            <option value="agent" <?php if ($row['user_type'] == 'agent') echo 'selected'; ?>>Agent</option>
                                            <option value="admin" <?php if ($row['user_type'] == 'admin') echo 'selected'; ?>>Admin</option>
                                        </select>
                                </td>
                                <td><?php echo htmlspecialchars($row['reg_date'] ? date("Y-m-d H:i", strtotime($row['reg_date'])) : 'N/A'); ?></td>
                                <td>
                                    <?php if ($row['user_id'] != $current_admin_id): ?>
                                        <button type="submit" class="btn-update-role">Update Role</button>
                                    <?php else: ?>
                                        <span>(Current Admin)</span>
                                    <?php endif; ?>
                                    </form>
                                    <?php if ($row['user_id'] != $current_admin_id): ?>
                                    <form action="user_process.php" method="POST" class="user-delete-form" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id_to_delete" value="<?php echo $row['user_id']; ?>">
                                        <button type="submit" class="btn-delete-user">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                            }
                        } else {
                            echo "<tr><td colspan='10'>No users found.</td></tr>";
                        }
                    } // end of $conn check
                    ?>
                

            </table>
        </main>
    </div>
</body>

</html>