<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $request_id = $_GET['id'];

    // Prepare the query to delete the transportation request
    $query = "DELETE FROM produce_requests WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $request_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Successfully deleted
        header("Location: index.php?status=deleted");
    } else {
        // Error occurred
        header("Location: index.php?status=error");
    }
    exit();
} else {
    // Invalid request
    header("Location: index.php");
    exit();
}
