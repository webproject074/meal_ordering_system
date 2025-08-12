<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Get order ID and action from URL
$orderId = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (empty($orderId) || empty($action)) {
    setMessage('Order ID and action are required', 'error');
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    setMessage('Invalid action', 'error');
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Find the order
$orderIndex = -1;
foreach ($orders as $index => $order) {
    if ($order['id'] === $orderId) {
        $orderIndex = $index;
        break;
    }
}

// If order not found, redirect to dashboard
if ($orderIndex === -1) {
    setMessage('Order not found', 'error');
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Check if order is pending
if ($orders[$orderIndex]['status'] !== 'pending') {
    setMessage('Only pending orders can be approved or rejected', 'error');
    redirect(BASE_URL . '/admin/view_order.php?id=' . $orderId);
}

// Update order status
$orders[$orderIndex]['status'] = ($action === 'approve') ? 'approved' : 'rejected';

// Save changes
if (writeJsonFile(ORDERS_FILE, $orders)) {
    $message = ($action === 'approve') ? 'Order approved successfully' : 'Order rejected successfully';
    setMessage($message, 'success');
} else {
    setMessage('Failed to update order', 'error');
}

// Redirect back to view order page
redirect(BASE_URL . '/admin/view_order.php?id=' . $orderId);
?>
