<?php
// kirim_rekap.php (Di laptop Puskesmas, contoh: Puskesmas Sukamaju)

// Mengaktifkan laporan kesalahan PHP untuk debugging.
// Hapus atau komentar baris ini setelah aplikasi stabil di lingkungan produksi.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file konfigurasi database lokal Puskesmas.
include 'config/database.php';

// URL API penerima replikasi di Dinkes Kota.
// *** PENTING: GANTI DENGAN IP ATAU DOMAIN LAPTOP DINKES KOTA YANG SEBENARNYA ***
// Pastikan firewall di laptop Dinkes Kota mengizinkan koneksi masuk ke port 80/443 (HTTP/HTTPS)
// dan MySQL mengizinkan koneksi dari IP Puskesmas.
$dinkes_api_url = "http://[IP_LAPTOP_DINKES_KOTA]/proyek_puskesmas/dinkes_kota/api/menerima_replikasi.php";
// Contoh: "http://192.168.219.100/proyek_puskesmas/dinkes_kota/api/menerima_replikasi.php"

// Nama Puskesmas ini (harus sesuai dengan yang diharapkan oleh API Dinkes Kota).
// GANTI SESUAI PUSKESMAS ANDA: 'Sukamaju' atau 'Mekarsari'
$asal_puskesmas = "Sukamaju"; // Untuk Puskesmas Sukamaju

// Tanggal yang akan direkap.
// Secara default, ini akan merekap data untuk TANGGAL KEMARIN.
// Jika Anda ingin merekap data hari ini, ubah strtotime("-1 day") menjadi "today" atau biarkan kosong.
$tanggal_rekap = date("Y-m-d", strtotime("-1 day"));

// Pastikan koneksi database Puskesmas berhasil sebelum melanjutkan.
if ($conn_puskesmas->connect_error) {
    echo "Fatal Error: Koneksi ke database Puskesmas gagal: " . $conn_puskesmas->connect_error . "\n";
    exit; // Hentikan eksekusi script jika koneksi gagal
}

echo "Memulai proses rekap data untuk tanggal {$tanggal_rekap} dari {$asal_puskesmas}.\n";

// --- Query untuk Mengagregasi Data dari tabel 'kunjungan' ---

// 1. Hitung Total Kunjungan
$jumlah_kunjungan = 0;
// Menggunakan DATE() untuk mengabaikan bagian waktu dari tanggal_kunjungan jika ada
$query_total_kunjungan = "SELECT COUNT(*) AS total_kunjungan FROM kunjungan WHERE DATE(tanggal_kunjungan) = ?";
$stmt_total_kunjungan = $conn_puskesmas->prepare($query_total_kunjungan);
if ($stmt_total_kunjungan) {
    $stmt_total_kunjungan->bind_param("s", $tanggal_rekap);
    $stmt_total_kunjungan->execute();
    $result_total_kunjungan = $stmt_total_kunjungan->get_result();
    $row_total_kunjungan = $result_total_kunjungan->fetch_assoc();
    $jumlah_kunjungan = $row_total_kunjungan['total_kunjungan'] ?? 0;
    $stmt_total_kunjungan->close();
} else {
    echo "Error menyiapkan query total kunjungan: " . $conn_puskesmas->error . "\n";
}

// 2. Hitung Jumlah Pasien Baru
// Asumsi: Pasien baru adalah mereka yang id_pasien-nya muncul untuk pertama kali pada tanggal tersebut
// dari tabel kunjungan. Jika definisi "pasien baru" di sistem Anda lebih kompleks
// (misalnya, pasien yang tidak pernah terdaftar di Puskesmas sebelumnya),
// Anda perlu menyesuaikan logika query ini atau mengambil dari tabel pasien master.
$jumlah_pasien_baru = 0;
// Subquery untuk menemukan id_pasien yang pertama kali muncul pada tanggal_rekap
$query_pasien_baru = "
    SELECT COUNT(DISTINCT T1.id_pasien) AS total_pasien_baru
    FROM kunjungan T1
    WHERE DATE(T1.tanggal_kunjungan) = ?
    AND NOT EXISTS (
        SELECT 1
        FROM kunjungan T2
        WHERE T2.id_pasien = T1.id_pasien
        AND DATE(T2.tanggal_kunjungan) < ?
    )";
