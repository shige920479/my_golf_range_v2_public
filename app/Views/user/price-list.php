<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>

<main>
  <div class="site-visual reserve">
    <img src="<?= url("images/golf_chair.jpg");?>" alt="" />
    <h1 class="site-title">ご利用料金</h1>
  </div>
  <div class="wrapper">
    <h2 class="form-title">料金表</h2>
      <table class="price-table">
        <thead>
          <tr>
            <th>&nbsp;</th><th>&nbsp;</th><th class="txt-center">平日</th><th class="txt-center">土日</th>
          </tr>
        </thead>
        <tbody>
            <tr>
              <td rowspan="2">ドライビングレンジ</td>
              <td>入場料金（1人あたり）</td>
              <td class="txt-center" colspan="2">
                <?php if ($change['rangeFee'] !== null):?>
                  <p><?= number_format($current['rangeFee']['entrance_fee']) . '円'?></p>
                  <span class='e-date'>
                    <?= '※' . date('n/d', strtotime($change['rangeFee']['effective_date']))
                     . "～ " . number_format($change['rangeFee']['entrance_fee']) . "円";?>
                  </span>
                <?php else:?>
                  <p><?= number_format($current['rangeFee']['entrance_fee']) . '円' ;?></p>
                <?php endif;?>
              </td>
            </tr>
            <tr>
              <td>レンジ利用料金（/H）</td>
              <td class="txt-center">
                <?php if ($change['rangeFee'] !== null):?>
                  <p><?= number_format($current['rangeFee']['weekday_fee']) . '円';?></p>
                  <span class='e-date'>
                    <?= '※' . date('n/d', strtotime($change['rangeFee']['effective_date']))
                    . "～ " . number_format($change['rangeFee']['weekday_fee']) . "円";?>
                  </span>
                <?php else:?>
                  <p><?= $current['rangeFee']['weekday_fee'] . '円';?></p>
                <?php endif; ?>
              </td>
              <td class="txt-center">
                <?php if ($change['rangeFee'] !== null):?>
                  <p><?= number_format($current['rangeFee']['holiday_fee']) . '円';?></p>
                  <span class='e-date'>
                    <?= '※' . date('n/d', strtotime($change['rangeFee']['effective_date']))
                    . "～ " . number_format($change['rangeFee']['holiday_fee']) . "円";?>
                  </span>
                <?php else:?>
                  <p><?= $current['rangeFee']['holiday_fee'] . '円';?></p>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td>レンタルクラブ</td>
              <td>クラブ利用料金（/H）</td>
              <td class="txt-center" colspan="2">
                <?php if ($change['rentalFee'] !== null):?>
                  <p><?= number_format($current['rentalFee']['rental_fee']) . '円';?></p>
                  <span class='e-date'>
                    <?= '※' . date('n/d', strtotime($change['rentalFee']['effective_date']))
                    . "～ " . number_format($change['rentalFee']['rental_fee']) . "円";?>
                  </span>
                <?php else:?>
                  <p><?= $current['rentalFee']['rental_fee'] . '円';?></p>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td>シャワー</td>
              <td>25分/回</td>
              <td class="txt-center" colspan="2">
                <?php if ($change['showerFee'] !== null):?>
                  <p><?= number_format($current['showerFee']['shower_fee']) . '円';?></p>
                  <span class='e-date'>
                    <?= '※' . date('n/d', strtotime($change['showerFee']['effective_date']))
                    . "～ " . number_format($change['showerFee']['shower_fee']) . "円";?>
                  </span>
                <?php else:?>
                  <p><?= $current['showerFee']['shower_fee'] . '円';?></p>
                <?php endif; ?>
              </td>
            </tr>
        </tbody>
      </table>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>