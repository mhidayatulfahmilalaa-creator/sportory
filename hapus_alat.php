<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$id = $_GET['id'];

// Hapus data alat
$query = "DELETE FROM alat_olahraga WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Alat olahraga berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus alat olahraga!";
}

redirect('dashboard.php');
?>