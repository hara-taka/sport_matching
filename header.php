<header>
  <div class="header">
    <h1><a href="index.php">SportMatching</a></h1>
    <nav class="top_nav">
      <ul>
        <?php if(empty($_SESSION['user_id'])): ?>

          <li><a href="user_register.php">ユーザー登録</a></li>
          <li><a href="login.php">ログイン</a></li>

        <?php else: ?>

          <li><a href="index.php">一覧</a></li>
          <li><a href="like.php?status=like">Like</a></li>
          <li><a href="chatIndex.php">チャット</a></li>
          <li><a href="logout.php">ログアウト</a></li>

        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>