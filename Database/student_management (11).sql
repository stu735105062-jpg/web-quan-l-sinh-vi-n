SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Tạo và sử dụng Database
CREATE DATABASE IF NOT EXISTS `student_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `student_management`;

-- --------------------------------------------------------

-- 2. Cấu trúc bảng `classes`
CREATE TABLE IF NOT EXISTS `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `teacher_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dữ liệu bảng `classes`
INSERT IGNORE INTO `classes` (`class_id`, `class_name`, `teacher_name`) VALUES
(1, 'CNTT K15A', 'Nguyễn Văn A'),
(2, 'Kế Toán K12', 'Trần Thị B'),
(3, 'CNPM', 'Thầy Hải'),
(4, 'Công nghệ phần mềm', 'Hai Long');

-- --------------------------------------------------------

-- 3. Cấu trúc bảng `student_info`
CREATE TABLE IF NOT EXISTS `student_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_student_class` (`class_id`),
  CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hợp nhất dữ liệu sinh viên cũ và mới
INSERT IGNORE INTO `student_info` (`id`, `name`, `email`, `phone`, `class_id`) VALUES
(1, 'nguyễn bá lương', 'nguyenbalong03072005@gmail.com', '000000', 3),
(2, 'Nguyễn bá lương', 'hnue132@hnue.com', '000000', 2),
(13, 'AHMED SAHAL ADAM', 'ahmedsahal@gmail.com', '634294218', 1),
(14, 'ajama', 'ahmedsahal@gmail.com', '0634916040', 1),
(15, 'caasha xusseen', 'caasha@gmail.com', '0634189019', NULL),
(16, 'Mohamed Ali', 'Mohammed@gmail.com', '06345552890', NULL),
(17, 'foosiya cali adan', 'foosiya@gmail.com', '777777', NULL);

-- --------------------------------------------------------

-- 4. Cấu trúc bảng `courses`
CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_name` varchar(255) NOT NULL,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `courses` (`course_id`, `course_name`) VALUES
(1, 'Lập trình PHP'),
(2, 'Cơ sở dữ liệu'),
(3, 'Kỹ năng mềm'),
(4, 'CNTT1'),
(5, 'CNPM');

-- --------------------------------------------------------

-- 5. Cấu trúc bảng `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','student') DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '123456', 'admin');

-- --------------------------------------------------------

-- 6. Cấu trúc bảng `attendance`
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `status` enum('present','absent') DEFAULT 'present',
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_attendance_student` (`student_id`),
  CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 7. Cấu trúc bảng `grades`
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `score` decimal(4,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_grade_student` (`student_id`),
  KEY `fk_grade_course` (`course_id`),
  CONSTRAINT `fk_grade_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grade_student` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;