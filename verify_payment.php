<?php
include "db.php";
session_start();

// Redirect if not logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['user_id'];
    $total_amount = 0;
    
    // Calculate total from session cart
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    // Prepare shipping address
    $shipping_address = "{$_POST['first_name']} {$_POST['last_name']}\n";
    $shipping_address .= "{$_POST['address']}\n";
    $shipping_address .= "{$_POST['city']}, {$_POST['state']} {$_POST['zip']}\n";
    $shipping_address .= "Phone: {$_POST['phone']}";
    
    $notes = $_POST['notes'] ?? '';
    $payment_method = 'Razorpay';
    $payment_id = $_POST['razorpay_payment_id'] ?? 'manual_' . time();
    
    // Insert order
    $conn->query("
        INSERT INTO orders (
            patient_id, 
            order_date, 
            total_amount, 
            status, 
            shipping_address,
            payment_method
        ) VALUES (
            $patient_id, 
            NOW(), 
            $total_amount, 
            'pending', 
            '" . $conn->real_escape_string($shipping_address) . "',
            '" . $conn->real_escape_string($payment_method) . "'
        )
    ");
    $order_id = $conn->insert_id;
    
    // Insert order items
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $price = $item['price'];
        $quantity = $item['quantity'];
        $conn->query("
            INSERT INTO order_items (
                order_id, 
                product_id, 
                quantity, 
                price
            ) VALUES (
                $order_id, 
                $product_id, 
                $quantity, 
                $price
            )
        ");
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect to order confirmation
    header("Location: order_confirmation.php?order_id=$order_id&payment_id=$payment_id");
    exit;
} else {
    // If directly accessed, redirect to products
    header("Location: products.php");
    exit;
}
?>