<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include 'includes/config.php';
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

    <section class="bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-xl space-y-8">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Account Settings
                </h2>
            </div>
            <form action="includes/account-process.php" method="POST" class="space-y-6">
                <input type="hidden" id="userID" name="userID" value="<?php echo $_SESSION['userID'] ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"><?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_SESSION['city'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>"
                           class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                </div>
                
                <div>
                    <button type="submit" name="update"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-10 max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-xl space-y-6 border-t-4 border-red-500">
            <h2 class="text-center text-2xl font-bold text-red-700">
                Delete Account
            </h2>
            <p class="text-center text-sm text-gray-600">
                Warning: Deleting your account is an irreversible action. 
                If you proceed, your personal data will be removed, and your properties (if any) will be taken offline.
                You will be logged out and will not be able to access your account again.
            </p>
            <form action="includes/account-process.php" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone and you will be logged out immediately.');">
                <input type="hidden" name="action" value="delete_account">
                <input type="hidden" name="userID" value="<?php echo $_SESSION['userID']; ?>">
                <div class="text-center">
                    <button type="submit"
                            class="w-full md:w-auto inline-flex justify-center py-3 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </section>

  
   

</body>

</html>