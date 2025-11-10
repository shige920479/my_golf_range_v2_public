<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>

<main>
  <div class="site-visual reserve">
    <img src="<?= url("images/login_back.jpg");?>" alt="" />
    <h1 class="site-title">予約内容のご確認</h1>
  </div>
  <div class="wrapper mb">
    <h2 class="form-title">予約確認・変更・キャンセル</h2>
    <table id="reserve-list">
      <thead>
        <tr><th>予約ID</th><th>予約日</th><th>レンジ予約</th><th>レンタルクラブ</th><th>シャワー利用</th><th>変更・キャンセル</th></tr>
      </thead>
      <tbody>
        <?php if (empty($reservations)): ?>
          <td colspan="6">変更可能なご予約はございません</td>
        <?php else:?>
          <?php foreach($reservations as $reserve): ?>
            <tr>
              <td><?= h($reserve['id']);?></td>
              <td><?= h($reserve['reserve_date']);?></td>
              <td>
                <div>
                  <p><?= $reserve['range_name'];?></p>
                  <p><?= $reserve['start_time'] . 'ー' . $reserve['end_time'];?></p>
                </div>
              </td>
              <td>
                <?php if($reserve['brand'] !== null && $reserve['model'] !== null):?>
                  <div>
                    <p><?= $reserve['brand'];?></p>
                    <p><?= $reserve['model'];?></p>
                  </div>
                <?php else: ?>
                  <?= '利用しない';?>
                <?php endif;?>
              </td>
              <td><?= $reserve['shower_time'] ?? '利用しない';?></td>
              <td>
                <?php if($reserve['reserve_date'] === $today && $reserve['start_time'] <= $expired):?>
                  <span>変更できません</span>
                <?php else:?>
                  <form action="<?= url("/reservation/{$reserve['id']}/edit");?>" method="get">
                    <button class="form-btn">変更する</button>
                  </form>
                  <form action="<?= url("/reservation/{$reserve['id']}/delete");?>" method="post">
                    <input type="hidden" name="token" value="<?= h($csrfToken);?>">
                    <button type="button" class="form-btn cancel" data-reserve-id="<?= h($reserve['id']);?>">
                      キャンセル
                    </button>
                  </form>
                <?php endif;?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif;?>
      </tbody>
    </table>
  </div>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>