<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

//likeをした、されたユーザーの情報の取得
try {
  $pdo = dbConnect();

  if($_GET['status'] == 'like'){
    $stmt = $pdo->prepare('SELECT users.id, users.name FROM users LEFT JOIN reactions ON users.id = reactions.to_user_id
                          WHERE reactions.from_user_id = :user_id');
  }elseif($_GET['status'] == 'liked'){
    $stmt = $pdo->prepare('SELECT users.id, users.name FROM users LEFT JOIN reactions ON users.id = reactions.from_user_id
                          WHERE reactions.to_user_id = :user_id');
  }

  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

  exit($e->getMessage());

}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Like</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="likeUser_wrapper container">
      <a href="?status=like"><button type="button" class="btn">相手へのLike</button></a>
      <a href="?status=liked"><button type="button" class="btn">相手からのLike</button></a>
      <div class="likeUserData">
        <?php foreach($result as $likeUser): ?>
          <div class="likeUser">
            <a href="profile.php?id=<?= $likeUser['id'] ?>">
              <img src=<?= showImg(sanitize($likeUser['image'])); ?>>
              <h2><?= sanitize($likeUser['name']); ?></h2>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </body>
</html>