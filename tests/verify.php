<?php
declare(strict_types=1);

/**
 * think-poster 适配层验证脚本
 *
 * 运行：php tests/verify.php
 * 需要先执行 composer install。
 */

use Kkokk\Poster\Cache\CacheRepository;
use Kkokk\Poster\Html\HtmlManager;
use think\App;
use Youvision\ThinkPoster\Captcha\CaptchaManager;
use Youvision\ThinkPoster\Facade\Cache;
use Youvision\ThinkPoster\Facade\Poster;
use Youvision\ThinkPoster\Image\PosterManager;
use Youvision\ThinkPoster\Service;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'think-poster-verify-' . uniqid();

function verify(bool $condition, string $message): void
{
    if ($condition) {
        echo "[OK] {$message}\n";
        return;
    }

    fwrite(STDERR, "[FAIL] {$message}\n");
    exit(1);
}

function removeDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($directory);
}

@mkdir($work, 0777, true);

$app = new App($root);

// 缓存配置必须在 initialize() 之前设置，框架初始化阶段的 ModelService 会读取缓存。
$app->config->set([
    'default' => 'file',
    'stores'  => [
        'file' => [
            'type' => 'file',
            'path' => $work . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
        ],
    ],
], 'cache');

$app->initialize();

$app->config->set([
    'save_path' => $work . DIRECTORY_SEPARATOR . 'poster',
    'cache'    => ['namespace' => 'verify'],
], 'poster');

$app->register(Service::class);
$app->boot();

// 配置与服务注册
verify(config('poster.driver') === 'gd', 'poster.driver 默认值已合并');
verify($_SERVER['DOCUMENT_ROOT'] === rtrim($root . DIRECTORY_SEPARATOR . 'public', '/\\'), 'CLI 下已补齐 DOCUMENT_ROOT');
verify(app('poster') instanceof PosterManager, 'poster 容器绑定为适配层 PosterManager');
verify(app('poster.cache') instanceof CacheRepository, 'poster.cache 容器绑定为 CacheRepository');
verify(app('poster.captcha') instanceof CaptchaManager, 'poster.captcha 容器绑定为适配层 CaptchaManager');
verify(app('poster.html') instanceof HtmlManager, 'poster.html 容器绑定为 HtmlManager');
verify(poster()->supportedExtensions()[0] === 'gd', '默认图片驱动与配置一致');

// 路径助手
$expectedPublic = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
verify(poster_path() === $expectedPublic, 'poster_path() 返回 public 目录');
verify(
    poster_path('uploads/a.png') === $expectedPublic . 'uploads' . DIRECTORY_SEPARATOR . 'a.png',
    'poster_path() 解析相对路径'
);
verify(
    poster_path('/uploads/a.png') === $expectedPublic . 'uploads' . DIRECTORY_SEPARATOR . 'a.png',
    'poster_path() 把“/”开头的路径解析到 public 目录'
);
verify(poster_path($work) === $work, 'poster_path() 保留绝对路径');

// 缓存适配
poster_cache()->put('probe', 'value', 60);
verify(poster_cache()->get('probe') === 'value', 'ThinkCacheAdapter 写入与读取');
verify(poster_cache()->has('probe') === true, 'ThinkCacheAdapter has()');
poster_cache()->forget('probe');
verify(poster_cache()->has('probe') === false, 'ThinkCacheAdapter forget()');
Cache::put('facade', 'ok', 60);
verify(Cache::get('facade') === 'ok', 'Cache 门面可用');
Cache::forget('facade');

// 海报生成
$result = Poster::buildIm(240, 160, [255, 255, 255])
    ->buildText('ThinkPoster', 20, 80, 28, [0, 0, 0], 200)
    ->getPoster();

$output = $work . DIRECTORY_SEPARATOR . 'poster' . DIRECTORY_SEPARATOR . 'poster.png';
verify(
    isset($result['url']) && is_file($output) && filesize($output) > 0,
    '海报生成并写入 save_path'
);

// 二维码
$qrPath = $work . DIRECTORY_SEPARATOR . 'qrcode.png';
$qr = Poster::Qr('https://www.thinkphp.cn', $qrPath);
verify(is_file($qrPath) && filesize($qrPath) > 0, '二维码生成并写入指定路径');

// 验证码生成与校验
$captcha = poster_captcha()->type('input');
$data = $captcha->get();
verify(is_array($data) && isset($data['key'], $data['img']), '输入验证码生成返回 key 与 img');
verify(str_starts_with($data['img'], 'data:image/png'), '验证码图片为 PNG base64');
verify(is_string($data['img']) && strlen($data['img']) > 100, '验证码图片内容非空');

$answer = poster_cache()->get($data['key']);
verify($answer !== null && $answer !== false, '验证码答案已写入 ThinkPHP 缓存');
verify($captcha->check($data['key'], (string) $answer), '验证码校验通过');
verify(!$captcha->check($data['key'], (string) $answer), '验证码答案一次性消费');

// HTML 管理器可用（未安装 wkhtmltoimage 时不执行转换）
verify(poster_html()->supportedChannels() === ['wk'], 'HTML 通道配置可用');

removeDirectory($work);

echo "\n全部验证通过。\n";