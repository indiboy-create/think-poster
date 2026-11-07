<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Captcha;

use Kkokk\Poster\Cache\AbstractCacheAdapter;
use Kkokk\Poster\Captcha\CaptchaManager as BaseCaptchaManager;
use Kkokk\Poster\Captcha\Strategies\CaptchaStrategyInterface;
use Kkokk\Poster\Captcha\Strategies\Click\ClickCaptcha;
use Kkokk\Poster\Captcha\Strategies\Input\InputCaptcha;
use Kkokk\Poster\Captcha\Strategies\Rotate\RotateCaptcha;
use Kkokk\Poster\Captcha\Strategies\Slider\SliderCaptcha;

/**
 * 验证码管理器
 *
 * 在 kkokk/poster 的 CaptchaManager 基础上注入 ThinkPHP 配置：
 * - driver：默认 gd 或 imagick
 * - common：所有验证码类型共用的策略配置
 * - slider/click/rotate/input：按验证码类型分组的策略配置
 */
class CaptchaManager extends BaseCaptchaManager
{
    /**
     * @var array 验证码配置，来自 config/poster.php 的 captcha 节点
     */
    protected $options = [];

    public function __construct(array $options = [], ?AbstractCacheAdapter $cacheAdapter = null)
    {
        parent::__construct();

        $this->options = $options;
        $driver = (string) ($options['driver'] ?? '');
        $this->defaultDriver = in_array($driver, ['gd', 'imagick'], true) ? $driver : 'gd';

        if ($cacheAdapter !== null) {
            $this->setCache($cacheAdapter);
        }
    }

    /**
     * 为新建的验证码策略注入配置
     */
    protected function configure(CaptchaStrategyInterface $generator)
    {
        $common = $this->options['common'] ?? [];
        $channel = $this->resolveChannelName($generator);
        $configs = $this->options[$channel] ?? [];

        if (is_array($common) && $common !== []) {
            $generator->config($common);
        }

        if (is_array($configs) && $configs !== []) {
            $generator->config($configs);
        }

        return $generator;
    }

    /**
     * 根据策略实例解析对应的配置节点名称
     */
    protected function resolveChannelName(CaptchaStrategyInterface $generator): string
    {
        return match (true) {
            $generator instanceof SliderCaptcha => 'slider',
            $generator instanceof ClickCaptcha  => 'click',
            $generator instanceof RotateCaptcha => 'rotate',
            $generator instanceof InputCaptcha  => 'input',
            default => strtolower(str_replace('Captcha', '', substr(strrchr(get_class($generator), '\\') ?: '', 1))),
        };
    }
}