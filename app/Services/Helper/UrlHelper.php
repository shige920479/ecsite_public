<?php
namespace App\Services\Helper;

class UrlHelper
{
  public static function sanitizeHeaderValue(string $value): string
  {
    return (str_replace(["\r", "\n"], '', $value));
  }

  public static function isRelative(string $url):bool
  {
    $parseUrl = parse_url($url);
    return $parseUrl !== false && ! isset($parseUrl['scheme']) && ! isset($parseUrl['host']);
  }

  public static function toAbsolute(string $path): string
  {
    if(str_starts_with($path, PATH)) {
      return self::sanitizeHeaderValue($path);
    }
    $joined = rtrim(PATH, '/') . '/' . ltrim($path, '/');
    return self::sanitizeHeaderValue($joined);
  }

  public static function isSameOrigin(string $url)
  {
    $parseUrl = parse_url($url);
    if($parseUrl === false) return false;

    $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SCRIPT_NAME'] ?? '');

    if(! isset($parseUrl['host'])) return true;

    $urlScheme = $parseUrl['scheme'] ?? $scheme;
    $urlHost = $parseUrl['host'] ?? $host;
    $urlPort = $parseUrl['port'] ?? null;

    $currentPort = $_SERVER['SERVER_PORT'] ?? null;
    $defPort = ($scheme === 'https') ? 443 : 80;
    if((int)$currentPort === $defPort) $currentPort = null;

    return ($urlScheme === $scheme) && ($urlHost === $host) &&
            (($urlPort ?? $currentPort) === $currentPort);
  }
}