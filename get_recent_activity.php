<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Get recent activity data
$recent_activities = [];
$activity_query = "
    (SELECT 'user_registered' as type, username as title, created_at as timestamp, CONCAT('New user registered: ', username) as description FROM users ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'product_added' as type, name as title, created_at as timestamp, CONCAT('New product added: ', name) as description FROM products ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'appointment_booked' as type, CONCAT('Appointment for ', patient_name) as title, appointment_date as timestamp, CONCAT('New appointment booked for ', patient_name) as description FROM appointments ORDER BY appointment_date DESC LIMIT 3)
    UNION
    (SELECT 'order_placed' as type, CONCAT('Order #', id) as title, order_date as timestamp, CONCAT('New order placed: #', id) as description FROM orders ORDER BY order_date DESC LIMIT 3)
    ORDER BY timestamp DESC LIMIT 10
";

$activity_result = mysqli_query($conn, $activity_query);
if ($activity_result && mysqli_num_rows($activity_result) > 0) {
    while ($row = mysqli_fetch_assoc($activity_result)) {
        $row['formatted_time'] = date('M j, Y g:i A', strtotime($row['timestamp']));
        $recent_activities[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($recent_activities);
?>