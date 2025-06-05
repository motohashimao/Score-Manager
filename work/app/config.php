<?php

session_start();//セッション開始

// define('DSN', 'mysql:host=db;dbname=myapp;charset=utf8mb4');//データベース接続
define('DSN', 'mysql:host=db;dbname=scoremanager;charset=utf8mb4');
define('DB_USER', 'smuser');//データベースユーザー名
define('DB_PASS', 'smpass');//データベースパスワード、PDOでデータベースに接続するときに使う
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);


//
spl_autoload_register(function ($class) {// クラスの自動読み込み機能（オートローダー） を定義しています。
  $prefix = 'MyApp\\';//MyApp\ という名前空間から始まるクラス名だけを対象にします。

  if (strpos($class, $prefix) === 0) {
    $fileName = sprintf(__DIR__ . '/%s.php', substr($class, strlen($prefix)));//クラス名からMyApp\を取り除いた部分をファイル名に変換しています。

    if (file_exists($fileName)) {
      require($fileName);
    } else {
      echo 'File not found: ' . $fileName;
      exit;
    }
  }
});
