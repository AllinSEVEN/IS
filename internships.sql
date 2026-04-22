CREATE DATABASE internships;

CREATE TABLE student (
 student_id VARCHAR(11) PRIMARY KEY,
 fullname VARCHAR(100) NOT NULL,
 email VARCHAR(100),
 phone VARCHAR(20),
 year INT,
 password VARCHAR(50) NOT NULL DEFAULT '1234');

CREATE TABLE company(
 company_id VARCHAR(13) PRIMARY KEY,
 company_name VARCHAR(200) NOT NULL,
 address VARCHAR(300),
 contact_name VARCHAR(100));

CREATE TABLE staff (
 staff_id VARCHAR(13) PRIMARY KEY,
 username VARCHAR(50) UNIQUE,
 fullname VARCHAR(100) NOT NULL,
 role ENUM('admin', 'teacher') NOT NULL,
 password VARCHAR(255) NOT NULL DEFAULT '1234');

CREATE TABLE status (
 status_id INT PRIMARY KEY,
 status_name VARCHAR(150) NOT NULL
);

INSERT INTO status (status_id, status_name) VALUES
(1, 'รับเรื่องเข้าระบบ'),
(2, 'อาจารย์ที่ปรึกษาอนุมัติ'),
(3, 'ออกใบส่งตัวแล้ว'),
(4, 'ฝึกงานเสร็จสิ้น'),
(9, 'ยกเลิก (เอกสารผิดพลาด)');

CREATE TABLE internship_request (
 request_id INT AUTO_INCREMENT PRIMARY KEY,
 student_id VARCHAR(11) NOT NULL,
 company_id VARCHAR(13) ,
 start_date DATE,
 end_date DATE,
 status INT DEFAULT 1,
 created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

 FOREIGN KEY (student_id) REFERENCES student(student_id),
 FOREIGN KEY (company_id) REFERENCES company(company_id),
 FOREIGN KEY (status) REFERENCES status(status_id));

CREATE TABLE status_log (
 log_id INT AUTO_INCREMENT PRIMARY KEY,
 request_id INT NOT NULL,
 old_status INT,
 new_status INT,
 changed_by VARCHAR(50),
 changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 note TEXT,

 FOREIGN KEY (request_id) REFERENCES internship_request(request_id));

 
INSERT INTO staff (staff_id,username, password, role, fullname) values
('1','admin', '1234', 'admin', 'เจ้าหน้าที่คณะ'),
('2','teacher', '1234', 'teacher', 'อาจารย์ที่ปรึกษา');

INSERT INTO student (student_id, fullname, email, phone, year) VALUES
('65101010001','สมชาย ใจดี','st01@swu.ac.th','0810000001',3),
('65101010002','สมหญิง รักเรียน','st02@swu.ac.th','0810000002',3),
('65101010003','วิชัย มานะ','st03@swu.ac.th','0810000003',3),
('65101010004','นภา สดใส','st04@swu.ac.th','0810000004',3),
('65101010005','ธนกร พากเพียร','st05@swu.ac.th','0810000005',3),
('65101010006','ปิยะ ตั้งใจ','st06@swu.ac.th','0810000006',3),
('65101010007','กานดา ขยัน','st07@swu.ac.th','0810000007',3),
('65101010008','รัตนา สุขใจ','st08@swu.ac.th','0810000008',3),
('65101010009','ชัยวัฒน์ เก่งกาจ','st09@swu.ac.th','0810000009',3),
('65101010010','พิมพ์ใจ อดทน','st10@swu.ac.th','0810000010',3);

ALTER TABLE staff
ADD email VARCHAR(150) AFTER fullname;

INSERT INTO staff (staff_id, fullname, username, email, role, password)
VALUES
(3,'อ.ดร.ดิษฐ์ สุทธิวงศ์','Dit', 'dit@g.swu.ac.th','teacher','1234'),
(4,'อ.ดร.ฐิติ อติชาติชยากร','Thiti', 'thitik@g.swu.ac.th', 'teacher','1234'),
(5,'ผศ.ดร.วิภากร วัฒนสินธุ์','Vipakorn', 'vipakorn@g.swu.ac.th', 'teacher','1234'),
(6,'อ.ดร.โชคธำรงค์ จงจอหอ','Chokthamrong', 'chokthamrong@g.swu.ac.th', 'teacher','1234'),
(7,'อ.โชติมา วัฒนะ','Chotima', 'chotimaw@g.swu.ac.th', 'teacher','1234'),
(8,'ผศ.ดร.ดุษฎี สีวังคำ','Dussadee', 'dussadee@g.swu.ac.th', 'teacher','1234'),
(9,'ผศ.ดร.ศศิพิมล ประพินพงศกร','Sasipimol', 'sasipimol@g.swu.ac.th', 'teacher','1234'),
(10,'อ.ดร.ศุมรรษตรา แสนวา','Sumattra', 'sumattra@g.swu.ac.th', 'teacher','1234');
 
