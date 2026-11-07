<?php
declare(strict_types=1);

namespace Youvision\ThinkPoster\Facade;

use think\Facade;

/**
 * 海报门面
 *
 * @method static \Kkokk\Poster\Image\ExtensionInterface extension($name = null)
 * @method static \Kkokk\Poster\Image\ExtensionInterface fresh($name = null)
 * @method static \Kkokk\Poster\Image\PosterManager reset($name = null)
 * @method static \Kkokk\Poster\Image\Builder config($params = [])
 * @method static \Kkokk\Poster\Image\Builder buildIm($w, $h, $rgba = [], $alpha = false)
 * @method static \Kkokk\Poster\Image\Builder buildImDst($src, $w = 0, $h = 0)
 * @method static \Kkokk\Poster\Image\Builder buildBg($w, $h, $rgba = [], $alpha = false, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $callback = null)
 * @method static array Qr($text, $outfile = false, $level = 'L', $size = 4, $margin = 1, $saveAndPrint = 0)
 *
 * @see \Youvision\ThinkPoster\Image\PosterManager
 * @see \Kkokk\Poster\Image\Builder
 */
class Poster extends Facade
{
    protected static function getFacadeClass()
    {
        return 'poster';
    }
}