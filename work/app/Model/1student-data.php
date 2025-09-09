<?php
session_start();
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');
require_once(__DIR__ . '/../app/Model/student.php');

$pdo = \MyApp\Database::getInstance();
$id = $_GET['id'] ?? '';
$studentModel = new StudentModel($pdo);

if (!$id) {
    echo "アクセス出来てません";
    exit;
}

// $student = fetchStudentById($pdo, (int)$id);
$student = $studentModel->fetchStudentById((int)$id);

if (!$student) {
    echo "生徒が見つかりません。";
    exit;
}

// 成績データ取得
// $scores = fetchScoresGroupedByTest($pdo, (int)$id);
$scores = $studentModel->fetchScoresGroupedByTest((int)$id);


// // バリデーションエラーと前回の入力値を受け取る
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

// 選択済みクラスの値をセット
$selectedClass = $old['class'] ?? $student['class'] ?? '';

// 性別の選択値をセット
$selectedGender = $old['gender'] ?? $student['gender'] ?? '';

//テスト情報の取得
$testTypes = getTestTypes();

//科目
$subjects = getSubjects(); // ヘッダーとデータ両方で使える

// クラス一覧取得
$classes = getClassList();
$selectedClass = $_GET['class'] ?? ($old['class'] ?? $student['class'] ?? '');

//画像
$photoPath = $_SESSION['old']['photo_path'] ?? ($student['image'] ?? 'image/noimage.png');

