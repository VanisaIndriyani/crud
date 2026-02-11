<?php
$host = 'localhost';
$db   = 'validation_db';
$user = 'root';
$pass = '';

// Deteksi environment (Local vs Hosting)
// Ganti 'bitubi.my.id' dengan domain Anda jika berbeda
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1') {
    // --- KREDENSIAL HOSTING (ISI SESUAI CPANEL) ---
    // Cara isi:
    // 1. Buka cPanel -> MySQL Databases
    // 2. Buat Database baru (misal: u123456_validation_db)
    // 3. Buat User baru (misal: u123456_user) dan Password
    // 4. Add User to Database (Centang ALL PRIVILEGES)
    // 5. Masukkan data tersebut di bawah ini:
    
    $host = 'localhost'; // Biasanya tetap localhost
    $db   = 'bitubimy_crud'; // cth: u123456_validation_db
    $user = 'bitubimy_izsaa';   // cth: u123456_user
    $pass = 'jokiizsaa200504';   // Password yang Anda buat
}
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Determine if we are on a local server or hosting environment
$is_local = (php_sapi_name() === 'cli') || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']));
?>
