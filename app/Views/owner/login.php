<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<section id="owner-login-wrapper">
    <div><img src="<?= url("images/logo.png");?>" alt=""></div>
  <div id="manager-title">管理者ログイン</div>
  <form action="<?= url("/owner/login"); ?>" method="post">
    <input type="hidden" name="token" value="<?= h($csrfToken);?>">
    <div class="login-box">
      <h3>Sign Up</h3>
      <ul>
        <div class="input">
          <label for="email">メールアドレス</label>
          <input type="email" name="email" id="email" value="<?= $session->flash('old.email'); ?>"/>
          <?= ($msg = $session->flash('errors.email')) ? "<span class='error-msg'>{$msg}</span>": "";?>
        </div>
        <div class="input">
          <label for="password">パスワード</label>
          <input type="password" name="password" id="password"/>
          <?= ($msg = $session->flash('errors.password')) ? "<span class='error-msg'>{$msg}</span>": "";?>
        </div>
        <button type="submit">Login</button>
      </ul>
    </div>
  </form>
</section>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>