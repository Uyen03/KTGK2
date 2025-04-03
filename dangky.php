<?php
session_start();
include 'config.php';

$stmt = $conn->prepare("SELECT * FROM HocPhan WHERE SoLuongDuKien > 0");
$stmt->execute();
$hocphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['add'])) {
    $MaHP = $_GET['add'];
    if (!in_array($MaHP, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $MaHP;
        
        // Thêm thông báo khi thêm học phần thành công
        $success_message = "Đã thêm học phần vào giỏ!";
    } else {
        // Thông báo nếu học phần đã có trong giỏ
        $error_message = "Học phần này đã có trong giỏ của bạn!";
    }
}

// Đếm số học phần trong giỏ
$cart_count = count($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký học phần</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4285f4;
            --primary-dark: #3367d6;
            --secondary-color: #f1f1f1;
            --text-color: #333;
            --light-text: #666;
            --danger-color: #ea4335;
            --success-color: #34a853;
            --warning-color: #fbbc05;
            --border-radius: 8px;
            --box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        header h1 {
            color: var(--primary-color);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-menu {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .nav-links {
            display: flex;
            gap: 10px;
        }

        .cart-button {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .cart-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger-color);
            color: white;
            font-size: 0.7em;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            text-decoration: none;
            background-color: white;
            color: var(--text-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: rgba(52, 168, 83, 0.2);
            color: var(--success-color);
            border: 1px solid rgba(52, 168, 83, 0.3);
        }

        .alert-danger {
            background-color: rgba(234, 67, 53, 0.2);
            color: var(--danger-color);
            border: 1px solid rgba(234, 67, 53, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
        }

        table tr {
            background-color: white;
            border-bottom: 1px solid #f1f1f1;
            transition: var(--transition);
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        table tr:last-child {
            border-bottom: none;
        }

        table td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .credit-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            background-color: rgba(66, 133, 244, 0.2);
            color: var(--primary-color);
            font-weight: 600;
        }

        .quantity-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            background-color: rgba(52, 168, 83, 0.2);
            color: var(--success-color);
            font-weight: 600;
        }

        .action-buttons a {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-size: 0.85em;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
        }

        .action-buttons a:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        .action-buttons a i {
            margin-right: 4px;
            font-size: 0.9em;
        }

        .search-container {
            padding: 15px;
            background-color: var(--secondary-color);
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .search-container form {
            display: flex;
            gap: 10px;
        }

        .search-container input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            flex-grow: 1;
        }

        .search-container button {
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        footer {
            margin-top: 30px;
            text-align: center;
            color: var(--light-text);
            font-size: 0.9em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .nav-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .search-container form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-book"></i> Đăng ký học phần</h1>
            
            <a href="cart.php" class="cart-button">
                <i class="fas fa-shopping-cart"></i> Giỏ học phần
                <?php if($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </header>

        <div class="nav-menu">
            <div class="nav-links">
                <a href="index.php" class="btn"><i class="fas fa-home"></i> Trang chủ</a>
            </div>
        </div>

        <?php if(isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if(isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <div class="search-container">
            <form action="" method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm học phần..." />
                <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="10%">Mã HP</th>
                    <th width="40%">Tên học phần</th>
                    <th width="15%">Số tín chỉ</th>
                    <th width="20%">Số lượng dự kiến</th>
                    <th width="15%">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hocphans as $hp): ?>
                <tr>
                    <td><strong><?php echo $hp['MaHP']; ?></strong></td>
                    <td><?php echo $hp['TenHP']; ?></td>
                    <td><span class="credit-badge"><?php echo $hp['SoTinChi']; ?> tín chỉ</span></td>
                    <td><span class="quantity-badge"><?php echo $hp['SoLuongDuKien']; ?> sinh viên</span></td>
                    <td class="action-buttons">
                        <a href="dangky.php?add=<?php echo $hp['MaHP']; ?>">
                            <i class="fas fa-plus-circle"></i> Thêm vào giỏ
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Hệ thống Đăng ký Học phần</p>
        </footer>
    </div>
</body>
</html>