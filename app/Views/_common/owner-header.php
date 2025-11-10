<header id="owner-header">
  <div><img src="<?= url("images/logo_white.png");?>" alt=""></div>
  <div>
    <form action="<?= url("/owner/logout");?>"   method="post">
      <input type="hidden" name="token" value="<?= h($csrfToken);?>">
      <button type="button" id="owner-logout">ログアウト</button>
    </form>
    <p>管理者ページ</p>
  </div>
</header>