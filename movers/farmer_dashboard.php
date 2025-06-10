<?php  
// Start session to access session variables
session_start(); 

// Include database connection file (assumed to set $conn as MySQLi connection)
include 'db.php';

// Check if the user is logged in and has the role 'farmer'
// If not, redirect to login page for security
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

// Get the logged-in farmer's user ID from session
$farmer_id = $_SESSION['user_id'];

// Prepare and execute query to fetch farmer's profile info securely using prepared statements
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();

// Prepare and execute query to fetch all produce transportation requests by this farmer
// Orders them by request date descending (newest first)
$query_requests = "SELECT * FROM produce_requests WHERE user_id = ? ORDER BY request_date DESC";
$stmt_requests = $conn->prepare($query_requests);
$stmt_requests->bind_param('i', $farmer_id);
$stmt_requests->execute();
$requests_result = $stmt_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata for character set and responsiveness -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <title>Movers - Farmer Dashboard</title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet" />
    
    <style>
        /* Custom styles */
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
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: rgb(49, 132, 228);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        table {
            background-color: white;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
  <div class="container-fluid">
    <!-- Brand / Site name -->
    <a class="navbar-brand fw-bold" href="#">Movers</a>
    
    <!-- Hamburger menu button for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- Navbar links -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link fw-semibold" href="farmer_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link fw-semibold" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link fw-semibold" href="#orders">My Orders</a></li>
        <li class="nav-item"><a class="nav-link fw-semibold text-danger" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content Container -->
<div class="container">
    <!-- Greeting -->
    <h2>Welcome, <?php echo htmlspecialchars($farmer['name']); ?></h2>

    <!-- Profile Card -->
    <div class="card mt-3">
        <div class="card-header">Your Profile</div>
        <div class="card-body">
            <!-- Display farmer's name and email -->
            <h5 class="card-title">Name: <?php echo htmlspecialchars($farmer['name']); ?></h5>
            <p>Email: <?php echo htmlspecialchars($farmer['email']); ?></p>
            
            <!-- Link to edit profile -->
            <a href="profile.php" class="btn btn-outline-primary">Edit Profile</a>
        </div>
    </div>

    <!-- Produce Transportation Request Form -->
    <div class="card mt-4">
        <div class="card-header">Submit Transportation Request</div>
        <div class="card-body">
            <!-- The form submits to submit_request.php, calls showMpesaModal() on submit -->
            <form id="requestForm" action="submit_request.php" method="POST" onsubmit="showMpesaModal(event)">
                <!-- Produce Type dropdown with optgroups -->
                <div class="mb-3">
                    <label class="form-label">Produce Type</label>
                    <select class="form-select" name="produce_type" required>
                        <optgroup label="Perishable">
                            <option value="Milk">Milk</option>
                            <option value="Eggs">Eggs</option>
                            <option value="Fruits">Fruits</option>
                            <option value="Vegetables">Vegetables</option>
                            <option value="Flowers">Flowers</option>
                            <option value="Fresh Meat">Fresh Meat</option>
                        </optgroup>
                        <optgroup label="Non-Perishable">
                            <option value="Cereals">Cereals</option>
                            <option value="Grains">Grains</option>
                            <option value="Dried Beans">Dried Beans</option>
                            <option value="Nuts">Nuts</option>
                            <option value="Onions">Onions</option>
                            <option value="Garlic">Garlic</option>
                        </optgroup>
                    </select>
                </div>
                <!-- Quantity input -->
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity" required>
                </div>
                <!-- Pickup location -->
                <div class="mb-3">
                    <label class="form-label">Pickup Location</label>
                    <input type="text" class="form-control" name="pickup_location" required>
                </div>
                <!-- Dropoff location -->
                <div class="mb-3">
                    <label class="form-label">Drop-off Location</label>
                    <input type="text" class="form-control" name="dropoff_location" required>
                </div>
                <!-- Pickup date and time -->
                <div class="mb-3">
                  <label class="form-label">Pickup Date and Time</label>
<input type="datetime-local" name="pickup_date" class="form-control" required min="<?php echo date('Y-m-d\TH:i'); ?>">

                </div>
                <!-- Submit button -->
                <button type="submit" class="btn btn-success">Submit Request</button>
            </form>
        </div>
    </div>

    <!-- Display List of Transportation Requests -->
    <div id="orders" class="card mt-4 mb-5">
        <div class="card-header">Your Transportation Requests</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Produce</th>
                        <th>Status</th>
                        <th>Pickup</th>
                        <th>Drop-off</th>
                        <th>Cost (Ksh)</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loop through each request from database -->
                    <?php while ($request = $requests_result->fetch_assoc()) : ?>
                        <tr>
                            <!-- Produce type -->
                            <td><?= htmlspecialchars($request['produce_type']); ?></td>
                            <td>
                                <?php 
                                    // Display status with badge colors
                                    $status = $request['status'];
                                    if ($status == 'Completed') echo "<span class='badge bg-success'>Completed</span>";
                                    elseif ($status == 'Approved') echo "<span class='badge bg-primary'>Approved</span>";
                                    else echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                ?>
                            </td>
                            <!-- Pickup and drop-off locations -->
                            <td><?= htmlspecialchars($request['pickup_location']); ?></td>
                            <td><?= htmlspecialchars($request['dropoff_location']); ?></td>
                            <!-- Cost formatted to 2 decimals -->
                            <td><?= number_format($request['transport_cost'], 2); ?></td>
                            <!-- Request date -->
                            <td><?= htmlspecialchars($request['request_date']); ?></td>
                            <td>
                                <!-- Remove button links to remove_request.php with the request id -->
                                <a href="remove_request.php?id=<?= $request['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                                <!-- If request is approved, show a button to generate receipt -->
                                <?php if ($status === 'Approved'): ?>
                                    <a href="generate_receipt.php?id=<?= $request['id']; ?>" class="btn btn-secondary btn-sm">Receipt</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MPesa Confirmation Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1" aria-labelledby="mpesaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <!-- This form submits to Web3Forms API to capture MPesa number -->
    <form action="https://api.web3forms.com/submit" method="POST" class="modal-content">
      <input type="hidden" name="access_key" value="3f3807e9-6e7b-4594-ba7b-a5b312851ced">
      <div class="modal-header">
        <h5 class="modal-title" id="mpesaModalLabel">Confirm MPesa Number</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Please enter your MPesa number for confirmation:</p>
        <div class="mb-3">
          <!-- Input must start with 07 followed by 8 digits -->
          <label for="mpesaNumber" class="form-label">MPesa Number</label>
          <input type="text" class="form-control" name="mpesa_number" id="mpesaNumber" required pattern="^07\d{8}$" placeholder="e.g. 0712345678">
        </div>
        <!-- Hidden fields to set email subject, from name, and redirect URL -->
        <input type="hidden" name="subject" value="MPesa Number Submission">
        <input type="hidden" name="from_name" value="Farmer Dashboard">
        <input type="hidden" name="redirect" value="thank_you.html" />
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Send MPesa Number</button>
      </div>
    </form>
  </div>
</div>

<!-- Show alert message on request removal success or failure -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-<?php echo ($_GET['status'] === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show position-fixed bottom-0 end-0 m-3" role="alert" style="z-index:1050;">
        <?php echo htmlspecialchars($_GET['message'] ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Bootstrap JS Bundle with Popper for modal and navbar toggling -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Function to show the MPesa confirmation modal on form submission
    function showMpesaModal(event) {
        // Prevent default form submission so modal can show first
        event.preventDefault();

        // Show the modal
        let mpesaModal = new bootstrap.Modal(document.getElementById('mpesaModal'));
        mpesaModal.show();

        // Optional: You might want to submit the form after MPesa modal confirmation,
        // but here the modal's form submits separately to web3forms API.
    }
</script>

</body>
</html>
