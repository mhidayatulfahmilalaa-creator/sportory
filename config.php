<?php
// Konfigurasi Database
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sportory');

// Membuat koneksi database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset ke utf8mb4
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk menampilkan alert dan redirect
function alert_redirect($message, $url) {
    echo "<script>
            alert('" . addslashes($message) . "');
            window.location.href = '" . $url . "';
          </script>";
    exit();
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>