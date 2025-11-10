<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>
<main>
  <div class="site-visual reserve">
      <img src="<?= url("images/login_back.jpg");?>" alt="" />
      <h1 class="site-title">予約変更内容のご確認</h1>
  </div>
  <div class="wrapper">
    <h2 class="form-title">変更内容 (ご予約番号：<?= h($id);?>)</h2>
    <p>
      ※下記の内容にて変更します。宜しければ登録ボタンをクリックしてください
    </p>
    <form action="<?= url("/reservation/{$id}/confirm"); ?>" method="post">
      <?php include APP_PATH . '/Views/user/components/reservation-confirm.php';?>
    <form>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>