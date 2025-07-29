<?php
// hapus_kunjungan.php
include 'config/database.php';

if (!isset($_GET['id'])) {
    echo "ID kunjungan tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

$query = "DELETE FROM kunjungan WHERE id_kunjungan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: daftar_kunjungan.php");
    exit;
} else {
    echo "Gagal menghapus data kunjungan.";
}
?>
