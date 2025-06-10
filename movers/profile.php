<?php 
session_start();
include 'db.php';

// Redirect if not logged in or not a farmer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// Fetch farmer's details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$farmer = $result->fetch_assoc();

// Function to update profile
function updateProfile($userId, $name, $email, $farm_name, $conn) {
    // Check if email already exists (excluding current user)
    $check_email = "SELECT * FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_email);
    $check_stmt->bind_param('si', $email, $userId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    // If the email exists, return an error message
    if ($check_result->num_rows > 0) {
        return "This email is already in use. Please choose another one.";
    } else {
        // Update the profile
        $update_query = "UPDATE users SET name = ?, email = ?, farm_name = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('sssi', $name, $email, $farm_name, $userId);
        $update_stmt->execute();

        // Return success message
        return "Profile updated successfully!";
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $farm_name = $_POST['farm_name'];

    // Call the updateProfile function
    $message = updateProfile($farmer_id, $name, $email, $farm_name, $conn);

    // Display the message (success or error)
    if ($message === "Profile updated successfully!") {
        header("Location: profile.php");
        exit();
    } else {
        $error_message = $message; // If an error occurs, display it
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('assets/path-cornfield-countryside.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'Poppins', sans-serif;
            backdrop-filter: brightness(0.9);
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            margin-top: 50px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            font-weight: 600;
        }
        .btn-success {
            font-weight: 500;
            padding: 10px 25px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Movers</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Profile Content -->
<div class="container">
    <h2 class="mb-4">Farmer Profile</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-success text-white">Your Profile Information</div>
        <div class="card-body">
            <form method="POST" action="profile.php">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($farmer['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($farmer['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="farm_name" class="form-label">Farm Name</label>
                    <input type="text" class="form-control" name="farm_name" value="<?php echo htmlspecialchars($farmer['farm_name']); ?>" required>
                </div>
                <button type="submit" class="btn btn-success">Update Profile</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
