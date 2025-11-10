<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>
<main>
  <div class="site-visual reserve">
      <img src="<?= url("images/red_ball.jpg");?>" alt="" />
      <h1 class="site-title">新規予約内容のご確認</h1>
  </div>
  <div class="wrapper">
    <h2 class="form-title">予約内容</h2>
    <p>
      ※下記の内容にて登録します。宜しければ登録ボタンをクリックしてください
    </p>
    <form action="<?= url("/reservation/confirm"); ?>" method="post">
      <?php include APP_PATH . '/Views/user/components/reservation-confirm.php';?>
    <form>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>