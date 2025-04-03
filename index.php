<?php
session_start();
include 'config.php';

// Kiểm tra nếu sinh viên đã đăng nhập
if (!isset($_SESSION['MaSV'])) {
    header("Location: login.php");  // Nếu chưa đăng nhập, chuyển hướng đến trang login
    exit();
}
// Số sinh viên mỗi trang
$students_per_page = 4;

// Lấy trang hiện tại từ URL (mặc định là trang 1 nếu không có trang)
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Tính toán offset (vị trí bắt đầu của kết quả trong cơ sở dữ liệu)
$offset = ($page - 1) * $students_per_page;

// Truy vấn lấy dữ liệu sinh viên với phân trang
$stmt = $conn->prepare("SELECT * FROM SinhVien LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $students_per_page, PDO::PARAM_INT);
$stmt->execute();
$sinhviens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tổng số sinh viên để tính toán tổng số trang
$stmt_total = $conn->prepare("SELECT COUNT(*) FROM SinhVien");
$stmt_total->execute();
$total_students = $stmt_total->fetchColumn();
$total_pages = ceil($total_students / $students_per_page);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Sinh Viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info p {
            margin-right: 15px;
        }
        
        .logout-btn {
            background-color: var(--light-color);
            color: var(--dark-color);
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background-color: #e74c3c;
            color: white;
        }
        
        .actions-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .control-panel {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .control-panel a {
            display: inline-block;
            margin-right: 15px;
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .control-panel a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .student-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .student-table th, .student-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .student-table th {
            background-color: var(--dark-color);
            color: white;
            font-weight: 600;
        }
        
        .student-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .student-table tr:hover {
            background-color: #e9f5ff;
        }
        
        .student-table img {
            border-radius: 50%;
            object-fit: cover;
            width: 50px;
            height: 50px;
            border: 2px solid var(--primary-color);
        }
        
        .action-links a {
            display: inline-block;
            margin-right: 8px;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .edit-btn {
            background-color: var(--warning-color);
        }
        
        .delete-btn {
            background-color: var(--danger-color);
        }
        
        .detail-btn {
            background-color: var(--success-color);
        }
        
        .action-links a:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: var(--dark-color);
            background-color: white;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .user-info {
                margin-bottom: 15px;
                flex-direction: column;
            }
            
            .control-panel {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .control-panel a {
                margin-bottom: 10px;
            }
            
            .student-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1><i class="fas fa-user-graduate"></i> Hệ Thống Quản Lý Sinh Viên</h1>
                <div class="user-info">
                    <p>Xin chào, <strong><?php echo $_SESSION['HoTen']; ?></strong> (MSSV: <?php echo $_SESSION['MaSV']; ?>)</p>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="control-panel">
            <a href="create.php"><i class="fas fa-plus-circle"></i> Thêm sinh viên mới</a>
            <a href="dangky.php"><i class="fas fa-book"></i> Đăng ký học phần</a>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
        </div>

        <h2><i class="fas fa-list"></i> Danh Sách Sinh Viên</h2>
        <p>Hiển thị <?php echo count($sinhviens); ?> sinh viên trên tổng số <?php echo $total_students; ?></p>
        
        <table class="student-table">
            <thead>
                <tr>
                    <th>Mã SV</th>
                    <th>Họ Tên</th>
                    <th>Giới Tính</th>
                    <th>Ngày Sinh</th>
                    <th>Hình</th>
                    <th>Mã Ngành</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sinhviens as $sv): ?>
                <tr>
                    <td><?php echo $sv['MaSV']; ?></td>
                    <td><?php echo $sv['HoTen']; ?></td>
                    <td><?php echo $sv['GioiTinh']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($sv['NgaySinh'])); ?></td>
                    <td><img src="<?php echo !empty($sv['Hinh']) ? $sv['Hinh'] : 'images/default-avatar.png'; ?>" alt="<?php echo $sv['HoTen']; ?>"></td>
                    <td><?php echo $sv['MaNganh']; ?></td>
                    <td class="action-links">
                        <a href="edit.php?MaSV=<?php echo $sv['MaSV']; ?>" class="edit-btn"><i class="fas fa-edit"></i> Sửa</a>
                        <a href="delete.php?MaSV=<?php echo $sv['MaSV']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?')" class="delete-btn"><i class="fas fa-trash-alt"></i> Xóa</a>
                        <a href="detail.php?MaSV=<?php echo $sv['MaSV']; ?>" class="detail-btn"><i class="fas fa-info-circle"></i> Chi tiết</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="index.php?page=1"><i class="fas fa-angle-double-left"></i></a>
                <a href="index.php?page=<?php echo $page-1; ?>"><i class="fas fa-angle-left"></i></a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="index.php?page=<?php echo $i; ?>" <?php if($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="index.php?page=<?php echo $page+1; ?>"><i class="fas fa-angle-right"></i></a>
                <a href="index.php?page=<?php echo $total_pages; ?>"><i class="fas fa-angle-double-right"></i></a>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Hệ Thống Quản Lý Sinh Viên | Thiết kế bởi <a href="#">QLSV</a></p>
        </div>
    </footer>
</body>
</html>