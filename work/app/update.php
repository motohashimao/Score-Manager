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

// バリデーション
$errors = validateStudentData($_POST);
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
    redirect('/index.php');
    exit;
}

// 選択した成績削除処理
if (isset($_POST['deleteScores'])) {
    // チェックされたtest_idを受け取る
    $student_id = (int)($_POST['id'] ?? 0);
    $selected = $_POST['selected_scores'] ?? [];

    if (!empty($selected)) {
        // 0にリセット更新する処理
        deleteSelectedScores($pdo, $student_id, $selected);
        $_SESSION['message'] = "選択したテストの成績を削除しました。";
    } else {
        $_SESSION['message'] = "削除対象が選択されていません。";
    }
    redirect("/student-data.php?id=$student_id");
    exit;
}

// 更新処理（生徒情報と成績）
if (isset($_POST['updateStudent'])) {
    // 生年月日チェック（空欄はnull）
    $birth_date_raw = $_POST['birth_date'] ?? '';
    $birth_date = ($birth_date_raw === '') ? null : $birth_date_raw;

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
    $stmt->execute([
        ':class' => $_POST['class'] ?? '',
        ':class_no' => $_POST['class_no'] ?? '',
        ':last_name' => $_POST['last_name'] ?? '',
        ':first_name' => $_POST['first_name'] ?? '',
        ':last_name_kana' => $_POST['last_name_kana'] ?? '',
        ':first_name_kana' => $_POST['first_name_kana'] ?? '',
        ':gender' => $_POST['gender'] ?? '',
        ':birth_date' => $birth_date,
        ':tel_number' => $_POST['tel_number'] ?? '',
        ':email' => $_POST['email'] ?? '',
        ':parent_last_name' => $_POST['parent_last_name'] ?? '',
        ':parent_first_name' => $_POST['parent_first_name'] ?? '',
        ':parent_tel_number' => $_POST['parent_tel_number'] ?? '',
        ':memo' => $_POST['memo'] ?? '',
        ':id' => $student_id,
    ]);

    // テスト成績更新（各教科）
    if (isset($_POST['scores']) && is_array($_POST['scores'])) {
    updateTestScores($pdo, $student_id, $_POST['scores']);
}

     // 写真アップロード処理
    $upload_errors = [
        UPLOAD_ERR_OK => 'エラーなし',
        UPLOAD_ERR_INI_SIZE => 'アップロードファイルがphp.iniのupload_max_filesizeを超過',
        UPLOAD_ERR_FORM_SIZE => 'アップロードファイルがHTMLフォームのMAX_FILE_SIZEを超過',
        UPLOAD_ERR_PARTIAL => 'ファイルが部分的にしかアップロードされなかった',
        UPLOAD_ERR_NO_FILE => 'ファイルがアップロードされなかった',
        UPLOAD_ERR_NO_TMP_DIR => 'テンポラリフォルダが見つからない',
        UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗',
        UPLOAD_ERR_EXTENSION => 'PHP拡張によってアップロードが停止'
    ];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $result = handlePhotoUpload($student_id, $_FILES['photo'], $pdo);
        if ($result === false) {
            $_SESSION['errors'][] = "対応していないファイル形式です。";
            redirect("/student-data.php?id=$student_id");
            exit;
         }
    }

    // 更新完了後、編集画面に戻る
     $_SESSION['message'] = "生徒情報を更新しました。";
    redirect("/student-data.php?id=$student_id");
    exit;
}