// // 使い終わったら消す（再読み込みで残らないように）
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
        <h1 class="list">
          <a href="index.php" style="text-decoration: none; color: inherit;">
          成績管理システム<br>(Score Manager)
          </a>
        </h1>
        <button class="btn logout-btn" onclick="location.href='logout.php'">ログアウト</button>
    </header>
    <!--main-->
    <main class="main-content">
      <h2>生徒データ編集</h2>
      <button class="btn index-btn" onclick="location.href='index.php'">生徒一覧</a></button>
        <?php if (!empty($errors)): ?>
          <div class="notice error">
            <?php foreach ($errors as $error): ?>
              <p><?= h($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['message'])): ?>
          <div class="notice message">
          <?= h($_SESSION['message']) ?>
          </div>
        <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
      <!-- 個人情報 -->
        <form action="update.php" method="post" enctype="multipart/form-data" >
          <div class="personal-info">
            <input type="hidden" name="id" value="<?= h($student['id']) ?>">
            <!-- 入力フォーム -->
          <table class="form-table">
            <tr>
              <th>クラス名<span style="color:#eb9d7d;">*(必須)</span></th>
              <td>
                <select name="class">
                  <option value="">選択してください</option>
                   <?php foreach ($classes as $class): ?>
                   <option value="<?= $class ?>" <?= ($selectedClass === $class) ? 'selected' : '' ?>><?= $class ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
            <tr>
              <th>クラス番号<span style="color:#eb9d7d;">*(必須)</span></th>
              <td>
                <input type="text" name="class_no" value="<?= inputValue('class_no',$student['class_no']) ?>" placeholder="例: 1">
              </td>
            </tr>
            <tr>
              <th>氏名<span style="color:#eb9d7d;">*(必須)</span></th>
              <td colspan="3">
                <div class="name-fields">
                  <input type="text" name="last_name" value="<?= inputValue('last_name', $student['last_name']) ?>" placeholder="姓">
                  <input type="text" name="first_name" value="<?= inputValue('first_name',$student['first_name']) ?>" placeholder="名">
                </div>
              </td>
            </tr>
            <tr>
              <th>氏名かな<span style="color:#eb9d7d;">*(必須)</span></th>
              <td colspan="3">
                <div class="name-fields">
                  <input type="text" name="last_name_kana" value="<?= inputValue('last_name_kana',$student['last_name_kana']) ?>" placeholder="せい">
                  <input type="text" name="first_name_kana" value="<?= inputValue('first_name_kana',$student['first_name_kana']) ?>" placeholder="めい">
                </div>
              </td>
            </tr>
            <tr>
              <th>性別<span style="color:#eb9d7d;">*(必須)</span></th>
              <td>
                <select name="gender">
                  <option value="" <?= ($selectedGender === '' || $selectedGender === null) ? 'selected' : '' ?>>選択してください</option>
                  <option value="1" <?= ($selectedGender == 1) ? 'selected' : '' ?>>男性</option>
                  <option value="2" <?= ($selectedGender == 2) ? 'selected' : '' ?>>女性</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>生年月日</th>
                <td>
                  <input type="date" name="birth_date" value="<?= inputValue('birth_date',$student['birth_date']) ?>">
                </td>
            </tr>
            <tr>
              <th>連絡先</th>
              <td>
                <input type="text" name="tel_number" value="<?= inputValue('tel_number',$student['tel_number']) ?>" placeholder="電話番号">
                <p class="form-note">※ ハイフン（-）なしの10〜11桁の半角数字で入力してください。</p>
              </td>
            </tr>
            <tr>
              <th>E-mail</th>
              <td>
                <input type="email" name="email" value="<?= inputValue('email',$student['email']) ?>" placeholder="例: test@example.com">
              </td>
            </tr>
            <tr>
              <th>保護者氏名</th>
                <td colspan="3">
                  <div class="name-fields">
                    <input type="text" name="parent_last_name" value="<?= inputValue('parent_last_name',$student['parent_last_name']) ?>" placeholder="姓">
                    <input type="text" name="parent_first_name" value="<?= inputValue('parent_first_name',$student['parent_first_name']) ?>" placeholder="名">
                  </div>
                </td>
            </tr>
            <tr>
              <th>保護者連絡先</th>
                <td>
                  <input type="text" name="parent_tel_number" value="<?= inputValue('parent_tel_number',$student['parent_tel_number']) ?>" placeholder="電話番号">
                  <p class="form-note">※ ハイフン（-）なしの10〜11桁の半角数字で入力してください。</p>
                  </td>
            </tr>
          </table>
        <!-- 写真セクション -->
          <div class="photo-section">
            <div class="photo-placeholder">
              <?php
                if (empty($student['image']) || $student['image'] === 'noimage') {
                    $photo = '/image/noimage.png';
                } else {
                    $photo = '/' . ltrim(h($student['image']), '/');
                }
              ?>
              <img id="preview" src="<?= $photo ?>" alt="student photo">
            </div>
            <label for="photo-upload" class="btn image-btn">写真を選択</label>
            <input type="file" id="photo-upload" name="photo" accept="image/*" style="display: none;">
          </div>
        </div>
      <!-- テスト成績表 -->
        <div class="test-results data-box">
         <h3>テスト成績一覧</h3>
          <div class="test-results data-box">
            <table>
              <thead>
                <tr>
                  <th>選択</th>
                  <th>テスト<br>年月日</th>
                  <th>テスト名</th>
                <?php foreach ($subjects as $key => $label): ?>
            <th><?= h($label) ?></th>
          <?php endforeach; ?>
                  <th>合計点</th>
                  <th>平均点</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($scores as $index => $score): ?>
                  <?php
                    $total = 0;
                    $count = 0;
                    foreach (array_keys($subjects) as $subject) {
                      $point = $old['scores'][$index][$subject] ?? $score[$subject] ?? '';
                      if ($point !== '') {
                        $total += (int)$point;
                        $count++;
                      }
                    }
                    $average = $count > 0 ? round($total / $count, 1) : 0;
                  ?>
                  <tr>
                    <td><input type="checkbox" name="selected_scores[]" value="<?= h($score['test_id']) ?>"></td>
                    <td><?= h($score['test_date']) ?></td>
                    <td><?= $testTypes[$score['test_cd']] ?? '不明なテスト' ?></td>
                    <?php foreach ($subjects as $key => $label): ?>
                    <?php
                      $postScore = $old['scores'][$index][$key] ?? $score[$key] ?? '';
                    ?>
                    <td><input type="text" name="scores[<?= $index ?>][<?= $key ?>]" value="<?= h($postScore) ?>"></td>
                    <?php endforeach; ?>
                    <td class="total"><?= $total ?></td>
                    <td class="average"><?= $average ?></td>
                    <input type="hidden" name="scores[<?= $index ?>][test_id]" value="<?= h($score['test_id']) ?>">
                    <input type="hidden" name="scores[<?= $index ?>][score_id]" value="<?= h($score['score_id']) ?>">
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