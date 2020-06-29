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
    if(!empty($result)){
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

//サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//画像表示
function showImg($path){
  if($path){
    return 'img/'.$path;
  }else{
    return 'img/defaultImage.png';
  }
}