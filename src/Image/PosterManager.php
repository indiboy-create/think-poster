<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Image;

use Kkokk\Poster\Image\PosterManager as BasePosterManager;

/**
 * 海报管理器
 *
 * 在 kkokk/poster 的 PosterManager 基础上支持配置默认驱动：
 * 调用 buildIm() 等便捷方法且未指定 extension 时，使用配置中的 driver。
 */
class PosterManager extends BasePosterManager
{
    /**
     * @var string 默认驱动
     */
    protected $defaultDriver = 'gd';

    public function __construct($path = null, string $defaultDriver = 'gd')
    {
        parent::__construct($path);

        $this->defaultDriver = in_array($defaultDriver, ['gd', 'imagick'], true)
            ? $defaultDriver
            : 'gd';
    }

    /**
     * 支持的扩展列表，首个为默认扩展
     *
     * @return array
     */
    public function supportedExtensions()
    {
        return $this->defaultDriver === 'imagick'
            ? ['imagick', 'gd']
            : ['gd', 'imagick'];
    }
}