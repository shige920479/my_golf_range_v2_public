<?php
use App\Services\Helper\UrlHelper;

function isProduction(): bool
{
  return (APP_ENV ?? 'local') === 'production';
}

function url(string $path = ''): string
{
  return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function normalizeUri(string $uri): string
{
  if(BASE_URL !== '/' && str_starts_with($uri, BASE_URL)) {
    return '/' . ltrim(substr($uri, strlen(BASE_URL)), '/');
  }
  return $uri;
}

function h(?string $string): string
{
  return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * リダイレクト処理
 * @param string $path   リダイレクト先（絶対URL）
 * @param int $status    初期値は302
 */
function redirect(string $absoluteUrl, int $status = 302): void
{
  if (! UrlHelper::isSameOrigin($absoluteUrl)) {
    $absoluteUrl = url('/');
  }

  $target = UrlHelper::sanitizeHeaderValue($absoluteUrl);

  header('Location: ' . $target, true, $status);
  exit;
}

function only(array $keys, array $array) :array
{
  return array_intersect_key($array, array_flip($keys));
}

function array_fill_keys_with_null(array $keys): array
{
  return array_fill_keys($keys, null);
}
