<?php 
session_start();
include 'db.php';

// Redirect if not logged in or if the role is not 'farmer'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

// Retrieve form data
$farmer_id = $_SESSION['user_id'];
$produce_type = $_POST['produce_type'];
$quantity = $_POST['quantity'];
$pickup_location = $_POST['pickup_location'];
$dropoff_location = $_POST['dropoff_location'];
$pickup_date = $_POST['pickup_date'];
$mpesa_number = $_POST['mpesa_number'];

// Automatically categorize produce
$category = in_array($produce_type, ['Eggs', 'Milk', 'Flowers']) ? 'Perishable' : 'Non-Perishable';

// Prepare and insert into DB
$query = "INSERT INTO produce_requests (
    user_id, produce_type, category, quantity, pickup_location, dropoff_location, pickup_date, mpesa_number, status, request_date
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("issdssss", $farmer_id, $produce_type, $category, $quantity, $pickup_location, $dropoff_location, $pickup_date, $mpesa_number);

// Execute and redirect
if ($stmt->execute()) {
    header("Location: thankyou.html");
    exit();
} else {
    echo "Error submitting request: " . $stmt->error;
}
?>

