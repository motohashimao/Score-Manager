<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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

// バリデーション
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
    $errors = array();
    $subjectMap = getSubjects(); //日本語にする

    foreach ($scores as $index => $scoreSet) {
        foreach ($subjectMap as $key => $name) {
            $val = isset($scoreSet[$key]) ? $scoreSet[$key] : '';
            if ($val === '') continue;

            if (!ctype_digit((string)$val) || (int)$val < 0 || (int)$val > 100) {
                $errors["score_{$index}_{$key}"] = $name . "の点数は0〜100の半角数字で入力してください。";
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

// フォーム値保持
function inputValue(string $key, $default = '') {
    global $old;

    if (isset($old[$key])) {
        return htmlspecialchars($old[$key], ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
}
