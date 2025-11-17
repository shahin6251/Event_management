<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Selection | Event Management</title>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts for a nice font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="role_select.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <!-- Main container for the entire page -->
    <div class="min-h-screen flex flex-col items-center justify-center p-4">

        <!-- Header Section -->
        <header class="text-center mb-12 animate-fadeInUp">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">Welcome to Our Platform</h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">Choose your role to get started.</p>
        </header>

        <!-- Grid container for the two role sections -->
        <main class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 max-w-4xl w-full">

            <!-- Organizer Section -->
            <div class="card-container h-80 animate-fadeInUp animate-delay-200">
                <div class="card relative w-full h-full text-center">
                    <!-- Front of the Organizer Card -->
                    <div class="card-front absolute w-full h-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 flex flex-col justify-center items-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">For Organizers</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Manage events, track attendance, and engage with your audience.</p>
                    </div>
                    <!-- Back of the Organizer Card -->
                    <div class="card-back absolute w-full h-full bg-indigo-500 rounded-xl shadow-lg p-8 flex flex-col justify-center items-center">
                        <h2 class="text-2xl font-bold text-white mb-4">Organizer Portal</h2>
                        <div class="space-y-3 w-full">
                            <a href="organizer_signup.php" class="block w-full bg-white text-indigo-600 font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-transform transform hover:scale-105 text-center">
                                Sign Up as Organizer
                            </a>
                            <a href="login.php?role=organizer" class="block w-full bg-indigo-600 border-2 border-white text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-transform transform hover:scale-105 text-center">
                                Login as Organizer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Section -->
            <div class="card-container h-80 animate-fadeInUp animate-delay-400">
                <div class="card relative w-full h-full text-center">
                    <!-- Front of the Customer Card -->
                    <div class="card-front absolute w-full h-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 flex flex-col justify-center items-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">For Customers</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Discover events, book tickets, and enjoy unique experiences.</p>
                    </div>
                    <!-- Back of the Customer Card -->
                    <div class="card-back absolute w-full h-full bg-teal-500 rounded-xl shadow-lg p-8 flex flex-col justify-center items-center">
                        <h2 class="text-2xl font-bold text-white mb-4">Customer Portal</h2>
                        <div class="space-y-3 w-full">
                            <a href="customer_signup.php" class="block w-full bg-white text-teal-600 font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-transform transform hover:scale-105 text-center">
                                Sign Up as Customer
                            </a>
                            <a href="login.php?role=customer" class="block w-full bg-teal-600 border-2 border-white text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-transform transform hover:scale-105 text-center">
                                Login as Customer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer Section -->
        <footer class="mt-12 text-center animate-fadeInUp animate-delay-400">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Need help? <a href="mailto:support@eventmanagement.com" class="text-indigo-600 hover:text-indigo-800 font-medium">Contact Support</a>
            </p>
        </footer>

    </div>

    <script src="role_select.js"></script>
</body>
</html>
