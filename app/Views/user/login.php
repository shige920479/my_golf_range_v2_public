<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>
    <main>
      <div class="site-visual">
        <img src="<?= url("images/login_back.jpg");?>" alt="" />
        <h1 class="site-title">会員専用 ログインページ</h1>
      </div>
      <div class="wrapper">
        <h2 class="form-title">予約・ログイン</h2>
        <p>※アドレス等間違いのない様にお願い致します</p>
        <form action="<?= url("/login"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken);?>">
          <table class="form-table">
            <tbody>
              <tr>
                <th>
                  <label for="email">メールアドレス<span>必須</span></label>
                </th>
                <td><input type="email" name="email" value="<?= $session->flash('old.email'); ?>"/>
                  <?= ($msg = $session->flash('errors.email')) ? "<span class='error-msg'>{$msg}</span>": "";?>
                </td>
              </tr>
              <tr>
                <th>
                  <label>パスワード<span>必須</span></label>
                </th>
                <td>
                  <input type="password" name="password"/>
                  <?= ($msg = $session->flash('errors.password')) ? "<span class='error-msg'>{$msg}</span>": "";?>
                </td>
              </tr>
            </tbody>
          </table>

          <button type="submit" class="form-btn">送信する</button>
        </form>
      </div>
    </main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>

