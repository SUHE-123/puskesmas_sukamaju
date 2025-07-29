<?php
// edit_pasien.php
include 'config/database.php';

$id_pasien = $_GET['id'] ?? 0;
$pasien = null;

if ($id_pasien > 0) {
    $stmt = $conn->prepare("SELECT * FROM pasien WHERE id_pasien = ?");
    $stmt->bind_param("i", $id_pasien);
    $stmt->execute();
    $result = $stmt->get_result();
    $pasien = $result->fetch_assoc();
    $stmt->close();
}

if (!$pasien) {
    echo "<script>alert('Pasien tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pasien = $_POST['nama_pasien'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];

    $stmt = $conn->prepare("UPDATE pasien SET nama_pasien = ?, tanggal_lahir = ?, alamat = ?, no_telepon = ? WHERE id_pasien = ?");
    $stmt->bind_param("ssssi", $nama_pasien, $tanggal_lahir, $alamat, $no_telepon, $id_pasien);

    if ($stmt->execute()) {
        echo "<script>alert('Data pasien berhasil diperbarui!'); window.location.href='index.php';</script>";
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
    <title>Edit Pasien - Puskesmas Sukamaju</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Data Pasien</h1>
            <p>Puskesmas Sukamaju - Manajemen Data Pasien</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nama_pasien">Nama Pasien:</label>
                    <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" value="<?php echo htmlspecialchars($pasien['nama_pasien']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($pasien['tanggal_lahir']); ?>">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <textarea class="form-control" id="alamat" name="alamat"><?php echo htmlspecialchars($pasien['alamat']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($pasien['no_telepon']); ?>">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Update Pasien</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>