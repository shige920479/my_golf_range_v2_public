<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>仮登録のご案内</title>
    <base href="<?= url('/');?>"?>">
    <link rel="shortcut icon" href="<?= url("images/golfball.svg");?>">
    <link rel="stylesheet" href="<?= url("css/reset.css");?>" />
    <link rel="stylesheet" href="<?= url("css/style.css");?>" />
  </head>
  <body>
    <div class="wrapper temp-register-email">
      <div id="mail-header">
        <h1>[MY_GOLF_RANGE]会員登録のご確認</h1>
        <h2>MY_GOLF_RANGE<span>&lt;owner@example.com&gt;</span></h2>
        <p>To 自分</p>
      </div>
      <div id="mail-content">
        <p>※本メールは自動配信メールです</p>
        <p>仮登録有難うございます</p>
        <p>登録はまだ完了しておりません。</p>
        <p>下記URLにアクセスし、会員登録を進めてください。</p>
        <p>
          <a href="<?= h(url($toRegisterLink)); ?>">
            会員登録ページへのリンクURL
          </a>
        </p>
        <p>24時間経過するとURLは無効となります</p>
        <p>24時間経過後ははじめからお手続きください。</p>
      </div>
    </div>
  </body>
</html>