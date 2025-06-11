<!-- 処理機能 -->

<?php
// utils.php - 共通関数まとめ

// HTMLエスケープ
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// リダイレクト
function redirect($url) {
    header("Location: $url");
    exit;
}

// ログインチェック
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

// 生徒一覧取得（検索 + ページネーション対応）
function getStudents($pdo, $class = '', $name = '', $limit = 30, $offset = 0) {
    $sql = "SELECT * FROM students WHERE 1=1";
    $params = [];

    if ($class !== '') {
        $sql .= " AND class = :class";
        $params['class'] = $class;
    }

    if ($name !== '') {
        $sql .= " AND (
            last_name_kana LIKE :name1 OR
            first_name_kana LIKE :name2 OR
            last_name LIKE :name3 OR
            first_name LIKE :name4
        )";
        $params['name1'] = "%$name%";
        $params['name2'] = "%$name%";
        $params['name3'] = "%$name%";
        $params['name4'] = "%$name%";
    }

    $sql .= " ORDER BY id ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 生徒件数取得（検索条件付き）
function getStudentCount($pdo, $class = '', $name = '') {
    $sql = "SELECT COUNT(*) FROM students WHERE 1=1";
    $params = [];

    if ($class !== '') {
        $sql .= " AND class = :class";
        $params['class'] = $class;
    }

    if ($name !== '') {
        $sql .= " AND (
            last_name_kana LIKE :name1 OR
            first_name_kana LIKE :name2 OR
            last_name LIKE :name3 OR
            first_name LIKE :name4
        )";
        $params['name1'] = "%$name%";
        $params['name2'] = "%$name%";
        $params['name3'] = "%$name%";
        $params['name4'] = "%$name%";
    }

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }

    $stmt->execute();
    return (int)$stmt->fetchColumn();
}


// バリデーション関連

// 必須チェック
function validateRequired($value, $fieldName) {
    return (trim($value) === '') ? "{$fieldName}は必須項目です。" : '';
}

// メール形式チェック
function validateEmail($email) {
    return (!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "メールアドレスの形式が正しくありません。" : '';
}

// 数値チェック
function validateNumeric($value, $fieldName) {
    return (!is_numeric($value)) ? "{$fieldName}は数字で入力してください。" : '';
}

// 日付バリデーション
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// 学生情報バリデーション
function validateStudentData($data) {
    $errors = [];

    if (empty($data['class']) || !in_array($data['class'], ['A', 'B', 'C', 'D', 'E'])) {
        $errors['class'] = 'クラスはA〜Eのいずれかを選んでください。';
    }

    if (empty($data['class_no']) || !ctype_digit($data['class_no'])) {
        $errors['class_no'] = 'クラス番号は半角数字で入力してください。';
    }

    if (empty($data['last_name']) || empty($data['first_name'])) {
        $errors['name'] = '氏名（姓・名）を入力してください。';
    }

    if (empty($data['last_name_kana']) || empty($data['first_name_kana'])) {
        $errors['name_kana'] = 'ふりがな（姓・名）を入力してください。';
    }

    if (empty($data['gender']) || !in_array($data['gender'], ['1', '2'])) {
        $errors['gender'] = '性別を選択してください。';
    }

    if (!empty($data['birth_date']) && !validateDate($data['birth_date'])) {
        $errors['birth_date'] = '正しい生年月日を入力してください。';
    }

    if (!empty($data['tel_number']) && !preg_match('/^\d{10,11}$/', $data['tel_number'])) {
        $errors['tel_number'] = '電話番号は10〜11桁の半角数字で入力してください。';
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = '有効なメールアドレスを入力してください。';
    }

    if (!empty($data['parent_last_name']) || !empty($data['parent_first_name'])) {
        if (empty($data['parent_last_name']) || empty($data['parent_first_name'])) {
            $errors['parent_name'] = '保護者の姓と名の両方を入力してください。';
        }
    }

    if (!empty($data['parent_tel_number']) && !preg_match('/^\d{10,11}$/', $data['parent_tel_number'])) {
        $errors['parent_tel_number'] = '保護者の電話番号は10〜11桁の半角数字で入力してください。';
    }

    return $errors;
}

// テスト成績バリデーション
function validateScores($scores) {
    $errors = [];
    foreach ($scores as $index => $scoreSet) {
        foreach (['japanese', 'math', 'english', 'science', 'society'] as $subject) {
            if (isset($scoreSet[$subject]) && $scoreSet[$subject] !== '') {
                if (!ctype_digit($scoreSet[$subject]) || (int)$scoreSet[$subject] < 0 || (int)$scoreSet[$subject] > 100) {
                    $errors["score_{$index}_{$subject}"] = "{$subject}の点数は0〜100の半角数字で入力してください。";
                }
            }
        }
    }
    return $errors;
}


// 表示変換

// 性別をテキスト化
function genderToText($gender) {
    return $gender == 1 ? '男性' : ($gender == 2 ? '女性' : '不明');
}


// 生徒データ処理

// 生徒完全削除（スコア・画像含む）
function deleteStudent($pdo, $student_id) {
    $pdo->prepare("DELETE FROM scores WHERE student_id = ?")->execute([$student_id]);

    $uploadDir = __DIR__ . '/../uploads/';
    foreach (glob($uploadDir . "student_{$student_id}.*") as $old) {
        if (is_file($old)) unlink($old);
    }

    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$student_id]);
}

