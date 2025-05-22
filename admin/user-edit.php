<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  include '../includes/config.php'; // Database Connection
  include '../includes/header.php';
  session_start(); // Start the session

  // Ensure user is logged in and is an admin, otherwise redirect to signin page
  if (!isset($_SESSION['authenticated']) || $_SESSION['usertype'] !== 'admin') {
      header('Location: ../signin.php');
      exit();
  }
  $admin_username = $_SESSION['username']; // Renamed to avoid conflict

  // Check if user_id is provided
  if (!isset($_GET['id']) || empty($_GET['id'])) {
      echo "<script>
              alert('User ID not provided.');
              window.location.href = 'users.php';
            </script>";
      exit();
  }

  $user_id = mysqli_real_escape_string($conn, $_GET['id']); // Sanitize user_id

  // Fetch user data
  $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) == 1) {
      $user_data = mysqli_fetch_assoc($result);
  } else {
      echo "<script>
              alert('User not found.');
              window.location.href = 'users.php';
            </script>";
      exit();
  }
  ?>
  <link rel="stylesheet" href="assets/admin-style.css">
  <title>Edit User - Admin</title>
</head>

<body>

  <div class="admin">
    <header class="admin__header">
      <a href="#" class="logo">
        <h1>Red Rooster Farm</h1>
      </a>
      <div class="toolbar">
      <h3> Hello, <?php echo htmlspecialchars($admin_username); ?></h3>
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
      <h2>Edit User: <?php echo htmlspecialchars($user_data['username']); ?></h2>
      <form action="user-process.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_data['user_id']); ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>

        <label for="address">Address:</label>
        <textarea id="address" name="address"><?php echo htmlspecialchars($user_data['address']); ?></textarea>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_data['city']); ?>">

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>">

        <label for="role">Role:</label>
        <select id="role" name="role" required>
          <option value="buyer" <?php echo ($user_data['role'] == 'buyer') ? 'selected' : ''; ?>>Buyer</option>
          <option value="seller" <?php echo ($user_data['role'] == 'seller') ? 'selected' : ''; ?>>Seller</option>
          <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>

        <input type="submit" value="Update User" name="updateUser">
      </form>
    </main>

  </div>
</body>

</html>
