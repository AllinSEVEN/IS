<?php
session_start();
include('db_connect.php');

// 1. ตรวจสอบสิทธิ์ (Security Check)
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'student') {
    header("Location: login.php");
    exit();
}

// 2. การประมวลผลเมื่อกดบันทึก (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];
    // ใช้ staff_id จาก session ที่เราตั้งไว้ในหน้า login
    $changed_by = $_SESSION['staff_id']; 
    $old_status = $_POST['old_status'];

    $stmt = $conn->prepare("UPDATE internship_request SET status = ? WHERE request_id = ?");
    $stmt->bind_param("ii", $new_status, $request_id);
    
    if ($stmt->execute()) {
        // บันทึกลง Log เพื่อดูประวัติการเปลี่ยนสถานะ
        $log_stmt = $conn->prepare("INSERT INTO status_log (request_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("iiis", $request_id, $old_status, $new_status, $changed_by);
        $log_stmt->execute();
        
        echo "<script>alert('บันทึกสถานะเรียบร้อย'); window.location.href='view_all.php';</script>";
    }
}

// 3. ดึงข้อมูลคำร้อง (GET)
if (!isset($_GET['id'])) { 
    header("Location: view_all.php"); 
    exit(); 
}

$id = $_GET['id'];
$sql = "SELECT ir.*, st.fullname, st.email, st.phone, st.year, 
               c.company_name, c.address, c.contact_name, s.status_name
        FROM internship_request ir 
        JOIN student st ON ir.student_id = st.student_id 
        LEFT JOIN company c ON ir.company_id = c.company_id 
        JOIN status s ON ir.status = s.status_id
        WHERE ir.request_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานะ | Internship Admin</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">🎓 Admin Dashboard</div>
        <div class="nav-links">
            <a href="view_all.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem;">ย้อนกลับ</a>
        </div>
    </nav>

    <div class="container" style="max-width: 800px;">
        <div class="card">
            <h2>จัดการสถานะคำขอฝึกงาน</h2>
            <p style="color: var(--gray-dark); margin-bottom: 25px;">เลขที่คำขอ: #<?= $row['request_id'] ?></p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div style="background: var(--gray-light); padding: 20px; border-radius: 8px;">
                    <h4 style="color: var(--primary-red); margin-bottom: 10px;">ข้อมูลนิสิต</h4>
                    <p><strong>ชื่อ-นามสกุล:</strong> <?= $row['fullname'] ?></p>
                    <p><strong>รหัสนิสิต:</strong> <?= $row['student_id'] ?></p>
                    <p><strong>ชั้นปี:</strong> <?= $row['year'] ?></p>
                    <p><strong>อีเมล:</strong> <?= $row['email'] ?></p>
                </div>

                <div style="background: var(--gray-light); padding: 20px; border-radius: 8px;">
                    <h4 style="color: var(--primary-red); margin-bottom: 10px;">ข้อมูลบริษัท</h4>
                    <p><strong>บริษัท:</strong> <?= $row['company_name'] ?: '-' ?></p>
                    <p><strong>ผู้ติดต่อ:</strong> <?= $row['contact_name'] ?: '-' ?></p>
                    <p style="font-size: 0.9rem;"><strong>ที่อยู่:</strong> <?= $row['address'] ?: '-' ?></p>
                </div>
            </div>

            <hr>

            <div style="max-width: 500px; margin: 0 auto;">
                <h3 class="text-center mb-4">อัปเดตสถานะ</h3>
                <form method="POST">
                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                    <input type="hidden" name="old_status" value="<?= $row['status'] ?>">

                    <div class="form-group">
                        <label>สถานะปัจจุบัน</label>
                        <div class="badge badge-pending" style="padding: 10px 20px; font-size: 1rem;">
                            <?= $row['status_name'] ?>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label for="status">เปลี่ยนเป็นสถานะ:</label>
                        <select name="status" id="status" required style="width: 100%;">
                            <?php
                            $allowed = ($_SESSION['role'] == 'admin') ? [1, 3, 4, 9] : [2, 9];
                            $statuses = $conn->query("SELECT * FROM status");
                            while ($s = $statuses->fetch_assoc()) {
                                if (in_array($s['status_id'], $allowed)) {
                                    $selected = ($s['status_id'] == $row['status']) ? 'selected' : '';
                                    echo "<option value='{$s['status_id']}' $selected>{$s['status_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4" style="width: 100%; padding: 15px;">บันทึกการเปลี่ยนแปลง</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
