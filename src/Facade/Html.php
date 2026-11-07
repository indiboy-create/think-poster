<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Facade;

use think\Facade;

/**
 * HTML 转图片门面
 *
 * @method static \Kkokk\Poster\Html\Builder channel($channel = '')
 * @method static \Kkokk\Poster\Html\Builder load($html)
 *
 * @see \Kkokk\Poster\Html\HtmlManager
 * @see \Kkokk\Poster\Html\Builder
 */
class Html extends Facade
{
    protected static function getFacadeClass()
    {
        return 'poster.html';
    }
}