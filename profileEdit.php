<?php

require('function.php');

session_start();

if(empty($_SESSION['user_id'])){
  header("Location:login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

$error = [];

//プロフィール情報取得
$profile = getProfile($user_id);

if($_POST){

  $name = $_POST['name'];
  $gender = $_POST['gender'];
  $age = $_POST['age'];
  $category1 = $_POST['category1'];
  $category2 = $_POST['category2'];
  $category3 = $_POST['category3'];
  $comment = $_POST['comment'];
  $email = $_POST['email'];

  //画像アップロード処理
  if($_FILES['image']['name']){
    try {
      $path = $_FILES['image']['name'];
      $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

      if($_FILES['image']['size'] > 2097152){
          throw new RuntimeException('ファイルサイズが大きすぎます');
      }

      if($file_ext !== 'gif' && $file_ext !== 'png' && $file_ext !== 'jpg'){
        throw new RuntimeException('画像形式が未対応です');
      }

      $path = 'img/'.sha1_file($_FILES['image']['tmp_name']).'.'.$file_ext;
      if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }

      chmod($path, 0644);

    } catch (RuntimeException $e) {

      $error['image'] = $e->getMessage();

    }

  }elseif($_FILES['image']['name'] == false){

    $result = getProfile($user_id);
    $path = $result['image'];

  }

  //未入力チェック
  $error = emptyCheck($error, $name, 'name');
  $error = emptyCheck($error, $email, 'email');

  if(count($error) === 0){

    //最小、最大文字数チェック
    $error = userNameMaxLenCheck($error, $name, 'name');
    $error = maxLenCheck($error, $email, 'email');
    $error = commentMaxLenCheck($error, $comment, 'comment');

    //email形式チェック
    $error = emailFormatCheck($error, $email, 'email');

    //email重複チェック
    $error = profileEditEmailDoubleCheck($error, $email, $user_id);

    //スポーツジャンル重複チェック
    $error = sportCategoryDoubleCheck($error, $category1, $category2, $category3, 'category');

    if(count($error) === 0){

      try {
        // データベース接続
        $pdo = dbConnect();

        $stmt = $pdo->prepare('UPDATE users SET name = :name, age = :age, gender = :gender, image = :image, sport_category1 = :sport_category1,
                sport_category2 = :sport_category2, sport_category3 = :sport_category3, comment = :comment, email = :email, updated_at = :updated_at WHERE id = :id');
        $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':age', $age, PDO::PARAM_STR);
        $stmt->bindValue(':gender', $gender, PDO::PARAM_INT);
        $stmt->bindValue(':image', $path, PDO::PARAM_STR);
        $stmt->bindValue(':sport_category1', $category1, PDO::PARAM_STR);
        $stmt->bindValue(':sport_category2', $category2, PDO::PARAM_STR);
        $stmt->bindValue(':sport_category3', $category3, PDO::PARAM_STR);
        $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->execute();

        header("Location:profile.php?id={$user_id}");

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
    <title>プロフィール編集</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <?php
      require('header.php');
    ?>
  </head>
  <body>
    <div class="profileEdit_wrapper container">
      <form action="" method="post" enctype="multipart/form-data">
        <table>
          <div class="error">
            <?php
              if(!empty($error['image'])) echo $error['image'];
            ?>
            <tr>
              <td class="userColumn">プロフィール画像</td>
              <td><input type="file" name="image"></td>
            </tr>

            <tr>
              <td class="userColumn">ユーザー名</td>
              <td><input type="text" name="name" value="<?= sanitize($profile['name']); ?>">
                <div class="error">
                  <?php
                    if(!empty($error['name'])) echo $error['name'];
                  ?>
                </div>
              </td>
            </tr>

            <tr>
              <td class="userColumn">性別</td>
              <td>
                <select name="gender">
                  <option value=""></option>
                  <option value="1" <?= $profile['gender'] == '1' ? 'selected' : '' ?>>男性</option>
                  <option value="2" <?= $profile['gender'] == '2' ? 'selected' : '' ?>>女性</option>
                </select>
              </td>
            </tr>

            <tr>
              <td class="userColumn">年齢</td>
              <td>
                <select name="age">
                  <option value="10" <?= $profile['age'] == '10' ? 'selected' : '' ?>>10代</option>
                  <option value="20" <?= $profile['age'] == '20' ? 'selected' : '' ?>>20代</option>
                  <option value="30" <?= $profile['age'] == '30' ? 'selected' : '' ?>>30代</option>
                  <option value="40" <?= $profile['age'] == '40' ? 'selected' : '' ?>>40代</option>
                  <option value="50" <?= $profile['age'] == '50' ? 'selected' : '' ?>>50代</option>
                  <option value="60" <?= $profile['age'] == '60' ? 'selected' : '' ?>>60代</option>
                  <option value="70" <?= $profile['age'] == '70' ? 'selected' : '' ?>>70代</option>
                  <option value="80" <?= $profile['age'] == '80' ? 'selected' : '' ?>>80代</option>
                  <option value="90" <?= $profile['age'] == '90' ? 'selected' : '' ?>>90代</option>
                </select>
              </td>
            </tr>

            <tr>
              <td class="userColumn">スポーツジャンル</td>
              <td>
                <select name="category1">
                  <option value=""></option>
                  <option value="baseball" <?= $profile['sport_category1'] == 'baseball' ? 'selected' : '' ?>>野球</option>
                  <option value="soccer" <?= $profile['sport_category1'] == 'soccer' ? 'selected' : '' ?>>サッカー</option>
                  <option value="volleyball" <?= $profile['sport_category1'] == 'volleyball' ? 'selected' : '' ?>>バレーボール</option>
                  <option value="tennis" <?= $profile['sport_category1'] == 'tennis' ? 'selected' : '' ?>>テニス</option>
                  <option value="basketball" <?= $profile['sport_category1'] == 'basketball' ? 'selected' : '' ?>>バスケットボール</option>
                  <option value="badminton" <?= $profile['sport_category1'] == 'badminton' ? 'selected' : '' ?>>バドミントン</option>
                  <option value="tabletennis" <?= $profile['sport_category1'] == 'tabletennis' ? 'selected' : '' ?>>卓球</option>
                  <option value="handball" <?= $profile['sport_category1'] == 'handball' ? 'selected' : '' ?>>ハンドボール</option>
                  <option value="swimming" <?= $profile['sport_category1'] == 'swimming' ? 'selected' : '' ?>>水泳</option>
                  <option value="running" <?= $profile['sport_category1'] == 'runninng' ? 'selected' : '' ?>>ランニング</option>
                  <option value="combatsport" <?= $profile['sport_category1'] == 'combatsport' ? 'selected' : '' ?>>格闘技</option>
                  <option value="other" <?= $profile['sport_category1'] == 'other' ? 'selected' : '' ?>>その他</option>
                </select>
                <select name="category2">
                  <option value=""></option>
                  <option value="baseball" <?= $profile['sport_category2'] == 'baseball' ? 'selected' : '' ?>>野球</option>
                  <option value="soccer" <?= $profile['sport_category2'] == 'soccer' ? 'selected' : '' ?>>サッカー</option>
                  <option value="volleyball" <?= $profile['sport_category2'] == 'valleyball' ? 'selected' : '' ?>>バレーボール</option>
                  <option value="tennis" <?= $profile['sport_category2'] == 'tennis' ? 'selected' : '' ?>>テニス</option>
                  <option value="basketball" <?= $profile['sport_category2'] == 'basketball' ? 'selected' : '' ?>>バスケットボール</option>
                  <option value="badminton" <?= $profile['sport_category2'] == 'badminton' ? 'selected' : '' ?>>バドミントン</option>
                  <option value="tabletennis" <?= $profile['sport_category2'] == 'tabletennis' ? 'selected' : '' ?>>卓球</option>
                  <option value="handball" <?= $profile['sport_category2'] == 'handball' ? 'selected' : '' ?>>ハンドボール</option>
                  <option value="swimming" <?= $profile['sport_category2'] == 'swimming' ? 'selected' : '' ?>>水泳</option>
                  <option value="running" <?= $profile['sport_category2'] == 'runninng' ? 'selected' : '' ?>>ランニング</option>
                  <option value="combatsport" <?= $profile['sport_category2'] == 'combatsport' ? 'selected' : '' ?>>格闘技</option>
                  <option value="other" <?= $profile['sport_category2'] == 'other' ? 'selected' : '' ?>>その他</option>
                </select>
                <select name="category3">
                  <option value=""></option>
                  <option value="baseball" <?= $profile['sport_category3'] == 'baseball' ? 'selected' : '' ?>>野球</option>
                  <option value="soccer" <?= $profile['sport_category3'] == 'soccer' ? 'selected' : '' ?>>サッカー</option>
                  <option value="volleyball" <?= $profile['sport_category3'] == 'volleyball' ? 'selected' : '' ?>>バレーボール</option>
                  <option value="tennis" <?= $profile['sport_category3'] == 'tennis' ? 'selected' : '' ?>>テニス</option>
                  <option value="basketball" <?= $profile['sport_category3'] == 'basketball' ? 'selected' : '' ?>>バスケットボール</option>
                  <option value="badminton" <?= $profile['sport_category3'] == 'badminton' ? 'selected' : '' ?>>バドミントン</option>
                  <option value="tabletennis" <?= $profile['sport_category3'] == 'tabletennis' ? 'selected' : '' ?>>卓球</option>
                  <option value="handball" <?= $profile['sport_category3'] == 'handball' ? 'selected' : '' ?>>ハンドボール</option>
                  <option value="swimming" <?= $profile['sport_category3'] == 'swimming' ? 'selected' : '' ?>>水泳</option>
                  <option value="running" <?= $profile['sport_category3'] == 'runninng' ? 'selected' : '' ?>>ランニング</option>
                  <option value="combatsport" <?= $profile['sport_category3'] == 'combatsport' ? 'selected' : '' ?>>格闘技</option>
                  <option value="other" <?= $profile['sport_category3'] == 'other' ? 'selected' : '' ?>>その他</option>
                </select>
                <div class="error">
                  <?php
                    if(!empty($error['category'])) echo $error['category'];
                  ?>
                </div>
              </td>
            </tr>

            <tr>
              <td class="userColumn">自己紹介</td>
              <td>
                <textarea name="comment" rows="10" cols="50"><?= sanitize($profile['comment']); ?></textarea>
                <div class="error">
                  <?php
                    if(!empty($error['comment'])) echo $error['comment'];
                  ?>
                </div>
              </td>
            </tr>

            <tr>
              <td class="userColumn">メールアドレス</td>
              <td>
                <input type="email" name="email" value="<?= sanitize($profile['email']); ?>">
                <div class="error">
                  <?php
                    if(!empty($error['email'])) echo $error['email'];
                  ?>
                </div>
              </td>
            </tr>

          </div>
        </table>

        <input type="submit" class="btn" value="修正">
      </form>
    </div>
  </body>
</html>