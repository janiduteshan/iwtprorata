<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  include '../includes/config.php'; // Database Connection
  include '../includes/config.php'; // Database Connection
  include '../includes/header.php';
  session_start(); // Start the session

  // Role check: Ensure user is logged in and is a 'seller'
  if (!isset($_SESSION['authenticated']) || $_SESSION['role'] !== 'seller') {
      header('Location: ../signin.php'); 
      exit();
  }
  $username = $_SESSION['username'];
  ?>
  <link rel="stylesheet" href="assets/seller-style.css"> <!-- Changed to seller-style.css -->
</head>

<body>

  <div class="seller-layout"> <!-- Changed class -->
    <header class="seller-layout__header"> <!-- Changed class -->
      <a href="dashboard.php" class="logo"> <!-- Link to seller dashboard -->
        <h1>Seller - Red Rooster Farm</h1> <!-- Changed title -->
      </a>
      <div class="toolbar">
      <h3> Hello, <?php echo htmlspecialchars($username); ?></h3> <!-- Added htmlspecialchars -->
        <a href="../logout.php" class="logout">
          Log Out
        </a>
      </div>
    </header>
    <nav class="seller-layout__nav"> <!-- Changed class -->
      <ul class="menu">
        <li class="menu__item">
          <a class="menu__link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="menu__item">
          <a class="menu__link" href="products.php">Manage Products</a>
        </li>
        <li class="menu__item">
          <a class="menu__link" href="product-add.php">Add New Product</a>
        </li>
        <li class="menu__item">
          <a class="menu__link" href="../logout.php">Log out</a>
        </li>
      </ul>
    </nav>
    <main class="seller-layout__main"> <!-- Changed class -->
      <h2>Add New Product</h2> <!-- Changed title -->
      <form action="product-process.php" method="POST" enctype="multipart/form-data">
        <label for="pname">Product Name:</label>
        <input type="text" id="pname" name="pname" placeholder="Enter Product Name">

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" placeholder="Enter Product Price">

        <label for="qty">Qty:</label>
        <input type="text" id="qty" name="qty" placeholder="Enter Product Qty">

        <label for="pCode">Product Code:</label>
        <input type="text" id="pCode" name="pCode" placeholder="Enter Product Code">

        <label for="img">Product Image:</label>
        <input type="file" id="img" name="img">


        <label for="brand">Brand:</label>
        <?php
        // Retrieve brands from the database
        $sql_brand = "SELECT * FROM brands";
        $sql_brand_run = $conn->query($sql_brand);

        // Check if there are any brands
        if ($sql_brand_run->num_rows > 0) {
          ?>
          <select name="brand" id="brand">
            <?php
            // Loop through each brand
            while ($row_brand = $sql_brand_run->fetch_assoc()) {
              $brandName = $row_brand['name'];
              ?>
              <option value="<?php echo $brandName; ?>"><?php echo $brandName; ?></option>
              <?php
            }
            ?>
          </select>
          <?php
        } else {
          echo "No brands found.";
        }
        ?>
        <label for="category">Category:</label>
        <?php
        // Retrieve brands from the database
        $sql_brand = "SELECT * FROM category";
        $sql_brand_run = $conn->query($sql_brand);

        // Check if there are any brands
        if ($sql_brand_run->num_rows > 0) {
          ?>
          <select name="category" id="category">
            <?php
            // Loop through each brand
            while ($row_brand = $sql_brand_run->fetch_assoc()) {
              $brandName = $row_brand['name'];
              ?>
              <option value="<?php echo $brandName; ?>"><?php echo $brandName; ?></option>
              <?php
            }
            ?>
          </select>
          <?php
        } else {
          echo "No brands found.";
        }
        ?>

        <input type="submit" value="Add Product" name="addItem"> <!-- Changed button text -->
      </form>
    </main>

  </div>
</body>

</html>