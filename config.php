<?php
$servername = "localhost";
$username = "root"; // Thay bằng username của bạn
$password = "123456"; // Thay bằng password của bạn
$dbname = "KTGK";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8'");
} catch(PDOException $e) {
    echo "Kết nối thất bại: " . $e->getMessage();
}
?>