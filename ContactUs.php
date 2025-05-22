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

    <div class="container page-container contact-us-page">
        <h1>Get In Touch</h1>
        <p class="sub-heading">We'd love to hear from you! Please fill out the form below or use our contact details.</p>

        <!-- Display Success/Error Messages for form submission -->
        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p class='success-message'>Your message has been sent successfully! We'll get back to you soon.</p>";
            } elseif ($_GET['status'] == 'error') {
                $error_msg = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : "An error occurred. Please try again.";
                echo "<p class='error-message'>" . $error_msg . "</p>";
            }
        }
        ?>

        <div class="contact-content-wrapper">
            <section class="contact-form-section">
                <h2>Send Us a Message</h2>
                <form action="includes/process_contact_form.php" method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" name="subject" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea name="message" id="message" rows="6" required></textarea>
                    </div>
                    <button type="submit" name="submit_contact_form" class="btn">Send Message</button>
                </form>
            </section>

            <section class="contact-info-section">
                <h2>Contact Information</h2>
                <div class="info-item">
                    <h3><i class="ri-map-pin-line"></i> Address</h3>
                    <p>123 Landview Street<br>Property City, LC 54321<br>Country</p>
                </div>
                <div class="info-item">
                    <h3><i class="ri-phone-line"></i> Phone</h3>
                    <p><a href="tel:+15555263669">+1-555-LAND-NOW</a> (Support Hotline)</p>
                </div>
                <div class="info-item">
                    <h3><i class="ri-mail-send-line"></i> Email</h3>
                    <p><a href="mailto:support@onlinelandsales.com">support@onlinelandsales.com</a> (General Inquiries)</p>
                    <p><a href="mailto:sales@onlinelandsales.com">sales@onlinelandsales.com</a> (Sales Department)</p>
                </div>
                <div class="info-item">
                    <h3><i class="ri-time-line"></i> Business Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p>Saturday: 10:00 AM - 4:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
            </section>
        </div>

        <!-- Optional: Embedded Map -->
        <section class="map-section">
            <h2>Our Location</h2>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.086002338865!2d-122.41941548468154!3d37.77492927975903!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808c0e8f2323%3A0x8c6f7fd2b8c11c4a!2sSan%20Francisco%20City%20Hall!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                width="100%" 
                height="450" 
                style="border:0; border-radius: 8px;" 
                allowfullscreen="" 
                loading="lazy"
                title="Our Office Location">
            </iframe>
        </section>

    </div> <!-- /.container -->

    <?php
    // include 'includes/footer.php'; // If you have a common footer
    ?>
</body>
</html>
<style>
/* Basic styling for Contact Us page - can be moved to a CSS file */
.page-container { padding-top: 20px; padding-bottom: 20px; }
.contact-us-page h1 { text-align: center; margin-bottom: 10px; font-size: 2.5em; color: #333; }
.contact-us-page .sub-heading { text-align: center; margin-bottom: 30px; font-size: 1.1em; color: #666; }

.contact-content-wrapper { display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 30px; }
.contact-form-section { flex: 2; min-width: 300px; background-color: #f9f9f9; padding: 25px; border-radius: 8px; }
.contact-info-section { flex: 1; min-width: 280px; background-color: #f9f9f9; padding: 25px; border-radius: 8px; }

.contact-form-section h2, .contact-info-section h2, .map-section h2 { font-size: 1.8em; color: #444; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

.contact-form .form-group { margin-bottom: 20px; }
.contact-form .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
.contact-form .form-group input[type="text"],
.contact-form .form-group input[type="email"],
.contact-form .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1em;
}
.contact-form .form-group textarea { resize: vertical; min-height: 120px; }
.contact-form .btn { background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; }
.contact-form .btn:hover { background-color: #0056b3; }

.contact-info-section .info-item { margin-bottom: 20px; }
.contact-info-section .info-item h3 { font-size: 1.3em; color: #333; margin-bottom: 8px; display: flex; align-items: center; }
.contact-info-section .info-item h3 i { margin-right: 10px; color: #007bff; }
.contact-info-section .info-item p { font-size: 1em; line-height: 1.6; color: #555; margin: 0 0 5px 0; }
.contact-info-section .info-item a { color: #0056b3; text-decoration: none; }
.contact-info-section .info-item a:hover { text-decoration: underline; }

.map-section { margin-bottom: 20px; }

.success-message { background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; text-align:center; }
.error-message { background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px; text-align:center; }

@media (max-width: 768px) {
    .contact-content-wrapper { flex-direction: column; }
}
</style>
