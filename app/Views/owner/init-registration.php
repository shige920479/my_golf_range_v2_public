<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/owner-header.php';?>
<?php include APP_PATH  . '/Views/owner/components/side-menu.php';?>
<main>
  <div id="owner-container">
    <div id="owner-wrapper">
      <section class="init-setting">
        <form action="<?= url("/owner/initial/drivingRange"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          <p class="setting-title">ドライビングレンジ登録
            <?php if($message = $session->flash('success.drivingRange')):?>
              <span class="success"><?= $message;?></span>
            <?php endif;?>
            <?php if($errorMsg = $session->flash('errors.drivingRange')):?>
              <span class="error-msg"><?= $errorMsg;?></span>
            <?php endif;?>
          </p>
          <button type="submit" class="form-btn">設定登録</button>
          <table>
            <thead>
              <tr>
                <th><p>①</p></th>
                <th><p>②</p></th>
                <th><p>③</p></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="text" name="name[]" value="<?= h($session->flash('old.name_0')); ?>"></td>
                <td><input type="text" name="name[]" value="<?= h($session->flash('old.name_1')); ?>"></td>
                <td><input type="text" name="name[]" value="<?= h($session->flash('old.name_2')); ?>"></td>
              </tr>
              <tr>
                <td class="error-msg"><?= $session->flash('errors.name_0') ;?></td>
                <td class="error-msg"><?= $session->flash('errors.name_1') ;?></td>
                <td class="error-msg"><?= $session->flash('errors.name_2') ;?></td>
              </tr>
              </tbody>
          </table>
          <input type="hidden" name="mode" value="set_drivingRange">

        </form>
      </section>
      <section class="init-setting">
        <form action="<?= url("/owner/initial/rangeFee"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          <p class="setting-title">ドライビングレンジ料金登録
            <?php if($message = $session->flash('success.rangeFee')):?>
              <span class="success"><?= $message;?></span>
            <?php endif;?>
          </p>
          <button type="submit" class="form-btn">設定登録</button>
          <table>
            <thead>
              <tr>
                <th><p>チャージ（円/人）</p></th>
                <th><p>平日料金（円/H）</p></th>
                <th><p>休日料金（円/H）</p></th>
                <th><p>適用開始日</p></th>
              </tr>
            </thead>
              <tbody>
                <tr>
                  <td><input type="number" name="entrance_fee" value="<?= h($session->flash('old.entrance_fee')); ?>"></td>
                  <td><input type="number" name="weekday_fee" value="<?= h($session->flash('old.weekday_fee')); ?>"></td>
                  <td><input type="number" name="holiday_fee" value="<?= h($session->flash('old.holiday_fee"')); ?>"></td>
                  <?php $old = $section === 'rangeFee' && ! empty($effectiveDateOld) ? $effectiveDateOld : ''; ?>
                  <td><input type="date" name="effective_date" value="<?= h($old);?>"></td>
                </tr>
                <tr>
                    <td class="error-msg"><?= $session->flash('errors.entrance_fee') ;?></td>
                    <td class="error-msg"><?= $session->flash('errors.weekday_fee') ;?></td>
                    <td class="error-msg"><?= $session->flash('errors.holiday_fee') ;?></td>
                    <td class="error-msg">
                    <?php if ($section === 'rangeFee' && ! empty($effectiveDateError)):?>
                      <?= $effectiveDateError;?>
                    <?php endif;?>
                    </td>
                  </tr>
              </tbody>
          </table>
        </form>
      </section>
      <section class="init-setting">
        <form action="<?= url("/owner/initial/rental"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          <p class="setting-title">レンタルクラブ登録
            <?php if($message = $session->flash('success.rental')):?>
              <span class="success"><?= $message;?></span>
            <?php endif;?>
            <?php if($errorMsg = $session->flash('errors.brand') ?? $session->flash('errors.model')):?>
              <span class="error-msg"><?= $errorMsg;?></span>
            <?php endif;?>
          </p>
          <button type="submit" class="form-btn">設定登録</button>
          <table>
            <thead>
              <tr>
                <th><p>ブランド</p></th>
                <th colspan="2"><p>モデル</p></th>
              </tr>
            </thead>
              <tbody>
                <tr>
                  <td><input type="text" name="brand[]" value="<?= h($session->flash('old.brand_0')); ?>"></td>
                  <td colspan="2"><input type="text" name="model[]" value="<?= h($session->flash('old.model_0')); ?>"></td>
                </tr>
                <tr>
                  <td class="error-msg"><?= $session->flash('errors.brand_0'); ?></td>
                  <td colspan="2" class="error-msg"><?= $session->flash('errors.model_0'); ?></td>
                </tr>
                <tr>
                  <td><input type="text" name="brand[]" value="<?= h($session->flash('old.brand_1')); ?>"></td>
                  <td colspan="2"><input type="text" name="model[]" value="<?= h($session->flash('old.model_1')); ?>"></td>
                </tr>
                <tr>
                  <td class="error-msg"><?= $session->flash('errors.brand_1'); ?></td>
                  <td colspan="2" class="error-msg"><?= $session->flash('errors.model_1'); ?></td>
                </tr>
              </tbody>
              
          </table>
        </form>
      </section>
      <section class="init-setting">
        <form action="<?= url("/owner/initial/rentalFee"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          <p class="setting-title">レンタルクラブ料金
            <?php if($message = $session->flash('success.rentalFee')):?>
              <span class="success"><?= $message;?></span>
            <?php endif;?>
          </p>
          <button type="submit" class="form-btn">設定登録</button>
          <table>
            <thead>
              <tr>
                <th><p>レンタル料金（円/H）</p></th>
                <th><p>適用開始日</p></th>
              </tr>
            </thead>
              <tbody>
                <tr>
                  <td><input type="text" name="rental_fee" value="<?= h($session->flash('old.rental_fee'));?>"></td>
                  <?php $old = $section === 'rentalFee' && ! empty($effectiveDateOld) ? $effectiveDateOld : ''; ?>
                  <td><input type="date" name="effective_date" value="<?= h($old);?>"></td>
                </tr>
                <tr>
                  <td class="error-msg"><?= $session->flash('errors.rental_fee'); ?></td>
                  <td class="error-msg">
                    <?php if ($section === 'rentalFee' && ! empty($effectiveDateError)):?>
                      <?= $effectiveDateError;?>
                    <?php endif;?>
                  </td>
                </tr>
              </tbody>
          </table>
        </form>
      </section>
      <section class="init-setting">
        <form action="<?= url("/owner/initial/showerFee"); ?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken); ?>">
          <p class="setting-title">シャワー料金登録
            <?php if($message = $session->flash('success.showerFee')):?>
              <span class="success"><?= $message;?></span>
            <?php endif;?>
          </p>
          <button type="submit" class="form-btn">設定登録</button>
          <table>
            <thead>
              <tr>
                <th><p>料金（円/回）</p></th>
                <th><p>適用開始日</p></th>
              </tr>
            </thead>
              <tbody>
                <tr>
                  <td><input type="number" name="shower_fee" value="<?= h($session->flash('old.shower_fee'))?>"></td>
                  <?php $old = $section === 'showerFee' &&  ! empty($effectiveDateOld) ? $effectiveDateOld : ''; ?>
                  <td><input type="date" name="effective_date" value="<?= h($old);?>"></td>
                </tr>     
                <tr>
                  <td class="error-msg"><?= $session->flash('errors.shower_fee');?></td>
                  <td class="error-msg">
                    <?php if ($section === 'showerFee' && ! empty($effectiveDateError)):?>
                      <?= $effectiveDateError;?>
                    <?php endif;?>
                  </td>
                </tr>     
              </tbody>
          </table>
        </form>
      </section>
    </div>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>

