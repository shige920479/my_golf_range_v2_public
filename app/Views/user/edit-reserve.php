<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/header.php';?>

<main>
  <section class="site-visual reserve">
    <img src="<?= url("images/login_back.jpg");?>" alt="" />
    <p class="site-title">ご予約内容の変更</p>
  </section>
  <section class="wrapper mb">
    <div id="reserve-flex">
      <div id="reserve-input">
        <p class="area-title">変更情報入力エリア</p>
        <form action="<?= url("/reservation/" . h($reservation['id']) . "/update") ;?>" method="post">
          <input type="hidden" name="token" value="<?= h($csrfToken);?>">
          <input type="hidden" name="method" value="update">
          <div id="input-area">
            <div id="range">
              <p class="select-title">ドライビングレンジ選択</p>
              <select name="range_id">
                <option value="">選択してください</option>
                <?php $oldRangeId = $session->flash('old.range_id')
                                    ?? $temporary['range_id']
                                    ?? $reservation['range_id'];
                ?>
                <?php foreach($form['range'] as $range) :?>
                  <option value="<?= h($range['id']);?>" <?= $range['id'] === (int)$oldRangeId ? 'selected' : ''; ?>>
                    <?= $range['name'] ?? '';?>
                  </option>
                <?php endforeach;?>
              </select>
                <small><?= $session->flash('errors.range_id'); ?></small>
            </div>
            <div id="number">
              <p class="select-title">ご利用人数</p>
              <select name="number">
                <option value="">選択してください</option>
                <?php $oldNumber = $session->flash('old.number')
                                   ?? $temporary['number']
                                   ?? $reservation['number'];
                ?>
                <option value="1" <?= (int)$oldNumber === 1 ? 'selected' : ''; ?>>1名様</option>
                <option value="2" <?= (int)$oldNumber === 2 ? 'selected' : ''; ?>>2名様</option>
              </select>
                <small><?= $session->flash('errors.number'); ?></small>
            </div>
            <div id="reserve-date">
              <p class="select-title">ご利用日時</p>
              <p>ご利用日</p>
              <select name="reserve_date">
                <option value="">選択してください</option>
                <?php $oldDate = $session->flash('old.reserve_date')
                                  ?? $temporary['reserve_date']
                                  ?? $reservation['reserve_date'];
                ?>
                <?php foreach($reservableDates as $date): ?>
                  <option value="<?= h($date['value']); ?>" <?= $date['value'] === $oldDate ? 'selected' : ''; ?>>
                    <?= $date['label'];?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small><?= $session->flash('errors.reserve_date');?></small>
              <div id="time-flex">
                <div>
                  <p>ご利用開始時間</p>
                  <select name="start_time" id="start-time">
                    <option value="">選択してください</option>
                    <?php $oldStart = $session->flash('old.start_time')
                                      ?? $temporary['start_time']
                                      ?? $reservation['start_time'];
                    ?>
                    <?php foreach($startTimes as $startTime):?>
                      <option value="<?= h($startTime['value']);?>" <?= $startTime['value'] == $oldStart ? 'selected' : ''; ?>>
                        <?= $startTime['label'];?>
                      </option>
                    <?php endforeach;?>
                  </select>
                </div>
                <div>
                  <p>ご利用終了時間</p>
                  <select name="end_time" id="end-time">
                    <option value="">選択してください</option>
                    <?php $oldEnd = $session->flash('old.end_time')
                                    ?? $temporary['end_time']
                                    ?? $reservation['end_time'];
                    ?>
                    <?php foreach($endTimes as $endTime):?>
                      <option value="<?= h($endTime['value']);?>" <?= $endTime['value'] == $oldEnd ? 'selected' : ''; ?>>
                        <?= $endTime['label'];?>
                      </option>
                    <?php endforeach;?>
                  </select>
                </div>
              </div>
              <small><?= $session->flash('errors.start_time') ?? $session->flash('errors.end_time')?></small>
            </div>
            <div id="option">
              <p class="select-title">レンタルオプション</p>
              <div>
                <?php $oldRental = $session->flash('old.rental')
                                    ?? $temporary['rental']
                                    ?? ($reservation['rental_id'] !== null ? 1 : null);
                ?>
                <input type="checkbox" name="rental" class="check-box" value="1" id="rental"
                <?=  (int)$oldRental === 1 ? 'checked' : ''; ?> />
                <label for="rental">利用する</label>
              </div>
              <small><?= $session->flash('errors.rental'); ?></small>
              <ul>
                <li>
                  <p>クラブ</p>
                  <select id="club-select" name="rental_id">
                    <option value="">選択してください</option>
                      <?php $oldRentalId = $session->flash('old.rental_id')
                                            ?? $temporary['rental_id']
                                            ?? $reservation['rental_id'];
                      ?>
                      <?php foreach($rentals as $rental): ?>
                        <option data-model="<?= $rental['model'] ?>" class="club" value="<?= h($rental['id']);?>"
                          <?= (int)$oldRentalId === $rental['id'] ? 'selected': '';?> class="club">
                          <?= $rental['brand'];?>
                        </option>
                      <?php endforeach;?>
                  </select>
                </li>
                <li>
                  <p>モデル</p>
                  <p id="model-display"></p>
                </li>
              </ul>
              <small><?= $session->flash('errors.rental_id') ;?></small>
            </div>
            <div id="shower-room">
              <p class="select-title">シャワールーム利用</p>
              <div>
                <?php $oldShower = $session->flash('old.shower')
                                    ?? $temporary['shower']
                                    ?? ($reservation['shower_time'] !== null ? 1 : null);
                ?>
                <input type="checkbox" name="shower" class="check-box" value="1" id="shower"
                <?= (int)$oldShower === 1 ? 'checked' : ''; ?>/>
                <label for="shower">利用する</label>
              </div>
              <small><?= $session->flash('errors.shower'); ?></small>
              <div>
                <p>ご利用開始時間（ご利用時間25分）</p>
                <select name="shower_time" id="shower-time">
                  <option value="">選択してください</option>
                  <?php $oldShowerTime = $session->flash('old.shower_time')
                                          ?? $temporary['shower_time']
                                          ?? $reservation['shower_time']
                  ?>
                  <?php foreach($startTimes as $startTime):?>
                    <option value="<?= h($startTime['value']);?>" <?= $startTime['value'] === $oldShowerTime ? 'selected' : ''; ?>>
                      <?= $startTime['label'];?>
                    </option>
                  <?php endforeach;?>
                </select>
                <label for="shower-time"></label>
              </div>
              <small><?= $session->flash('errors.shower_time');?></small>
            </div>
            <div>
              <button type="submit" class="form-btn update">予約内容を変更する</button>
            </div>
          </div>
          <input type="hidden" name="back_url" value="<?= h($backUrl); ?>">
        </form>
      </div>
      <div id="reserve-info">
        <p class="area-title">情報確認エリア</p>
        <div id="information-area">
          <div class="date-weather-flex">
            <div class="search-date">
              <p class="select-title">空き状況確認</p>
              <form action="<?= url('/reservation/' . h($reservation['id']) . '/edit') ?>" method="get">
                <select name="search_date">
                  <?php foreach($reservableDates as $date):?>
                    <option value="<?= $date['value'];?>" <?= $date['value'] === $searchDate ? 'selected' : '';?>>
                      <?= $date['label'] ;?>
                    </option>
                  <?php endforeach;?>
                </select>
                <input type="hidden" name="mode" value="reservation">
                <button type="submit" class="form-btn">検索</button>
              </form>
            </div>
            <div class="weather">
              <?php include(APP_PATH . '/Views/user/components/weather.php') ?>
            </div>
          </div>
          <?php include(APP_PATH . '/Views/user/components/time-table.php');?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>