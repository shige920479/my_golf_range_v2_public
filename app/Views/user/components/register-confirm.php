<h2 class="form-title">入力フォーム</h2>
<p>
  ※下記の内容にて登録します。宜しければ登録ボタンをクリックしてください
</p>
<form action="<?= url("/register/confirm"); ?>" method="post">
  <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
  <table class="form-table">
    <tbody>
      <tr>
        <th><label for="lastname">お名前</label></th>
        <td><?= h($inputs['lastname']) . h($inputs['firstname']) . " 様"; ?></td>
      </tr>
      <tr>
        <th><label for="lastnamekana">フリガナ</label></th>
        <td><?= h($inputs['lastnamekana']) . h($inputs['firstnamekana']) . " 様"; ?></td>
      </tr>
      <tr>
        <th><label>メールアドレス</label></th>
        <td><?= h($inputs['email']); ?></td>
      </tr>
      <tr>
        <th><label for="phone">電話番号</label></th>
        <td><?= h($inputs['phone']) ?></td>
      </tr>
      <tr>
        <th><label for="gender">性別</label></th>
        <td>
          <?php if (isset($inputs['gender'])): ?>
            <?= $inputs['gender'] === 'male' ? '男性' : '女性'; ?>
          <?php else: ?>
            <?= "" ?>
          <?php endif;?>
        </td>
      </tr>
      <tr>
        <th><label for="password">パスワード</label></th>
        <td><?= h(substr_replace($inputs['password'], '*****', -5)) ?></td>
      </tr>
      <tr>
        <th><label for="consent">同意事項</label></th>
        <td>同意する</td>
      </tr>
    </tbody>
  </table>
  <div class="btn-flex">
    <a class="form-btn back-btn" href="<?= h(url($backUrl)); ?>">修正する</a>
    <button type="submit" class="form-btn">登録する</button>
  </div>
</form>