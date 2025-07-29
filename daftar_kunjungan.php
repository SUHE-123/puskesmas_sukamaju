<?php
// daftar_kunjungan.php
include 'config/database.php';

// Urutan DESC berarti kunjungan terbaru akan berada di paling atas (No. 1)
$query = "SELECT k.id_kunjungan, p.nama_pasien, k.tanggal_kunjungan, k.diagnosa, k.tindakan, k.biaya 
          FROM kunjungan k
          JOIN pasien p ON k.id_pasien = p.id_pasien
          ORDER BY k.tanggal_kunjungan DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kunjungan - Puskesmas Sukamaju</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daftar Kunjungan Pasien</h1>
            <p>Puskesmas Sukamaju - Rekam Medis Kunjungan</p>
        </div>

        <div class="action-buttons">
            <a href="tambah_kunjungan.php" class="btn btn-primary">Tambah Kunjungan Baru</a>
            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar Pasien</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Pasien</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Diagnosa</th>
                        <th>Tindakan</th>
                        <th>Biaya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $nomor = 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $nomor; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pasien']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($row['tanggal_kunjungan'])); ?></td>
                        <td><?php echo htmlspecialchars($row['diagnosa']); ?></td>
                        <td><?php echo htmlspecialchars($row['tindakan']); ?></td>
                        <td>Rp <?php echo number_format($row['biaya'], 2, ',', '.'); ?></td>
                        <td class="actions">
                            <a href="edit_kunjungan.php?id=<?php echo $row['id_kunjungan']; ?>" class="btn btn-warning">Edit</a>
                            <a href="hapus_kunjungan.php?id=<?php echo $row['id_kunjungan']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data kunjungan ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php $nomor++; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="no-data">
                <p>Belum ada data kunjungan.</p>
                <a href="tambah_kunjungan.php" class="btn btn-primary" style="margin-top: 15px;">Tambah Kunjungan Pertama</a>
            </div>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>
</body>
</html>