<?php
session_start();
// --- 1. KẾT NỐI DB ---
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// Kiểm tra quyền (Chỉ Admin mới được vào)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("<div class='container mt-5 alert alert-danger'>Bạn không có quyền truy cập trang này!</div>");
}

// --- 2. XỬ LÝ CẬP NHẬT QUYỀN ---
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    $update_sql = "UPDATE users SET role = '$new_role' WHERE id = $user_id";
    if (mysqli_query($conn, $update_sql)) {
        $msg = "Cập nhật quyền thành công!";
    }
}

// --- 3. LẤY DANH SÁCH NGƯỜI DÙNG ---
$query = "SELECT id, username, full_name, role FROM users";
$users = mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phân quyền người dùng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fc; }
        .card { border: none; border-radius: 12px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1); }
        .badge-admin { background-color: #e74a3b; }
        .badge-staff { background-color: #f6c23e; }
        .badge-student { background-color: #4e73df; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-gray-800"><i class="bi bi-shield-lock-fill text-primary"></i> Quản lý phân quyền</h3>
        <a href="home.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Quay lại Dashboard</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ và Tên</th>
                    <th>Quyền hiện tại</th>
                    <th>Thay đổi quyền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td class="fw-bold"><?= $u['username'] ?></td>
                    <td><?= $u['full_name'] ?? 'Chưa cập nhật' ?></td>
                    <td>
                        <span class="badge badge-<?= $u['role'] ?>">
                            <?= strtoupper($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" class="form-select form-select-sm" style="width: 130px;">
                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>ADMIN</option>
                                <option value="staff" <?= $u['role'] == 'staff' ? 'selected' : '' ?>>STAFF</option>
                                <option value="student" <?= $u['role'] == 'student' ? 'selected' : '' ?>>STUDENT</option>
                            </select>
                            <button type="submit" name="update_role" class="btn btn-primary btn-sm">Lưu</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>