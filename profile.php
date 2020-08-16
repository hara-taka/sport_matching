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

//Likeボタンクリック時の処理
if($_POST){

  try {
    $pdo = dbConnect();

    //Likeボタンを押した相手からすでにLikeされているかの確認
    $stmt1 = $pdo->prepare('SELECT * FROM reactions WHERE from_user_id = :from_user_id AND to_user_id = :to_user_id');
    $stmt1->bindValue(':from_user_id', $to_user_id, PDO::PARAM_INT);
    $stmt1->bindValue(':to_user_id', $user_id, PDO::PARAM_INT);
    $stmt1->execute();
    $result = $stmt1->fetch(PDO::FETCH_ASSOC);

    //Likeボタンを押した相手からすでにLikeされていたときの処理
    if($result){
      $stmt2 = $pdo->prepare('INSERT INTO reactions (from_user_id,to_user_id,status,created_at,updated_at)
                          VALUES(:from_user_id,:to_user_id,:status,:created_at,:updated_at)');
      $stmt2->bindValue(':from_user_id', $user_id, PDO::PARAM_INT);
      $stmt2->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
      $stmt2->bindValue(':status', 1, PDO::PARAM_INT);
      $stmt2->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt2->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt2->execute();

      $stmt3 = $pdo->prepare('UPDATE reactions SET status = :status WHERE from_user_id = :from_user_id AND to_user_id = :to_user_id');
      $stmt3->bindValue(':status', 1, PDO::PARAM_INT);
      $stmt3->bindValue(':from_user_id', $to_user_id, PDO::PARAM_INT);
      $stmt3->bindValue(':to_user_id', $user_id, PDO::PARAM_INT);
      $stmt3->execute();

      //chat_roomテーブルへのデータの挿入
      $stmt4 = $pdo->prepare('INSERT INTO chat_room (created_at,updated_at)VALUES(:created_at,:updated_at)');
      $stmt4->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt4->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt4->execute();

      //chat_roomテーブルへ挿入したデータのidの取得
      $chat_room_id = $pdo->lastInsertId();

      //chat_room_userテーブルへのデータの挿入
      $stmt5 = $pdo->prepare('INSERT INTO chat_room_user (chat_room_id, from_user_id, to_user_id)
                          VALUES(:chat_room_id, :from_user_id, :to_user_id)');
      $stmt5->bindValue(':chat_room_id', $chat_room_id, PDO::PARAM_INT);
      $stmt5->bindValue(':from_user_id', $user_id, PDO::PARAM_INT);
      $stmt5->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
      $stmt5->execute();

      $stmt6 = $pdo->prepare('INSERT INTO chat_room_user (chat_room_id, from_user_id, to_user_id)
                          VALUES(:chat_room_id, :from_user_id, :to_user_id)');
      $stmt6->bindValue(':chat_room_id', $chat_room_id, PDO::PARAM_INT);
      $stmt6->bindValue(':from_user_id', $to_user_id, PDO::PARAM_INT);
      $stmt6->bindValue(':to_user_id', $user_id, PDO::PARAM_INT);
      $stmt6->execute();

    }elseif(!$result){

      //Likeボタンを押した相手からLikeされていないときの処理
      $stmt7 = $pdo->prepare('INSERT INTO reactions (from_user_id,to_user_id,status,created_at,updated_at)
                              VALUES(:from_user_id,:to_user_id,:status,:created_at,:updated_at)');
      $stmt7->bindValue(':from_user_id', $user_id, PDO::PARAM_INT);
      $stmt7->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
      $stmt7->bindValue(':status', 0, PDO::PARAM_INT);
      $stmt7->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt7->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
      $stmt7->execute();
    }

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
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="profile_wrapper container">
      <img src=<?= showImg(sanitize($profile['image'])); ?>>
      <a href="profileEdit.php">プロフィール編集</a>
      <table>
        <tr>
          <td class="userColumn">ユーザー名</td>
          <td><?= sanitize($profile['name']); ?></td>
        </tr>
        <tr>
          <td class="userColumn">性別</td>
          <td><?= sanitize(showProfileGender($profile)); ?></td>
        </tr>
        <tr>
          <td class="userColumn">年齢</td>
          <td><?= sanitize(showProfileAge($profile)); ?></td>
        </tr>
        <tr>
          <td class="userColumn">スポーツジャンル</td>
          <td>
            <?= sanitize(showProfileSportCategory($profile['sport_category1'])); ?>
            <?= sanitize(showProfileSportCategory($profile['sport_category2'])); ?>
            <?= sanitize(showProfileSportCategory($profile['sport_category3'])); ?>
          </td>
        </tr>
        <tr>
          <td class="userColumn">自己紹介</td>
          <td><?= sanitize($profile['comment']); ?></td>
        </tr>
      </table>
      <?php if($profile['id'] !== $user_id): ?>
        <form action="" method="post">
          <button type="submit" name="like" class="btn">Like</button>
        </form>
      <?php endif; ?>
    </div>
  </body>
</html>