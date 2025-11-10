<div class="edit-wrapper">
  <p class="setting-title">レンタルクラブ料金変更</p>
  <form action="<?= url("/owner/create/option/rentalFee"); ?>" method="post">
    <input type="hidden" name="token" value="<?= h($csrfToken);?>">
    <ul class="edit-input">
      <li>
        <label for="">時間料金</label>
        <span><?= $session->flash('errors.rental_fee'); ?></span>
        <?php $rentalFee = $session->flash('old.rental_fee') ?? $fee['rental_fee'] ?? '';?>
        <input type="number" name="rental_fee" value="<?= h($rentalFee); ?>">
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