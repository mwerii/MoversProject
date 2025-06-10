<?php
session_start();
include 'db.php';

// Check if the user is logged in and is a farmer (not admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the farmer's requests
$query = "SELECT * FROM produce_requests WHERE user_id = ? ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('assets/terraces-7878191_1280.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            margin-top: 50px;
            padding: 20px;
        }
        h2 {
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="mb-4">My Transportation Orders</h2>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Produce Type</th>
                    <th>Quantity</th>
                    <th>Pickup</th>
                    <th>Drop-off</th>
                    <th>Status</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['produce_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['pickup_location']); ?></td>
                        <td><?php echo htmlspecialchars($row['dropoff_location']); ?></td>
                        <td>
                            <?php 
                                $status = $row['status'];
                                if ($status == 'Completed') {
                                    echo "<span class='badge bg-success'>Completed</span>";
                                } elseif ($status == 'Approved') {
                                    echo "<span class='badge bg-primary'>Approved</span>";
                                } else {
                                    echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
