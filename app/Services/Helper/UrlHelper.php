<?php
namespace App\Services\Helper;

class UrlHelper
{
    /**
     * ヘッダーに使う値を安全化する
     * 改行や制御文字を削除し、ヘッダーインジェクションを防ぐ
     */
    public static function sanitizeHeaderValue(string $value): string
    {
        return preg_replace('/[\x00-\x1F\x7F]+/u', '', $value) ?? '';
    }

    /**
     * 同一オリジン（同じスキーム・ホスト・ポート）か判定
     * 外部サイトへのリダイレクトを禁止するために利用
     */
    public static function isSameOrigin(string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false) return false;

        $currentScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $currentHost   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');

        $urlScheme = $parts['scheme'] ?? $currentScheme;
        $urlHost   = $parts['host'] ?? $currentHost;

        return ($urlScheme === $currentScheme)
            && (strcasecmp($urlHost, $currentHost) === 0); // 大文字小文字は無視
    }
}