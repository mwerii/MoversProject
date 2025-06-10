<?php
session_start();
include 'db.php';

// Check if farmer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Validate and get request ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request ID.");
}

$request_id = intval($_GET['id']);

// Fetch the request from DB and confirm it belongs to the logged in farmer
$query = "SELECT * FROM produce_requests WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $request_id, $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Request not found or you do not have permission to view this receipt.");
}

$request = $result->fetch_assoc();

// Only allow receipt if status is Approved
if ($request['status'] !== 'Approved') {
    die("Receipt is only available for approved requests.");
}

// Format date nicely
$formatted_date = date("F j, Y, g:i a", strtotime($request['request_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Receipt for Request #<?= htmlspecialchars($request_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f7f7;
            padding: 30px;
        }
        .receipt-container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            color: #3173e4;
        }
        .receipt-info {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9rem;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="receipt-container">
    <h2>Transportation Request Receipt</h2>

    <div class="receipt-info"><strong>Request ID:</strong> <?= htmlspecialchars($request_id) ?></div>
    <div class="receipt-info"><strong>Produce Type:</strong> <?= htmlspecialchars($request['produce_type']) ?></div>
    <div class="receipt-info"><strong>Quantity:</strong> <?= htmlspecialchars($request['quantity']) ?></div>
    <div class="receipt-info"><strong>Pickup Location:</strong> <?= htmlspecialchars($request['pickup_location']) ?></div>
    <div class="receipt-info"><strong>Drop-off Location:</strong> <?= htmlspecialchars($request['dropoff_location']) ?></div>
    <div class="receipt-info"><strong>Pickup Date & Time:</strong> <?= htmlspecialchars($formatted_date) ?></div>
    <div class="receipt-info"><strong>Transport Cost:</strong> Ksh <?= number_format($request['transport_cost'], 2) ?></div>
    <div class="receipt-info"><strong>Status:</strong> <?= htmlspecialchars($request['status']) ?></div>

    <div class="footer">
        Thank you for using Movers. Safe travels!
    </div>
    
    <div class="print-button">
        <button onclick="window.print()">Print Receipt</button>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
