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
      <!-- Removed Brands, Category, Users links for seller -->
      <li class="menu__item">
        <a class="menu__link" href="../logout.php">Log out</a>
      </li>
    </ul>
  </nav>
        <main class="seller-layout__main"> <!-- Changed class -->
            <h2>My Products</h2> <!-- Changed title -->
            <a href="product-add.php" class="btn" style="background-color: #5cb85c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-bottom: 15px; display: inline-block;">Add New Product</a> <!-- Changed button text and added styling -->
            <table id="customers">
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Product Code</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
                <tr>
                    <?php
                    $sql = "SELECT * FROM product";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            ?>
                        <tr>
                            <td>
                                <?php echo $row['product_id']; ?>
                            </td>
                            <td>
                                <?php echo $row['product_name']; ?>
                            </td>
                            <td><img src="../assets/images/uploads/<?php echo $row['product_image']; ?>" alt="avatar"
                                    style="width: 80px; height: 80px; object-fit: cover;"></td>
                            <td>
                                <?php echo $row['product_price']; ?>
                            </td>
                            <td>
                                <?php echo $row['qty']; ?>
                            </td>
                            <td>
                                <?php echo $row['product_code']; ?>
                            </td>
                            <td>
                                <?php echo $row['brand']; ?>
                            </td>
                            <td>
                                <?php echo $row['category']; ?>
                            </td>
                            <td>
                                <a href="product-edit.php?product_id=<?php echo $row['product_id']; ?>"
                                    title="Edit" style="color: #f0ad4e; margin-right: 5px;"><i class="ri-pencil-fill"></i> Edit</a>
                                <a href="product-process.php?deleteProduct=true&product_id=<?php echo $row['product_id']; ?>"
                                    class="del-badge" title="Delete" style="color: #d9534f;" onclick="return confirm('Are you sure you want to delete this product?');"><i class="ri-delete-bin-7-fill"></i> Delete</a>
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