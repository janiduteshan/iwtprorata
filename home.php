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
    <section class="hero-section" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/hero_placeholder.jpg');">
        <div class="container mx-auto px-4 py-16 md:py-24 text-center text-white flex flex-col items-center justify-center min-h-[60vh] md:min-h-[70vh]">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4">Find Your Perfect Land</h1>
            <p class="text-lg sm:text-xl md:text-2xl max-w-2xl mb-8">Discover your ideal plot with our comprehensive listings. Whether for agriculture, residence, or investment, your search starts here.</p>
            <a href="LandListings.php" class="px-8 py-3 bg-yellow-500 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-yellow-600 transition duration-300 text-lg">Find Land Now</a>
        </div>
    </section>

    <!-- Search Bar Section -->
    <section class="search-bar-section bg-gray-100 py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-10 text-gray-800">Search for Land</h2>
            <form action="LandListings.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-6 items-end bg-white p-8 rounded-lg shadow-lg">
                <div class="xl:col-span-2">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" id="location" placeholder="Enter city, area, or zip code"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div class="xl:col-span-1">
                    <label for="property_type" class="block text-sm font-medium text-gray-700 mb-1">Property Type</label>
                    <select name="property_type" id="property_type"
                            class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                        <option value="">Any Type</option>
                        <?php
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
                <div>
                    <label for="min_size" class="block text-sm font-medium text-gray-700 mb-1">Min Size (acres)</label>
                    <input type="number" name="min_size" id="min_size" placeholder="e.g., 1"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div>
                    <label for="max_size" class="block text-sm font-medium text-gray-700 mb-1">Max Size (acres)</label>
                    <input type="number" name="max_size" id="max_size" placeholder="e.g., 100"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div>
                    <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Min Price ($)</label>
                    <input type="number" name="min_price" id="min_price" placeholder="e.g., 10000"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div class="xl:col-span-1">
                    <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
                    <input type="number" name="max_price" id="max_price" placeholder="e.g., 500000"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                <div class="xl:col-span-1">
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Listings Section -->
    <section class="featured-listings-section py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-10 text-gray-800">Featured Properties</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
                            echo "<div class='bg-white rounded-lg shadow-lg overflow-hidden flex flex-col transition-transform duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1'>";
                            echo "<a href='PropertyDetail.php?property_id=" . htmlspecialchars($prop['property_id']) . "' class='block h-full flex flex-col'>";
                            
                            $image_path = !empty($prop['main_image_url']) ? htmlspecialchars($prop['main_image_url']) : 'assets/images/placeholder_property.png';
                            echo "<img src='" . $image_path . "' alt='" . htmlspecialchars($prop['title']) . "' class='w-full h-48 object-cover'>";
                            
                            echo "<div class='p-6 flex-grow flex flex-col'>";
                            echo "<h3 class='text-xl font-semibold text-gray-800 mb-2'>" . htmlspecialchars($prop['title']) . "</h3>";
                            echo "<p class='text-sm text-gray-600 mb-1'><i class='ri-map-pin-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['location_text']) . "</p>";
                            echo "<p class='text-sm text-gray-600 mb-3'><i class='ri-fullscreen-line mr-1 text-green-500'></i>" . htmlspecialchars($prop['size_value']) . " " . htmlspecialchars($prop['size_unit']) . "</p>";
                            echo "<p class='text-2xl font-bold text-green-600 mt-auto'>$" . number_format($prop['price'], 2) . "</p>";
                            echo "</div>"; // end p-6
                            echo "</a>";
                            echo "</div>"; // end card
                        }
                    } else {
                        echo "<p class='col-span-full text-center text-gray-500'>No featured properties available at the moment.</p>";
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