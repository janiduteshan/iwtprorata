<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  include '../includes/config.php'; // Database Connection
  include '../includes/header.php';
  session_start(); // Start the session
  if (!isset($_SESSION['username']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php"); // Redirect if not admin
    exit();
  }
  $username = $_SESSION['username'];
  ?>
  <link rel="stylesheet" href="assets/admin-style.css">
  <title>Add New User</title>
</head>

<body>

  <div class="admin">
    <header class="admin__header">
      <a href="#" class="logo">
        <h1>Red Rooster Farm</h1>
      </a>
      <div class="toolbar">
        <h3> Hello, <?php echo htmlspecialchars($username); ?></h3>
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
      <h2>Create New User</h2>

      <?php
      if (isset($_SESSION['error_message'])) {
        echo '<p style="color:red;">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']);
      }
      ?>

      <form action="user_process.php" method="POST" class="form-add-property">
        <input type="hidden" name="action" value="create_user">

        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
          <label for="full_name">Full Name:</label>
          <input type="text" id="full_name" name="full_name">
        </div>

        <div class="form-group">
          <label for="address">Address:</label>
          <textarea id="address" name="address"></textarea>
        </div>

        <div class="form-group">
          <label for="city">City:</label>
          <input type="text" id="city" name="city">
        </div>

        <div class="form-group">
          <label for="phone">Phone:</label>
          <input type="text" id="phone" name="phone">
        </div>

        <div class="form-group">
          <label for="user_type">User Type:</label>
          <select id="user_type" name="user_type">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
            <option value="admin">Admin</option>
            <option value="agent">Agent</option>
          </select>
        </div>

        <button type="submit" class="btn-submit">Create User</button>
      </form>
    </main>
  </div>
</body>

</html>
