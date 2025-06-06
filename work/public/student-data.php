<?php

require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

$pdo = \MyApp\Database::getInstance();

$id = $_GET['id'] ?? '';

if (!$id) {
    echo "アクセス出来てませんわ★";
    exit;
}

// バリデーションエラーと前回の入力値を受け取る
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

// 使い終わったら消す（再読み込みで残らないように）
unset($_SESSION['errors'], $_SESSION['old']);


// 生徒情報の取得
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
// 生徒が存在しない場合の処理
if (!$student) {
    echo "生徒が見つかりません。";
    exit;
 }

// 成績情報の取得
$sql = "SELECT
          scores.id AS score_id,          -- スコアID（主キー）
          scores.test_id,                 -- テストID（外部キー）
          tests.test_date,
          scores.score,
          subjects.name AS subject_name,
          tests.test_cd
        FROM scores
        JOIN subjects ON scores.subject_id = subjects.id
        JOIN tests ON scores.test_id = tests.id
        WHERE scores.student_id = :student_id
        ORDER BY tests.test_date DESC, subjects.sort ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':student_id' => $id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$scores = $results;

// テスト情報の整理
$testTypes = [
  1 => '中間テスト',
  2 => '期末テスト',
  3 => '総合テスト'
];

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
            'score_id' => $row['score_id'],
            'test_date' => $row['test_date'],
            'test_cd' => $row['test_cd'],
            'japanese' => null,
            'math' => null,
            'english' => null,
            'science' => null,
            'society' => null
        ];
    }

    $key = $subjectMap[$row['subject_name']] ?? null;
    if ($key) {
        $grouped[$testKey][$key] = $row['score'];
    }
}

$scores = array_values($grouped);



// 表示後にエラー情報をクリア
unset($_SESSION['errors'], $_SESSION['old']);


