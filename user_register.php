<?php

require('function.php');

//エラーメッセージ用配列
$error = [];

if(!empty($_POST)){

  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  $hash_pass = password_hash($pass, PASSWORD_DEFAULT);

  //未入力チェック
  $error = emptyCheck($error, $name, 'name');
  $error = emptyCheck($error, $email, 'email');
  $error = emptyCheck($error, $pass, 'pass');
  $error = emptyCheck($error, $pass_re, 'pass_re');

  if($error['name'] === null && $error['email'] === null &&
    $error['pass'] === null && $error['pass_re'] === null){

    //最小、最大文字数チェック
    $error = MinLenCheck($error, $pass, 'pass');
    $error = userNameMaxLenCheck($error, $name, 'name');
    $error = maxLenCheck($error, $email, 'email');
    $error = maxLenCheck($error, $pass, 'pass');

    //email形式チェック
    $error = emailFormatCheck($error, $email, 'email');

    //email重複チェック
    $error = emailDoubleCheck($error, $email);

    //パスワード同一チェック
    $error = passSameCheck($error, $pass, $pass_re, 'pass_re');

    if($error['name'] === null && $error['email'] === null &&
      $error['pass'] === null && $error['pass_re'] === null){

      try {
        // データベース接続
        $pdo = dbConnect();

        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,created_at,updated_at) VALUES(:name,:email,:pass,:created_at,:updated_at)');
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $hash_pass, PDO::PARAM_STR);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->execute();

        header("Location:user_register.php");

        exit;

      } catch (PDOException $e) {

        exit($e->getMessage());
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ユーザー登録</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div class="userResister_wrapper">
          <h1>Register</h1>
          <form action="" method="post">
            <h2>ユーザー名</h2>
            <div class="error">
              <?php
                if(!empty($error['name'])) echo $error['name'];
              ?>
            </div>
            <input type="text" name="name">

            <h2>メールアドレス</h2>
            <div class="error">
              <?php
                if(!empty($error['email'])) echo $error['email'];
              ?>
            </div>
            <input type="email" name="email">

            <h2>パスワード</h2>
            <div class="error">
              <?php
                if(!empty($error['pass'])) echo $error['pass'];
              ?>
            </div>
            <input type="password" name="pass">

            <h2>パスワード(確認用)</h2>
            <div class="error">
              <?php
                if(!empty($error['pass_re'])) echo $error['pass_re'];
              ?>
            </div>
            <input type="password" name="pass_re">
            <input type="submit" class="btn" value="登録する">
          </form>
        </div>
    </body>
</html>