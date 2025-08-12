<?php
require_once 'config.php';

/**
 * Read a JSON file and return the data as an array
 * @param string $filename
 * @return array
 */
function readJsonFile($filename) {
    if (!file_exists($filename)) return [];
    $json = file_get_contents($filename);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * Write an array to a JSON file
 * @param string $filename
 * @param array $data
 * @return bool
 */
function writeJsonFile($filename, $data) {
    return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

/**
 * --- JSON file data access used ---
 * Use readJsonFile() and writeJsonFile() for data access
 */


/**
 * Generate a unique ID
 * @return string Unique ID
 */
function generateUniqueId() {
    return uniqid() . bin2hex(random_bytes(8));
}

/**
 * Hash a password
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password
 * @param string $password Password to verify
 * @param string $hash Hash to verify against or plain text password
 * @return bool True if the password matches the hash, false otherwise
 */
function verifyPassword($password, $hash) {
    // For development purposes, allow plain text password comparison
    // File swap completed - all references updated
    if ($password === $hash) {
        return true;
    }
    // Also try standard password verification
    return password_verify($password, $hash);
}

/**
 * Get user by username
 * @param string $username Username to search for
 * @return array|null User data or null if not found
 */
function getUserByUsername($username) {
    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

/**
 * Get user by ID
 * @param string $id User ID to search for
 * @return array|null User data or null if not found
 */
function getUserById($id) {
    $users = readJsonFile(USERS_FILE);
    foreach ($users as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

/**
 * Check if a user is logged in
 * @return bool True if the user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return getUserById($_SESSION['user_id']);
}

/**
 * Check if the current user has a specific role
 * @param string $role Role to check for
 * @return bool True if the user has the role, false otherwise
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    return $user['role'] === $role;
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get current timestamp in Sri Lanka timezone
 * @param string $format Format to use (default: Y-m-d H:i:s)
 * @return string Current timestamp in specified format
 */
function getCurrentTimestamp($format = 'Y-m-d H:i:s') {
    return date($format);
}

/**
 * Get current date in Sri Lanka timezone
 * @param string $format Format to use (default: Y-m-d)
 * @return string Current date in specified format
 */
function getCurrentDate($format = 'Y-m-d') {
    return date($format);
}

/**
 * Format a date
 * @param string $date Date to format
 * @param string $format Format to use
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Format a price
 * @param float $price Price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return 'LKR ' . number_format($price, 2);
}

/**
 * Display a message
 * @param string $message Message to display
 * @param string $type Type of message (success, error, warning, info)
 */
function setMessage($message, $type = 'info') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear the message
 * @return array|null Message or null if no message
 */
function getMessage() {
    if (!isset($_SESSION['message'])) {
        return null;
    }
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    return $message;
}

/**
 * Check if the user is authorized to access a page
 * @param array $allowedRoles Roles that are allowed to access the page
 */
function requireAuth($allowedRoles = []) {
    if (!isLoggedIn()) {
        setMessage('You must be logged in to access this page', 'error');
        redirect(BASE_URL . '/home.php');
    }
    
    if (!empty($allowedRoles)) {
        $user = getCurrentUser();
        if (!in_array($user['role'], $allowedRoles)) {
            setMessage('You do not have permission to access this page', 'error');
            redirect(BASE_URL . '/home.php');
        }
    }
}
/**
 * Get the serial number (e.g., 001, 002, ...) for an order based on its ID.
 * Orders are sorted by date (newest first) from the global order list.
 * @param string $orderId Order ID to find
 * @return string 3-digit serial number, or 'N/A' if not found
 */
function getOrderSerialNumber($orderId) {
    $orders = readJsonFile(ORDERS_FILE);
    // Sort by date (newest first)
    usort($orders, function($a, $b) {
        return strtotime($b['order_date']) - strtotime($a['order_date']);
    });
    foreach ($orders as $idx => $order) {
        if ($order['id'] === $orderId) {
            return str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
        }
    }
    return 'N/A';
}

?>
