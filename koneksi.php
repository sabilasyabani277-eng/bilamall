<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "bilamall");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>