<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../app/config.php');
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');
require_once(__DIR__ . '/../app/Model/Student.php');

$pdo = MyApp\Database::getInstance();
$studentModel = new StudentModel($pdo);

// POSTデータ取得
$studentData = $studentModel->collectStudentPostData();

// バリデーション
$errors = validateStudentData($studentData);
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    redirect('/signup.php');
}

// INSERT
$sql = $studentModel->buildInsertSql();
$stmt = $pdo->prepare($sql);
$stmt->execute($studentModel->prepareStudentInsertParams($studentData));

// 新しい student_id
$student_id = $pdo->lastInsertId();

// 画像アップロード
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $filename = $studentModel->uploadPhoto($student_id, $_FILES['photo']);
    if ($filename) {
        $stmt = $pdo->prepare("UPDATE students SET image = ? WHERE id = ?");
        $stmt->execute(array($filename, $student_id));
    }
}

$_SESSION['message'] = "新規登録が完了しました。";
redirect("/student-data.php?id=" . $student_id);
