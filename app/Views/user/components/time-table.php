<div id="ranage-info">
  <p class="select-title">ドライビングレンジ</p>
  <?php foreach ($rangeMatrix as $range => $rows): ?>
    <table class="range-table">
      <tbody>
        <tr><th class="range-num" colspan="28"><?= h($range) ?></th></tr>
        <tr class="time">
          <?php foreach ($calenderTimes as $time): ?>
            <td colspan="2"><?= h($time['label']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr class="time-zone">
          <?php foreach ($rows as $col): ?>
            <?php
              $class = match($col['status']) {
                'own' => 'reserve-col-own',
                'mainte', 'other' => 'reserve-col',
                default => ''
              };
            ?>
            <td class="<?= $class; ?>">&nbsp;</td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  <?php endforeach; ?>

<div id="option-info">
  <p class="select-title">レンタルクラブ / シャワールーム</p>
  <?php foreach ($optionMatrix as $facility => $rows): ?>
    <table class="range-table">
      <tbody>
        <tr><th class="range-num" colspan="28"><?= h($facility) ?></th></tr>
        <tr class="time">
          <?php foreach ($calenderTimes as $time): ?>
            <td colspan="2"><?= h($time['label']) ?></td>
          <?php endforeach; ?>
        </tr>
        <tr class="time-zone">
          <?php foreach ($rows as $col): ?>
            <?php
              $class = match($col['status']) {
                'own' => 'reserve-col-own',
                'mainte', 'other' => 'reserve-col',
                default => ''
              };
            ?>
            <td class="<?= $class; ?>">&nbsp;</td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>
  <?php endforeach; ?>
</div>