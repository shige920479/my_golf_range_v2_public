<input type="hidden" name="token" value="<?= h($csrfToken);?>">
<table class="form-table reserve-confirm">
  <tbody>
    <tr>
      <th>ご利用日時</th>
      <td><?= h($otherData['format_date']) ?></td>
    </tr>
    <tr>
      <th>レンジ名</th>
      <td><?= h($otherData['range_name']);?></td>
    </tr>
    <tr>
      <th>ご利用時間</th>
      <td>
        <?= substr(h($reservation['start_time']),0,5)?>
        <span>～</span>
        <?= substr(h($reservation['end_time']),0,5) ?>
      </td>
    </tr>
    <tr>
      <th>オプションのご利用</th>
      <td>
        <?php if($reservation['rental'] !== '' && $reservation['rental'] === "1") :?>
          <?= h($otherData['brand']) . ' / ' . h($otherData['model']); ?>
        <?php else :?>
          <?= '利用しない';?>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>シャワー室のご利用</th>
      <td>
        <?php if($reservation['shower'] !== '' && $reservation['shower'] === "1") :?>
          <?= substr(h($reservation['shower_time']),0,5); ?>
        <?php else :?>
          <?= '利用しない';?>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <th>ご利用料金</th>
      <td>
        <div class="table">
          <div class="table-row">
            <div class="table-cell">入場料金</div>
            <div class="table-cell"><?= h($reservation['number']);?>名様</div>
            <div class="table-cell fee" data-fee="<?= h($reservation['entrance_fee']);?>">
              <?= h(number_format($reservation['entrance_fee']));?>円
            </div>
          </div>
          <div class="table-row">
            <div class="table-cell">レンジ使用料金</div>
            <div class="table-cell"><?= h($reservation['usage_time']); ?>時間</div>
            <div class="table-cell fee" data-fee="<?= h($reservation['range_hourly_fee']);?>">
              <?= h(number_format($reservation['range_hourly_fee']));?>円
            </div>
          </div>
          <?php if($reservation['rental'] !== '' && $reservation['rental'] === "1") :?>
            <div class="table-row">
              <div class="table-cell">レンタルクラブ料金</div>
              <div class="table-cell"><?= h($reservation['usage_time']); ?>時間</div>
              <div class="table-cell fee" data-fee="<?= h($reservation['rental_fee']);?>">
                <?= h(number_format($reservation['rental_fee']));?>円
              </div>
            </div>
          <?php endif; ?>
          <?php if($reservation['shower'] !== '' && $reservation['shower'] === "1") :?>
            <div class="table-row">
              <div class="table-cell">シャワー利用料金</div>
              <div class="table-cell">&nbsp;</div>
              <div class="table-cell fee" data-fee="<?= h($reservation['shower_fee']);?>">
                <?= h(number_format($reservation['shower_fee']));?>円
              </div>
            </div>
          <?php endif; ?>
          <div class="table-row">
            <div class="table-cell">ご利用料金合計</div>
            <div class="table-cell">&nbsp;</div>
            <div class="table-cell" id="total-fee"></div>
          </div>
        </div>
      </td>
    </tr>
  </tbody>
</table>
<div id="btn-flex">
  <a href="<?= h(url($reservation['back_url'])); ?>">入力内容を訂正する</a>
  <button type="submit" class="form-btn reserve">登録する</button>
</div>