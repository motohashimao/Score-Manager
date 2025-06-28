<?php
session_start();

require_once(__DIR__ . '/../app/config.php');
require_once(__DIR__ . '/../app/database.php');
require_once(__DIR__ . '/../app/utils.php');

use MyApp\Database;
use MyApp\Utils;
use MyApp\User;

$pdo = Database::getInstance();
$error = '';
$email = '';
$pass = '';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $email = trim($_POST['email']);
//     $pass  = $_POST['password'];
// バリデーション
//     $error = validateRequired($email, 'メールアドレス');
//     if (!$error) $error = validateRequired($pass, 'パスワード');
//     if (!$error) $error = validateEmail($email);
//     if (!$error) {
//         $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
//         $stmt->execute([$email]);
//         $user = $stmt->fetch(PDO::FETCH_ASSOC);

//         if ($user && password_verify($pass, $user['password'])) {
//             $_SESSION['user_id'] = $user['id'];
//             $_SESSION['user_name'] = $user['name'];
//             $_SESSION['is_admin'] = $user['is_admin'] ?? false;

//             header('Location: ./index.php');
//             exit;
//         } else {
//             $error = 'メールアドレスまたはパスワードが間違っています。';
//         }
//     }
// }

// $error = validateRequired($email, 'メールアドレス');
// if (!$error) $error = validateRequired($pass, 'パスワード');
// if (!$error) $error = validateLoginEmail($email);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // バリデーション
    $error = validateRequired($email, 'メールアドレス');
    if (!$error) $error = validateRequired($pass, 'パスワード');
    if (!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレスまたはパスワードが間違っています。';
    }

    if (!$error) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = $user['is_admin'] ?? false;

            header('Location: ./index.php');
            exit;
        } else {
            $error = 'メールアドレスまたはパスワードが間違っています。';
        }
    }
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login_container">
        <header class="login_header">成績管理システム<br>(Score Manager)</header>
        <div class="login">
        <h2>ログイン</h2>
        <?php if ($error): ?>
            <p class="notice error"><?= h($error) ?></p>
        <?php endif; ?>
        <form action="" method="POST" class="login-form">
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="メールアドレス" value="<?= h($email) ?>">
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="パスワード">
            </div>
            <button type="submit" class="btn">ログイン</button>
        </form>
    </div>
</body>
</html>