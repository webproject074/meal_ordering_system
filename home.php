<?php
require_once 'includes/functions.php';

// Handle logout
if (isset($_GET['logout'])) {
    // Clear session
    session_unset();
    session_destroy();
    
    // Start a new session for messages
    session_start();
    
    setMessage('You have been logged out successfully', 'success');
    redirect(BASE_URL . '/home.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = getUserByUsername($username);
    
    if ($user && verifyPassword($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        
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
    } else {
        // Login failed
        setMessage('Invalid username or password', 'error');
    }
}

// Check if user is already logged in
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

// Get any messages
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-b from-primary-600 to-primary-700 min-h-screen flex items-center justify-center p-4">
    
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-primary-600 py-4 px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h1 class="ml-2 text-xl font-bold text-white"><?php echo SITE_NAME; ?></h1>
                </div>
                <a href="<?php echo BASE_URL; ?>/home.php" class="text-primary-100 hover:text-white text-sm font-medium">
                    Back to Home
                </a>
            </div>
        </div>
        
        <div class="py-8 px-6">
            <h2 class="text-2xl font-bold text-primary-800 mb-6 text-center">Login to Your Account</h2>
            
            <?php if ($message): ?>
                <div class="mb-6 p-3 rounded <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div class="mb-6">
                    <button type="submit" class="w-full bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-300">
                        Login
                    </button>
                </div>
            </form>
            
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-2">Default Credentials:</p>
                <ul class="text-sm text-gray-600 space-y-1 ml-4 list-disc">
                    <li>Admin: admin / password</li>
                    <li>Canteen: canteen / password</li>
                    <li>Department: hr / password (Human Resources)</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