$stmt_pasien_baru = $conn_puskesmas->prepare($query_pasien_baru);
if ($stmt_pasien_baru) {
    $stmt_pasien_baru->bind_param("ss", $tanggal_rekap, $tanggal_rekap);
    $stmt_pasien_baru->execute();
    $result_pasien_baru = $stmt_pasien_baru->get_result();
    $row_pasien_baru = $result_pasien_baru->fetch_assoc();
    $jumlah_pasien_baru = $row_pasien_baru['total_pasien_baru'] ?? 0;
    $stmt_pasien_baru->close();
} else {
    echo "Error menyiapkan query pasien baru: " . $conn_puskesmas->error . "\n";
}


// 3. Cari Diagnosa Terbanyak
$diagnosa_terbanyak = '-';
$query_diagnosa_terbanyak = "
    SELECT diagnosa
    FROM kunjungan
    WHERE DATE(tanggal_kunjungan) = ? AND diagnosa IS NOT NULL AND diagnosa != ''
    GROUP BY diagnosa
    ORDER BY COUNT(*) DESC
    LIMIT 1";
$stmt_diagnosa_terbanyak = $conn_puskesmas->prepare($query_diagnosa_terbanyak);
if ($stmt_diagnosa_terbanyak) {
    $stmt_diagnosa_terbanyak->bind_param("s", $tanggal_rekap);
    $stmt_diagnosa_terbanyak->execute();
    $result_diagnosa_terbanyak = $stmt_diagnosa_terbanyak->get_result();
    $row_diagnosa_terbanyak = $result_diagnosa_terbanyak->fetch_assoc();
    $diagnosa_terbanyak = $row_diagnosa_terbanyak['diagnosa'] ?? '-'; // Default jika tidak ada diagnosa
    $stmt_diagnosa_terbanyak->close();
} else {
    echo "Error menyiapkan query diagnosa terbanyak: " . $conn_puskesmas->error . "\n";
}

// Tutup koneksi Puskesmas setelah selesai mengambil data.
$conn_puskesmas->close();

// Data yang akan dikirim ke API Dinkes Kota dalam format JSON.
$post_data = [
    'tanggal_rekap'      => $tanggal_rekap,
    'asal_puskesmas'     => $asal_puskesmas,
    'jumlah_pasien_baru' => $jumlah_pasien_baru,
    'jumlah_kunjungan'   => $jumlah_kunjungan,
    'diagnosa_terbanyak' => $diagnosa_terbanyak
];

echo "Data yang akan dikirim: " . json_encode($post_data) . "\n";

// Inisialisasi cURL untuk mengirim data ke API Dinkes Kota.
$ch = curl_init($dinkes_api_url);

// Set opsi cURL.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan transfer sebagai string daripada langsung menampilkannya.
curl_setopt($ch, CURLOPT_POST, true);           // Mengatur metode permintaan HTTP ke POST.
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data)); // Mengirim data POST dalam format JSON.
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Mengatur header Content-Type ke application/json.

// Eksekusi cURL dan dapatkan respons dari server Dinkes Kota.
$response = curl_exec($ch);

// Periksa kesalahan cURL (misalnya, masalah jaringan, URL salah).
if (curl_errno($ch)) {
    echo "Error cURL saat mengirim data ke Dinkes Kota: " . curl_error($ch) . "\n";
} else {
    // Decode respons JSON dari API Dinkes Kota.
    $decoded_response = json_decode($response, true);
    if ($decoded_response && isset($decoded_response['status'])) {
        echo "Status Pengiriman ke Dinkes Kota: " . $decoded_response['status'] . "\n";
        echo "Pesan dari Dinkes Kota: " . $decoded_response['message'] . "\n";
    } else {
        // Jika respons tidak dalam format JSON yang diharapkan.
        echo "Respons tidak valid dari Dinkes Kota: " . $response . "\n";
    }
}

// Tutup sesi cURL.
curl_close($ch);

echo "\nProses pengiriman data rekap harian untuk tanggal {$tanggal_rekap} dari {$asal_puskesmas} selesai.\n";

?>