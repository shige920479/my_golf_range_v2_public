<?php

use App\Controller\HomeController;
use App\Controller\Owner\AuthController as OwnerAuthController;
use App\Controller\Owner\InitRegisterController;
use App\Controller\Owner\SettingController;
use App\Controller\SessionDestroyController;
use App\Controller\User\AuthController;
use App\Controller\User\CancelReservationController;
use App\Controller\User\CreateReservationController;
use App\Controller\User\MypageController;
use App\Controller\User\RegisterController;
use App\Controller\User\UpdateReservationController;

$router->get('/', HomeController::class, 'index');
$router->get('/price', HomeController::class, 'showPriceList');

// ユーザー仮登録
$router->get('/register/temporary', RegisterController::class, 'showTemporayForm');
$router->post('/register/temporary', RegisterController::class, 'storeTemporary');
$router->get('/register/verify', RegisterController::class, 'showVerifyForm');
// ユーザー本登録
$router->get('/register/profile', RegisterController::class, 'showProfileForm');
$router->post('/register/profile', RegisterController::class, 'storeProfile');
$router->get('/register/confirm', RegisterController::class, 'showConfirm');
$router->post('/register/confirm', RegisterController::class, 'submit');
$router->get('/register/complete', RegisterController::class, 'complete');
// ユーザー認証
$router->get('/login', AuthController::class, 'loginForm');
$router->post('/login', AuthController::class, 'login');
$router->post('/logout', AuthController::class, 'logout');

//新規予約画面
$router->get('/reservation', CreateReservationController::class, 'index');
$router->post('/reservation', CreateReservationController::class, 'send');
$router->get('/reservation/confirm', CreateReservationController::class, 'confirm');
$router->post('/reservation/confirm', CreateReservationController::class, 'store');
$router->get('/reservation/complete', CreateReservationController::class, 'complete');

//予約変更
$router->get('/reservation/{id}/edit', UpdateReservationController::class, 'edit');
$router->post('/reservation/{id}/update', UpdateReservationController::class, 'update');
$router->get('/reservation/{id}/confirm', UpdateReservationController::class, 'updateConfirm');
$router->post('/reservation/{id}/confirm', UpdateReservationController::class, 'updateStore');
$router->get('/reservation/updateComplete', UpdateReservationController::class, 'updateComplete');

//予約キャンセル
$router->post('/reservation/{id}/delete', CancelReservationController::class, 'delete');

//Mypage
$router->get('/mypage', MypageController::class, 'index');

//セッション（予約）情報を破棄
$router->get('/session_destroy', SessionDestroyController::class, 'destroy');

//オーナー認証
$router->get('/owner/login', OwnerAuthController::class, 'loginForm');
$router->post('/owner/login', OwnerAuthController::class, 'login');
$router->post('/owner/logout', OwnerAuthController::class, 'logout');

//オーナー設定
$router->get('/owner/index', SettingController::class, 'index');
$router->get('/owner/create/rangeFee', SettingController::class, 'createRangeFee');
$router->post('/owner/create/rangeFee', SettingController::class, 'storeRangeFee');
$router->get('/owner/create/option/{table}', SettingController::class, 'createOptionFee');
$router->post('/owner/create/option/{table}', SettingController::class, 'storeOptionFee');
$router->get('/owner/mainte/{table}/{id}', SettingController::class, 'editMainte');
$router->post('/owner/mainte/{table}/{id}', SettingController::class, 'updateMainte');

$router->get('/owner/initial', InitRegisterController::class, 'create');
$router->post('/owner/initial/drivingRange', InitRegisterController::class, 'storeRange');
$router->post('/owner/initial/rangeFee', InitRegisterController::class, 'storeRangeFee');
$router->post('/owner/initial/rental', InitRegisterController::class, 'storeRental');
$router->post('/owner/initial/rentalFee', InitRegisterController::class, 'storeRentalFee');
$router->post('/owner/initial/showerFee', InitRegisterController::class, 'storeShowerFee');