<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_CANTEEN]);

// Get order ID and status from URL
$orderId = $_GET['id'] ?? '';
$newStatus = $_GET['status'] ?? '';

if (empty($orderId) || empty($newStatus)) {
    setMessage('Order ID and status are required', 'error');
    redirect(BASE_URL . '/canteen/dashboard.php');
}

// Validate status
$validStatuses = ['order seen', 'preparing', 'ready', 'completed'];
if (!in_array($newStatus, $validStatuses)) {
    setMessage('Invalid status', 'error');
    redirect(BASE_URL . '/canteen/dashboard.php');
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
    redirect(BASE_URL . '/canteen/dashboard.php');
}

// Check if order is in a valid state to update
$currentStatus = $orders[$orderIndex]['status'];
$validTransition = false;

if ($currentStatus === 'approved' && $newStatus === 'order seen') {
    $validTransition = true;
} elseif ($currentStatus === 'order seen' && $newStatus === 'preparing') {
    $validTransition = true;
} elseif ($currentStatus === 'approved' && $newStatus === 'preparing') {
    $validTransition = true;
} elseif ($currentStatus === 'preparing' && $newStatus === 'ready') {
    $validTransition = true;
} elseif ($currentStatus === 'ready' && $newStatus === 'completed') {
    $validTransition = true;
}

if (!$validTransition) {
    setMessage('Invalid status transition', 'error');
    redirect(BASE_URL . '/canteen/view_order.php?id=' . $orderId);
}

// Update order status
$orders[$orderIndex]['status'] = $newStatus;

// Save changes
if (writeJsonFile(ORDERS_FILE, $orders)) {
    setMessage('Order status updated successfully', 'success');
} else {
    setMessage('Failed to update order status', 'error');
}

// Redirect back to view order page
redirect(BASE_URL . '/canteen/view_order.php?id=' . $orderId);
?>
