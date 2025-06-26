<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

$pdo = \MyApp\Database::getInstance();

// POSTデータ取得
$student_id = $_POST['id'] ?? '';
if (!$student_id) {
    echo "不正なアクセスです。";
    exit;
}

// POSTデータをまとめて取得
$studentData = collectStudentPostData();
// $params = buildStudentParams($studentData);
 // バリデーション（スコアも含む）
$errors = validateStudentData($studentData);

if (!empty($_POST['scores'])) {
    $scoreErrors = validateScores($_POST['scores']);
    $errors = array_merge($errors, $scoreErrors);
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    redirect("/student-data.php?id=$student_id");
    exit;
}

// 削除処理（生徒そのもの）
if (isset($_POST['deleteStudent'])) {
    deleteStudent($pdo, $student_id);
    $_SESSION['message'] = '生徒情報を削除しました。';
    redirect('/index.php');
    exit;
}

// 選択した成績削除処理
if (isset($_POST['deleteScores'])) {
    // チェックされたtest_idを受け取る
    $student_id = (int)($_POST['id'] ?? 0);
    $selected = $_POST['selected_scores'] ?? [];

    if (empty($selected)) {
        $_SESSION['errors'][] = "削除したいテストを選択してください。";
    } else {
        deleteSelectedScores($pdo, $student_id, $selected);
        $_SESSION['message'] = "選択したテストの成績を削除しました。";
    }
    redirect("/student-data.php?id=$student_id");
    exit;
}

// 更新処理（生徒情報と成績）
if (isset($_POST['updateStudent'])) {
    // 生徒情報更新
    $sql = "UPDATE students SET
        class = :class, class_no = :class_no,
        last_name = :last_name, first_name = :first_name,
        last_name_kana = :last_name_kana, first_name_kana = :first_name_kana,
        gender = :gender, birth_date = :birth_date,
        tel_number = :tel_number, email = :email,
        parent_last_name = :parent_last_name, parent_first_name = :parent_first_name,
        parent_tel_number = :parent_tel_number, memo = :memo,
        updated_at = NOW()
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(prepareStudentUpdateParams($studentData, $student_id));

    // テスト成績更新（各教科）
    if (isset($_POST['scores']) && is_array($_POST['scores'])) {
        updateTestScores($pdo, $student_id, $_POST['scores']);
    }

    // 写真アップロード処理
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        uploadStudentPhoto($student_id, $_FILES['photo'], $pdo, "/student-data.php?id=$student_id");
    }

    // 更新完了後、編集画面に戻る
     $_SESSION['message'] = "生徒情報を更新しました。";
    redirect("/student-data.php?id=$student_id");
    exit;
}