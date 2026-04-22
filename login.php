<?php
session_start();
include('db_connect.php');

$loginError = "";

if (isset($_POST['login'])) {
    $username = $_POST['Username'];
    $password = $_POST['Password'];

    // เช็คฝั่ง Student
    $stmt = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $password === $user['password']) {
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['role'] = 'student';
        header("Location: view_status.php");
        exit();
    } 

    // เช็คฝั่ง Staff
    $stmt2 = $conn->prepare("SELECT * FROM staff WHERE username = ?");
    $stmt2->bind_param("s", $username);
    $stmt2->execute();
    $staff = $stmt2->get_result()->fetch_assoc();

    if ($staff && $password === $staff['password']) {
        $_SESSION['staff_id'] = $staff['staff_id'];
        $_SESSION['role'] = $staff['role'];
        header("Location: view_all.php");
        exit();
    } else {
        $loginError = "Username หรือ Password ไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Internship System</title>
    <link rel="stylesheet" href="main.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #D32F2F 0%, #B71C1C 100%);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }
        .login-logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .login-title {
            margin-bottom: 30px;
            color: var(--gray-dark);
        }
    </style>
</head>
<body>

    <div class="card login-card">
        <div style="font-size: 3.5rem; margin-bottom: 10px;">🎓</div>
        <h2 class="login-title">Internship Portal</h2>
        <p style="color: var(--gray-dark); margin-bottom: 25px; font-size: 0.95rem;">ระบบจัดการข้อมูลการฝึกงานนิสิต</p>
        
        <?php if($loginError): ?>
            <p style="color:var(--primary-red); margin-bottom: 20px; font-weight: 500;"><?= $loginError ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group" style="text-align: left;">
                <label for="Username">รหัสนิสิต / ชื่อผู้ใช้งาน</label>
                <input type="text" name="Username" id="Username" placeholder="Username" required>
            </div>
            
            <div class="form-group" style="text-align: left;">
                <label for="Password">รหัสผ่าน</label>
                <input type="password" name="Password" id="Password" placeholder="Password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary mt-4" style="width: 100%;">เข้าสู่ระบบ</button>
        </form>
        
        <p class="mt-4" style="font-size: 0.85rem; color: var(--gray-dark); opacity: 0.6;">
            © University Internship Management System
        </p>
    </div>
</body>
</html>
