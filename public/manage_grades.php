<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// 1. XỬ LÝ THÊM LỚP (Đã cập nhật thêm teacher_name)
if (isset($_POST['add_class'])) {
    $cname = mysqli_real_escape_string($conn, $_POST['class_name']);
    $tname = mysqli_real_escape_string($conn, $_POST['teacher_name']); // Lấy tên giảng viên
    
    // Thêm vào DB với cả tên lớp và tên giảng viên
    mysqli_query($conn, "INSERT INTO classes (class_name, teacher_name) VALUES ('$cname', '$tname')");
    $msg = "Thêm lớp học thành công!";
}

// 2. XỬ LÝ XÓA LỚP
if (isset($_GET['delete'])) {
    $cid = $_GET['delete'];
    $check = mysqli_query($conn, "SELECT id FROM student_info WHERE class_id = $cid");
    if (mysqli_num_rows($check) > 0) {
        $error = "Không thể xóa lớp đang có sinh viên!";
    } else {
        mysqli_query($conn, "DELETE FROM classes WHERE class_id = $cid");
        header("Location: manage_classes.php");
    }
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light" id="appHtml">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Lớp học | SMS PRO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #6366f1; --sidebar-w: 260px; }
        body { background-color: var(--bs-body-bg); font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar { width: var(--sidebar-w); height: 100vh; position: fixed; background: var(--bs-tertiary-bg); border-right: 1px solid var(--bs-border-color); padding: 2rem 1.5rem; }
        .main-content { margin-left: var(--sidebar-w); padding: 2.5rem; }
        .card-pro { background: var(--bs-custom-card-bg, #ffffff); border: 1px solid var(--bs-border-color); border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        [data-bs-theme="dark"] .card-pro { --bs-custom-card-bg: #1e1e2d; }
        .class-icon { width: 50px; height: 50px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    </style>
</head>
<body>

<div class="sidebar d-none d-lg-block">
    <div class="d-flex align-items-center mb-5">
        <div class="bg-primary text-white rounded-3 p-2 me-2"><i class="bi bi-mortarboard-fill"></i></div>
        <span class="fw-bold fs-4">SMS PRO</span>
    </div>
    <nav class="nav flex-column gap-2">
        <a href="home.php" class="nav-link text-muted"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a>
        <a href="#" class="nav-link text-muted"><i class="bi bi-people me-2"></i> Sinh viên</a>
        <a href="#" class="nav-link active bg-primary bg-opacity-10 text-primary rounded-3 fw-bold"><i class="bi bi-collection me-2"></i> Lớp học</a>
        <hr>
        <button onclick="toggleTheme()" class="btn btn-outline-secondary btn-sm w-100 mt-3"><i class="bi bi-moon-stars"></i> Giao diện</button>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Hệ thống Lớp học</h2>
            <p class="text-muted mb-0">Quản lý cấu trúc tổ chức của trường</p>
        </div>
        <button class="btn btn-primary px-4 py-2 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addClass">
            <i class="bi bi-plus-circle me-2"></i>Tạo lớp mới
        </button>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4"><?= $error ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php 
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM student_info WHERE class_id = c.class_id) as total_students 
                FROM classes c";
        $res = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($res)):
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-pro p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="class-icon"><i class="bi bi-door-open"></i></div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item text-danger" href="?delete=<?= $row['class_id'] ?>" onclick="return confirm('Xác nhận xóa lớp?')">Xóa lớp</a></li>
                        </ul>
                    </div>
                </div>
                <h5 class="fw-bold mb-1"><?= $row['class_name'] ?></h5>
                <p class="text-primary small mb-1"><i class="bi bi-person-badge me-1"></i> GV: <?= $row['teacher_name'] ?? 'Chưa cập nhật' ?></p>
                <p class="text-muted small">Mã lớp: CLS-00<?= $row['class_id'] ?></p>
                
                <div class="d-flex align-items-center mt-4">
                    <div class="flex-grow-1">
                        <div class="small fw-bold mb-1"><?= $row['total_students'] ?> Sinh viên</div>
                        <div class="progress" style="height: 6px; width: 80%;">
                            <div class="progress-bar bg-primary" style="width: <?= $row['total_students'] * 5 ?>%"></div>
                        </div>
                    </div>
                    <a href="class_details.php?id=<?= $row['class_id'] ?>" class="btn btn-sm btn-outline-primary rounded-3">Chi tiết</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="addClass" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <form method="POST">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">Khởi tạo lớp học mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">TÊN LỚP HỌC</label>
                        <input type="text" name="class_name" class="form-control bg-light border-0 p-3 rounded-4" placeholder="VD: Công nghệ thông tin K15" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">GIẢNG VIÊN CHỦ NHIỆM</label>
                        <input type="text" name="teacher_name" class="form-control bg-light border-0 p-3 rounded-4" placeholder="VD: Thầy Nguyễn Văn A" required>
                    </div>
                    <div class="p-3 bg-primary-subtle rounded-4 small text-primary">
                        <i class="bi bi-info-circle-fill me-2"></i> Lớp học mới sẽ xuất hiện trong danh sách chọn khi bạn thêm sinh viên mới.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="add_class" class="btn btn-primary w-100 py-3 rounded-4 fw-bold">XÁC NHẬN TẠO LỚP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleTheme() {
        const html = document.getElementById('appHtml');
        const current = html.getAttribute('data-bs-theme');
        html.setAttribute('data-bs-theme', current === 'light' ? 'dark' : 'light');
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>