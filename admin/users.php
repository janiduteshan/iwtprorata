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
        <a class="menu__link" href="products.php">Products</a>
      </li>
      <li class="menu__item">
        <a class="menu__link" href="brands.php">Brands</a>
      </li>
      <li class="menu__item">
        <a class="menu__link" href="category.php">Category</a>
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
            <div style="margin-bottom: 20px;">
                <a href="user-add.php" class="btn" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px;">Add New User</a>
            </div>
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
                    <th>Role</th>
                    <th>User Type (Legacy)</th>
                    <th>Reg Date</th>
                    <th>Actions</th>
                </tr>
                <tr>
                    <?php
                    $sql = "SELECT * FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            ?>
                        <tr>
                            <td>
                                <?php echo $row['user_id']; ?>
                            </td>
                            <td>
                                <?php echo $row['username']; ?>
                            </td>
                            <td>
                                <?php echo $row['email']; ?>
                            </td>
                            <td>
                                <?php echo $row['full_name']; ?>
                            </td>
                            <td>
                                <?php echo $row['address']; ?>
                            </td>
                            <td>
                                <?php echo $row['city']; ?>
                            </td>
                            <td>
                                <?php echo $row['phone']; ?>
                            </td>
                            <td>
                                <?php echo $row['role']; ?>
                            </td>
                            <td>
                                <?php echo $row['user_type']; ?>
                            </td>
                            <td>
                                <?php echo $row['reg_date']; ?>
                            </td>
                            <td>
                                <a href="user-edit.php?id=<?php echo $row['user_id']; ?>" class="btn-edit" style="margin-right: 5px;">Edit</a>
                                <a href="user-process.php?deleteUser=true&id=<?php echo $row['user_id']; ?>" 
                                   class="btn-delete" 
                                   style="background-color: #f44336; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;"
                                   onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                        <?php
                        }
                    } else {
                        echo "0 results";
                    }

                    ?>
                </tr>

            </table>
        </main>

    </div>
</body>

</html>