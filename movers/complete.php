<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "UPDATE transportation_requests SET status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=completed");
    } else {
        echo "Error updating status.";
    }
} else {
    echo "Invalid request.";
}
?>
