<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];


//マッチングしたユーザーの一覧の取得
try {
  $pdo = dbConnect();

  $stmt = $pdo->prepare('SELECT users.name,users.image,chat_room_user.chat_room_id,chat_room_user.to_user_id
                        FROM users
                        JOIN chat_room_user ON users.id = chat_room_user.to_user_id
                        WHERE chat_room_user.from_user_id = :user_id');
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
    <title>chat</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="chatIndexUser_wrapper container">
      <?php foreach($result as $chatUser): ?>
        <div class="chatIndexUser">
          <a href="chat.php?room_id=<?= $chatUser['chat_room_id'] ?>&chat_user=<?= $chatUser['to_user_id'] ?>">
            <img src=<?= showImg(sanitize($chatUser['image'])); ?>>
            <h2><?= sanitize($chatUser['name']); ?></h2>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </body>
</html>