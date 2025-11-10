<h2 class="form-title">入力フォーム</h2>
<p>※アドレス等間違いのない様にお願い致します</p>
<form action="<?= url("/register/profile"); ?>" method="post">
  <input type="hidden" name="token" value="<?= h($csrfToken);?>">
  <table class="form-table">
    <tbody>
      <tr>
        <th><label for="lastname">お名前<span>必須</span></label></th>
        <td>
          <div class="input-name">
            <label class="label-box" for="lastname">性  </label>
            <?php $lastname = $session->flash("old.lastname") ?? $session->flash("register.lastname") ?? '' ?>
            <input type="text" name="lastname" id="lastname" value="<?= h($lastname) ?>"/>
            <?= ($msg = $session->flash('errors.lastname')) ? "<span  class='error-msg'>{$msg}</span>" : "" ; ?>
          </div>
          <div>
            <label class="label-box" for="firstname">名  </label>
            <?php $firstname = $session->flash("old.firstname") ?? $session->flash("register.firstname") ?? '' ?>
            <input type="text" name="firstname" id="firstname" value="<?= h($firstname);?>" />
            <?= ($msg = $session->flash('errors.firstname')) ? "<span  class='error-msg'>{$msg}</span>" : "" ; ?>
          </div>
        </td>
      </tr>
      <tr>
        <th><label for="lastnamekana">フリガナ<span>必須</span></label></th>
        <td>
          <div class="input-name">
            <label class="label-box" for="lastnamekana">セイ</label>
            <?php $lastnamekana = $session->flash("old.lastnamekana") ?? $session->flash("register.lastnamekana") ?? '' ?>
            <input type="text" name="lastnamekana" id="lastnamekana" value="<?= h($lastnamekana); ?>"/>
            <?= ($msg = $session->flash('errors.lastnamekana')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
          </div>
          <div>
            <label class="label-box" for="firstnamekana">メイ</label>
            <?php $firstnamekana = $session->flash("old.firstnamekana") ?? $session->flash("register.firstnamekana") ?? '' ?>
            <input type="text" name="firstnamekana" id="firstnamekana" value="<?= h($firstnamekana); ?>"/>
            <?= ($msg = $session->flash('errors.firstnamekana')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
          </div>
        </td>
      </tr>
      <tr>
        <th><label>メールアドレス</label></th>
        <td>
          <?= h($email) ?? ''; ?>
          <input type="hidden" name="email" value="<?= h($email) ?? ''; ?>">
        </td>
      </tr>
      <tr>
        <th><label for="phone">電話番号<span>必須</span></label></th>
        <td>
          <?php $phone = $session->flash("old.phone") ?? $session->flash("register.phone") ?? '' ?>
          <input type="text" name="phone" id="phone" value="<?= h($phone); ?>"/>
          <?= ($msg = $session->flash('errors.phone')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
        </td>
      </tr>
      <tr>
        <th><label for="gender">性別</label></th>
        <td>
          <?php $gender = $session->flash('old.gender') ?? $session->flash('register.gender') ?? ''?>
          <input type="radio" name="gender" value="male" <?= ($gender  === 'male') ? 'checked' : ''; ?>/>男性
          <input type="radio" name="gender" value="female" <?= ($gender  === 'female') ? 'checked' : ''; ?>/>女性
          <?= ($msg = $session->flash('errors.gender')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
        </td>
      </tr>
      <tr>
        <th><label for="password">パスワード<span>必須</span></label></th>
        <td>
          <input type="password" name="password" id="password" />
          <?= ($msg = $session->flash('errors.password')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
        </td>
      </tr>
      <tr>
        <th><label for="password_confirmation">パスワード（再入力）<span>必須</span></label></th>
        <td><input type="password" name="password_confirmation" id="password_confirmation" />
        <?= ($msg = $session->flash('errors.password_confirmation')) ? "<span  class='error-msg'>{$msg}</span>": "" ; ?>
      </td>
      </tr>
      <tr>
        <th><label for="consent">同意事項<span>必須</span></label></th>
        <td><input type="checkbox" name="consent" id="consent" />同意する</td>
      </tr>
    </tbody>
  </table>
  <button type="submit" id="regist-btn" class="form-btn">登録する</button>
</form>