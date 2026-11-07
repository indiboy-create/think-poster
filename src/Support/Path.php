<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Support;

/**
 * 路径解析工具
 *
 * kkokk/poster 对相对路径的处理依赖 $_SERVER['DOCUMENT_ROOT']，
 * 在 ThinkPHP 8 的 CLI、队列、常驻进程中该值可能不存在或不符合项目目录结构，
 * 因此适配层统一在出入口把相对路径解析为绝对路径。
 */
class Path
{
    /**
     * 判断路径是否为绝对路径、协议地址或 Phar 地址
     */
    public static function isAbsolute(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, 'phar://') || str_starts_with($path, 'data:')) {
            return true;
        }

        return (bool) preg_match('~^(?:[a-zA-Z]:[\\\\/]|/|\\\\|[a-z][a-z0-9+.\-]*://)~i', $path);
    }

    /**
     * 统一为当前系统的目录分隔符
     */
    public static function separator(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * 在指定基准目录下解析路径
     *
     * @param string $path 待解析路径，绝对路径或协议地址会原样返回
     * @param string $base 基准目录，留空时使用项目 public 目录
     */
    public static function resolve(string $path, string $base = ''): string
    {
        if ($path === '' || self::isAbsolute($path)) {
            return $path;
        }

        $base = $base !== ''
            ? rtrim(self::separator($base), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            : self::publicPath();

        return $base . ltrim(self::separator($path), DIRECTORY_SEPARATOR);
    }

    /**
     * 判断是否为带盘符、UNC、协议或 Phar 的路径
     *
     * 与 isAbsolute() 不同，本方法不把以单个“/”开头的路径视为磁盘绝对路径，
     * 便于 poster_path() 按 ThinkPHP 习惯把“/uploads/a.png”解析到 public 目录。
     */
    public static function isVfs(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, 'phar://') || str_starts_with($path, 'data:')) {
            return true;
        }

        if (preg_match('~^[a-zA-Z]:[\\\\/]~', $path)) {
            return true;
        }

        if (str_starts_with($path, '\\\\') || str_starts_with($path, '//')) {
            return true;
        }

        return (bool) preg_match('~^[a-z][a-z0-9+.\-]*://~i', $path);
    }

    /**
     * 获取项目 public 目录下的路径
     */
    public static function publicPath(string $path = ''): string
    {
        $base = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR;

        if ($path === '') {
            return $base;
        }

        if (self::isVfs($path)) {
            return $path;
        }

        return $base . ltrim(self::separator($path), DIRECTORY_SEPARATOR);
    }

    /**
     * 把以分隔符结尾的目录路径统一为“目录 + 分隔符”的形式
     */
    public static function directory(string $path): string
    {
        return rtrim(self::separator($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}