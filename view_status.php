<?php
session_start();
include('db_connect.php');

// 1. ตรวจสอบสิทธิ์
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student' || !isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// 2. ดึงข้อมูลนิสิต
$stmt_user = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt_user->bind_param("s", $student_id);
$stmt_user->execute();
$student = $stmt_user->get_result()->fetch_assoc();

// 3. ดึงรายการคำขอฝึกงาน
$sql = "SELECT ir.*, s.status_name, c.company_name 
        FROM internship_request ir
        JOIN status s ON ir.status = s.status_id
        LEFT JOIN company c ON ir.company_id = c.company_id
        WHERE ir.student_id = ?
        ORDER BY ir.created_at DESC";

$stmt_req = $conn->prepare($sql);
$stmt_req->bind_param("s", $student_id);
$stmt_req->execute();
$result = $stmt_req->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการฝึกงาน | Internship System</title>
    <link rel="stylesheet" href="CSS/main.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">🎓 Internship Portal</div>
        <div class="nav-links">
            <span style="font-weight: 500; color: var(--gray-dark);">สวัสดี, <?= $student['fullname'] ?></span>
            <a href="login.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem;">ออกจากระบบ</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h3>ข้อมูลส่วนตัวนิสิต</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <label>รหัสนิสิต</label>
                    <p style="font-weight: 600;"><?= $student['student_id'] ?></p>
                </div>
                <div>
                    <label>ชื่อ-นามสกุล</label>
                    <p style="font-weight: 600;"><?= $student['fullname'] ?></p>
                </div>
                <div>
                    <label>ชั้นปี</label>
                    <p style="font-weight: 600;"><?= $student['year'] ?></p>
                </div>
                <div>
                    <label>อีเมล</label>
                    <p style="font-weight: 600;"><?= $student['email'] ?></p>
                </div>
            </div>
        </div>

        <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>ตรวจสอบสถานะการฝึกงาน</h2>
            <a href="register.php" class="btn btn-primary">+ กรอกข้อมูลการฝึกงาน</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        <th>วันที่ยื่น</th>
                        <th>สถานประกอบการ</th>
                        <th>ระยะเวลาฝึกงาน</th>
                        <th>สถานะปัจจุบัน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $i = 1; while($row = $result->fetch_assoc()): 
                            $status_class = '';
                            if ($row['status'] == 1) $status_class = 'badge-pending';
                            elseif ($row['status'] == 2) $status_class = 'badge-approved';
                            elseif ($row['status'] == 3) $status_class = 'badge-rejected';
                        ?>
                        <tr>
                            <td>#<?= $row['request_id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td style="font-weight: 500;"><?= $row['company_name'] ?: '-' ?></td>
                            <td>
                                <span style="font-size: 0.85rem;">
                                    <?= date('d/m/Y', strtotime($row['start_date'])) ?> - <?= date('d/m/Y', strtotime($row['end_date'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $status_class ?>"><?= $row['status_name'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 40px; color: var(--gray-dark);">ยังไม่มีข้อมูลการยื่นคำร้องฝึกงาน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="friendly-footer">
        <p>© 2024 ระบบจัดการข้อมูลการฝึกงาน | มหาวิทยาลัย</p>
    </footer>
</body>
</html>