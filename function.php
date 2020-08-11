<?php
//DB接続
function dbConnect(){
  $pdo = new PDO(
    'mysql:dbname=sport;host=localhost;charset=utf8',
    'root',
    'root',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
  return $pdo;
}

//未入力チェック
function emptyCheck($error, $str, $key){
  if($str === ''){
    $error[$key] = '入力必須です';
    return $error;
  }
  return $error;
}

//最小文字数チェック(パスワード)
function MinLenCheck($error, $str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    $error[$key] = '6文字以上でご入力してください';
    return $error;
  }
  return $error;
}

//最大文字数チェック(ユーザー名)
function userNameMaxLenCheck($error, $str, $key, $max = 20){
  if(mb_strlen($str) > $max){
    $error[$key] = '20文字以内でご入力してください';
    return  $error;
  }
  return $error;
}

//最大文字数チェック(email,パスワード)
function maxLenCheck($error, $str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    $error[$key] = '255文字以内でご入力してください';
    return $error;
  }
  return $error;
}

//最大文字数チェック(自己紹介)
function commentMaxLenCheck($error, $str, $key, $max = 200){
  if(mb_strlen($str) > $max){
    $error[$key] = '200文字以内でご入力してください';
  }
  return $error;
}

//email形式チェック
function emailFormatCheck($error, $str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    $error[$key] = 'Emailの形式で入力してください';
  }
  return $error;
}

//email重複チェック
function emailDoubleCheck($error, $email){
  try {
    $pdo = dbConnect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
      $error['email'] = 'このメールアドレスは既に登録されています';
    }
    return $error;

  } catch (Exception $e) {

    exit($e->getMessage());

  }
}

//email重複チェック(プロフィール編集用)
function profileEditEmailDoubleCheck($error, $email, $user_id){
  try {
    $pdo = dbConnect();

    $stmt1 = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt1->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare('SELECT email FROM users WHERE id = :id');
    $stmt2->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt2->execute();
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

    if($result1 && $email !== $result2['email']){
      $error['email'] = 'このメールアドレスは既に登録されています';
    }
    return $error;

  } catch (Exception $e) {

    exit($e->getMessage());

  }
}

//パスワード同一チェック
function passSameCheck($error, $str1, $str2, $key){
  if($str1 !== $str2){
    $error[$key] = '「パスワード」「パスワード(確認用)」が不一致です';
  }
  return $error;
}

//スポーツジャンル重複チェック
function sportCategoryDoubleCheck($error, $category1, $category2, $category3,$key){

  $sport_category_array = [$category1, $category2, $category3];
  $category_array = array_filter($sport_category_array, "strlen");
  $value_count = array_count_values($category_array);
  $max = max($value_count);

  if ($max == 2 || $max == 3) {
    $error[$key] = '1異なるスポーツジャンルを選択してください';
  }

  return $error;
}

//サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//画像表示
function showImg($path){
  if($path){
    return $path;
  }else{
    return 'img/defaultImage.png';
  }
}

//プロフィール情報取得
function getProfile($user_id){
  try {
    // データベースに接続
    $pdo = dbConnect();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    return $profile;

  } catch (PDOException $e) {

    exit($e->getMessage());

  }
}

//プロフィール表示(スポーツジャンル)
function showProfileSportCategory($profile){
  switch($profile){
    case 'baseball':
      $sport_category = '野球';
      return $sport_category;
      break;
    case 'soccer':
      $sport_category = 'サッカー';
      return $sport_category;
      break;
    case 'volleyball':
      $sport_category = 'バレーボール';
      return $sport_category;
      break;
    default;
      $sport_category = '';
      return $sport_category;
  }
}

//プロフィール表示(性別)
function showProfileGender($profile){
  switch($profile['gender']){
    case '1':
      $gender = '男性';
      return $gender;
      break;
    case '2':
      $gender = '女性';
      return $gender;
      break;
    default:
      $gender = '';
      return $gender;
  }
}

//プロフィール表示(年齢)
function showProfileAge($profile){
  switch($profile['age']){
    case '10':
      $age = '10代';
      return $age;
      break;
    case '20':
      $age = '20代';
      return $age;
      break;
    case '30':
      $age = '30代';
      return $age;
      break;
    case '40':
      $age = '40代';
      return $age;
      break;
    case '50':
      $age = '50代';
      return $age;
      break;
    case '60':
      $age = '60代';
      return $age;
      break;
    case '70':
      $age = '70代';
      return $age;
      break;
    case '80':
      $age = '80代';
      return $age;
      break;
    case '90':
      $age = '90代';
      return $age;
      break;
    default:
      $age = '';
      return $age;
  }
}