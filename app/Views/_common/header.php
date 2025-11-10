<header id="header" class="wrapper">
  <div id="header-left">
    <h1 id="logo">
      <a href="<?= url('/');?>">
        <img src="<?= url("images/logo.png");?>" alt="ロゴ" />
      </a>
    </h1>
    
    <?php if (! $isLoggedIn):?>
      <nav id="nav-menu">
        <ul>
          <li class="nav-items"><a href="<?= url('/');?>">トップページ</a></li>
          <li class="nav-items"><a href="<?= url("price");?>">ご利用料金</a></li>
          <li class="nav-items"><a href="<?= url("register/temporary");?>">会員登録</a></li>
          <li class="nav-items"><a href="<?= url("reservation");?>">予約・マイページ</a></li>
          <li class="nav-items"><a href="<?= url('/');?>">アクセス</a></li>
        </ul>
      </nav>
    <?php else:?>
      <?php if (! $isConfirm) :?>
      <nav id="nav-menu">
        <ul>
          <li class="nav-items"><a href="<?= url('/');?>">トップページ</a></li>
          <li class="nav-items"><a href="<?= url("price");?>">ご利用料金</a></li>
          <li class="nav-items"><a href="<?= url("reservation");?>">新規予約</a></li>
          <li class="nav-items"><a href="<?= url("mypage");?>">予約確認・変更</a></li>
          <li class="nav-items"><p><?= $_SESSION['user']['name'] . ' 様'; ?></p></li>
        </ul>
        </nav>
        <?php else:?>
        <nav id="nav-menu">
          <ul>
            <li class="nav-items">
              <!-- <a href="javascript:void(0);" class="with-confirm" data-href="<?= url('/'); ?>">トップページ</a> -->
              <a href="javascript:void(0);" class="with-confirm" data-href="/">トップページ</a>
            </li>
            <li class="nav-items">
              <!-- <a href="javascript:void(0);" class="with-confirm" data-href="<?= url("price"); ?>">ご利用料金</a> -->
              <a href="javascript:void(0);" class="with-confirm" data-href="price">ご利用料金</a>
            </li>
            <li class="nav-items">
              <!-- <a href="javascript:void(0);" class="with-confirm" data-href="<?= url("reservation"); ?>">予約・マイページ</a> -->
              <a href="javascript:void(0);" class="with-confirm" data-href="reservation">予約・マイページ</a>
            </li>
            <li class="nav-items">
              <!-- <a href="javascript:void(0);" class="with-confirm" data-href="<?= url("mypage"); ?>">予約確認・変更</a> -->
              <a href="javascript:void(0);" class="with-confirm" data-href="mypage">予約確認・変更</a>
            </li>
            <li class="nav-items"><p><?= $_SESSION['user']['name'] . ' 様'; ?></p></li>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif;?>
  </div>
  <div id="header-right">
      <div id="contact">
        <a href="<?= url("/");?>">お問合せ</a>
      </div>
      <div>
        <?php if (! $isLoggedIn):?>
          <a href="<?= url("login");?>" class="auth-btn">会員ページへ</a>
        <?php else:?>
          <form action=<?= url("/logout");?> method="post" id="logout-form">
            <button type="button" class="auth-btn" id="logout">ログアウト</button>
            <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          </form>
        <?php endif;?>
      </div>
  </div>
</header>