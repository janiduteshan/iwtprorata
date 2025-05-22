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
      <h2>Add New User</h2>
      <form action="user-process.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter Username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>

        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" placeholder="Enter Full Name">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Enter Email" required>

        <label for="address">Address:</label>
        <textarea id="address" name="address" placeholder="Enter Address"></textarea>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" placeholder="Enter City">

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" placeholder="Enter Phone">

        <label for="role">Role:</label>
        <select id="role" name="role" required>
          <option value="buyer" selected>Buyer</option>
          <option value="seller">Seller</option>
          <option value="admin">Admin</option>
        </select>

        <input type="submit" value="Add User" name="addUser">
      </form>
    </main>

  </div>
</body>

</html>
