<h2 class="form-title">入力フォーム</h2>
<p>※アドレス等間違いのない様にお願い致します
  <?= ($msg = $session->flash('errors.register')) ? "<span class='error-msg'>{$msg}</span>": "";?>
</p>
<form action="<?= url('/register/temporary'); ?>" method="post">
  <input type="hidden" name="token" value="<?= h($csrfToken);?>">
  <table class="form-table">
    <tbody>
      <tr>
        <th>
          <label for="email">メールアドレス<span>必須</span></label>
        </th>
        <td>
          <input type="email" name="email" value="<?= $session->flash('old.email'); ?>"/>
          <?= ($msg = $session->flash('errors.email')) ? "<span class='error-msg'>$msg</span>" : '';?>
        </td>
      </tr>
      <tr>
        <th>
          <label>メールアドレス（再入力）<span>必須</span></label>
        </th>
        <td>
          <input type="email" name="email_confirmation" value="<?= $session->flash('old.email_confirmation'); ?>"/>
          <?= ($msg = $session->flash('errors.email_confirmation')) ? "<span class='error-msg'>$msg</span>" : '';?>
        </td>
      </tr>
    </tbody>
  </table>
  <button type="submit" class="form-btn">送信する</button>
</form>