<?php
require_once 'includes/functions.php';

// If user is already logged in, redirect to their dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    
    // Redirect based on role
    switch ($user['role']) {
        case ROLE_ADMIN:
            redirect(BASE_URL . '/admin/dashboard.php');
            break;
        case ROLE_DEPARTMENT:
            redirect(BASE_URL . '/department/dashboard.php');
            break;
        case ROLE_CANTEEN:
            redirect(BASE_URL . '/canteen/dashboard.php');
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 1s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-slow': 'bounce 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .text-shadow {
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .text-shadow-lg {
                text-shadow: 0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08);
            }
            .bg-pattern {
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230ea5e9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-50 font-sans bg-pattern">
    <!-- Header -->
    <header class="bg-primary-600 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h1 class="ml-3 text-3xl font-bold text-white text-shadow"><?php echo SITE_NAME; ?></h1>
            </div>
            <nav class="hidden md:flex space-x-8">
                <a href="#features" class="text-white hover:text-primary-200 transition-colors duration-300">Features</a>
                <a href="#about" class="text-white hover:text-primary-200 transition-colors duration-300">About</a>
            </nav>
            <div>
                <a href="<?php echo BASE_URL; ?>/home.php" class="bg-white text-primary-600 hover:bg-primary-50 font-semibold py-2 px-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105 flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-600 to-primary-700 text-white py-24">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-primary-500 rounded-full opacity-20 animate-pulse"></div>
            <div class="absolute top-20 right-10 w-72 h-72 bg-primary-400 rounded-full opacity-10 animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-10 left-1/4 w-56 h-56 bg-primary-300 rounded-full opacity-10 animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center relative z-10">
            <div class="md:w-1/2 mb-10 md:mb-0 animate-fade-in">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight text-shadow-lg">
                    <span class="block">Streamline Your</span>
                    <span class="text-primary-200">Meal Ordering Process</span>
                </h1>
                <p class="text-xl mb-8 text-primary-100 max-w-lg animate-slide-up" style="animation-delay: 0.3s;">
                    A simple and efficient way to manage meal orders across departments in your organization. Save time and reduce errors with our intuitive system.
                </p>
                <div class="flex flex-wrap gap-4 animate-slide-up" style="animation-delay: 0.6s;">
                    <a href="<?php echo BASE_URL; ?>/home.php" class="bg-white text-primary-600 hover:bg-primary-50 font-semibold py-3 px-8 rounded-lg shadow-md inline-flex items-center transition duration-300 ease-in-out transform hover:scale-105">
                        <i class="fas fa-rocket mr-2"></i> Get Started
                    </a>
                    <a href="#features" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-600 font-semibold py-3 px-8 rounded-lg inline-flex items-center transition duration-300">
                        <i class="fas fa-info-circle mr-2"></i> Learn More
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center items-center animate-fade-in" style="animation-delay: 0.3s;">
    
                <div class="relative">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-primary-400 to-primary-300 rounded-lg blur opacity-75 group-hover:opacity-100 transition duration-1000 group-hover:duration-200 animate-pulse"></div>
                    <div class="relative rounded-lg shadow-2xl max-w-full h-auto transform transition-all duration-500 hover:scale-105 bg-primary-100 p-4 flex items-center justify-center" style="min-height: 300px; min-width: 400px;">
                        <div class="text-center">
                            <i class="fas fa-utensils text-6xl text-primary-600 mb-4"></i>
                            <h3 class="text-xl font-bold text-primary-700">Canteen Management System</h3>
                            <p class="text-primary-600 mt-2">Streamline your meal ordering process</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white opacity-10"></div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Why Choose Us</span>
                <h2 class="text-4xl font-bold text-gray-900 mt-2 mb-4">Powerful Features</h2>
                <p class="text-lg text-gray-600">Our canteen management system offers everything you need to streamline your meal ordering process.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div class="bg-gray-50 rounded-xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-utensils text-2xl text-primary-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Easy Ordering</h3>
                    <p class="text-gray-600">Place orders quickly with our intuitive interface. Select items, specify quantities, and submit your order in just a few clicks.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-gray-50 rounded-xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-calendar-alt text-2xl text-primary-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Advanced Planning</h3>
                    <p class="text-gray-600">Schedule meals in advance with our request date feature. Plan ahead for special events and ensure everything is ready when needed.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-gray-50 rounded-xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-tasks text-2xl text-primary-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Order Management</h3>
                    <p class="text-gray-600">Track order status in real-time. Edit or delete pending orders, and view complete order history with all details.</p>
                </div>
            </div>
            
            <div class="mt-16 text-center">
                <a href="<?php echo BASE_URL; ?>/home.php" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-8 rounded-lg shadow-md inline-flex items-center transition-colors duration-300">
                    <i class="fas fa-arrow-right mr-2"></i> Start Using These Features
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gradient-to-br from-primary-50 to-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <div class="rounded-lg shadow-xl w-full bg-primary-50 p-8 flex items-center justify-center" style="min-height: 300px;">
                        <div class="text-center">
                            <i class="fas fa-users text-6xl text-primary-600 mb-4"></i>
                            <h3 class="text-xl font-bold text-primary-700">Our Team</h3>
                            <p class="text-primary-600 mt-2">Dedicated to simplifying meal management</p>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">About Our System</span>
                    <h2 class="text-4xl font-bold text-gray-900 mt-2 mb-6">Simplifying Meal Management</h2>
                    <p class="text-lg text-gray-600 mb-6">Our canteen management system streamlines the process of ordering meals for departments within your organization. With an intuitive interface and efficient workflow, we make it easy to place, track, and manage food orders.</p>
                    <p class="text-lg text-gray-600 mb-8">Whether you're a department ordering meals, a canteen staff preparing orders, or an administrator overseeing the process, our system provides the tools you need to make meal management simple and efficient.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="<?php echo BASE_URL; ?>/home.php" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 px-8 rounded-lg shadow-md inline-flex items-center transition-colors duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i> Get Started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    
    <!-- CTA Section -->
    <section class="relative bg-primary-700 text-white py-20 overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary-600 rounded-full opacity-30 -mt-20 -mr-20"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-primary-800 rounded-full opacity-30 -mb-20 -ml-20"></div>
        
        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-bold mb-6">Ready to Streamline Your Meal Ordering?</h2>
            <p class="text-xl text-primary-100 mb-10 max-w-3xl mx-auto">Join our platform today and experience the convenience of our meal ordering system. Save time, reduce errors, and make meal management effortless.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>/home.php" class="bg-white text-primary-600 hover:bg-primary-50 font-semibold py-3 px-8 rounded-lg shadow-md inline-flex items-center transition duration-300 ease-in-out transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login Now
                </a>
                <a href="#features" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-primary-600 font-semibold py-3 px-8 rounded-lg inline-flex items-center transition duration-300">
                    <i class="fas fa-info-circle mr-2"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary-800 text-white pt-16 pb-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Logo and About -->
                <div class="col-span-1">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h2 class="ml-2 text-2xl font-bold"><?php echo SITE_NAME; ?></h2>
                    </div>
                    <p class="text-primary-200 mb-6">Simplifying meal management for organizations. Our platform makes ordering and managing meals efficient and hassle-free.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-primary-700 hover:bg-primary-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-facebook-f text-white"></i>
                        </a>
                        <a href="#" class="bg-primary-700 hover:bg-primary-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-twitter text-white"></i>
                        </a>
                        <a href="#" class="bg-primary-700 hover:bg-primary-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-instagram text-white"></i>
                        </a>
                        <a href="#" class="bg-primary-700 hover:bg-primary-600 h-10 w-10 rounded-full flex items-center justify-center transition-colors duration-300">
                            <i class="fab fa-linkedin-in text-white"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold mb-6 text-white">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-primary-200 hover:text-white transition-colors duration-300 flex items-center"><i class="fas fa-chevron-right text-xs mr-2"></i> Features</a></li>
                        <li><a href="#about" class="text-primary-200 hover:text-white transition-colors duration-300 flex items-center"><i class="fas fa-chevron-right text-xs mr-2"></i> About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/home.php" class="text-primary-200 hover:text-white transition-colors duration-300 flex items-center"><i class="fas fa-chevron-right text-xs mr-2"></i> Login</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold mb-6 text-white">Contact Info</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-primary-400"></i>
                            <span class="text-primary-200">123 Business Avenue, Office Park, Suite 101</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone-alt mr-3 text-primary-400"></i>
                            <span class="text-primary-200">+1 (555) 123-4567</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-primary-400"></i>
                            <span class="text-primary-200">info@canteensystem.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock mr-3 text-primary-400"></i>
                            <span class="text-primary-200">Mon-Fri: 9:00 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="mt-12 pt-8 border-t border-primary-700 flex flex-col md:flex-row justify-between items-center">
                <p class="text-primary-300 text-sm mb-4 md:mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <div>
                    <ul class="flex flex-wrap justify-center space-x-6 text-sm text-primary-300">
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Support</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
