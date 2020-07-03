<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

try {
  // データベースに接続
  $pdo = dbConnect();

  $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
  $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

  exit($e->getMessage());

}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>プロフィール</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
    <div class="profile_wrapper">
      <a href="profileEdit.php">プロフィール編集</a>
      <?= showImg(sanitize($profile['image'])); ?>
      <h2>ユーザー名</h2>
      <?= sanitize($profile['name']); ?>
      <h2>性別</h2>
      <?= sanitize(showProfileGender($profile)); ?>
      <h2>年齢</h2>
      <?= sanitize(showProfileAge($profile)); ?>
      <h2>スポーツジャンル</h2>
      <?= sanitize(showProfileSportCategory1($profile)); ?>
      <?= sanitize(showProfileSportCategory2($profile)); ?>
      <?= sanitize(showProfileSportCategory3($profile)); ?>
      <h2>自己紹介</h2>
      <?= sanitize($profile['comment']); ?>
    </div>
  </body>
</html>