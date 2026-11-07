<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster;

use Kkokk\Poster\Cache\CacheRepository;
use Kkokk\Poster\Cache\NamespacedCacheAdapter;
use Kkokk\Poster\Html\HtmlManager;
use Kkokk\Poster\Image\PosterManager as BasePosterManager;
use think\Service as ThinkService;
use Youvision\ThinkPoster\Cache\ThinkCacheAdapter;
use Youvision\ThinkPoster\Captcha\CaptchaManager;
use Youvision\ThinkPoster\Image\PosterManager;
use Youvision\ThinkPoster\Support\Path;

/**
 * ThinkPHP 8 服务注册
 *
 * composer.json 通过 extra.think.services 自动发现本服务，
 * 负责合并默认配置、把 kkokk/poster 的各管理器注册到容器，
 * 并把 ThinkPHP 缓存接入验证码模块。
 */
class Service extends ThinkService
{
    public function register(): void
    {
        $default = require dirname(__DIR__) . '/config/poster.php';
        $current = (array) $this->app->config->get('poster', []);
        $config = array_replace_recursive($default, $current);

        $this->app->config->set($config, 'poster');

        $this->configureDocumentRoot($config);
        $this->configureHtmlBinary($config);

        $this->registerCache($config);
        $this->registerPoster($config);
        $this->registerCaptcha($config);
        $this->registerHtml();
    }

    public function boot(): void
    {
    }

    /**
     * 注册海报管理器
     */
    protected function registerPoster(array $config): void
    {
        $this->app->bind('poster', function () use ($config) {
            $savePath = $this->resolveSavePath($config);
            $driver = (string) ($config['driver'] ?? 'gd');

            return new PosterManager($savePath . 'poster.png', $driver);
        });

        $this->app->bind(PosterManager::class, fn () => $this->app->make('poster'));
        $this->app->bind(BasePosterManager::class, fn () => $this->app->make('poster'));
    }

    /**
     * 注册缓存仓库与 ThinkPHP 缓存适配器
     */
    protected function registerCache(array $config): void
    {
        $this->app->bind('poster.cache.adapter', function () use ($config) {
            $adapter = new ThinkCacheAdapter($this->app->make('cache'));
            $namespace = (string) ($config['cache']['namespace'] ?? '');

            if ($namespace !== '') {
                $adapter = new NamespacedCacheAdapter($adapter, $namespace);
            }

            return $adapter;
        });

        $this->app->bind('poster.cache', function () {
            return new CacheRepository($this->app->make('poster.cache.adapter'));
        });

        $this->app->bind(CacheRepository::class, fn () => $this->app->make('poster.cache'));
    }

    /**
     * 注册验证码管理器
     */
    protected function registerCaptcha(array $config): void
    {
        $this->app->bind('poster.captcha', function () use ($config) {
            $captcha = (array) ($config['captcha'] ?? []);
            $driver = (string) ($captcha['driver'] ?? '');
            $driver = $driver !== '' ? $driver : (string) ($config['driver'] ?? 'gd');

            $manager = new CaptchaManager($captcha, $this->app->make('poster.cache.adapter'));
            $manager->extension($driver);

            return $manager;
        });

        $this->app->bind(CaptchaManager::class, fn () => $this->app->make('poster.captcha'));
    }

    /**
     * 注册 HTML 转图片管理器
     */
    protected function registerHtml(): void
    {
        $this->app->bind('poster.html', fn () => new HtmlManager());
        $this->app->bind(HtmlManager::class, fn () => $this->app->make('poster.html'));
    }

    /**
     * 计算默认输出目录
     */
    protected function resolveSavePath(array $config): string
    {
        $path = trim((string) ($config['save_path'] ?? ''));

        if ($path === '') {
            return $this->app->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'poster' . DIRECTORY_SEPARATOR;
        }

        if (!Path::isAbsolute($path)) {
            $path = $this->app->getRootPath() . ltrim($path, '/\\');
        }

        return Path::directory($path);
    }

    /**
     * 兼容 kkokk/poster 对 $_SERVER['DOCUMENT_ROOT'] 的依赖
     *
     * 上游用该值拼接相对输出路径，CLI、队列与常驻进程中它通常不存在，
     * 此时回退到项目的 public 目录。Web 请求中已有的值保持不变。
     */
    protected function configureDocumentRoot(array $config): void
    {
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            return;
        }

        $root = trim((string) ($config['document_root'] ?? ''));

        if ($root === '') {
            $_SERVER['DOCUMENT_ROOT'] = rtrim($this->app->getRootPath() . 'public', '/\\');
            return;
        }

        if (!Path::isAbsolute($root)) {
            $root = $this->app->getRootPath() . ltrim($root, '/\\');
        }

        $_SERVER['DOCUMENT_ROOT'] = rtrim(Path::separator($root), '/\\');
    }

    /**
     * 把 wkhtmltoimage / wkhtmltopdf 所在目录加入当前进程 PATH
     */
    protected function configureHtmlBinary(array $config): void
    {
        $path = trim((string) ($config['html']['binary_path'] ?? ''));

        if ($path === '') {
            return;
        }

        if (!Path::isAbsolute($path)) {
            $path = $this->app->getRootPath() . ltrim($path, '/\\');
        }

        $path = rtrim(Path::separator($path), DIRECTORY_SEPARATOR);
        $current = getenv('PATH') ?: '';

        if (stripos($current, $path) === false) {
            putenv('PATH=' . $path . PATH_SEPARATOR . $current);
        }
    }
}