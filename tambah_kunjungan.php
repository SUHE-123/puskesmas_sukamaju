<?php
// tambah_kunjungan.php
include 'config/database.php';

$pasien_list = [];
$pasien_result = $conn->query("SELECT id_pasien, nama_pasien FROM pasien ORDER BY nama_pasien ASC");
while ($row = $pasien_result->fetch_assoc()) {
    $pasien_list[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pasien = $_POST['id_pasien'];
    $tanggal_kunjungan = $_POST['tanggal_kunjungan'];
    $diagnosa = $_POST['diagnosa'];
    $tindakan = $_POST['tindakan'];
    $biaya = $_POST['biaya'];

    $stmt = $conn->prepare("INSERT INTO kunjungan (id_pasien, tanggal_kunjungan, diagnosa, tindakan, biaya) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssd", $id_pasien, $tanggal_kunjungan, $diagnosa, $tindakan, $biaya);

    if ($stmt->execute()) {
        echo "<script>alert('Kunjungan berhasil ditambahkan!'); window.location.href='daftar_kunjungan.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kunjungan - Puskesmas Sukamaju</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tambah Kunjungan Baru</h1>
            <p>Puskesmas Sukamaju - Manajemen Kunjungan Pasien</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="id_pasien">Pilih Pasien:</label>
                    <select class="form-control" id="id_pasien" name="id_pasien" required>
                        <option value="">-- Pilih Pasien --</option>
                        <?php foreach ($pasien_list as $pasien): ?>
                            <option value="<?php echo $pasien['id_pasien']; ?>"><?php echo htmlspecialchars($pasien['nama_pasien']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tanggal_kunjungan">Tanggal Kunjungan:</label>
                    <input type="datetime-local" class="form-control" id="tanggal_kunjungan" name="tanggal_kunjungan" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="diagnosa">Diagnosa:</label>
                    <input type="text" class="form-control" id="diagnosa" name="diagnosa" required>
                </div>
                <div class="form-group">
                    <label for="tindakan">Tindakan:</label>
                    <textarea class="form-control" id="tindakan" name="tindakan"></textarea>
                </div>
                <div class="form-group">
                    <label for="biaya">Biaya:</label>
                    <input type="number" step="0.01" class="form-control" id="biaya" name="biaya" value="0.00">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Simpan Kunjungan</button>
                    <a href="daftar_kunjungan.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>