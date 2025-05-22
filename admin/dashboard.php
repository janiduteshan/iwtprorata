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
      <h2>Dashboard</h2>
      <div class="row">
        <div class="card">
          <h3>Listed Properties</h3>
          <h1>
            <?php
            $sql_props = "SELECT COUNT(*) AS total_properties FROM properties";
            $result_props = $conn->query($sql_props);
            echo ($result_props && $result_props->num_rows > 0) ? $result_props->fetch_assoc()['total_properties'] : 0;
            ?>
          </h1>
          <a href="properties.php">View All</a>
        </div>
        <div class="card">
          <h3>Property Types</h3>
          <h1>
            <?php
            $sql_types = "SELECT COUNT(*) AS total_property_types FROM property_types";
            $result_types = $conn->query($sql_types);
            echo ($result_types && $result_types->num_rows > 0) ? $result_types->fetch_assoc()['total_property_types'] : 0;
            ?>
          </h1>
          <a href="property_types.php">View All</a>
        </div>
        <div class="card">
          <h3>Total Inquiries</h3>
          <h1>
            <?php
            $sql_inq = "SELECT COUNT(*) AS total_inquiries FROM inquiries";
            $result_inq = $conn->query($sql_inq);
            echo ($result_inq && $result_inq->num_rows > 0) ? $result_inq->fetch_assoc()['total_inquiries'] : 0;
            ?>
          </h1>
          <!-- Optional: Link to an inquiries management page if created -->
          <!-- <a href="inquiries.php">View All</a> -->
        </div>
        <div class="card">
          <h3>Registered Users</h3>
          <h1>
            <?php
            $sql = "SELECT COUNT(*) AS total_users FROM users";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $totalRows = $row['total_users'];
              echo $totalRows;

            } else {
              echo "0 results";
            }

            ?>
          </h1>
          <a href="users.php">View All</a>
        </div>
      </div>


    </main>

  </div>
</body>

</html>