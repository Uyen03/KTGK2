<?php
session_start();
include 'config.php';

// Kiểm tra nếu sinh viên đã đăng nhập
if (!isset($_SESSION['MaSV'])) {
    header("Location: login.php");  // Nếu chưa đăng nhập, chuyển hướng đến trang login
    exit();
}

if (isset($_GET['MaHP'])) {
    $maSV = $_SESSION['MaSV'];
    $maHP = $_GET['MaHP'];

    // Truy vấn xóa học phần khỏi giỏ
    $stmt = $conn->prepare("DELETE FROM cart WHERE MaSV = :MaSV AND MaHP = :MaHP");
    $stmt->bindParam(':MaSV', $maSV);
    $stmt->bindParam(':MaHP', $maHP);
    $stmt->execute();

    // Sau khi xóa, quay lại trang giỏ hàng
    header("Location: cart.php");
    exit();
} else {
    echo "Lỗi: Không có học phần nào để xóa.";
}
?>