// 画像アップロード処理
function handlePhotoUpload(int $student_id, array $file, PDO $pdo): ?string {
    // エラーチェック
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    // アップロード先ディレクトリの絶対パス取得・作成
    $uploadDir = realpath(__DIR__ . '/../public/uploads') ?: (__DIR__ . '/../public/uploads');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadDir = rtrim($uploadDir, '/') . '/';
    var_dump($uploadDir);

    // ファイル情報取得
    $tmpName = $file['tmp_name'];
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // 拡張子・MIMEタイプチェック
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($ext, $allowedExtensions, true) || !in_array($mime, $allowedMimes, true)) {
        return false;
    }

    // 既存ファイルの削除
    foreach (glob($uploadDir . "student_{$student_id}.*") as $oldFile) {
        if (is_file($oldFile)) unlink($oldFile);
    }

    // 新ファイル名・パス
    $newFileName = "student_{$student_id}." . $ext;
    $destination = $uploadDir . $newFileName;

    // ファイル移動
    var_dump($tmpName); // 一時ファイルのパス
    var_dump($ext);     // 拡張子
    var_dump($mime);    // MIMEタイプ


    if (move_uploaded_file($tmpName, $destination)) {
        // DBに画像パスを保存
        echo "move_uploaded_file 成功<br>";
        if (file_exists($destination)) {
            echo "ファイルは確かに存在しています";
        } else {
            echo "ファイルは move_uploaded_file 直後に存在していません！";
        }
        $photoPath = 'uploads/' . $newFileName;
        $stmt = $pdo->prepare("UPDATE students SET image = :image WHERE id = :id");
        $stmt->execute([':image' => $photoPath, ':id' => $student_id]);
        return $photoPath;
    } else {
        echo "move_uploaded_file 失敗<br>";
        error_log("Failed to move uploaded file from $tmpName to $destination");
        return false;
    }
    var_dump($destination);

}

// 成績更新
function updateTestScores($pdo, $student_id, $scores) {
    $subjects = ['japanese' => 1, 'math' => 2, 'english' => 3, 'science' => 4, 'society' => 5];
    foreach ($scores as $score) {
        $test_id = $score['test_id'] ?? null;
        if (!$test_id) continue;

        foreach ($subjects as $key => $subject_id) {
            if (isset($score[$key])) {
                $val = max(0, min((int)$score[$key], 100)); // 0〜100の範囲
                $pdo->prepare("UPDATE scores SET score = ? WHERE student_id = ? AND test_id = ? AND subject_id = ?")
                    ->execute([$val, $student_id, $test_id, $subject_id]);
            }
        }
    }
}

//テストスコア削除(0を表示)
function deleteSelectedScores(PDO $pdo, array $test_ids) {
    // 数値に変換しつつ空要素を除外
    $test_ids = array_filter(array_map('intval', $test_ids));
    
    if (count($test_ids) === 0) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($test_ids), '?'));

    // SQL文の準備
    $sql = "DELETE FROM scores WHERE test_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // 実行
    $result = $stmt->execute($test_ids);

    return $result;
}
