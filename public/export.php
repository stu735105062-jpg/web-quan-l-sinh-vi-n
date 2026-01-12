<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'student_management');
mysqli_set_charset($conn, "utf8mb4");

// Đặt tên file
$filename = "Bang_Diem_Sinh_Vien_" . date('Ymd') . ".xls";

// Header để trình duyệt hiểu là file Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// Truy vấn dữ liệu
$query = "SELECT s.id, s.name, c.class_name, (SELECT COALESCE(AVG(score), 0) FROM grades WHERE student_id = s.id) as gpa 
          FROM student_info s LEFT JOIN classes c ON s.class_id = c.class_id";
$res = mysqli_query($conn, $query);
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    .table-style { border-collapse: collapse; width: 100%; }
    .table-style th { background-color: #4e73df; color: #ffffff; border: 1px solid #dee2e6; }
    .table-style td { border: 1px solid #dee2e6; text-align: center; }
    .header-title { font-size: 20px; font-weight: bold; color: #4e73df; text-align: center; }
</style>

<table>
    <tr>
        <td colspan="4" class="header-title">DANH SÁCH KẾT QUẢ HỌC TẬP</td>
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
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td style="text-align: left;"><?php echo $row['name']; ?></td>
                <td><?php echo $row['class_name']; ?></td>
                <td style="background-color: <?php echo ($row['gpa'] < 5) ? '#f8d7da' : '#d1e7dd'; ?>;">
                    <?php echo number_format($row['gpa'], 2); ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>