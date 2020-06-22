<?php

require('function.php');

session_start();

$error = [];

if($_POST){

  $email = $_POST['email'];
  $pass = $_POST['pass'];

  //未入力チェック
  $error = emptyCheck($error, $email, 'email');
  $error = emptyCheck($error, $pass, 'pass');

  if(count($error) === 0){

    //最小、最大文字数チェック
    $error = MinLenCheck($error, $pass, 'pass');
    $error = maxLenCheck($error, $email, 'email');
    $error = maxLenCheck($error, $pass, 'pass');

    //email形式チェック
    $error = emailFormatCheck($error, $email, 'email');

    if(count($error) === 0){

      try {
        // データベースに接続
        $pdo = dbConnect();

        $stmt = $pdo->prepare('SELECT password,id  FROM users WHERE email = :email');
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // パスワード照合
        if($result && password_verify($pass, $result['password'])){

          //ユーザーIDを格納
          //$_SESSION['user_id'] = $result['id'];

          header("Location:login.php");

        }else{

          $error['login'] = 'メールアドレスまたはパスワードが正しくありません';

        }

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
      <title>ログイン</title>
      <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="login_wrapper">
      <h1>Login</h1>
      <form action="" method="post">
        <h2>メールアドレス</h2>
        <div class="error">
          <?php
            if(!empty($error['email'])) echo $error['email'];
          ?>
          <?php
            if(!empty($error['login'])) echo $error['login'];
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
        <input type="submit" class="btn" value="ログイン">
      </form>
    </div>
  </body>
</html>