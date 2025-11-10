<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/owner-header.php';?>
<?php include APP_PATH  . '/Views/owner/components/side-menu.php';?>
<main>
  <div id="owner-container">
    <div id="owner-wrapper">
      <section id="pricing-sec">
        <div class="setting-title">
          <p>ドライビングレンジ料金設定
          <?php if($message = $session->flash('success.range')):?>
            <span class="success"><?= $message;?></span>
          <?php endif;?>
          <?php if($message = $session->flash('errors.range')):?>
            <span class='error-msg'><?= $message;?></span>
          <?php endif;?>
          </p>
          <a href="<?= url("owner/create/rangeFee");?>">登録</a>
        </div>
        <table class="form-table setting">
          <thead>
            <tr>
              <th colspan="5">&nbsp;</th>
              <th colspan="2" class="ta-c">現行料金</th>
              <th colspan="2" class="ta-c">改定予定
                <span><?= $featureRangeFee['effective_date'] ?? '' ;?></span>
              </th>
            </tr>
            <tr>
              <th colspan="4">&nbsp;</th>
              <th class="ta-c">単位</th>
              <th class="ta-c">平日料金</th>
              <th class="ta-c">休日料金</th>
              <th class="ta-c">平日料金</th>
              <th class="ta-c">休日料金</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td rowspan="2" colspan="2">ドライビングレンジ</td>
              <td colspan="2">レンジチャージ</td>
              <td class="ta-c">円/人</td>
              <td class="ta-c"><?= $currentRangeFee['entrance_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $currentRangeFee['entrance_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $featureRangeFee['entrance_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $featureRangeFee['entrance_fee'] ?? '設定なし';?></td>
            </tr>
            <tr>
              <td colspan="2">時間料金</td>
              <td class="ta-c">円/人</td>
              <td class="ta-c"><?= $currentRangeFee['weekday_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $currentRangeFee['holiday_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $featureRangeFee['weekday_fee'] ?? '設定なし';?></td>
              <td class="ta-c"><?= $featureRangeFee['holiday_fee'] ?? '設定なし';?></td>
            </tr>
          </tbody>
        </table>
      </section>
      <section id="rental-sec">
        <p class="setting-title">オプション料金設定
          <?php if($message = $session->flash('success.rentalFee') ?? $session->flash('success.showerFee')):?>
            <span class="success"><?= $message;?></span>
          <?php endif;?>
          <?php if($message = $session->flash('errors.rentalFee') ?? $session->flash('errors.showerFee')):?>
            <span class='error-msg'><?= $message;?></span>
          <?php endif;?>
        </p>
        <table class="form-table setting">
          <thead>
            <tr>
              <th colspan="5">&nbsp;</th>
              <th colspan="2" class="ta-c">現行料金</th>
              <th colspan="2" class="ta-c">改定予定
                <span><?= $featureRentalFee['effective_date'] ?? '' ;?></span>
              </th>
            </tr>
            <tr>
              <th colspan="1">メーカー</th>
              <th colspan="4">モデル</th>
              <th colspan="2" class="ta-c">料金/H</th>
              <th colspan="2" class="ta-c">料金/H</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($mainte['rental'] as $index => $rental) :?>
              <tr>
                <td colspan="1"><?= $rental['name'];?></td>
                <td colspan="4"><?= $rental['model'];?></td>
                <td colspan="2" class="ta-c"><?= $currentRentalFee['rental_fee'] ?? '設定なし';?></td>
                <td colspan="1" class="ta-c"><?= $featureRentalFee['rental_fee'] ?? '設定なし';?></td>
                <?php if($index === 0):?>
                  <td colspan="1" rowspan="2" class="ta-c"><a href="<?= url("owner/create/option/rentalFee");?>">登録</a></td>
                <?php endif;?>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="5">シャワー利用</td>
              <td colspan="2" class="ta-c"><?= $currentShowerFee['shower_fee'] ?? '設定なし';?></td>
              <td colspan="1" class="ta-c"><?= $featureShowerFee['shower_fee'] ?? '設定なし';?></td>
              <td colspan="1" class="ta-c"><a href="<?= url("owner/create/option/showerFee");?>">登録</a></td>

            </tr>
          </tbody>
        </table>
      </section>

      <section id="mainte-sec">
        <p class="setting-title">メンテナンス日設定
          <?php if($message = $session->flash('success.mainte_date')):?>
            <span class="success"><?= $message;?></span>
          <?php endif;?>
        </p>
        <table class="form-table setting">
          <thead>
            <tr>
              <th colspan="7">&nbsp;</th>
              <th colspan="1" class="ta-c">設定日</th>
              <th colspan="1" class="ta-c">登録リンク</th>
            </tr>
          </thead>
          <tbody>
            <?php if(! isset($mainte)) :?>
              <p>未登録です</p>
            <?php else:?>
              <?php $rangeCount = count($mainte['range']); ?>
              <?php foreach($mainte['range'] as $index => $range):?>
                <?php if($index === 0):?>
                  <tr>
                    <td rowspan="<?= $rangeCount;?>" colspan="2">ドライビングレンジ</td>
                    <td colspan="5"><?= $range['name'];?></td>
                    <td colspan="1" class="ta-c"><?= $range['mainte_date'] ?? "予定なし";?></td>
                    <td colspan="1" class="ta-c">
                      <a href=<?= "/owner/mainte/drivingRange/{$range['id']}"?>>登録</a>
                    </td>
                  </tr>
                <?php else:?>
                  <tr>
                    <td colspan="5"><?= $range['name'];?></td>
                    <td colspan="1" class="ta-c"><?= $range['mainte_date'] ?? "予定なし";?></td>
                    <td colspan="1" class="ta-c"><a href=<?= "/owner/mainte/drivingRange/{$range['id']}"?>>登録</a></td>
                  </tr>
                <?php endif;?>
              <?php endforeach;?>
              <?php foreach ($mainte['rental'] as $index => $rental) :?>
                <?php if($index === 0):?>
                    <tr>
                      <td rowspan="2" colspan="2">レンタルクラブ</td>
                      <td colspan="1"><?= $rental['name'];?></td>
                      <td colspan="4"><?= $rental['model'];?></td>
                      <td colspan="1" class="ta-c"><?= $rental['mainte_date'] ?? "予定なし";?></td>
                      <td colspan="1" class="ta-c"><a href=<?= "/owner/mainte/rental/{$rental['id']}"?>>登録</a></td>
                    </tr>
                <?php else:?>
                      <td colspan="1"><?= $rental['name'];?></td>
                      <td colspan="4"><?= $rental['model'];?></td>
                      <td colspan="1" class="ta-c"><?= $rental['mainte_date'] ?? "予定なし";?></td>
                      <td colspan="1" class="ta-c"><a href=<?= "/owner/mainte/rental/{$rental['id']}"?>>登録</a></td>
                <?php endif;?>
              <?php endforeach;?>
                <tr>
                  <td colspan="7">シャワールーム</td>
                  <td colspan="1" class="ta-c"><?= $mainte['shower'][0]['mainte_date'] ?? "予定なし";?></td>
                  <td colspan="1" class="ta-c"><a href=<?= "/owner/mainte/shower/{$mainte['shower'][0]['id']}"?>>登録</a></td>
                </tr>
            <?php endif;?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>