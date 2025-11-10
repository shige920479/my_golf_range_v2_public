<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>エラー発生</title>
  <link rel="stylesheet" href="<?= url("css/reset.css");?>">
  <link rel="shortcut icon" href="<?= url("images/favicon.png");?>" type="image/x-icon">
  <style>
    .error-wrapper {
      width: 1000px;
      margin: 80px auto;
      text-align: center;
    }
    li {
      list-style: none;
    }
    #error-home-link {
      margin: 10px 0 20px 0;
    }
  </style>
</head>
<body>
  <div class="error-wrapper">
      <h1><?= $code ?> Error</h1>
      <p><?= $message ?></p>
      <div id="error-home-link"><a href="<?= url('/');?>">ホームに戻る</a></div>
      <div>
        <p>お問合せ先</p>
        <ul>
          <li>XXXXX株式会社 カスタマセンター</li>
          <li>Tel : XXX-XXXX-XXXX</li>
          <li>email : XXXX@XXXX.com</li>
        </ul>
      </div>
  </div>
</body>
</html>