?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>student list</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="wrap">
    <!--header-->
    <header class="header-logo">
        <h1 class="list">成績管理システム<br>(Score Manager)</h1>
        <button class="btn logout-btn" onclick="location.href='logout.php'">ログアウト</button>
    </header>
    <!--main-->
    <main class="main-content">
      <h2>生徒データ編集</h2>
      <button class="btn index-btn" onclick="location.href='index.php'">生徒一覧</a></button>
        <?php if (!empty($errors)): ?>
          <div class="error" style="color: red;">
            <?php foreach ($errors as $error): ?>
              <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <!-- 個人情報 -->
      <!-- 写真セクション -->
      <form method="post" action="/app/update.php" enctype="multipart/form-data" >
        <div class="personal-info">
        <input type="hidden" name="id" value="<?= h($student['id']) ?>">
        <!-- 入力フォーム -->
        <table class="form-table">
          <tr>
            <th>クラス名<span style="color:#eb9d7d;">*(必須)</span></th>
            <td>
              <select name="class">
                <option value=""> </option>
                <?php foreach (['A','B','C','D','E'] as $class): ?>
                <option value="<?= $class ?>" <?= ($student['class'] === $class) ? 'selected' : '' ?>><?= $class ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th>クラス番号<span style="color:#eb9d7d;">*(必須)</span></th>
            <td>
              <input type="text" name="class_no" value="<?= h($student['class_no']) ?>" placeholder="例: 1">
            </td>
          </tr>
          <tr>
            <th>氏名<span style="color:#eb9d7d;">*(必須)</span></th>
            <td colspan="3">
              <div class="name-fields">
                <input type="text" name="last_name" value="<?= h($student['last_name']) ?>" placeholder="姓">
                <input type="text" name="first_name" value="<?= h($student['first_name']) ?>" placeholder="名">
              </div>
            </td>
          </tr>
          <tr>
            <th>氏名かな<span style="color:#eb9d7d;">*(必須)</span></th>
            <td colspan="3">
              <div class="name-fields">
                <input type="text" name="last_name_kana" value="<?= h($student['last_name_kana']) ?>" placeholder="せい">
                <input type="text" name="first_name_kana" value="<?= h($student['first_name_kana']) ?>" placeholder="めい">
              </div>
            </td>
          </tr>
          <tr>
            <th>性別<span style="color:#eb9d7d;">*(必須)</span></th>
            <td>
              <select name="gender">
                <option value=""></option>
                <option value="1" <?= ($student['gender'] == 1) ? 'selected' : '' ?>>男性</option>
                <option value="2" <?= ($student['gender'] == 2) ? 'selected' : '' ?>>女性</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>生年月日</th>
              <td>
                <input type="date" name="birth_date" value="<?= h($student['birth_date']) ?>">
              </td>
          </tr>
          <tr>
            <th>連絡先</th>
            <td>
              <input type="text" name="tel_number" value="<?= h($student['tel_number']) ?>" placeholder="電話番号">
            </td>
          </tr>
          <tr>
            <th>E-mail</th>
            <td>
              <input type="email" name="email" value="<?= h($student['email']) ?>" placeholder="例: test@example.com">
            </td>
          </tr>
          <tr>
            <th>保護者氏名</th>
              <td colspan="3">
                <div class="name-fields">
                  <input type="text" name="parent_last_name" value="<?= h($student['parent_last_name']) ?>" placeholder="姓">
                  <input type="text" name="parent_first_name" value="<?= h($student['parent_first_name']) ?>" placeholder="名">
                </div>
              </td>
          </tr>
          <tr>
            <th>保護者連絡先</th>
              <td>
                <input type="text" name="parent_tel_number" value="<?= h($student['parent_tel_number']) ?>" placeholder="電話番号">
                </td>
          </tr>
        </table>
        <!-- 写真セクション -->
        <div class="photo-section">
          <div class="photo-placeholder">
           <?php
              // 写真のパスを判定
              if (empty($student['image']) || $student['image'] === 'noimage') {
                  $photo = '/image/noimage.png';
              } else {
                  $photo = '/' . h($student['image']);
              }
              ?>
              <img id="preview" src="<?= $photo ?>" alt="student photo">

          </div>
          <?php var_dump($student['image']); ?>

            <label for="photo-upload" class="btn image-btn">写真を選択</label>
            <input type="file" id="photo-upload" name="photo" accept="image/*" style="display: none;">
      </div>
      </div>
      <!-- テスト成績表 -->
        <div class="test-results data-box">
          <h3>テスト成績一覧</h3>
          <table>
            <thead>
              <tr>
                <th>選択</th>
                <th>テスト<br>年月日</th>
                <th>テスト名</th>
                <th>国語</th>
                <th>数学</th>
                <th>英語</th>
                <th>理科</th>
                <th>社会</th>
                <th>合計点</th>
                <th>平均点</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($scores as $index => $score): ?>
              <?php
                $total = 0;
                $count = 0;
                foreach (['japanese', 'math', 'english', 'science', 'society'] as $subject) {
                if (isset($score[$subject])) {
                  $total += $score[$subject];
                  $count++;
                  }
                }
                $average = $count > 0 ? round($total / $count, 1) : 0;
              ?>
              <tr>
                <td><input type="checkbox" name="selected_scores[]" value="<?= h($score['id']) ?>"></td>
                <td><?= h($score['test_date']) ?></td>
                <td><?= $testTypes[$score['test_cd']] ?? '不明なテスト' ?></td>
                <td><input type="text" name="scores[<?= $index ?>][japanese]" value="<?= h($score['japanese']) ?>"></td>
                <td><input type="text" name="scores[<?= $index ?>][math]" value="<?= h($score['math']) ?>"></td>
                <td><input type="text" name="scores[<?= $index ?>][english]" value="<?= h($score['english']) ?>"></td>
                <td><input type="text" name="scores[<?= $index ?>][science]" value="<?= h($score['science']) ?>"></td>
                <td><input type="text" name="scores[<?= $index ?>][society]" value="<?= h($score['society']) ?>"></td>
                <td class="total"><?= $total ?></td>
                <td class="average"><?= $average ?></td>

               <input type="hidden" name="scores[<?= $index ?>][test_id]" value="<?= h($score['test_id']) ?>">
              <input type="hidden" name="scores[<?= $index ?>][score_id]" value="<?= h($score['score_id']) ?>">
                <!-- debug -->
<td>test_id: <?= $score['test_id'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="button-area">
          <button type="submit" class="btn d-btn" name="deleteScores">テスト成績削除</button>
        </div>
      <!-- メモ -->
      <div class="memo-section data-box">
        <h3>メモ</h3>
        <textarea name="memo" id="memo" class="memo"><?= h($student['memo'] ?? '') ?></textarea>
          <div class="button-area">
              <button type="submit" class="btn" name="updateStudent">修正</button>
              <button type="submit" class="btn delete-btn" name="deleteStudent">全削除</button>
          </div>
      </div>
      </form>
    </main>
  </div>
  <script src="main.js"></script>
</body>
</html>