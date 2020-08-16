<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if($_GET){
  $chat_room_id = $_GET['room_id'];
  $to_user_id = $_GET['chat_user'];
}

//マッチングしたユーザーとのチャットメッセージ一覧取得
try {
  $pdo = dbConnect();

  $stmt = $pdo->prepare('SELECT users.name,users.image,chat_message.message
                        FROM users
                        JOIN chat_message ON users.id = chat_message.user_id
                        WHERE (chat_message.user_id = :user_id OR chat_message.user_id = :to_user_id) AND chat_message.chat_room_id = :chat_room_id
                        ORDER BY chat_message.created_at DESC');
  $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->bindValue(':to_user_id', $to_user_id, PDO::PARAM_INT);
  $stmt->bindValue(':chat_room_id', $chat_room_id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

  exit($e->getMessage());

}

//チャット送信処理
if($_POST){

  try {
    $pdo = dbConnect();

    $stmt = $pdo->prepare('INSERT INTO chat_message (user_id,chat_room_id,message,created_at,updated_at)
                          VALUES(:user_id,:chat_room_id,:message,:created_at,:updated_at)');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':chat_room_id', $chat_room_id, PDO::PARAM_INT);
    $stmt->bindValue(':message', $_POST['message'], PDO::PARAM_STR);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->execute();

    header("Location: chat.php?room_id={$chat_room_id}&chat_user={$to_user_id}");

  } catch (PDOException $e) {

    exit($e->getMessage());
  }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>chat</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="chat_wrapper container">
      <div class="scroll">
        <?php foreach($result as $chat): ?>
          <div class="chatData_wrapper">
            <div class="userData">
              <img src=<?= showImg(sanitize($chat['image'])); ?>>
              <h2 class="userName"><?= sanitize($chat['name']); ?></h2>
            </div>
            <div class="chatMessage">
              <h2><?= sanitize($chat['message']); ?></h2>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <form action="" method="post">
        <textarea name="message" rows="5" cols="70"></textarea>
        <input type="submit" class="btn" value="送信">
      </form>
    </div>
  </body>
</html>