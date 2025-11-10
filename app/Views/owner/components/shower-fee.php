<div class="edit-wrapper">
  <p class="setting-title">シャワー料金変更</p>
  <form action="<?= url("/owner/create/option/showerFee"); ?>" method="post">
    <input type="hidden" name="token" value="<?= h($csrfToken);?>">
    <ul class="edit-input">
      <li>
        <label for="">時間料金</label>
        <span><?= $session->flash('errors.shower_fee'); ?></span>
        <?php $showerFee = $session->flash('old.shower_fee') ?? $fee['shower_fee'] ?? '';?>
        <input type="number" name="shower_fee" value="<?= h($showerFee); ?>">
      </li>
      <li>
        <label for="">料金改定日</label>
        <span><?= $session->flash('errors.effective_date'); ?></span>
        <?php $effectiveDate = $session->flash('old.effective_date') ?? $fee['effective_date'] ?? '';?>
        <input type="date" name="effective_date" value="<?= h($effectiveDate);?>">
      </li>
      <li>
        <button type="submit" class="form-btn">登録する</button>
      </li>
    </ul>
  </form>
</div>