<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Facade;

use think\Facade;

/**
 * 缓存门面
 *
 * @method static \Kkokk\Poster\Cache\CacheRepository setAdapter(\Kkokk\Poster\Cache\AbstractCacheAdapter $adapter)
 * @method static mixed get($key, $default = null)
 * @method static mixed put($key, $value, $ttl = null)
 * @method static mixed pull($key, $default = null)
 * @method static bool has($key)
 * @method static bool forget($key)
 * @method static bool flush()
 *
 * @see \Kkokk\Poster\Cache\CacheRepository
 */
class Cache extends Facade
{
    protected static function getFacadeClass()
    {
        return 'poster.cache';
    }
}