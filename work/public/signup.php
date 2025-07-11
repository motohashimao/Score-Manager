<?php
session_start();
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

// バリデーションエラーと前回の入力値を受け取る
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

// クラス一覧を取得
$classes = getClassList();
$selectedClass = $_GET['class'] ?? ($old['class'] ?? $student['class'] ?? '');

// 選択済みクラスの値をセット
$selectedClass = $old['class'] ?? $student['class'] ?? '';
// 性別の選択値をセット
$selectedGender = $old['gender'] ?? $student['gender'] ?? '';

$photoPath = $_SESSION['old']['photo_path'] ?? ($student['image'] ?? 'image/noimage.png');

// 使い終わったら消す（再読み込みで残らないように）
unset($_SESSION['errors'], $_SESSION['old']);



?>


<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>signup</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
     <!--header-->
    <header class="header-logo">
      <h1 class="list">
          <a href="index.php" style="text-decoration: none; color: inherit;">
          成績管理システム<br>(Score Manager)
          </a>
        </h1>
      <button class="btn logout-btn" onclick="location.href='logout.php'">ログアウト</button>
    </header>
    <!-- メイン -->
    <main class="main-content">
      <h2>新規登録</h2>
      <button class="btn index-btn" onclick="location.href='index.php'">生徒一覧</a></button>
      <?php if (!empty($errors)): ?>
      <div class="notice error" style="color: red;">
          <?php foreach ($errors as $error): ?>
          <p><?= h($error) ?></p>
          <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <form action="user.php" method="post" enctype="multipart/form-data">
        <div class="personal-info">
        <!-- 入力フォーム -->
          <table class="form-table">
          <tr>
            <th>クラス名<span style="color:#eb9d7d;">*(必須)</span></th>
            <td>
              <select name="class">
                <option value=" ">選択してください</option>
                <?php foreach ($classes as $class): ?>
                <option value="<?= $class ?>" <?= ($selectedClass === $class) ? 'selected' : '' ?>><?= $class ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th>クラス番号<span style="color:#eb9d7d;">*(必須)</span></th>
            <td><input type="text" name="class_no" placeholder="例: 1"  value="<?= inputValue('class_no') ?>"></td>
          </tr>
          <tr>
            <th>氏名<span style="color:#eb9d7d;">*(必須)</span></th>
              <td colspan="3">
                <div class="name-fields">
                  <input type="text" name="last_name" placeholder="姓"  value="<?= inputValue('last_name') ?>">
                  <input type="text" name="first_name" placeholder="名"  value="<?= inputValue('first_name') ?>">
                </div>
              </td>
          </tr>
          <tr>
            <th>氏名かな<span style="color:#eb9d7d;">*(必須)</span></th>
            <td colspan="3">
              <div class="name-fields">
                <input type="text" name="last_name_kana" placeholder="せい"  value="<?= inputValue('last_name_kana') ?>">
                <input type="text" name="first_name_kana" placeholder="めい"  value="<?= inputValue('first_name_kana') ?>">
              </div>
            </td>
          </tr>
          <tr>
            <th>性別<span style="color:#eb9d7d;">*(必須)</span></th>
            <td>
              <select name="gender">
                <option value="">選択してください</option>
                <option value="1" <?= $selectedGender === '1' ? 'selected' : '' ?>>男性</option>
                <option value="2" <?= $selectedGender === '2' ? 'selected' : '' ?>>女性</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>生年月日</th>
              <td><input type="date" name="birth_date" value="<?= inputValue('birth_date') ?>"></td>
          </tr>
          <tr>
            <th>連絡先</th>
              <td>
                <input type="text" name="tel_number" placeholder="電話番号" value="<?= inputValue('tel_number') ?>">
                <p class="form-note">※ ハイフン（-）なしの10〜11桁の半角数字で入力してください。</p>
              </td>
          </tr>
          <tr>
            <th>E-mail</th>
             <td><input type="email" name="email" placeholder="例: test@example.com" value="<?= inputValue('email') ?>"></td>
          </tr>
          <tr>
            <th>保護者氏名</th>
            <td colspan="3">
              <div class="name-fields">
                <input type="text" name="parent_last_name" placeholder="姓" value="<?= inputValue('parent_last_name') ?>">
                <input type="text" name="parent_first_name" placeholder="名" value="<?= inputValue('parent_first_name') ?>">
              </div>
            </td>
          </tr>
          <tr>
            <th>保護者連絡先</th>
            <td>
               <input type="text" name="parent_tel_number" placeholder="電話番号" value="<?= inputValue('parent_tel_number') ?>">
            </td>
          </tr>
          </table>
        <!-- 写真 -->
          <div class="photo-section">
            <div class="photo-placeholder">
              <?php if (!empty($old['photo_path'])) {
                        $photo = h($old['photo_path']);
                    } elseif (!empty($student['image'])) {
                        $photo = '/' . ltrim(h($student['image']), '/');
                    } else {
                        $photo = 'image/noimage.png';
                  }
              ?>
              <img id="preview" src="<?= $photo ?>" alt="student photo">
            </div>
            <label for="photo-upload" class="btn image-btn">写真を選択</label>
            <input type="file" id="photo-upload" name="photo" accept="image/*" style="display: none;">
          </div>
        </div>
      <!-- メモ -->
        <div class="memo-section data-box">
          <h3>メモ</h3>
          <textarea name="memo" id="memo" class="memo"><?= h($old['memo'] ?? '') ?></textarea>
        </div>
      <!-- ボタン -->
        <div class="button-area">
          <button type="submit" class="btn signup-btn">新規登録</button>
        </div>
      </form>
    </main>
  </div>
  <script src="main.js"></script>
</body>
</html>
