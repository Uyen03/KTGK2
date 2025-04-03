<?php
session_start();
include 'config.php';

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['MaSV'])) {
    header("Location: login.php");  // Nếu chưa đăng nhập, chuyển hướng đến trang login
    exit();
}

// Lấy mã sinh viên từ session
$maSV = $_SESSION['MaSV'];

// Xử lý thêm học phần vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['MaHP'])) {
    $maHP = $_POST['MaHP'];
    $soLuong = $_POST['SoLuong'];

    // Truy vấn kiểm tra học phần đã có trong giỏ hàng chưa
    $stmt_check = $conn->prepare("SELECT * FROM cart WHERE MaSV = :MaSV AND MaHP = :MaHP");
    $stmt_check->bindParam(':MaSV', $maSV);
    $stmt_check->bindParam(':MaHP', $maHP);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        // Nếu học phần đã có trong giỏ hàng, cập nhật số lượng
        $stmt_update = $conn->prepare("UPDATE cart SET SoLuong = SoLuong + :SoLuong WHERE MaSV = :MaSV AND MaHP = :MaHP");
        $stmt_update->bindParam(':MaSV', $maSV);
        $stmt_update->bindParam(':MaHP', $maHP);
        $stmt_update->bindParam(':SoLuong', $soLuong);
        $stmt_update->execute();
    } else {
        // Nếu học phần chưa có trong giỏ hàng, thêm vào giỏ
        $stmt_insert = $conn->prepare("INSERT INTO cart (MaSV, MaHP, SoLuong) VALUES (:MaSV, :MaHP, :SoLuong)");
        $stmt_insert->bindParam(':MaSV', $maSV);
        $stmt_insert->bindParam(':MaHP', $maHP);
        $stmt_insert->bindParam(':SoLuong', $soLuong);
        $stmt_insert->execute();
    }

    // Thông báo thành công
    $success_message = "Đã thêm học phần vào giỏ hàng thành công!";
}

// Truy vấn để lấy thông tin giỏ hàng
$query = "SELECT c.MaHP, h.TenHP, c.SoLuong, h.SoTinChi 
          FROM cart c
          JOIN HocPhan h ON c.MaHP = h.MaHP
          WHERE c.MaSV = :MaSV";
$stmt = $conn->prepare($query);
$stmt->bindParam(':MaSV', $maSV);
$stmt->execute();
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hiển thị danh sách các học phần có thể đăng ký
$query_hp = "SELECT * FROM HocPhan";
$stmt_hp = $conn->prepare($query_hp);
$stmt_hp->execute();
$hocPhans = $stmt_hp->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng số tín chỉ
$totalCredits = 0;
foreach ($cartItems as $item) {
    $totalCredits += $item['SoTinChi'] * $item['SoLuong'];
}

// Xử lý hoàn tất đăng ký
if (isset($_POST['complete_registration'])) {
    // Thêm dữ liệu vào bảng dangky
    $stmt_dangky = $conn->prepare("INSERT INTO dangky (MaSV, NgayDK) VALUES (:MaSV, NOW())");
    $stmt_dangky->bindParam(':MaSV', $maSV);
    $stmt_dangky->execute();

    // Lấy MaDK vừa insert
    $maDK = $conn->lastInsertId();  // Lấy ID của bản ghi mới vừa thêm vào bảng dangky

    // Thêm vào bảng chitietdangky
    foreach ($cartItems as $item) {
        $stmt_chitiet = $conn->prepare("INSERT INTO chitietdangky (MaDK, MaHP) VALUES (:MaDK, :MaHP)");
        $stmt_chitiet->bindParam(':MaDK', $maDK);
        $stmt_chitiet->bindParam(':MaHP', $item['MaHP']);
        $stmt_chitiet->execute();
    }

    // Sau khi đăng ký thành công, xóa giỏ hàng
    $stmt_delete_cart = $conn->prepare("DELETE FROM cart WHERE MaSV = :MaSV");
    $stmt_delete_cart->bindParam(':MaSV', $maSV);
    $stmt_delete_cart->execute();

    // Chuyển hướng sau khi hoàn tất đăng ký
    header("Location: registration_success.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký học phần</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .cart-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .table th {
            background-color: #3498db;
            color: white;
        }

        .btn-register {
            background-color: #2ecc71;
            border-color: #27ae60;
        }

        .btn-register:hover {
            background-color: #27ae60;
            border-color: #219653;
        }

        .empty-cart {
            padding: 30px;
            text-align: center;
            color: #7f8c8d;
        }

        .remove-btn {
            color: #e74c3c;
        }

        .remove-btn:hover {
            color: #c0392b;
        }

        .credit-summary {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            padding: 10px 0;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4">Đăng Ký Học Phần</h2>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Giỏ hàng học phần -->
                <div class="cart-container">
                    <h3 class="section-title">
                        <i class="fas fa-shopping-cart me-2"></i>Giỏ hàng của bạn
                    </h3>

                    <?php if (count($cartItems) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã HP</th>
                                        <th>Tên học phần</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-center">Số tín chỉ</th>
                                        <th class="text-center">Tổng tín chỉ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td><?php echo $item['MaHP']; ?></td>
                                            <td><?php echo $item['TenHP']; ?></td>
                                            <td class="text-center"><?php echo $item['SoLuong']; ?></td>
                                            <td class="text-center"><?php echo $item['SoTinChi']; ?></td>
                                            <td class="text-center"><?php echo $item['SoTinChi'] * $item['SoLuong']; ?></td>
                                            <td class="text-end">
                                                <!-- Nút xóa với biểu tượng -->
                                                <a href="delete_cart.php?MaHP=<?php echo $item['MaHP']; ?>" class="remove-btn">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="credit-summary">
                                    <i class="fas fa-calculator me-2"></i>Tổng số tín chỉ: <?php echo $totalCredits; ?>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="checkout.php" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Hoàn tất đăng ký
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                            <p>Giỏ hàng của bạn hiện đang trống.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Form đăng ký học phần mới -->
                <div class="cart-container">
                    <h3 class="section-title">
                        <i class="fas fa-plus-circle me-2"></i>Đăng ký học phần mới
                    </h3>

                    <form method="POST" action="cart.php" class="row g-3">
                        <div class="col-md-8">
                            <label for="MaHP" class="form-label">Chọn học phần:</label>
                            <select name="MaHP" id="MaHP" class="form-select">
                                <?php foreach ($hocPhans as $hocPhan): ?>
                                    <option value="<?php echo $hocPhan['MaHP']; ?>">
                                        <?php echo $hocPhan['TenHP'] . ' (' . $hocPhan['SoTinChi'] . ' tín chỉ)'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="SoLuong" class="form-label">Số lượng:</label>
                            <input type="number" name="SoLuong" id="SoLuong" class="form-control" value="1" min="1">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-register">
                                <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                            </button>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>