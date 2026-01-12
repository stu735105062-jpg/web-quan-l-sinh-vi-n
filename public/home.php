<?php
// 1. Khởi tạo session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Kết nối Database (Thay đổi thông tin nếu cần)
$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'student_management';
$conn = mysqli_connect($host, $user, $pass, $db);
mysqli_set_charset($conn, "utf8mb4");

// 3. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit();
}

$u_id = $_SESSION['user_id'];

// 4. Lấy thông tin user hiện tại
$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = $u_id");
$current_user = mysqli_fetch_assoc($user_res);
$role = $current_user['role'] ?? 'student';

// 5. Xử lý các hành động (Actions)
$action = $_GET['action'] ?? '';

// Cập nhật quyền (Chỉ Admin)
if ($action == 'change_role' && $_SERVER['REQUEST_METHOD'] == 'POST' && $role == 'admin') {
    $target_id = intval($_POST['target_user_id']);
    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
    
    if ($target_id == $u_id) {
        $_SESSION['flash'] = ['msg' => 'Bạn không thể tự đổi quyền của chính mình!', 'type' => 'danger'];
    } else {
        mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = $target_id");
        $_SESSION['flash'] = ['msg' => 'Cập nhật quyền thành success!', 'type' => 'success'];
    }
    header("Location: home.php"); exit();
}

// Xóa sinh viên (Chỉ Admin)
if ($action == 'delete' && isset($_GET['id']) && $role == 'admin') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM student_info WHERE id = $id");
    $_SESSION['flash'] = ['msg' => 'Đã xóa sinh viên!', 'type' => 'warning'];
    header("Location: home.php"); exit();
}

// 6. Truy vấn dữ liệu hiển thị
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = !empty($search) ? " AND (s.name LIKE '%$search%' OR s.email LIKE '%$search%')" : "";

$query = "SELECT s.*, c.class_name, 
          (SELECT COALESCE(AVG(score), 0) FROM grades WHERE student_id = s.id) as gpa 
          FROM student_info s 
          LEFT JOIN classes c ON s.class_id = c.class_id 
          WHERE 1=1 $where";
$students = mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC);

// Thống kê điểm danh cho biểu đồ
$att_res = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM attendance GROUP BY status");
$att_data = ['present' => 0, 'absent' => 0];
while($row = mysqli_fetch_assoc($att_res)) {
    $att_data[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="vi" data-bs-theme="light" id="htmlTag">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS PRO - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-w: 260px; }
        body { background: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: var(--sidebar-w); height: 100vh; position: fixed; background: #4e73df; color: white; z-index: 1000; }
        .main-content { margin-left: var(--sidebar-w); padding: 2rem; }
        .card { border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); border-radius: 12px; }
        .nav-link { color: rgba(255,255,255,0.8); margin: 5px 15px; border-radius: 8px; }
        .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>

<div class="sidebar d-flex flex-column p-3">
    <h4 class="text-center fw-bold py-3"><i class="bi bi-mortarboard-fill"></i> SMS PRO</h4>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li><a href="home.php" class="nav-link active"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
        <li><a href="manage_grades.php" class="nav-link"><i class="bi bi-person-check me-2"></i> Bảng điểm lớp</a></li>
        <?php if($role != 'student'): ?>
            <li><a href="export.php?action=export" class="nav-link"><i class="bi bi-cloud-download me-2"></i> Xuất dữ liệu</a></li>
        <?php endif; ?>
    </ul>
    <div class="px-3 mb-3 text-center">
        <span class="badge bg-warning text-dark text-uppercase px-3 py-2">Quyền: <?= $role ?></span>
    </div>
    <a href="logout.php" class="btn btn-danger btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
</div>

<div class="main-content">
    <?php if(isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
            <?= $_SESSION['flash']['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <header class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Hệ Thống Quản Trị</h2>
        <div class="text-end">
            <span class="text-muted">Chào mừng, <b><?= htmlspecialchars($current_user['username']) ?></b></span>
        </div>
    </header>

    
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card p-3 h-100">
                <h6 class="fw-bold"><i class="bi bi-bar-chart-line me-2"></i>So sánh GPA sinh viên</h6>
                <canvas id="gpaChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 h-100 text-center">
                <h6 class="fw-bold"><i class="bi bi-pie-chart me-2"></i>Tỷ lệ điểm danh</h6>
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    <?php if($role == 'admin'): ?>
    <div class="card mb-4 border-start border-4 border-danger">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-danger">Quản lý phân quyền tài khoản</h5>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Quyền hiện tại</th>
                        <th>Thay đổi thành</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $u_query = mysqli_query($conn, "SELECT id, username, role FROM users");
                    while($user_item = mysqli_fetch_assoc($u_query)): 
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user_item['username']) ?></strong></td>
                        <td><span class="badge bg-secondary"><?= $user_item['role'] ?></span></td>
                        <form action="home.php?action=change_role" method="POST">
                            <input type="hidden" name="target_user_id" value="<?= $user_item['id'] ?>">
                            <td>
                                <select name="new_role" class="form-select form-select-sm w-auto">
                                    <option value="student" <?= $user_item['role']=='student'?'selected':'' ?>>Student</option>
                                    <option value="teacher" <?= $user_item['role']=='teacher'?'selected':'' ?>>Teacher</option>
                                    <option value="admin" <?= $user_item['role']=='admin'?'selected':'' ?>>Admin</option>
                                </select>
                            </td>
                            <td><button type="submit" class="btn btn-dark btn-sm">Cập nhật</button></td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-secondary">Danh sách sinh viên</h5>
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã SV</th>
                        <th>Họ và Tên</th>
                        <th>Lớp học</th>
                        <th>GPA</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): ?>
                    <tr>
                        <td class="fw-bold text-primary">#<?= $s['id'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                        </td>
                        <td><span class="badge bg-info-subtle text-info"><?= $s['class_name'] ?? 'Chưa rõ' ?></span></td>
                        <td><span class="badge bg-success fs-6"><?= number_format($s['gpa'], 2) ?></span></td>
                        <td class="text-end">
                            <?php if($role == 'admin'): ?>
                                <a href="home.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('Bạn chắc chắn muốn xóa?')"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                            <button class="btn btn-light btn-sm border"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. Dữ liệu GPA
    const gpaLabels = <?= json_encode(array_column($students, 'name')) ?>;
    const gpaData = <?= json_encode(array_column($students, 'gpa')) ?>;

    new Chart(document.getElementById('gpaChart'), {
        type: 'bar',
        data: {
            labels: gpaLabels,
            datasets: [{
                label: 'Điểm GPA',
                data: gpaData,
                backgroundColor: '#4e73df',
                borderRadius: 5
            }]
        },
        options: { scales: { y: { beginAtZero: true, max: 10 } } }
    });

    // 2. Dữ liệu Điểm danh
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Có mặt', 'Vắng'],
            datasets: [{
                data: [<?= $att_data['present'] ?>, <?= $att_data['absent'] ?>],
                backgroundColor: ['#1cc88a', '#e74a3b']
            }]
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>