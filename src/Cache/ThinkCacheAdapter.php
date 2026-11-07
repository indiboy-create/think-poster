<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Cache;

use Kkokk\Poster\Cache\AbstractCacheAdapter;

/**
 * ThinkPHP 缓存适配器
 *
 * 把 think\Cache 实例适配为 kkokk/poster 的缓存契约，
 * 供验证码答案存取以及 Facade 缓存调用使用。
 *
 * 上游虽然内置了 think\facade\Cache 的兼容分支，但该分支依赖
 * class_exists 静态判断，且 Laravel 项目同时存在时会优先使用 Laravel。
 * 这里显式注入 ThinkPHP 缓存实例，避免运行环境差异导致取错后端。
 */
class ThinkCacheAdapter extends AbstractCacheAdapter
{
    /**
     * @var \think\Cache
     */
    protected $cache;

    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function pull($key, $default = null)
    {
        // think\Cache 会把 pull 转发到当前缓存驱动，保持驱动的原子语义。
        return $this->cache->pull($key, $default);
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function forget($key)
    {
        return $this->cache->delete($key);
    }

    public function flush()
    {
        // 只清空当前缓存驱动，不影响其他缓存配置。
        return $this->cache->clear();
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }
}