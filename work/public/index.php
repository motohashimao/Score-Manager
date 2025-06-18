<!-- ブラウザ表示 http://localhost/-->
<!-- phpmyadmin http://localhost:8080-->

<?php

session_start();

require_once(__DIR__ . '/../app/utils.php');
require_once(__DIR__ . '/../app/database.php');

$pdo = \MyApp\Database::getInstance();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// 検索条件取得
$class = isset($_GET['class']) ? $_GET['class'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';


// ページング設定
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

$page = max((int)($_GET['page'] ?? 1), 1);
$perPage = 30;
$offset = ($page - 1) * $perPage;

// 生徒総数・ページ数
$totalStudents = getStudentCount($pdo, $class, $name);
$totalPages = ceil($totalStudents / $perPage);


// 生徒一覧取得
$students = getStudents($pdo, $class, $name, $perPage, $offset);

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
          <h2>生徒一覧</h2>
          <!--検索項目-->
          <header class="search">
            <form action="" method="get">
              <label for="class" class="label-class">クラス名</label>
                <select name="class" id="class" class="select-class">
                  <option></option>
                  <option <?= $class == 'A' ? 'selected' : '' ?>>A</option>
                  <option <?= $class == 'B' ? 'selected' : '' ?>>B</option>
                  <option <?= $class == 'C' ? 'selected' : '' ?>>C</option>
                  <option <?= $class == 'D' ? 'selected' : '' ?>>D</option>
                  <option <?= $class == 'E' ? 'selected' : '' ?>>E</option>
                </select>
              <label for="name">氏名[かな]</label>
              <input type="text" name="name" placeholder="例: やまだ" value="<?= h($name) ?>">
              <button type="submit" class="btn">検索</button>
              <button type="button" class="btn signup-btn" onclick="location.href='signup.php'">新規登録</button>
            </form>
          </header>

        <!--table-->
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>クラス名</th>
                  <th>クラス番号</th>
                  <th>氏名</th>
                  <th>氏名(かな)</th>
                  <th>性別</th>
                  <th>生年月日</th>
                </tr>
              </thead>
            <?php foreach ($students as $student): ?>
            <tr class="clickable-row" data-id="<?= h($student['id']) ?>">
              <td><?= h($student['class']) ?></td>
              <td><?= h($student['class_no']) ?></td>
              <td><?= h($student['last_name'] . ' ' . $student['first_name']) ?></td>
              <td><?= h($student['last_name_kana'] . ' ' . $student['first_name_kana']) ?></td>
              <td><?= h(genderToText($student['gender'])) ?></td>
              <td><?= h($student['birth_date']) ?></td>
            </tr>
            <?php endforeach; ?>
            </table>
          </div>
        <!--ページネーション-->
          <ul class="paging">
            <?php if ($page > 1): ?>
              <li><a href="?page=<?= $page - 1 ?>">前へ</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li><a href="?page=<?= $i ?>" <?= $i === $page ? 'style="font-weight:bold;"' : '' ?>>
                  <?= $i ?>
                </a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
              <li><a href="?page=<?= $page + 1 ?>">次へ</a></li>
            <?php endif; ?>
          </ul>
        </main>
      </div>
    <script src="main.js"></script>
</body>
</html>