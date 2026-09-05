<?php
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage(), 3, '/path/to/error.log');
    die('Connection failed. Please try again later.');
}
?>
