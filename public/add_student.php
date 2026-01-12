<?php
session_start();
// --- 1. KẾT NỐI CƠ SỞ DỮ LIỆU ---
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// Lấy danh sách lớp học để hiển thị trong thẻ <select>
$classes_res = mysqli_query($conn, "SELECT * FROM classes");

$msg = "";
$error = "";

// --- 2. XỬ LÝ KHI NGƯỜI DÙNG NHẤN NÚT LƯU ---
if (isset($_POST['btn_save'])) {
    // Làm sạch dữ liệu đầu vào để chống SQL Injection
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address  = mysqli_real_escape_string($conn, $_POST['address']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);

    // BƯỚC QUAN TRỌNG: CHẶN LỖI TRÙNG EMAIL TRƯỚC KHI INSERT
    // Điều này ngăn chặn lỗi Fatal error "Duplicate entry" tại dòng 47
    $check_query = "SELECT id FROM student_info WHERE email = '$email' LIMIT 1";
    $check_res   = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_res) > 0) {
        // Nếu email đã tồn tại, hiện thông báo lỗi đẹp mắt
        $error = "Lỗi nghiêm trọng: Email <b>'$email'</b> đã được đăng ký cho sinh viên khác!";
    } else {
        // Nếu email an toàn, tiến hành thêm mới vào bảng student_info
        $sql = "INSERT INTO student_info (name, email, phone, address, class_id) 
                VALUES ('$name', '$email', '$phone', '$address', '$class_id')";
        
        if (mysqli_query($conn, $sql)) {
            $msg = "Thành công! Hồ sơ sinh viên <b>$name</b> đã được lưu vào cơ sở dữ liệu.";
            // Xóa dữ liệu cũ trong POST để làm trống form sau khi thành công
            unset($_POST);
        } else {
            $error = "Lỗi hệ thống không xác định: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sinh viên mới | SMS PRO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f7fe; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .form-card { border: none; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .form-label { font-weight: 600; color: #4b5563; font-size: 0.85rem; letter-spacing: 0.3px; }
        .form-control, .form-select { padding: 12px; border-radius: 12px; border: 1px solid #dee2e6; }
        .form-control:focus { border-color: #4e73df; box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.1); }
        .btn-primary { background: #4e73df; border: none; padding: 14px; border-radius: 14px; font-weight: 700; transition: all 0.3s; }
        .btn-primary:hover { background: #2e59d9; transform: translateY(-1px); box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3); }
        .alert { border-radius: 15px; border: none; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <a href="class_details.php?id=<?= $_GET['add_to_class'] ?? '' ?>" class="btn btn-link text-decoration-none mb-3 p-0 text-muted">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách lớp
            </a>

            <div class="card form-card bg-white p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary d-inline-block p-3 rounded-circle mb-3">
                        <i class="bi bi-person-plus-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Hồ sơ sinh viên</h3>
                    <p class="text-muted">Dữ liệu được bảo mật và kiểm tra trùng lặp</p>
                </div>

                <?php if($msg): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div><?= $msg ?></div>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-uppercase">Họ và Tên</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                               placeholder="Nhập tên đầy đủ" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-uppercase">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                   placeholder="email@example.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-uppercase">Điện thoại</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" 
                                   placeholder="09xxx...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-uppercase">Phân vào lớp</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">-- Chọn lớp học --</option>
                            <?php 
                            mysqli_data_seek($classes_res, 0); 
                            while($row = mysqli_fetch_assoc($classes_res)): 
                            ?>
                                <option value="<?= $row['class_id'] ?>" 
                                    <?= ((isset($_GET['add_to_class']) && $_GET['add_to_class'] == $row['class_id']) || (isset($_POST['class_id']) && $_POST['class_id'] == $row['class_id'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['class_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-uppercase">Địa chỉ liên lạc</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Số nhà, tên đường..."><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                    </div>

                    <button type="submit" name="btn_save" class="btn btn-primary w-100 shadow">
                        <i class="bi bi-sd-card-fill me-2"></i> LƯU THÔNG TIN SINH VIÊN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>