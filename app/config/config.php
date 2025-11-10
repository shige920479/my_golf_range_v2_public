<?php
if (! defined('APP_ENV')) define('APP_ENV', $_ENV["APP_ENV"]);
if (! defined('APP_PATH')) define('APP_PATH', BASE_PATH . '/app');

if (APP_ENV === 'production') {
  if (! defined('BASE_URL')) define('BASE_URL', '/my_golf_range/');
} else {
  if (! defined('BASE_URL')) define('BASE_URL', '/');
}

// DB接続設定
if (! defined('DB_CONNECTION')) define('DB_CONNECTION', $_ENV["DB_CONNECTION"] ?? 'mysql');

if (DB_CONNECTION === 'sqlite') {
  // SQLite用
  if (! defined('DB_NAME')) define('DB_NAME', $_ENV["DB_DATABASE"] ?? ':memory:');
  if (! defined('DB_USER')) define('DB_USER', null);
  if (! defined('DB_PASS')) define('DB_PASS', null);
  if (! defined('DB_HOST')) define('DB_HOST', null); // SQLiteは不要
} else {
  // MySQL用
  if (! defined('DB_HOST')) define('DB_HOST', $_ENV["DB_HOST"]);
  if (! defined('DB_NAME')) define('DB_NAME', $_ENV["MYSQL_DATABASE"]);
  if (! defined('DB_USER')) define('DB_USER', $_ENV["MYSQL_USER"]);
  if (! defined('DB_PASS')) define('DB_PASS', $_ENV["MYSQL_PASSWORD"]);
}

if (! defined('MAX_RESERVE_DATE')) define('MAX_RESERVE_DATE', 21);
if (! defined('OPEN_TIME')) define('OPEN_TIME', '08:00');
if (! defined('CLOSE_TIME')) define('CLOSE_TIME', '22:00');

if (! defined('DEMO_RESET_ENABLED')) define('DEMO_RESET_ENABLED', $_ENV["DEMO_RESET_ENABLED"] ?? 0);

if(! defined('WEATHER_API_KEY') ) define('WEATHER_API_KEY', $_ENV['WEATHER_API_KEY']);
if(! defined('WEATHER_CACHE_EXPIRE') ) define('WEATHER_CACHE_EXPIRE', $_ENV['WEATHER_CACHE_EXPIRE']);
if(! defined('WEATHER_CACHE_CLEANUP_DAYS') ) define('WEATHER_CACHE_CLEANUP_DAYS', $_ENV['WEATHER_CACHE_CLEANUP_DAYS']);