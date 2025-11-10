<div class="edit-wrapper">
  <p class="setting-title">ドライビングレンジ料金変更</p>
  <form action=<?= url("/owner/create/rangeFee"); ?> method="post">
    <input type="hidden" name="token" value="<?= h($csrfToken);?>">
    <ul class="edit-input">
      <li>
        <label for="">レンジチャージ<small>（円/H）</small></label>
        <span><?= $session->flash('errors.entrance_fee'); ?></span>
        <?php $entranceFee = $session->flash('old.entrance_fee') ?? $fee['entrance_fee'] ?? '';?>
        <input type="number" name="entrance_fee" value="<?= h($entranceFee);?>">
      </li>
      <li>
        <label for="">平日料金<small>（円/H）</small></label>
        <span><?= $session->flash('errors.weekday_fee'); ?></span>
        <?php $weekdayFee = $session->flash('old.weekday_fee') ?? $fee['weekday_fee'] ?? ''; ?>
        <input type="number" name="weekday_fee" value="<?= h($weekdayFee); ?>">
      </li>
      <li>
        <label for="">休日料金<small>（円/H）</small></label>
        <span><?= $session->flash('errors.holiday_fee'); ?></span>
        <?php $holidayFee = $session->flash('old.holiday_fee') ?? $fee['holiday_fee'] ?? '';?>
        <input type="number" name="holiday_fee" value="<?= h($holidayFee);?>">
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