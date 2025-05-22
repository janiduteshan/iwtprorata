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
                    Login to your account
                </h2>
            </div>
            <form class="mt-8 space-y-6" action="includes/signin-process.php" method="POST">
                <input type="hidden" name="remember" value="true">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="Email address">
                    </div>
                    <div>
                        <label for="psw" class="sr-only">Password</label>
                        <input id="psw" name="psw" type="password" autocomplete="current-password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
                               placeholder="Password">
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-gray-900">
                            Remember me
                        </label>
                    </div>

                    <div >
                        <a href="#" class="font-medium text-green-600 hover:text-green-500">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" name="login"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Sign in
                    </button>
                </div>
            </form>
            <hr class="my-6 border-gray-300">
            <div class="text-sm text-center">
                <p class="text-gray-600">Don't have an account?
                    <a href="signup.php" class="font-medium text-green-600 hover:text-green-500">
                        Sign up
                    </a>
                </p>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </section>


  
    
</body>
</html>