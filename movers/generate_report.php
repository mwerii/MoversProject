<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$query = "SELECT * FROM produce_requests ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transportation Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            padding: 40px;
        }
        .report-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }
        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="report-container">
    <h2>Transportation Requests Report</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>User ID</th>
                <th>Produce Type</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Pickup</th>
                <th>Drop-off</th>
                <th>Request Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1;
            while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['produce_type']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                    <td><?= htmlspecialchars($row['dropoff_location']) ?></td>
                    <td><?= htmlspecialchars($row['request_date']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div class="print-button">
        <button onclick="window.print()">Print Report</button>
    </div>
</div>
</div>

</body>
</html>
