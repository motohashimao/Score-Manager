<?php
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

session_start();

// CSRF対策などを追加したい場合はここで確認

// 生徒IDを取得（POSTから想定）
$student_id = $_POST['student_id'] ?? '';

if (!$student_id) {
    echo "不正なアクセスです。";
    exit;
}

try {
    $pdo = \MyApp\Database::getInstance();
    deleteStudent($pdo, $student_id); // utils.phpで定義済みの関数
    redirect('../public/index.php');
} catch (Exception $e) {
    echo "削除に失敗しました: " . $e->getMessage();
}
