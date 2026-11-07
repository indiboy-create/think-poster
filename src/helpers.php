<?php
declare(strict_types=1);

use Kkokk\Poster\Cache\CacheRepository;
use Kkokk\Poster\Html\HtmlManager;
use Youvision\ThinkPoster\Captcha\CaptchaManager;
use Youvision\ThinkPoster\Image\PosterManager;
use Youvision\ThinkPoster\Support\Path;

if (!function_exists('poster')) {
    /**
     * 获取海报管理器
     */
    function poster(): PosterManager
    {
        return app('poster');
    }
}

if (!function_exists('poster_captcha')) {
    /**
     * 获取验证码管理器
     */
    function poster_captcha(): CaptchaManager
    {
        return app('poster.captcha');
    }
}

if (!function_exists('poster_html')) {
    /**
     * 获取 HTML 转图片管理器
     */
    function poster_html(): HtmlManager
    {
        return app('poster.html');
    }
}

if (!function_exists('poster_cache')) {
    /**
     * 获取海报缓存仓库
     */
    function poster_cache(): CacheRepository
    {
        return app('poster.cache');
    }
}

if (!function_exists('poster_path')) {
    /**
     * 把相对路径解析为 public 目录下的绝对路径
     *
     * 绝对路径、协议地址与 Phar 地址会原样返回。
     */
    function poster_path(string $path = ''): string
    {
        return Path::publicPath($path);
    }
}