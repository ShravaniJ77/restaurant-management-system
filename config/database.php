<?php
function getDbConnection(): mysqli
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'restaurant_management_db';

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}
?>
