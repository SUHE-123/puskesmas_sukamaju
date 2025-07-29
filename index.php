<?php
// index.php
include 'config/database.php';

// Catatan: Urutan data (ASC/DESC) akan memengaruhi siapa yang mendapat nomor 1.
// ORDER BY tanggal_daftar ASC -> Pasien terlama mendapat nomor 1
// ORDER BY tanggal_daftar DESC -> Pasien terbaru mendapat nomor 1
$query = "SELECT id_pasien, nama_pasien, tanggal_daftar FROM pasien ORDER BY tanggal_daftar ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Puskesmas Sukamaju</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistem Informasi Puskesmas Sukamaju</h1>
            <p>Manajemen Data Pasien Terpadu</p>
        </div>

        <div class="action-buttons">
            <a href="tambah_pasien.php" class="btn btn-primary">Tambah Pasien Baru</a>
            <a href="daftar_kunjungan.php" class="btn btn-secondary">Lihat Daftar Kunjungan</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Pasien</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $nomor = 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $nomor; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pasien']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($row['tanggal_daftar'])); ?></td>
                        <td class="actions">
                            <a href="edit_pasien.php?id=<?php echo $row['id_pasien']; ?>" class="btn btn-warning">Edit</a>
                            <a href="hapus_pasien.php?id=<?php echo $row['id_pasien']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus pasien ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php $nomor++; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="no-data">
                <p>Belum ada data pasien.</p>
                <a href="tambah_pasien.php" class="btn btn-primary" style="margin-top: 15px;">Tambah Pasien Pertama</a>
            </div>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>
</body>
</html>