<?php
session_start();
// include 'includes/config.php'; // Not strictly needed for DB for this static page, but process_contact_form will.
include 'includes/header.php'; // Includes HTML head, title, CSS links
?>

<body>
    <!-- Header Section -->
    <header>
        <?php include 'includes/menu.php'; ?>
    </header>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Get In Touch</h1>
        <p class="text-lg text-center text-gray-600 mb-10">We'd love to hear from you! Please fill out the form below or use our contact details.</p>

        <!-- Display Success/Error Messages for form submission -->
        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p class='mb-6 p-4 text-sm text-green-700 bg-green-100 rounded-lg'>Your message has been sent successfully! We'll get back to you soon.</p>";
            } elseif ($_GET['status'] == 'error') {
                $error_msg = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : "An error occurred. Please try again.";
                echo "<p class='mb-6 p-4 text-sm text-red-700 bg-red-100 rounded-lg'>" . $error_msg . "</p>";
            }
        }
        ?>

        <div class="flex flex-wrap md:flex-nowrap gap-10">
            <section class="w-full md:w-2/3 bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Send Us a Message</h2>
                <form action="includes/process_contact_form.php" method="POST" class="space-y-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name:</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject:</label>
                        <input type="text" name="subject" id="subject" required
                               class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message:</label>
                        <textarea name="message" id="message" rows="5" required
                                  class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                    </div>
                    <div>
                        <button type="submit" name="submit_contact_form"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Send Message
                        </button>
                    </div>
                </form>
            </section>

            <section class="w-full md:w-1/3 bg-gray-50 p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-700 mb-6">Contact Information</h2>
                <div class="space-y-6">
                    <div class="info-item">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center mb-1"><i class="ri-map-pin-line text-green-500 mr-2 text-xl"></i> Address</h3>
                        <p class="text-gray-600 text-sm">123 Landview Street<br>Property City, LC 54321<br>Country</p>
                    </div>
                    <div class="info-item">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center mb-1"><i class="ri-phone-line text-green-500 mr-2 text-xl"></i> Phone</h3>
                        <p class="text-gray-600 text-sm"><a href="tel:+15555263669" class="hover:text-green-600">+1-555-LAND-NOW</a> (Support Hotline)</p>
                    </div>
                    <div class="info-item">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center mb-1"><i class="ri-mail-send-line text-green-500 mr-2 text-xl"></i> Email</h3>
                        <p class="text-gray-600 text-sm"><a href="mailto:support@onlinelandsales.com" class="hover:text-green-600">support@onlinelandsales.com</a> (General Inquiries)</p>
                        <p class="text-gray-600 text-sm"><a href="mailto:sales@onlinelandsales.com" class="hover:text-green-600">sales@onlinelandsales.com</a> (Sales Department)</p>
                    </div>
                    <div class="info-item">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center mb-1"><i class="ri-time-line text-green-500 mr-2 text-xl"></i> Business Hours</h3>
                        <p class="text-gray-600 text-sm">Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p class="text-gray-600 text-sm">Saturday: 10:00 AM - 4:00 PM</p>
                        <p class="text-gray-600 text-sm">Sunday: Closed</p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Optional: Embedded Map -->
        <section class="mt-12">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">Our Location</h2>
            <div class="rounded-lg shadow-lg overflow-hidden">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.086002338865!2d-122.41941548468154!3d37.77492927975903!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808c0e8f2323%3A0x8c6f7fd2b8c11c4a!2sSan%20Francisco%20City%20Hall!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                    width="100%" 
                    height="450" 
                    class="border-0"
                    allowfullscreen="" 
                    loading="lazy"
                    title="Our Office Location">
                </iframe>
            </div>
        </section>

    </div> <!-- /.container -->

    <?php
    // include 'includes/footer.php'; // If you have a common footer
    ?>
</body>
</html>
