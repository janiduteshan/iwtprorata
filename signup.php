<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
        include 'includes/header.php'; // Database Connection
    ?>
</head>
<body>

    <!-- Header Section -->
    <header>
        <?php 
            include 'includes/menu.php'; // Database Connection
        ?>
    </header>

    <section class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 p-10 bg-white shadow-xl rounded-xl">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Create your account
                </h2>
            </div>
            <form class="mt-8 space-y-6" action="includes/signup-process.php" method="POST" onsubmit="return checkPassword()">
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="fullName" name="fullName" type="text" autocomplete="name" required
                           class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                           placeholder="Enter Your Name">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                           placeholder="Enter Email Address">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                           placeholder="Enter username">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                           placeholder="Enter Password">
                </div>
                <div>
                    <label for="conPassword" class="block text-sm font-medium text-gray-700">Re-enter Password</label>
                    <input id="conPassword" name="conPassword" type="password" autocomplete="new-password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                           placeholder="Re-enter Password">
                </div>
                <div>
                    <label for="user_type" class="block text-sm font-medium text-gray-700">Register as</label>
                    <select id="user_type" name="user_type" required
                            class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                        <option value="" disabled selected>Select role</option>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                        <option value="agent">Agent</option>
                    </select>
                </div>
                
                <!-- The "Remember me" and "Forgot password" are less relevant for a sign-up form -->
                <!-- but kept if structure must be similar or for potential future use -->
                <!-- <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div> -->

                <div>
                    <button type="submit" name="signup"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Sign up
                    </button>
                </div>
            </form>
            <hr class="my-6 border-gray-300">
            <div class="text-sm text-center">
                <p class="text-gray-600">Already have an account?
                    <a href="signin.php" class="font-medium text-green-600 hover:text-green-500">
                        Sign in
                    </a>
                </p>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </section>


 

    <script>
        // Check Password
        function checkPassword() {
            const password = document.getElementById("password").value;
            const rePassword = document.getElementById("conPassword").value;

            if(password != rePassword) {
                alert("Password Mismatch!");
                return false;
            } else {
                alert("Success!");
                return true;
            }
        }
    </script>
    
</body>
</html>