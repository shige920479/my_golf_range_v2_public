<div class="edit-wrapper">
  <p class="setting-title">メンテナンス日登録</p>

  <?php if($table === 'drivingRange'):?>
    <p>ドライビングレンジ</p>
    <div>
      <p>レンジ名</p>
      <p><?= $data['name'];?></p>
    </div>
  <?php endif;?>
  <?php if($table === 'rental'):?>
    <p>クラブレンタル</p>
    <div>
      <p>ブランド</p>
      <p><?= $data['brand'];?></p>
    </div>
    <div>
      <p>モデル</p>
      <p><?= $data['model'];?></p>
    </div>
  <?php endif;?>
  <?php if($table === 'shower'):?>
    <p>シャワールーム</p>
  <?php endif;?>

  <form action="<?= url("/owner/mainte/{$table}/{$id}"); ?>" method="post">
    <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
    <ul class="edit-input">
      <li>
      <label for="prev-mainte"><?php ?></label>
      <div id="prev-mainte">
        <span>前回メンテナンス日(予定日)</span>
        <?= $data['mainte_date'] ?? '設定なし';?>
      </div>
      </li>
      <li>
        <label for="mainte-date">予定日登録</label>
        <span class="errors-msg"><?= $session->flash('errors.mainte_date');?></span>
        <input id="mainte-date" type="date" name="mainte_date" placeholder="日付を選択">
      </li>
    </ul>
    <button type="submit" class="form-btn">登録する</button>
  </form>
</div>