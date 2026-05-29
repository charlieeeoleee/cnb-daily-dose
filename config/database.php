<?php
// Database settings for XAMPP/Laragon local development.
// Update the values below if your MySQL username, password, or database differs.
$host = '127.0.0.1';
$dbName = 'cb_daily_dose';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Check config/database.php and make sure MySQL is running.');
}

