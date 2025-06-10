<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$query_requests = "SELECT * FROM produce_requests ORDER BY request_date DESC";
$stmt_requests = $conn->prepare($query_requests);
$stmt_requests->execute();
$requests_result = $stmt_requests->get_result();

$query_summary = "SELECT produce_type, COUNT(*) AS total_requests FROM produce_requests GROUP BY produce_type";
$stmt_summary = $conn->prepare($query_summary);
$stmt_summary->execute();
$summary_result = $stmt_summary->get_result();
$summary_data = [];
while ($row = $summary_result->fetch_assoc()) {
    $summary_data[$row['produce_type']] = $row['total_requests'];
}

function getSuggestedTransport($produce_type, $load_size) {
    $produce_type = strtolower($produce_type);
    $load_size = strtolower($load_size);

    // Define perishable goods
    $perishables = ['vegetables', 'fruits', 'milk', 'meat', 'fish'];

    if (in_array($produce_type, $perishables)) {
        // For perishables, use Refrigerated Trucks only
        if ($load_size === 'small') {
            return 'Refrigerated Pickup';
        } elseif ($load_size === 'medium') {
            return 'Refrigerated Lorry';
        } elseif ($load_size === 'large') {
            return 'Refrigerated Trailer';
        }
    } else {
        // For non-perishables, use regular trucks
        if ($load_size === 'small') {
            return 'Pickup';
        } elseif ($load_size === 'medium') {
            return 'Normal Truck';
        } elseif ($load_size === 'large') {
            return 'Big Truck';
        }
    }

    return 'Unknown';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('assets/terraces-7878191_1280.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
        }
        .container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: rgb(49, 132, 228);
            color: white;
            font-weight: 500;
        }
        h2 {
            font-weight: 600;
            color: rgb(2, 2, 2);
        }
        table {
            background-color: white;
        }
        .status-completed {
            color: green;
            font-weight: bold;
        }
        .report-btn {
            float: right;
            margin-top: -10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">Movers</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-danger" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h2>Welcome, Admin</h2>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>All Transportation Requests</span>
            <a href="generate_report.php" class="btn btn-success btn-sm report-btn">Generate Report</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>User ID</th>
                        <th>Produce Type</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Pickup</th>
                        <th>Drop-off</th>
                        <th>Date</th>
                        <th>Transport Cost (Ksh)</th>
                        <th>Load Size</th>
                        <th>transport_mode</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $requests_result->fetch_assoc()) : ?>
                        <?php 
                            $suggestedTransport = getSuggestedTransport($request['produce_type'], $request['load_size']);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($request['user_id']) ?></td>
                            <td><?= htmlspecialchars($request['produce_type']) ?></td>
                            <td><?= htmlspecialchars($request['quantity']) ?></td>
                            <td>
                                <?php if ($request['status'] === 'Completed'): ?>
                                    <span class="status-completed">Completed</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($request['status']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($request['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($request['dropoff_location']) ?></td>
                            <td><?= htmlspecialchars($request['request_date']) ?></td>
                            <td><?= number_format($request['transport_cost'], 2) ?></td>
                            <td><?= htmlspecialchars($request['load_size'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($request['transport_mode'] ?? $suggestedTransport) ?></td>
                            <td>
                                <?php if ($request['status'] !== 'Completed'): ?>
                                    <form method="POST" action="update_status.php" class="d-flex flex-column">
                                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                                        <select name="status" class="form-select form-select-sm mb-2">
                                            <option value="Pending" <?= $request['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Approved" <?= $request['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                        </select>

                                        <select name="load_size" class="form-select form-select-sm mb-2" required>
                                            <option value="">Select Load Size</option>
                                            <option value="Small" <?= $request['load_size'] == 'Small' ? 'selected' : '' ?>>Small</option>
                                            <option value="Medium" <?= $request['load_size'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                            <option value="Large" <?= $request['load_size'] == 'Large' ? 'selected' : '' ?>>Large</option>
                                        </select>

                                       <select name="transport_mode" class="form-select form-select-sm mb-2" required>
    <option value="">Select Transport_mode</option>
    <option value="Pickup" <?= str_contains($suggestedTransport, 'Pickup') && !str_contains($suggestedTransport, 'Refrigerated') ? 'selected' : '' ?>>Pickup</option>
    <option value="Normal Truck" <?= str_contains($suggestedTransport, 'Normal Truck') ? 'selected' : '' ?>>Normal Truck</option>
    <option value="Big Truck" <?= str_contains($suggestedTransport, 'Big Truck') ? 'selected' : '' ?>>Big Truck</option>
    <option value="Refrigerated Pickup" <?= $suggestedTransport === 'Refrigerated Pickup' ? 'selected' : '' ?>>Refrigerated Pickup</option>
    <option value="Refrigerated Lorry" <?= $suggestedTransport === 'Refrigerated Lorry' ? 'selected' : '' ?>>Refrigerated Lorry</option>
    <option value="Refrigerated Trailer" <?= $suggestedTransport === 'Refrigerated Trailer' ? 'selected' : '' ?>>Refrigerated Trailer</option>
</select>


                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            name="transport_cost" 
                                            class="form-control form-control-sm mb-2" 
                                            placeholder="Enter cost (Ksh)" 
                                            value="<?= htmlspecialchars($request['transport_cost']) ?>"
                                            min="0"
                                        >

                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4 mb-5">
        <div class="card-header">Produce Requests Summary</div>
        <div class="card-body">
            <canvas id="requestsChart" height="100"></canvas>
        </div>
    </div>
</div>

<script>
    const produceTypes = <?php echo json_encode(array_keys($summary_data)); ?>;
    const totalRequests = <?php echo json_encode(array_values($summary_data)); ?>;

    const ctx = document.getElementById('requestsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: produceTypes,
            datasets: [{
                label: 'Number of Requests',
                data: totalRequests,
                backgroundColor: 'rgba(49, 132, 228, 0.5)',
                borderColor: 'rgb(49, 132, 228)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
