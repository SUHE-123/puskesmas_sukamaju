<?php
$host = '192.168.219.124';
$user = 'user1';
$password = ''; 
$database = 'db_puskesmas_sukamaju';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil!";
?>
