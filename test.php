<?php
require 'backend/baglanti.php';
$q = mysqli_query($baglanti, 'SELECT kullanici_adi, sifre, rol FROM personel');
if ($q) {
    while($r = mysqli_fetch_assoc($q)){
        print_r($r);
    }
} else {
    echo mysqli_error($baglanti);
}
