<?php
include 'config/database.php';

if (!isset($_GET['id'])) {
    echo "ID kunjungan tidak ditemukan.";
    exit;
}

$id = $_GET['id'];

$query = "SELECT * FROM kunjungan WHERE id_kunjungan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Data kunjungan tidak ditemukan.";
    exit;
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosa = $_POST['diagnosa'];
    $tindakan = $_POST['tindakan'];
    $biaya = $_POST['biaya'];

    $update = "UPDATE kunjungan SET diagnosa=?, tindakan=?, biaya=? WHERE id_kunjungan=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssdi", $diagnosa, $tindakan, $biaya, $id);

    if ($stmt->execute()) {
        header("Location: daftar_kunjungan.php");
        exit;
    } else {
        echo "Gagal memperbarui data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kunjungan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Kunjungan Pasien</h1>
            <p>Perbarui data kunjungan berikut</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="diagnosa">Diagnosa</label>
                    <input type="text" id="diagnosa" name="diagnosa" class="form-control" value="<?= htmlspecialchars($data['diagnosa']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="tindakan">Tindakan</label>
                    <input type="text" id="tindakan" name="tindakan" class="form-control" value="<?= htmlspecialchars($data['tindakan']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="biaya">Biaya</label>
                    <input type="number" id="biaya" name="biaya" class="form-control" value="<?= $data['biaya'] ?>" required>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="daftar_kunjungan.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
