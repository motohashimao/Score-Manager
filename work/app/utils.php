<?php

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
         // 空白削除
        $name = preg_replace('/[\s　]+/u', '', $name);

        $sql .= " AND (
            last_name_kana LIKE :name1 OR
        first_name_kana LIKE :name2 OR
        last_name LIKE :name3 OR
        first_name LIKE :name4 OR
        REPLACE(CONCAT(last_name, first_name), ' ', '') LIKE :name5 OR
        REPLACE(CONCAT(first_name, last_name), ' ', '') LIKE :name6 OR
        REPLACE(CONCAT(last_name_kana, first_name_kana), ' ', '') LIKE :name7 OR
        REPLACE(CONCAT(first_name_kana, last_name_kana), ' ', '') LIKE :name8
        )";

        foreach (range(1, 8) as $i) {
        $params["name$i"] = "%$name%";
        }
    }

    $sql .= " ORDER BY class ASC, class_no ASC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 生徒件数取得
function getStudentCount($pdo, $class = '', $name = '') {
    $sql = "SELECT COUNT(*) FROM students WHERE 1=1";
    $params = [];

    if ($class !== '') {
        $sql .= " AND class = :class";
        $params['class'] = $class;
    }

    if ($name !== '') {
        // 空白削除
        $name = preg_replace('/[\s　]+/u', '', $name);

        $sql .= " AND (
            last_name_kana LIKE :name1 OR
            first_name_kana LIKE :name2 OR
            last_name LIKE :name3 OR
            first_name LIKE :name4 OR
            REPLACE(CONCAT(last_name, first_name), ' ', '') LIKE :name5 OR
            REPLACE(CONCAT(first_name, last_name), ' ', '') LIKE :name6 OR
            REPLACE(CONCAT(last_name_kana, first_name_kana), ' ', '') LIKE :name7 OR
            REPLACE(CONCAT(first_name_kana, last_name_kana), ' ', '') LIKE :name8
        )";

        foreach (range(1, 8) as $i) {
            $params["name$i"] = "%$name%";
        }
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }
    $stmt->execute();

    return (int)$stmt->fetchColumn();
}


// バリデーション関連
//ログインパスワード
function validateRequired($value, $fieldName) {
    return trim($value) === '' ? 'メールアドレスまたはパスワードが間違っています。' : '';
}

// メールチェック
function validateEmail($email) {
    return (!filter_var($email, FILTER_VALIDATE_EMAIL)) ? "メールアドレスの形式が正しくありません。" : '';
}

// 数値チェック
function validateNumeric($value, $fieldName) {
    return (!is_numeric($value)) ? "{$fieldName}は数字で入力してください。" : '';
}

// 日付チェック
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
        $errors['tel_number'] = '電話番号は、ハイフン（-）なしの10〜11桁の半角数字で入力してください。';
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
// 性別
function genderToText($gender) {
    return $gender == 1 ? '男性' : ($gender == 2 ? '女性' : '不明');
}

// テスト種別
function getTestTypes(): array {
    return [
        1 => '中間テスト',
        2 => '期末テスト',
        3 => '総合テスト'
    ];
}

//科目
function getSubjects(): array {
    return [
        'japanese' => '国語',
        'math' => '数学',
        'english' => '英語',
        'science' => '理科',
        'society' => '社会'
    ];
}

//クラス名
function getClassList(): array {
    return ['A', 'B', 'C', 'D', 'E'];
}

// 生徒データ処理
//1人の生徒情報を取得
function fetchStudentById(PDO $pdo, int $student_id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    return $student ?: null;
}

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
function uploadStudentPhoto(int $student_id, array $file, PDO $pdo, string $redirectPath): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $uploadDir = __DIR__ . '/../public/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $file['tmp_name'];
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($ext, $allowedExtensions, true) || !in_array($mime, $allowedMimes, true)) {
        $_SESSION['errors'][] = 'アップロードできる画像は jpg, jpeg, png, gif のみです。';
        $_SESSION['old'] = $_POST;
        redirect($redirectPath);
        exit;
    }

    // 既存ファイルの削除
    foreach (glob($uploadDir . "student_{$student_id}.*") as $oldFile) {
        if (is_file($oldFile)) unlink($oldFile);
    }

    $newFileName = "student_{$student_id}." . $ext;
    $destination = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpName, $destination)) {
        $photoPath = 'uploads/' . $newFileName;
        $stmt = $pdo->prepare("UPDATE students SET image = :image WHERE id = :id");
        $stmt->execute([':image' => $photoPath, ':id' => $student_id]);

        $_SESSION['old'] = $_POST;
    $_SESSION['old']['photo_path'] = $photoPath;

        return $photoPath;
    } else {
        error_log("Failed to move uploaded file from $tmpName to $destination");
        $_SESSION['errors'][] = '画像のアップロードに失敗しました。';
        $_SESSION['old'] = $_POST;
        redirect($redirectPath);
        exit;
    }
}

