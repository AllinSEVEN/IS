<?php
session_start();
include('db_connect.php');

// ดึงข้อมูลนิสิตจาก Session มาแสดงโชว์ (แก้ไขไม่ได้)
$student_id_session = $_SESSION['student_id'];
$stmt_user = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt_user->bind_param("s", $student_id_session);
$stmt_user->execute();
$student = $stmt_user->get_result()->fetch_assoc();

// ประมวลผลเมื่อกดส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_id   = $_POST['company_id'];
    $company_name = $_POST['company_name'];
    $address      = $_POST['address'];
    $contact_name = $_POST['contact_name'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];

    // 1. ตรวจสอบก่อนว่ามีบริษัทนี้หรือยัง ถ้ายังไม่มีให้ INSERT เพิ่ม
   // 1. เช็ค company
$check_c = $conn->prepare("SELECT company_id FROM company WHERE company_id = ?");
$check_c->bind_param("s", $company_id);
$check_c->execute();

if ($check_c->get_result()->num_rows == 0) {
    $ins_com = $conn->prepare("INSERT INTO company (company_id, company_name, address, contact_name) VALUES (?, ?, ?, ?)");
    $ins_com->bind_param("ssss", $company_id, $company_name, $address, $contact_name);
    $ins_com->execute();
}

// 2. insert internship_request
    $stmt = $conn->prepare("
        INSERT INTO internship_request 
        (student_id, company_id, start_date, end_date, status) 
        VALUES (?, ?, ?, ?, 1)
    ");

$stmt->bind_param("ssss", $student_id_session, $company_id, $start_date, $end_date);

if ($stmt->execute()) {
    echo "<script>alert('บันทึกข้อมูลเรียบร้อย'); window.location.href='view_status.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนฝึกงาน | Internship System</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">🎓 Internship Portal</div>
        <div class="nav-links">
            <a href="view_status.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem;">ย้อนกลับ</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h3>ข้อมูลนิสิต</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <label>รหัสนิสิต</label>
                    <p style="font-weight: 600;"><?= $student['student_id'] ?></p>
                </div>
                <div>
                    <label>ชื่อ-นามสกุล</label>
                    <p style="font-weight: 600;"><?= $student['fullname'] ?></p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>รายละเอียดการฝึกงาน</h2>
            <p style="color: var(--gray-dark); margin-bottom: 20px; font-size: 0.9rem;">กรุณากรอกข้อมูลบริษัทและระยะเวลาการฝึกงานให้ครบถ้วน</p>
            
            <form method="post" action="review_register.php">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <div class="form-group">
                        <label for="company_id">รหัสบริษัท / เลขนิติบุคคล</label>
                        <input type="text" name="company_id" id="company_id" placeholder="ระบุรหัสบริษัท" required>
                    </div>
                    <div class="form-group">
                        <label for="company_name">ชื่อบริษัท / สถานประกอบการ</label>
                        <input type="text" name="company_name" id="company_name" placeholder="ระบุชื่อบริษัท" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">ที่อยู่บริษัท</label>
                    <textarea name="address" id="address" rows="3" placeholder="เลขที่, ถนน, แขวง/ตำบล, เขต/อำเภอ, จังหวัด"></textarea>
                </div>

                <div class="form-group">
                    <label for="contact_name">ชื่อผู้ติดต่อ / ผู้ควบคุมดูแล</label>
                    <input type="text" name="contact_name" id="contact_name" placeholder="ระบุชื่อผู้ติดต่อ">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="start_date">วันที่เริ่มฝึกงาน</label>
                        <input type="date" name="start_date" id="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">วันที่สิ้นสุดการฝึกงาน</label>
                        <input type="date" name="end_date" id="end_date" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">ตรวจสอบข้อมูลหน้าถัดไป →</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="friendly-footer">
        <p>© 2024 ระบบจัดการข้อมูลการฝึกงาน | มหาวิทยาลัย</p>
    </footer>
</body>
</html>
