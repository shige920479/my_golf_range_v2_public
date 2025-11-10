<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>
<main>
  <div class="site-visual reserve">
    <img src="<?= url("images/red_ball.jpg");?>" alt="" />
    <h1 class="site-title">新規予約完了</h1>
  </div>
  <div class="wrapper">
    <h2 class="form-title">予約完了</h2>
    <p id="complete-msg">ご予約有難うございます。</p>
    <div id="complete">
      <img src="<?= url("images/check-white.png");?>" alt="" />
    </div>
    <div class="complete-msg">
      <a href="<?= url("reservation");?>">予約・マイページへ戻る</a>
    </div>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>