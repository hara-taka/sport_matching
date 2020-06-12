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
function empty_check($str, $key){
  if($str === ''){
    global $error;
    $error[$key] = '入力必須です';
  }
}

//最小文字数チェック(パスワード)
function MinLen_check($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $error;
    $error[$key] = '6文字以上でご入力してください';
  }
}

//最大文字数チェック(ユーザー名)
function userNameMaxLen_check($str, $key, $max = 20){
  if(mb_strlen($str) > $max){
    global $error;
    $error[$key] = '20文字以内でご入力してください';
  }
}

//最大文字数チェック(email,パスワード)
function maxLen_check($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $error;
    $error[$key] = '255文字以内でご入力してください';
  }
}

//email形式チェック
function emailFormat_check($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $error;
    $error[$key] = 'Emailの形式で入力してください';
  }
}

//email重複チェック
function emailDouble_check(){
  global $error;
  try {
    $pdo = dbConnect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($result)){
      $error['email'] = 'このメールアドレスは既に登録されています';
    }
  } catch (Exception $e) {
    exit($e->getMessage());
  }
}

//パスワード同一チェック
function passSame_check($str1, $str2, $key){
  if($str1 !== $str2){
    global $error;
    $error[$key] = '「パスワード」「パスワード(確認用)が不一致です」';
  }
}
