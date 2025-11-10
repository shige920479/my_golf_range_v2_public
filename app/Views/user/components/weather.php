<?php if(!empty($weather)): ?>
  <?php foreach($weather as $data):?>
    <div class="weather-box">
      <div class="time sm"><?= \Carbon\Carbon::parse($data['dt'])->format('H:i');?></div>
      <div class="icon"><img src="<?= 'https://openweathermap.org/img/wn/' . $data['weather'][0]['icon'] . '@2x.png';?>" alt="" /></div>
      <div class="temp_max sm"><?= round($data['main']['temp']) . '℃';?></div>
    </div>
  <?php endforeach;?>
<?php endif;?>

