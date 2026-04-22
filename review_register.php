<?php
session_start();
include('db_connect.php');

$student_id_session = $_SESSION['student_id'];

// ดึงข้อมูลนิสิต
$stmt_user = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt_user->bind_param("s", $student_id_session);
$stmt_user->execute();
$student = $stmt_user->get_result()->fetch_assoc();


// ============================
// 🔵 กรณีกด Submit (บันทึกข้อมูล)
// ============================
if (isset($_POST['confirm'])) {

    $company_id   = $_POST['company_id'];
    $company_name = $_POST['company_name'];
    $address      = $_POST['address'];
    $contact_name = $_POST['contact_name'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];

    // check company
    $check_c = $conn->prepare("SELECT company_id FROM company WHERE company_id = ?");
    $check_c->bind_param("s", $company_id);
    $check_c->execute();

    if ($check_c->get_result()->num_rows == 0) {
        $ins_com = $conn->prepare("
            INSERT INTO company (company_id, company_name, address, contact_name)
            VALUES (?, ?, ?, ?)
        ");
        $ins_com->bind_param("ssss", $company_id, $company_name, $address, $contact_name);
        $ins_com->execute();
    }

    // insert internship
    $stmt = $conn->prepare("
        INSERT INTO internship_request 
        (student_id, company_id, start_date, end_date, status)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->bind_param("ssss", $student_id_session, $company_id, $start_date, $end_date);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกสำเร็จ'); window.location='view_status.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    exit();
}


// ============================
// 🟡 กรณีเข้ามาจาก form (เพื่อ review)
// ============================
$data = $_POST;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบข้อมูล | Internship System</title>
    <link rel="stylesheet" href="CSS/main.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">🎓 Internship Portal</div>
        <div class="nav-links">
            <button onclick="history.back()" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem;">ย้อนกลับไปแก้ไข</button>
        </div>
    </nav>

    <div class="container" style="max-width: 700px;">
        <div class="card">
            <h2 class="text-center">ตรวจสอบข้อมูลอีกครั้ง</h2>
            <p class="text-center" style="color: var(--gray-dark); margin-bottom: 30px;">กรุณาตรวจสอบข้อมูลที่ท่านกรอกก่อนทำการยืนยันเข้าสู่ระบบ</p>

            <div style="background: var(--light-red); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <h4 style="color: var(--primary-red); margin-bottom: 10px;">ข้อมูลนิสิต</h4>
                <p><strong>ชื่อ-นามสกุล:</strong> <?= $student['fullname'] ?> (<?= $student['student_id'] ?>)</p>
                <p><strong>ชั้นปี:</strong> <?= $student['year'] ?> | <strong>อีเมล:</strong> <?= $student['email'] ?></p>
            </div>

            <div style="padding: 0 10px;">
                <h4 style="margin-bottom: 15px; border-bottom: 2px solid var(--gray-medium); padding-bottom: 5px;">รายละเอียดการฝึกงาน</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label>รหัสบริษัท</label>
                        <p style="font-weight: 500;"><?= htmlspecialchars($data['company_id']) ?></p>
                    </div>
                    <div>
                        <label>ชื่อบริษัท</label>
                        <p style="font-weight: 500;"><?= htmlspecialchars($data['company_name']) ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <label>ที่อยู่สถานประกอบการ</label>
                    <p style="font-weight: 500;"><?= nl2br(htmlspecialchars($data['address'])) ?></p>
                </div>

                <div class="mb-4">
                    <label>ชื่อผู้ติดต่อ</label>
                    <p style="font-weight: 500;"><?= htmlspecialchars($data['contact_name']) ?></p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: var(--gray-light); padding: 15px; border-radius: 8px;">
                    <div>
                        <label>วันที่เริ่มฝึก</label>
                        <p style="font-weight: 600; color: var(--primary-red);"><?= date('d/m/Y', strtotime($data['start_date'])) ?></p>
                    </div>
                    <div>
                        <label>วันที่สิ้นสุด</label>
                        <p style="font-weight: 600; color: var(--primary-red);"><?= date('d/m/Y', strtotime($data['end_date'])) ?></p>
                    </div>
                </div>
            </div>

            <form method="post" style="margin-top: 30px;">
                <input type="hidden" name="company_id" value="<?= htmlspecialchars($data['company_id']) ?>">
                <input type="hidden" name="company_name" value="<?= htmlspecialchars($data['company_name']) ?>">
                <input type="hidden" name="address" value="<?= htmlspecialchars($data['address']) ?>">
                <input type="hidden" name="contact_name" value="<?= htmlspecialchars($data['contact_name']) ?>">
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($data['start_date']) ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($data['end_date']) ?>">

                <button type="submit" name="confirm" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    ยืนยันการส่งข้อมูลการฝึกงาน
                </button>
            </form>
        </div>
    </div>

</body>
</html>