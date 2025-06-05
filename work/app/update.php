<?php

session_start();
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

$pdo = \MyApp\Database::getInstance();

// POSTデータ取得
$student_id = $_POST['id'] ?? '';
if (!$student_id) {
    echo "不正なアクセスですわよ♡";
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

// テスト成績の一部削除
if (isset($_POST['deleteScores']) && !empty($_POST['selected_scores'])) {
    deleteSelectedScores($pdo, $student_id, $_POST['selected_scores']);
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
    foreach ($_POST['scores'] as $score) {
        foreach (['japanese' => '国語', 'math' => '数学', 'english' => '英語', 'science' => '理科', 'society' => '社会'] as $field => $subjectName) {
            if (isset($score[$field]) && $score[$field] !== '') {
                $stmt = $pdo->prepare("
                    UPDATE scores
                    SET score = :score
                    WHERE student_id = :student_id AND test_id = :test_id AND subject_id = (
                        SELECT id FROM subjects WHERE name = :subject_name
                    )
                ");
                $stmt->execute([
                    ':score' => $score[$field],
                    ':student_id' => $student_id,
                    ':test_id' => $score['test_id'],
                    ':subject_name' => $subjectName
                ]);
                
                // ちゃんと更新できたか確認
                echo "更新: test_id={$score['test_id']} / {$subjectName} → {$score[$field]}<br>";

                $error = $stmt->errorInfo();
                if ($error[0] !== '00000') {
                    echo "エラー: " . implode(', ', $error) . "<br>";
                }
            }
        }
    }
}

    
    
    // 写真アップロード処理（必要に応じて有効化）
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $result = handlePhotoUpload($student_id, $_FILES['photo'], $pdo);
        if ($result === false) {
            echo "対応していないファイル形式です。";
            exit;
        }
    }

    // 更新完了後、編集画面に戻る
    redirect("/student-data.php?id=$student_id");
    exit;
}

// 該当処理なし → 編集画面に戻す
redirect("/student-data.php?id=$student_id");
exit;
