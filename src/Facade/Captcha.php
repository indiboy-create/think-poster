<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Facade;

use think\Facade;

/**
 * 验证码门面
 *
 * @method static \Youvision\ThinkPoster\Captcha\CaptchaManager extension($driver = null)
 * @method static \Youvision\ThinkPoster\Captcha\CaptchaManager setCache(\Kkokk\Poster\Cache\AbstractCacheAdapter $cacheAdapter)
 * @method static \Kkokk\Poster\Captcha\Strategies\CaptchaStrategyInterface type($name = null)
 * @method static \Kkokk\Poster\Captcha\CaptchaGeneratorInterface config($params = [])
 * @method static bool check($key, $value, $leeway = 0, $secret = null)
 * @method static array get($expire = 0)
 *
 * @see \Youvision\ThinkPoster\Captcha\CaptchaManager
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return 'poster.captcha';
    }
}