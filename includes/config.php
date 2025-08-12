<?php
// Set timezone to Sri Lanka
date_default_timezone_set('Asia/Colombo');

// Database paths (using JSON files)
define('DATA_PATH', dirname(__DIR__) . '/data/');
define('USERS_FILE', DATA_PATH . 'users.json');
define('MENUS_FILE', DATA_PATH . 'menus.json');
define('ORDERS_FILE', DATA_PATH . 'orders.json');

// Session configuration
session_start();

// Site configuration
define('SITE_NAME', 'Meal Ordering System');
define('BASE_URL', '/new_system/canteen_system');

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DEPARTMENT', 'department');
define('ROLE_CANTEEN', 'canteen');
?>
