<?php
echo "<h1>PHP Test Sayfası</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Tarih: " . date('Y-m-d H:i:s') . "</p>";

// Dosya yollarını kontrol et
echo "<h2>Dosya Kontrolleri:</h2>";
$files_to_check = [
    '../admin/inc/db.php',
    'index.php',
    'login.php',
    'register.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color:green'>✓ $file - Mevcut</p>";
    } else {
        echo "<p style='color:red'>✗ $file - Bulunamadı</p>";
    }
}

// Veritabanı bağlantısını test et
echo "<h2>Veritabanı Testi:</h2>";
try {
    if (file_exists('../admin/inc/db.php')) {
        require_once '../admin/inc/db.php';
        echo "<p style='color:green'>✓ Veritabanı dosyası yüklendi</p>";
        
        if (isset($db)) {
            $test = $db->query("SELECT 1");
            echo "<p style='color:green'>✓ Veritabanı bağlantısı başarılı</p>";
            
            // Tabloları kontrol et
            $tables = $db->query("SHOW TABLES")->fetchAll();
            echo "<p>Tablolar: " . count($tables) . " adet</p>";
            foreach ($tables as $table) {
                echo "<p>- " . implode(', ', $table) . "</p>";
            }
        } else {
            echo "<p style='color:red'>✗ \$db değişkeni tanımlı değil</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Veritabanı dosyası bulunamadı</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Hata: " . $e->getMessage() . "</p>";
}

phpinfo();
?>