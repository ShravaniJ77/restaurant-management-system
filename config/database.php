<?php
function getDbConnection(): mysqli
{
    // Reads from environment variables in production; falls back to local
    // XAMPP/MAMP defaults for local development.
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'restaurant_management_db';
    $port = getenv('DB_PORT') ?: 3306;

    $connection = mysqli_init();
    $connection->real_connect($host, $username, $password, $database, (int) $port);

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}
?>
