<?php
// Set Timezone to WIB (Western Indonesia Time)
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';

// Determine if we are on a local server or hosting environment
// You can add more local domains here if needed (e.g., 'mysite.test')
$is_local = (php_sapi_name() === 'cli') || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']));

if ($is_local) {
    // Local Configuration (Laragon)
    $dbname = 'validation_db';
    $username = 'root';
    $password = ''; 
} else {
    // Hosting Configuration
    $dbname = 'bitubimy_crud';
    $username = 'bitubimy_izsaa';
    $password = 'jokiizsaa200504';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage() . ". <br>Please make sure you have created the database using the database.sql file.");
}
?>
