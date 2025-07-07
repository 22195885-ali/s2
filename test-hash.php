<?php
// test-hash.php

// Hash'lemek istediğin şifreyi buraya yaz
$password = 'ayse';

// BCRYPT algoritması ile hash oluştur
$hash = password_hash($password, PASSWORD_BCRYPT);

// Ekrana yazdır
echo "Şifre: $password\n";
echo "Hash: $hash";
?>
