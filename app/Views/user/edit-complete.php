<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>
<main>
  <div class="site-visual reserve">
    <img src="<?= url("images/login_back.jpg");?>" alt="" />
    <h1 class="site-title">変更登録完了</h1>
  </div>
  <div class="wrapper">
    <h2 class="form-title">予約変更完了</h2>
    <p id="complete-msg">ご予約内容を変更いたしました</p>
    <div id="complete">
      <img src="<?= url("images/check-white.png");?>" alt="" />
    </div>
    <div class="complete-msg">
      <a href="<?= url("mypage");?>">予約確認・変更 へ戻る</a>
    </div>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>