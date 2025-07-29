<?php
// hapus_pasien.php
include 'config/database.php';

$id_pasien = $_GET['id'] ?? 0;

if ($id_pasien > 0) {
    // Hapus kunjungan terkait terlebih dahulu (karena ada foreign key)
    $stmt_kunjungan = $conn->prepare("DELETE FROM kunjungan WHERE id_pasien = ?");
    $stmt_kunjungan->bind_param("i", $id_pasien);
    $stmt_kunjungan->execute();
    $stmt_kunjungan->close();

    // Hapus pasien
    $stmt_pasien = $conn->prepare("DELETE FROM pasien WHERE id_pasien = ?");
    $stmt_pasien->bind_param("i", $id_pasien);

    if ($stmt_pasien->execute()) {
        echo "<script>alert('Pasien dan kunjungan terkait berhasil dihapus!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $stmt_pasien->error;
    }
    $stmt_pasien->close();
} else {
    echo "<script>alert('ID Pasien tidak valid!'); window.location.href='index.php';</script>";
}
$conn->close();
?>