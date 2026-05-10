<?php
// Oturum varsa tekrar başlatma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// Türkçe Karakter (UTF-8) Desteği
// =============================================
ob_start();
mb_internal_encoding('UTF-8');
mb_language('Turkish');

$host   = 'localhost';
$dbname = 'hmts_db';
$user   = 'root';
$pass   = '';

$baglanti = mysqli_connect($host, $user, $pass, $dbname);

if (!$baglanti) {
    die("<div style='text-align:center;padding:60px;background:#0a0f1e;color:#ef4444;font-family:Inter,sans-serif;font-size:18px;'>
        Veritabani baglantisi kurulamadi: " . mysqli_connect_error() . "
    </div>");
}

// MySQL bağlantısını UTF-8 olarak ayarla
mysqli_set_charset($baglanti, 'utf8mb4');
mysqli_query($baglanti, "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_turkish_ci'");
mysqli_query($baglanti, "SET CHARACTER SET utf8mb4");
mysqli_query($baglanti, "SET SESSION collation_connection = 'utf8mb4_turkish_ci'");

