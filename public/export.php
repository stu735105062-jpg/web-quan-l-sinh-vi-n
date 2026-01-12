<?php
session_start();
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// 2. CẤU HÌNH FILE EXCEL
$filename = "Bang_Diem_Va_Chuyen_Can_" . date('Ymd') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// 3. TRUY VẤN DỮ LIỆU (Bổ sung thống kê chuyên cần)
// Giả sử bảng điểm danh là 'attendance', cột 'status' = 1 là đi học
$query = "SELECT 
            s.id, 
            s.name, 
            c.class_name, 
            (SELECT COALESCE(AVG(score), 0) FROM grades WHERE student_id = s.id) as gpa,
            (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status = 1) as total_present
          FROM student_info s 
          LEFT JOIN classes c ON s.class_id = c.class_id";

$res = mysqli_query($conn, $query);
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    .table-style { border-collapse: collapse; width: 100%; }
    .table-style th { background-color: #4e73df; color: #ffffff; border: 1px solid #dee2e6; height: 30px; }
    .table-style td { border: 1px solid #dee2e6; text-align: center; padding: 5px; }
    .header-title { font-size: 22px; font-weight: bold; color: #4e73df; text-align: center; }
    .bad-score { background-color: #f8d7da; color: #721c24; }
    .good-score { background-color: #d1e7dd; color: #0f5132; }
</style>

<table>
    <tr>
        <td colspan="5" class="header-title">BẢNG THỐNG KÊ KẾT QUẢ HỌC TẬP & CHUYÊN CẦN</td>
    </tr>
    <tr><td></td></tr>
</table>

<table class="table-style" border="1">
    <thead>
        <tr>
            <th style="background-color: #4e73df; color: white;">ID</th>
            <th style="background-color: #4e73df; color: white;">Họ và Tên</th>
            <th style="background-color: #4e73df; color: white;">Lớp</th>
            <th style="background-color: #4e73df; color: white;">Điểm GPA</th>
            <th style="background-color: #4e73df; color: white;">Số Buổi Có Mặt</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($res) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td style="text-align: left;"><?php echo $row['name']; ?></td>
                    <td><?php echo $row['class_name']; ?></td>
                    <td class="<?php echo ($row['gpa'] < 5) ? 'bad-score' : 'good-score'; ?>">
                        <?php echo number_format($row['gpa'], 2); ?>
                    </td>
                    <td>
                        <?php echo $row['total_present']; ?> buổi
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Không có dữ liệu sinh viên.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
