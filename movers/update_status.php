<?php
session_start();
include 'db.php';

// Only admins allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $load_size = $_POST['load_size'] ?? null;
    $transport_mode = $_POST['transport_mode'] ?? null;
    $transport_cost = $_POST['transport_cost'] ?? null;

    // Trim inputs
    $status = trim($status);
    $load_size = trim($load_size);
    $transport_mode = trim($transport_mode);
    $transport_cost = trim($transport_cost);

    // Validate inputs: all fields required and transport_cost numeric >= 0
    if (
        empty($request_id) ||
        empty($status) ||
        empty($load_size) ||
        empty($transport_mode) ||
        !is_numeric($transport_cost) ||
        floatval($transport_cost) < 0
    ) {
        $_SESSION['error'] = "All fields must be filled out correctly.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Optional: Validate that $status is one of allowed values
    $allowed_status = ['Pending', 'Approved', 'Completed'];
    if (!in_array($status, $allowed_status)) {
        $_SESSION['error'] = "Invalid status value.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Optional: Validate load_size values
    $allowed_load_sizes = ['Small', 'Medium', 'Large'];
    if (!in_array($load_size, $allowed_load_sizes)) {
        $_SESSION['error'] = "Invalid load size value.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Optional: Validate transport_mode values (you can extend this if needed)
    $allowed_transport_modes = [
        'Pickup', 'Normal Truck', 'Big Truck',
        'Refrigerated Pickup', 'Refrigerated Lorry', 'Refrigerated Trailer'
    ];
    if (!in_array($transport_mode, $allowed_transport_modes)) {
        $_SESSION['error'] = "Invalid transport mode.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Update database
    $query_update = "UPDATE produce_requests SET status = ?, load_size = ?, transport_mode = ?, transport_cost = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);

    if ($stmt_update === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: admin_dashboard.php");
        exit();
    }

    $stmt_update->bind_param("sssdi", $status, $load_size, $transport_mode, $transport_cost, $request_id);

    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Request updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update request: " . $stmt_update->error;
    }

    $stmt_update->close();

    header("Location: admin_dashboard.php");
    exit();
} else {
    // If not POST, redirect
    header("Location: admin_dashboard.php");
    exit();
}
?>
