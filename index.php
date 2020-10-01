<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

try {
  $pdo = dbConnect();

  $stmt = $pdo->prepare('SELECT * FROM users WHERE id != :id');
  $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

  exit($e->getMessage());

}

if($_GET){
  try {
    $pdo = dbConnect();

    $category = $_GET['category'];
    $gender = $_GET['gender'];
    $age = $_GET['age'];
    $keyword = $_GET['keyword'];

    if($_GET['keyword'] !== ''){

      $stmt = $pdo->prepare('SELECT * FROM users WHERE (gender = :gender OR age = :age OR sport_category1 = :sport_category1 OR
              sport_category2 = :sport_category2 OR sport_category3 = :sport_category3 OR comment LIKE :comment) AND id != :id');
      $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':gender', $gender, PDO::PARAM_INT);
      $stmt->bindValue(':age', $age, PDO::PARAM_INT);
      $stmt->bindValue(':sport_category1', $category, PDO::PARAM_STR);
      $stmt->bindValue(':sport_category2', $category, PDO::PARAM_STR);
      $stmt->bindValue(':sport_category3', $category, PDO::PARAM_STR);
      $stmt->bindValue(':comment', '%'.$keyword.'%', PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }elseif($_GET['keyword'] == ''){

      $stmt = $pdo->prepare('SELECT * FROM users WHERE (gender = :gender OR age = :age OR sport_category1 = :sport_category1 OR
              sport_category2 = :sport_category2 OR sport_category3 = :sport_category3) AND id != :id');
      $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':gender', $gender, PDO::PARAM_INT);
      $stmt->bindValue(':age', $age, PDO::PARAM_INT);
      $stmt->bindValue(':sport_category1', $category, PDO::PARAM_STR);
      $stmt->bindValue(':sport_category2', $category, PDO::PARAM_STR);
      $stmt->bindValue(':sport_category3', $category, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>一覧</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="indexUser_wrapper container">
      <div class="searchUser">
        <form action="" method="get" class="searchUserForm">
          <h1 class="search">検索</h1>
          <h2 class="category">カテゴリー</h2>
          <select name="category">
            <option value="category"></option>
            <option value="baseball">野球</option>
            <option value="soccer">サッカー</option>
            <option value="volleyball">バレーボール</option>
          </select>
          <h2 class="gender">性別</h2>
          <select name="gender">
            <option value="gender"></option>
            <option value="1">男性</option>
            <option value="2">女性</option>
          </select>
          <h2 class="age">年齢</h2>
          <select name="age">
            <option value="age"></option>
            <option value="10">10代</option>
            <option value="20">20代</option>
            <option value="30">30代</option>
            <option value="40">40代</option>
            <option value="50">50代</option>
            <option value="60">60代</option>
            <option value="70">70代</option>
            <option value="80">80代</option>
            <option value="90">90代</option>
          </select>
          <h2 class="sort">フリーワード</h2>
          <input type="text" name="keyword"></br>
          <input type="submit" value="この条件で検索" class="btn">
        </form>
      </div>
      <div class="indexUserData">
        <?php foreach($result as $userData): ?>
          <div class="indexUser">
            <a href="profile.php?id=<?= $userData['id'] ?>">
              <img src=<?= showImg(sanitize($userData['image'])); ?>>
              <?= sanitize($userData['name']); ?>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </body>
</html>