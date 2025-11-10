<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>

<main>
  <div class="site-visual">
    <img src="<?= url("images/scotland.jpg");?>" alt="" />
    <h1 class="site-title">会員登録</h1>
  </div>
  <div class="wrapper">
    <ul class="step-bar">
      <li class="item<?= $mode === 'temporary' ? ' is-current' : '';?>">STEP.1 仮登録</li>
      <li class="item<?= in_array($mode, ['profile', 'confirm']) ? ' is-current' :'';?>">STEP.2 本登録</li>
      <li class="item<?= $mode === 'complete' ? ' is-current' : '';?>">STEP.3 完了</li>
    </ul>
    <?php
      match ($mode) {
        'temporary' => include APP_PATH . '/Views/user/components/register-temporary.php',
        'profile' => include APP_PATH . '/Views/user/components/register-profile.php',
        'confirm' => include APP_PATH . '/Views/user/components/register-confirm.php',
        'complete' => include APP_PATH . '/Views/user/components/register-complete.php'
      }
    ?>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>
