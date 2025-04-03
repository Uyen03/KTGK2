<?php
session_start();
include 'config.php';

if (!isset($_SESSION['MaSV'])) {
    header("Location: login.php");
    exit;
}

$MaSV = $_SESSION['MaSV'];
$NgayDK = date('Y-m-d');

$stmt = $conn->prepare("INSERT INTO DangKy (NgayDK, MaSV) VALUES (?, ?)");
$stmt->execute([$NgayDK, $MaSV]);
$MaDK = $conn->lastInsertId();

foreach ($_SESSION['cart'] as $MaHP) {
    $stmt = $conn->prepare("INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)");
    $stmt->execute([$MaDK, $MaHP]);

    $stmt = $conn->prepare("UPDATE HocPhan SET SoLuongDuKien = SoLuongDuKien - 1 WHERE MaHP = ?");
    $stmt->execute([$MaHP]);
}

$_SESSION['cart'] = [];
echo "Đăng ký thành công! <a href='index.php'>Quay lại</a>";
?>