<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include 'includes/header.php';
    session_start(); // Start the session
    ?>
</head>

<body>
    <!-- Header Section -->
    <header>
        <?php
            include 'includes/menu.php';
        ?>
    </header>

    <!-- Hero Section -->
    <section class="my hero-section">
      
    <h1>Find Your Perfect Land</h1>
    <p>Discover your ideal plot with our comprehensive listings. Whether for agriculture, residence, or investment, your search starts here.</p>
    <a href="LandListings.php" class="btn">Find Land Now</a>

    </section>

    <!-- Search Bar Section -->
    <section class="search-bar-section">
        <div class="container">
            <h2>Search for Land</h2>
            <form action="LandListings.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" placeholder="Enter city, area, or zip code">
                </div>
                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select name="property_type" id="property_type">
                        <option value="">Any Type</option>
                        <?php
                        // Ensure config.php is included. It's in header.php, but good practice for direct db ops.
                        // include_once 'includes/config.php'; // Already included via header.php
                        
                        // Check if $conn is available from includes/header.php which includes config.php
                        if (isset($conn)) {
                            $sql_types = "SELECT property_type_id, name FROM property_types ORDER BY name ASC";
                            $types_result = $conn->query($sql_types);
                            if ($types_result && $types_result->num_rows > 0) {
                                while ($type_row = $types_result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($type_row['property_type_id']) . "'>" . htmlspecialchars($type_row['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>Could not fetch types</option>";
                            }
                        } else {
                            echo "<option value=''>DB connection error</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_size">Min Size (acres)</label>
                    <input type="number" name="min_size" id="min_size" placeholder="e.g., 1">
                </div>
                <div class="form-group">
                    <label for="max_size">Max Size (acres)</label>
                    <input type="number" name="max_size" id="max_size" placeholder="e.g., 100">
                </div>
                <div class="form-group">
                    <label for="min_price">Min Price ($)</label>
                    <input type="number" name="min_price" id="min_price" placeholder="e.g., 10000">
                </div>
                <div class="form-group">
                    <label for="max_price">Max Price ($)</label>
                    <input type="number" name="max_price" id="max_price" placeholder="e.g., 500000">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn search-btn">Search</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Listings Section -->
    <section class="featured-listings-section">
        <div class="container">
            <h2>Featured Properties</h2>
            <div class="listings-grid">
                <?php
                if (isset($conn)) {
                    // Fetch properties with a main image, ordered by date, limited to 4
                    $sql_featured = "SELECT property_id, title, location_text, price, size_value, size_unit, main_image_url 
                                     FROM properties 
                                     WHERE status = 'available' AND main_image_url IS NOT NULL AND main_image_url != ''
                                     ORDER BY date_listed DESC 
                                     LIMIT 4";
                    $featured_result = $conn->query($sql_featured);

                    if ($featured_result && $featured_result->num_rows > 0) {
                        while ($prop = $featured_result->fetch_assoc()) {
                            echo "<div class='listing-card'>";
                            echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "'>";
                            // Assuming main_image_url is a relative path to an uploads folder or a full URL
                            // For now, ensure a placeholder if image is missing, or structure CSS to handle it
                            $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png'; // Adjust placeholder path as needed
                            echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='listing-image'>";
                            echo "<div class='listing-details'>";
                            echo "<h3>" . htmlspecialchars($prop['title']) . "</h3>";
                            echo "<p class='location'>" . htmlspecialchars($prop['location_text']) . "</p>";
                            echo "<p class='price'>$" . number_format($prop['price'], 2) . "</p>";
                            echo "<p class='size'>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                            echo "</div>"; // end listing-details
                            echo "</a>";
                            echo "</div>"; // end listing-card
                        }
                    } else {
                        echo "<p>No featured properties available at the moment.</p>";
                    }
                } else {
                    echo "<p>Database connection error. Cannot load featured properties.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2>What Our Clients Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p>"A fantastic platform with a wide variety of land options. Found exactly what I was looking for in just a few clicks!"</p>
                    <h4>- Alex P., Happy Landowner</h4>
                </div>
                <div class="testimonial-card">
                    <p>"The search filters are incredibly helpful, and the detailed listings made my decision-making process much easier. Highly recommended!"</p>
                    <h4>- Sarah M., Real Estate Developer</h4>
                </div>
                <div class="testimonial-card">
                    <p>"Transparent, reliable, and user-friendly. This is the go-to place for anyone serious about purchasing land."</p>
                    <h4>- John B., Agricultural Investor</h4>
                </div>
            </div>
        </div>
    </section>





        

   
</body>

</html>