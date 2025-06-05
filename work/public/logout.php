<?php
// ログアウト処理

session_start();
$_SESSION = array();//セッションの中身をすべて削除

// redirect関数を自分で定義
function redirect($url) {
  header("Location: $url");
  exit;
}

session_destroy();//セッションを破壊
redirect('login.php');//ログイン画面へリダイレクトしたい



?>