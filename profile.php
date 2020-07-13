<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if($_GET['id']){
  $to_user_id = $_GET['id'];
}

try {
  // データベースに接続
  $pdo = dbConnect();

  $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
  $stmt->bindValue(':id', $to_user_id, PDO::PARAM_INT);
  $stmt->execute();
  $profile = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

  exit($e->getMessage());

}

//Likeボタンクリック時にreactionsテーブルにデータを挿入する処理
if($_POST){

  try {
    $pdo = dbConnect();

    $stmt = $pdo->prepare('INSERT INTO reactions (from_user_id,to_user_id,status,created_at,updated_at)
                          VALUES(:from_user_id,:to_user_id,:status,:created_at,:updated_at)');
    $stmt->bindValue(':from_user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':status', 1, PDO::PARAM_INT);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();

  } catch (PDOException $e) {

    exit($e->getMessage());

  }

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
      <?= sanitize(showProfileSportCategory($profile['sport_category1'])); ?>
      <?= sanitize(showProfileSportCategory($profile['sport_category2'])); ?>
      <?= sanitize(showProfileSportCategory($profile['sport_category3'])); ?>
      <h2>自己紹介</h2>
      <?= sanitize($profile['comment']); ?>
      <?php if($profile['id'] !== $user_id): ?>
        <form action="" method="post">
          <button type="submit" name="like">Like</button>
        </form>
      <?php endif; ?>
    </div>
  </body>
</html>