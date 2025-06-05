<!-- 一覧取得、画面表示 -->

<?php

session_start();

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/database.php');
require_once(__DIR__ . '/utils.php');

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
$gender = $_POST['gender'] ?? '';
$birth_date = $_POST['birth_date'] ?? '';
$tel_number = $_POST['tel_number'] ?? '';
$email = $_POST['email'] ?? '';
$parent_last_name = $_POST['parent_last_name'] ?? '';
$parent_first_name = $_POST['parent_first_name'] ?? '';
$parent_tel_number = $_POST['parent_tel_number'] ?? '';

// 必須項目チェック（名前とクラス番号がクラス名）
if (empty($last_name) || empty($first_name) || empty($class_no || empty($class))) {
    echo "必須項目が未入力です。";
    exit;
}

// INSERT文の準備
$sql = "INSERT INTO students (
    class, class_no,
    last_name, first_name,
    last_name_kana, first_name_kana,
    gender, birth_date,
    tel_number, email,
    parent_last_name, parent_first_name,
    parent_tel_number, image, created_at, updated_at
) VALUES (
    :class, :class_no,
    :last_name, :first_name,
    :last_name_kana, :first_name_kana,
    :gender, :birth_date,
    :tel_number, :email,
    :parent_last_name, :parent_first_name,
    :parent_tel_number, NOW(), NOW()
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
    ':parent_tel_number' => $parent_tel_number
]);

// 新しい student_id を取得
$student_id = $pdo->lastInsertId();

// ▼ 写真アップロード処理
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $tmpName = $_FILES['photo']['tmp_name'];
    $originalName = basename($_FILES['photo']['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (in_array($ext, $allowedExtensions) && in_array($mime, $allowedMimes)) {
        // 以前の画像がある場合削除（念のため）
        $oldFiles = glob($uploadDir . 'student_' . $student_id . '.*');
        foreach ($oldFiles as $file) {
            if (is_file($file)) unlink($file);
        }

        $newFileName = 'student_' . $student_id . '.' . $ext;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $photoPath = 'uploads/' . $newFileName;
            $pdo->prepare("UPDATE students SET photo_path = :photo_path WHERE id = :id")
                ->execute([
                    ':photo_path' => $photoPath,
                    ':id' => $student_id
                ]);
        }
    }
}


// 登録後、生徒データ編集にリダイレクト
redirect("../public/student-data.php?id=$student_id");
