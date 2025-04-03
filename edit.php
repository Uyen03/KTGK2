<?php
include 'config.php';

// Lấy thông tin sinh viên cần sửa
$MaSV = $_GET['MaSV'];
$stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = ?");
$stmt->execute([$MaSV]);
$sv = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $MaSV = $_POST['MaSV'];
    $HoTen = $_POST['HoTen'];
    $GioiTinh = $_POST['GioiTinh'];
    $NgaySinh = $_POST['NgaySinh'];
    $MaNganh = $_POST['MaNganh'];

    // Xử lý upload hình ảnh nếu có
    if (!empty($_FILES['Hinh']['name'])) {
        $Hinh = $_FILES['Hinh']['name'];
        move_uploaded_file($_FILES['Hinh']['tmp_name'], "Content/images/" . $Hinh);
        $HinhPath = "/Content/images/" . $Hinh;
    } else {
        // Giữ nguyên hình cũ nếu không upload hình mới
        $HinhPath = $sv['Hinh'];
    }

    // Cập nhật thông tin sinh viên
    $stmt = $conn->prepare("UPDATE SinhVien SET HoTen = ?, GioiTinh = ?, NgaySinh = ?, Hinh = ?, MaNganh = ? WHERE MaSV = ?");
    $stmt->execute([$HoTen, $GioiTinh, $NgaySinh, $HinhPath, $MaNganh, $MaSV]);

    // Chuyển hướng về trang danh sách
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin sinh viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #1cc88a;
            --text-color: #5a5c69;
            --border-color: #e3e6f0;
            --danger-color: #e74a3b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 10px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .form-control:read-only {
            background-color: #f8f9fc;
            cursor: not-allowed;
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 10px 20px;
            font-size: 16px;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-secondary {
            color: #fff;
            background-color: #858796;
            border-color: #858796;
        }
        
        .btn-secondary:hover {
            background-color: #717384;
            border-color: #6b6d7d;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .navbar-nav {
            display: flex;
            list-style: none;
        }
        
        .nav-item {
            margin-left: 15px;
        }
        
        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .current-image {
            max-width: 150px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            margin-bottom: 10px;
            border: 3px solid var(--border-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Quản lý Sinh viên</a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Trang chủ</a>
                </li>
            </ul>
        </nav>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-edit"></i> Sửa thông tin sinh viên
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="MaSV">Mã sinh viên</label>
                        <input type="text" class="form-control" id="MaSV" name="MaSV" value="<?php echo $sv['MaSV']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="HoTen" class="required">Họ tên</label>
                        <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo $sv['HoTen']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="GioiTinh" class="required">Giới tính</label>
                        <select class="form-control" id="GioiTinh" name="GioiTinh" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" <?php echo ($sv['GioiTinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nữ" <?php echo ($sv['GioiTinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="NgaySinh" class="required">Ngày sinh</label>
                        <input type="date" class="form-control" id="NgaySinh" name="NgaySinh" value="<?php echo $sv['NgaySinh']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Hình ảnh hiện tại</label>
                        <div>
                            <img src="<?php echo $sv['Hinh']; ?>" alt="<?php echo $sv['HoTen']; ?>" class="current-image">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="Hinh">Thay đổi hình ảnh (không bắt buộc)</label>
                        <input type="file" class="form-control" id="Hinh" name="Hinh" accept="image/*">
                        <small class="text-muted">Để trống nếu bạn muốn giữ nguyên hình ảnh hiện tại</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="MaNganh" class="required">Mã ngành</label>
                        <input type="text" class="form-control" id="MaNganh" name="MaNganh" value="<?php echo $sv['MaNganh']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <a href="detail.php?MaSV=<?php echo $sv['MaSV']; ?>" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>