<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');
require_once(__DIR__ . '/../app/Model/Student.php');

$pdo = MyApp\Database::getInstance();
$studentModel = new StudentModel($pdo);

$student_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$student_id) {
    echo "不正なアクセスです。";
    exit;
}

// POSTデータ
$studentData = $studentModel->collectStudentPostData();

// バリデーション
$errors = validateStudentData($studentData);
if (!empty($_POST['scores'])) {
    $scoreErrors = validateScores($_POST['scores']);
    $errors = array_merge($errors, $scoreErrors);
}
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    redirect("/student-data.php?id=" . $student_id);
    exit;
}

// 生徒削除
if (isset($_POST['deleteStudent'])) {
    $studentModel->deleteStudent($student_id);
    $_SESSION['message'] = "生徒情報を削除しました。";
    redirect('/index.php');
    exit;
}

// 選択成績削除
if (isset($_POST['deleteScores'])) {
    $selected = isset($_POST['selected_scores']) ? $_POST['selected_scores'] : array();
    if (empty($selected)) {
        $_SESSION['errors'][] = "削除したいテストを選択してください。";
    } else {
        $studentModel->deleteSelectedScores($student_id, $selected);
        $_SESSION['message'] = "選択したテストの成績を削除しました。";
    }
    redirect("/student-data.php?id=" . $student_id);
    exit;
}

// 生徒情報更新
if (isset($_POST['updateStudent'])) {
    $stmt = $pdo->prepare($studentModel->buildUpdateSql());
    $stmt->execute($studentModel->prepareStudentUpdateParams($studentData, $student_id));

    // 成績更新
    $rawScores = isset($_POST['scores']) ? $_POST['scores'] : array();
    $parsedScores = array();
    foreach ($rawScores as $scoreData) {
        $test_id = isset($scoreData['test_id']) ? $scoreData['test_id'] : 0;
        if (!$test_id) continue;
        foreach ($scoreData as $subject => $value) {
            if (in_array($subject, array('test_id','score_id'))) continue;
            $parsedScores[$test_id][$subject] = $value;
        }
    }
    $studentModel->updateTestScores($student_id, $parsedScores);

    // 画像アップロード
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $filename = $studentModel->uploadPhoto($student_id, $_FILES['photo']);
        if ($filename) {
            $stmt = $pdo->prepare("UPDATE students SET image = ? WHERE id = ?");
            $stmt->execute(array($filename, $student_id));
        }
    }

    $_SESSION['message'] = "生徒情報を更新しました。";
    redirect("/student-data.php?id=" . $student_id);
    exit;
}