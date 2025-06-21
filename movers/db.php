<?php
/**
 * db.php – PostgreSQL database connection helper
 * 
 * It first checks for environment variables, falling back to local dev defaults.
 */

// Read database connection settings from the environment or use local defaults
$host     = getenv('DB_HOST')     ?: 'localhost';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'mweri';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: '';

// Build PostgreSQL connection string
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Establish connection
$conn = pg_connect($conn_string);

// Error handling
if (!$conn) {
    die("Connection to PostgreSQL failed.");
}
?>
