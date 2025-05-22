<?php
session_start();
// include 'includes/config.php'; // Not strictly needed for DB for this static page
include 'includes/header.php'; // Includes HTML head, title, CSS links
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container page-container about-us-page">
        <h1>About Our Land Sales Platform</h1>

        <section class="content-section">
            <h2>Our Mission</h2>
            <p>
                Our mission is to provide a transparent, efficient, and user-friendly platform for buying and selling land. 
                We aim to connect sellers with serious buyers and provide all the necessary tools and information 
                to make informed decisions. We believe that acquiring land should be a straightforward process, 
                whether for agricultural use, residential development, or investment purposes.
            </p>
        </section>

        <section class="content-section">
            <h2>Our Vision</h2>
            <p>
                We envision a future where land transactions are simplified and accessible to everyone, everywhere. 
                Our goal is to become the leading online destination for land sales, recognized for our integrity, 
                extensive listings, and commitment to customer satisfaction. We strive to leverage technology 
                to empower our users and contribute to sustainable land development.
            </p>
        </section>
        
        <section class="content-section">
            <h2>Our Story (Optional Placeholder)</h2>
            <p>
                Founded in [Year], Our Land Sales Platform started with a simple idea: to make land transactions easier. 
                Frustrated by the complexities and lack of transparency in the traditional land market, our founders 
                set out to create a better way. Today, we are proud to have helped countless individuals and businesses 
                find their perfect piece of land.
            </p>
        </section>

        <section class="content-section why-choose-us">
            <h2>Why Choose Us?</h2>
            <ul>
                <li><strong>Verified Listings:</strong> We strive to ensure that all listings on our platform are accurate and from reputable sellers/agents.</li>
                <li><strong>Secure Platform:</strong> We prioritize the security of your data and transactions.</li>
                <li><strong>Expert Support:</strong> Our dedicated team is available to assist you at every step of your land acquisition journey.</li>
                <li><strong>Wide Selection:</strong> Explore a diverse range of land types, from agricultural plots to commercial development sites.</li>
                <li><strong>User-Friendly Interface:</strong> Our platform is designed to be intuitive and easy to navigate, making your search for land seamless.</li>
            </ul>
        </section>

        <section class="content-section team-section">
            <h2>Our Team</h2>
            <p>
                Our dedicated team of professionals, including real estate experts, tech innovators, and customer support specialists, 
                is passionate about land and committed to helping you achieve your goals. We are here to help you!
            </p>
            <!-- Placeholder for team member profiles if desired in the future -->
        </section>

    </div> <!-- /.container -->

    <?php
    // include 'includes/footer.php'; // If you have a common footer
    ?>
</body>
</html>