// 成績更新（存在しなければINSERT）
function updateTestScores($pdo, $student_id, $scores) {
    $subjects = [
        'japanese' => 1,
        'math' => 2,
        'english' => 3,
        'science' => 4,
        'society' => 5
    ];

    foreach ($scores as $score) {
        $test_id = $score['test_id'] ?? null;
        if (!$test_id) continue;

        foreach ($subjects as $key => $subject_id) {
            if (isset($score[$key]) && $score[$key] !== '') {
                $val = max(0, min((int)$score[$key], 100)); // 0〜100に制限

                // すでにレコードが存在するか確認
                $stmt = $pdo->prepare("
                    SELECT id FROM scores
                    WHERE student_id = ? AND test_id = ? AND subject_id = ?
                ");
                $stmt->execute([$student_id, $test_id, $subject_id]);
                $existing = $stmt->fetch();

                if ($existing) {
                    // 存在すればUPDATE
                    $update = $pdo->prepare("
                        UPDATE scores
                        SET score = ?
                        WHERE student_id = ? AND test_id = ? AND subject_id = ?
                    ");
                    $update->execute([$val, $student_id, $test_id, $subject_id]);
                } else {
                    // なければINSERT
                    $insert = $pdo->prepare("
                        INSERT INTO scores (student_id, test_id, subject_id, score)
                        VALUES (?, ?, ?, ?)
                    ");
                    $insert->execute([$student_id, $test_id, $subject_id, $val]);
                }
            }
        }
    }
}

//テストスコア削除(0を表示)
function deleteSelectedScores(PDO $pdo, int $student_id, array $test_ids) {
    $test_ids = array_filter(array_map('intval', $test_ids));

    if (count($test_ids) === 0) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($test_ids), '?'));

    $sql = "UPDATE scores SET score = 0 WHERE student_id = ? AND test_id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$student_id], $test_ids);
    return $stmt->execute($params);
}

// 成績情報をテスト単位で整理して取得
function fetchScoresGroupedByTest(PDO $pdo, int $student_id): array {
    $sql = "
        SELECT
            t.id AS test_id,
            t.test_date,
            t.test_cd,
            s.name AS subject_name,
            s.id AS subject_id,
            COALESCE(sc.score, 0) AS score,
            sc.id AS score_id
        FROM tests t
        CROSS JOIN subjects s
        LEFT JOIN scores sc
            ON sc.test_id = t.id
            AND sc.subject_id = s.id
            AND sc.student_id = :student_id
        ORDER BY t.test_date DESC, s.sort ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':student_id' => $student_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subjectMap = [
        '国語' => 'japanese',
        '数学' => 'math',
        '英語' => 'english',
        '理科' => 'science',
        '社会' => 'society'
    ];

    $grouped = [];
    foreach ($results as $row) {
        $testKey = $row['test_cd'] . '_' . $row['test_date'];
        if (!isset($grouped[$testKey])) {
            $grouped[$testKey] = [
                'test_id' => $row['test_id'],
                'test_date' => $row['test_date'],
                'test_cd' => $row['test_cd'],
                'score_id' => null,
                'japanese' => null,
                'math' => null,
                'english' => null,
                'science' => null,
                'society' => null,
            ];
        }

        $key = $subjectMap[$row['subject_name']] ?? null;
        if ($key !== null) {
            $grouped[$testKey][$key] = $row['score'];
        }
    }

    return array_values($grouped);
}

// POSTから生徒データを取得（共通処理）
function collectStudentPostData(): array {
    $birth_date_raw = $_POST['birth_date'] ?? '';
    return [
        'class' => $_POST['class'] ?? '',
        'class_no' => $_POST['class_no'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name_kana' => $_POST['last_name_kana'] ?? '',
        'first_name_kana' => $_POST['first_name_kana'] ?? '',
        'gender' => isset($_POST['gender']) ? (int)$_POST['gender'] : null,
        'birth_date' => $birth_date_raw,
        'tel_number' => $_POST['tel_number'] ?? '',
        'email' => $_POST['email'] ?? '',
        'parent_last_name' => $_POST['parent_last_name'] ?? '',
        'parent_first_name' => $_POST['parent_first_name'] ?? '',
        'parent_tel_number' => $_POST['parent_tel_number'] ?? '',
        'memo' => $_POST['memo'] ?? ''
    ];
}

function buildStudentParams(array $data): array {
    $birth_date = (DateTime::createFromFormat('Y-m-d', $data['birth_date'])) ? $data['birth_date'] : null;

    return [
        ':class' => $data['class'],
        ':class_no' => $data['class_no'],
        ':last_name' => $data['last_name'],
        ':first_name' => $data['first_name'],
        ':last_name_kana' => $data['last_name_kana'],
        ':first_name_kana' => $data['first_name_kana'],
        ':gender' => $data['gender'],
        ':birth_date' => $birth_date,
        ':tel_number' => $data['tel_number'],
        ':email' => $data['email'],
        ':parent_last_name' => $data['parent_last_name'],
        ':parent_first_name' => $data['parent_first_name'],
        ':parent_tel_number' => $data['parent_tel_number'],
        ':memo' => $data['memo'] ?? '',
    ];
}

function prepareStudentInsertParams(array $data): array {
    $params = buildStudentParams($data);
    $params[':image'] = null;
    $params[':student_deleted'] = 0;
    return $params;
}

function prepareStudentUpdateParams(array $data, int $student_id): array {
    $params = buildStudentParams($data);
    $params[':id'] = $student_id;
    return $params;
}

function inputValue(string $key, $default = '') {
    global $old;

    if (isset($old[$key])) {
        return htmlspecialchars($old[$key], ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
}

function selectSelected($name, $value, $student) {
    $old = $_SESSION['old'][$name] ?? null;
    $selectedValue = $old !== null ? $old : $student[$name] ?? null;
    return ($selectedValue == $value) ? 'selected' : '';
}

