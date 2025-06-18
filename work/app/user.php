<?php

session_start();

require_once(__DIR__ . '/../app/config.php');
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

use MyApp\Database;

// DB接続
$pdo = Database::getInstance();

// POSTデータ取得
$class = $_POST['class'] ?? '';
$class_no = $_POST['class_no'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$last_name_kana = $_POST['last_name_kana'] ?? '';
$first_name_kana = $_POST['first_name_kana'] ?? '';
$gender = isset($_POST['gender']) ? (int)$_POST['gender'] : null;
$birth_date_raw = $_POST['birth_date'] ?? '';
$birth_date = (DateTime::createFromFormat('Y-m-d', $birth_date_raw)) ? $birth_date_raw : null;
$tel_number = $_POST['tel_number'] ?? '';
$email = $_POST['email'] ?? '';
$parent_last_name = $_POST['parent_last_name'] ?? '';
$parent_first_name = $_POST['parent_first_name'] ?? '';
$parent_tel_number = $_POST['parent_tel_number'] ?? '';
$memo = $_POST['memo'] ?? '';

// 必須項目チェック
if (empty($last_name) || empty($first_name) || empty($class_no) || empty($class) || empty($class_no)) {
    $_SESSION['errors'] = ['必須項目が未入力です。'];
    $_SESSION['old'] = $_POST;
    redirect('/signup.php');
}

/// バリデーション用データ配列
$studentData = [
    'class' => $class,
    'class_no' => $class_no,
    'last_name' => $last_name,
    'first_name' => $first_name,
    'last_name_kana' => $last_name_kana,
    'first_name_kana' => $first_name_kana,
    'gender' => $gender,
    'birth_date' => $birth_date_raw,
    'tel_number' => $tel_number,
    'email' => $email,
    'parent_last_name' => $parent_last_name,
    'parent_first_name' => $parent_first_name,
    'parent_tel_number' => $parent_tel_number,
];

// バリデーション実行
$errors = validateStudentData($studentData);

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    redirect('/signup.php');
}


// INSERT文の準備
$sql = "INSERT INTO students (
    class, class_no,
    last_name, first_name,
    last_name_kana, first_name_kana,
    gender, birth_date,
    tel_number, email,
    parent_last_name, parent_first_name,
    parent_tel_number, image,
    student_deleted, created_at, updated_at
) VALUES (
    :class, :class_no,
    :last_name, :first_name,
    :last_name_kana, :first_name_kana,
    :gender, :birth_date,
    :tel_number, :email,
    :parent_last_name, :parent_first_name,
    :parent_tel_number,:image,
    :student_deleted,
    NOW(), NOW()
)";

// 実行
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':class' => $class,
    ':class_no' => $class_no,
    ':last_name' => $last_name,
    ':first_name' => $first_name,
    ':last_name_kana' => $last_name_kana,
    ':first_name_kana' => $first_name_kana,
    ':gender' => $gender,
    ':birth_date' => $birth_date,
    ':tel_number' => $tel_number,
    ':email' => $email,
    ':parent_last_name' => $parent_last_name,
    ':parent_first_name' => $parent_first_name,
    ':parent_tel_number' => $parent_tel_number,
    ':image' => null,
    ':student_deleted' => 0,
]);

// 新しい student_id を取得
$student_id = $pdo->lastInsertId();

// 画像アップロード処理
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $result = handlePhotoUpload($student_id, $_FILES['photo'], $pdo);
    if ($result === false) {
        $_SESSION['errors'] = ['アップロードできる画像は jpg, jpeg, png, gif のみです。'];
        $_SESSION['old'] = $_POST;
        redirect('/signup.php');
    }
}


// メモが入力されている場合のみ、更新
if (!empty($memo)) {
    $sql = "UPDATE students SET memo = :memo WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':memo' => $memo,
        ':id' => $student_id
    ]);
}

// 登録後、生徒データ編集にリダイレクト
redirect("/student-data.php?id=$student_id");
