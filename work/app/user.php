<?php

session_start();

require_once(__DIR__ . '/../app/config.php');
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

use MyApp\Database;

// DB接続
$pdo = Database::getInstance();

// POSTデータをまとめて取得
$studentData = collectStudentPostData();

// バリデーション実行
$errors = validateStudentData($studentData);
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    redirect('/signup.php');
}

// INSERT文
$sql = "INSERT INTO students (
    class, class_no,
    last_name, first_name,
    last_name_kana, first_name_kana,
    gender, birth_date,
    tel_number, email,
    parent_last_name, parent_first_name,
    parent_tel_number, memo, image,
    student_deleted, created_at, updated_at
) VALUES (
    :class, :class_no,
    :last_name, :first_name,
    :last_name_kana, :first_name_kana,
    :gender, :birth_date,
    :tel_number, :email,
    :parent_last_name, :parent_first_name,
    :parent_tel_number, :memo, :image,
    :student_deleted,
    NOW(), NOW()
)";

$stmt = $pdo->prepare($sql);
$stmt->execute(prepareStudentInsertParams($studentData));

// 新しい student_id を取得
$student_id = $pdo->lastInsertId();

// 画像アップロード処理
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    uploadStudentPhoto($student_id, $_FILES['photo'], $pdo, "/student-data.php?id=$student_id");
}

// 登録後、生徒データ編集にリダイレクト
 $_SESSION['message'] = "新規登録が完了しました。";
redirect("/student-data.php?id=$student_id");
