<?php
// Hata raporlarını aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "acillvcs_lisans";
$user = "acillvcs_admin";
$pass = "Ata120303*";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bağlantı test et
    $test = $db->query("SELECT 1");
    
} catch (PDOException $e) {
    // Hata detaylarını göster
    die("Veritabanı bağlantı hatası: " . $e->getMessage() . "<br>Host: $host<br>Database: $dbname<br>User: $user");
}
?>