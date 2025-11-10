<?php include APP_PATH  . '/Views/_common/head-start.php';?>
<?php include APP_PATH  . '/Views/_common/owner-header.php';?>
<?php include APP_PATH  . '/Views/owner/components/side-menu.php';?>
<main>
  <?php
    match ($currentUri) {
      '/owner/create/rangeFee' => include APP_PATH  . '/Views/owner/components/range-fee.php',
      '/owner/create/option/rentalFee' => include APP_PATH  . '/Views/owner/components/rental-fee.php',
      '/owner/create/option/showerFee' => include APP_PATH  . '/Views/owner/components/shower-fee.php',
      '/owner/mainte' => include APP_PATH  . '/Views/owner/components/mainte.php',
    }
  ?>
</main>
<?php include APP_PATH  . '/Views/_common/head-end.php';?>