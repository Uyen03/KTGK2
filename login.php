<?php
session_start();  // Phải gọi session_start() ở đầu file
include 'config.php';

// Kiểm tra nếu form đã được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maSV = $_POST['MaSV'];
    $password = $_POST['Password'];

    // Truy vấn kiểm tra mã sinh viên và mật khẩu
    $stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = :MaSV AND Password = :Password");
    $stmt->bindParam(':MaSV', $maSV);
    $stmt->bindParam(':Password', $password);
    $stmt->execute();
    $sinhVien = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sinhVien) {
        // Đăng nhập thành công, lưu thông tin sinh viên vào session
        $_SESSION['MaSV'] = $sinhVien['MaSV'];
        $_SESSION['HoTen'] = $sinhVien['HoTen'];

        // Chuyển hướng đến trang danh sách sinh viên
        header("Location: index.php");
        exit();
    } else {
        echo 'Mã sinh viên hoặc mật khẩu không đúng!';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng Nhập</title>
</head>
<body>
    <h2>Đăng nhập</h2>
    <form method="POST" action="login.php">
        <label for="MaSV">Mã Sinh Viên:</label>
        <input type="text" name="MaSV" required><br><br>

        <label for="Password">Mật khẩu:</label>
        <input type="password" name="Password" required><br><br>

        <button type="submit">Đăng Nhập</button>
    </form>
</body>
</html>
