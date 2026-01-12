<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// 1. KIỂM TRA QUYỀN TRUY CẬP
if (!isset($_SESSION['authenticated'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] === 'admin') {
    header("Location: home.php");
    exit();
}

$username = $_SESSION['username'];

// 2. TRUY VẤN DỮ LIỆU (Bỏ cột credits)
$sql = "SELECT s.id as student_id, s.name, s.email, c.class_name, 
               cr.course_name, g.score
        FROM users u
        JOIN student_info s ON u.id = s.user_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN courses cr ON c.class_id = cr.class_id
        LEFT JOIN grades g ON (s.id = g.student_id AND cr.course_id = g.course_id)
        WHERE u.username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Lấy thông tin chung
$student_name = $data[0]['name'] ?? $username;
$class_name = $data[0]['class_name'] ?? "Chưa phân lớp";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cổng thông tin sinh viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .student-card { border: none; border-radius: 15px; background: linear-gradient(135px, #667eea 0%, #764ba2 100%); color: white; }
        .table-container { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card student-card p-4 mb-4 shadow">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Xin chào, <?= htmlspecialchars($student_name) ?>!</h2>
                <p class="mb-0 opacity-75"><i class="bi bi-mortarboard-fill"></i> Lớp: <?= htmlspecialchars($class_name) ?></p>
            </div>
            <a href="logout.php" class="btn btn-light btn-sm fw-bold text-primary shadow-sm">
                <i class="bi bi-box-arrow-right"></i> ĐĂNG XUẤT
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Thông tin cá nhân</h5>
                    <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($data[0]['email'] ?? 'Chưa cập nhật') ?></p>
                    <p class="mb-2"><strong>Mã SV:</strong> #<?= htmlspecialchars($data[0]['student_id'] ?? '0') ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="table-container shadow-sm">
                <div class="bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold m-0 text-secondary"><i class="bi bi-calendar3 me-2"></i> Kết quả học tập</h5>
                    <span class="badge bg-primary">Học kỳ này</span>
                </div>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="80">STT</th>
                            <th class="text-start">Tên môn học</th>
                            <th width="150">Điểm số</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data) && $data[0]['course_name'] != null): ?>
                            <?php foreach ($data as $index => $row): ?>
                            <tr class="text-center">
                                <td><?= $index + 1 ?></td>
                                <td class="text-start fw-bold"><?= htmlspecialchars($row['course_name']) ?></td>
                                <td>
                                    <?php if ($row['score'] !== null): ?>
                                        <span class="badge <?= $row['score'] >= 5 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                            <?= number_format($row['score'], 1) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Chưa có điểm</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">Bạn chưa được phân môn học nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>