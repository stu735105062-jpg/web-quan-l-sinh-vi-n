<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($class_id == 0) { header("Location: manage_grades.php"); exit(); }

// Lấy thông tin lớp & môn học
$class_res = mysqli_query($conn, "SELECT * FROM classes WHERE class_id = '$class_id'");
$class_info = mysqli_fetch_assoc($class_res);
$class_name = $class_info['class_name'];

$course_res = mysqli_query($conn, "SELECT course_id FROM courses WHERE class_id = '$class_id' LIMIT 1");
if (mysqli_num_rows($course_res) > 0) {
    $course_id = mysqli_fetch_assoc($course_res)['course_id'];
} else {
    mysqli_query($conn, "INSERT INTO courses (course_name, class_id) VALUES ('$class_name', '$class_id')");
    $course_id = mysqli_insert_id($conn);
}

$msg = ""; $error = "";

// XỬ LÝ THÊM SINH VIÊN
if (isset($_POST['btn_add_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $email = mysqli_real_escape_string($conn, $_POST['student_email']);
    
    // 1. Kiểm tra Email này đã có trong LỚP NÀY chưa?
    $check_in_class = mysqli_query($conn, "SELECT id FROM student_info WHERE email = '$email' AND class_id = '$class_id'");
    
    if (mysqli_num_rows($check_in_class) > 0) {
        $error = "Lỗi: Email <b>$email</b> đã có trong danh sách lớp này rồi!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // 2. Kiểm tra Email đã có tài khoản User ở hệ thống chưa?
            $check_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$email'");
            if (mysqli_num_rows($check_user) > 0) {
                $user_id = mysqli_fetch_assoc($check_user)['id'];
            } else {
                // Chưa có thì tạo tài khoản mới
                mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$email', '123456', 'student')");
                $user_id = mysqli_insert_id($conn);
            }

            // 3. Thêm vào danh sách lớp
            mysqli_query($conn, "INSERT INTO student_info (name, email, class_id, user_id) VALUES ('$name', '$email', '$class_id', '$user_id')");

            mysqli_commit($conn);
            $msg = "Thêm sinh viên thành công!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Lỗi hệ thống không xác định.";
        }
    }
}

// XỬ LÝ LƯU ĐIỂM & ĐIỂM DANH
if (isset($_POST['btn_save_all'])) {
    $scores = $_POST['scores'] ?? [];
    $attendance = $_POST['attendance'] ?? [];
    $today = date('Y-m-d');
    foreach ($scores as $s_id => $score) {
        if ($score !== "") {
            mysqli_query($conn, "INSERT INTO grades (student_id, course_id, score) VALUES ('$s_id', '$course_id', '$score') ON DUPLICATE KEY UPDATE score = '$score'");
        }
        $status = isset($attendance[$s_id]) ? 'present' : 'absent';
        mysqli_query($conn, "INSERT INTO attendance (student_id, date, status) VALUES ('$s_id', '$today', '$status') ON DUPLICATE KEY UPDATE status = '$status'");
    }
    $msg = "Đã lưu dữ liệu ngày $today";
}

// TRUY VẤN HIỂN THỊ
$today = date('Y-m-d');
$list_students = mysqli_query($conn, "SELECT s.id, s.name, s.email, g.score, a.status 
    FROM student_info s 
    LEFT JOIN grades g ON s.id = g.student_id AND g.course_id = '$course_id'
    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$today'
    WHERE s.class_id = '$class_id' ORDER BY s.name ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý lớp: <?= htmlspecialchars($class_name) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <h3>Lớp: <?= htmlspecialchars($class_name) ?></h3>
        <a href="manage_grades.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5>Thêm sinh viên mới</h5>
                <form method="POST">
                    <input type="text" name="student_name" class="form-control mb-2" placeholder="Tên sinh viên" required>
                    <input type="email" name="student_email" class="form-control mb-2" placeholder="Email" required>
                    <button type="submit" name="btn_add_student" class="btn btn-primary w-100">Thêm vào lớp</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <form method="POST" class="card p-3 shadow-sm">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Danh sách lớp</h5>
                    <button type="submit" name="btn_save_all" class="btn btn-success">Lưu tất cả</button>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tên</th><th>Email</th><th>Điểm</th><th>Có mặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = mysqli_fetch_assoc($list_students)): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= $s['email'] ?></td>
                            <td><input type="number" step="0.1" name="scores[<?= $s['id'] ?>]" class="form-control" style="width:80px" value="<?= $s['score'] ?>"></td>
                            <td><input type="checkbox" name="attendance[<?= $s['id'] ?>]" <?= $s['status'] == 'present' ? 'checked' : '' ?>></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>
</body>
</html>