<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_DEPARTMENT]);

// Get current user
$user = getCurrentUser();

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
    setMessage('Order ID is required', 'error');
    redirect(BASE_URL . '/department/dashboard.php');
}

// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Find the order and check if it belongs to the current user
$orderIndex = -1;
$order = null;

foreach ($orders as $index => $o) {
    if ($o['id'] === $orderId && $o['department_id'] === $user['id']) {
        $order = $o;
        $orderIndex = $index;
        break;
    }
}

// If order not found, redirect to dashboard
if ($order === null) {
    setMessage('Order not found', 'error');
    redirect(BASE_URL . '/department/dashboard.php');
}

// Check if order is in pending status
if ($order['status'] !== 'pending') {
    setMessage('Only pending orders can be deleted', 'error');
    redirect(BASE_URL . '/department/view_order.php?id=' . $orderId);
}

// Delete the order
array_splice($orders, $orderIndex, 1);

// Save the updated orders
if (writeJsonFile(ORDERS_FILE, $orders)) {
    setMessage('Order deleted successfully', 'success');
} else {
    setMessage('Failed to delete order', 'error');
}

// Redirect to dashboard
redirect(BASE_URL . '/department/dashboard.php');
?>
