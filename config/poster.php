<?php
// +----------------------------------------------------------------------
// | think-poster 配置
// | 本包是对 kkokk/poster 的 ThinkPHP 8 适配层，以下配置均会被服务
// | 读取并应用到 kkokk/poster，未标注为“调用时传入”的配置项均会生效。
// +----------------------------------------------------------------------

return [
    // 图片驱动，可选：gd、imagick
    // gd：只需要 PHP 的 GD 扩展（默认）
    // imagick：需要安装 PHP 的 imagick 扩展
    // 该配置同时作为海报生成和验证码生成的默认驱动
    'driver' => 'gd',

    // 默认输出目录
    // 留空：使用 public/poster 目录（不存在时由 kkokk/poster 自动创建）
    // 绝对路径：直接使用该路径
    // 相对路径：相对于项目根目录
    // 说明：未显式调用 getPoster($path) 时，输出文件名为 poster.png
    'save_path' => '',

    // kkokk/poster 解析相对输出路径时的基准目录
    // 留空：使用项目的 public 目录
    // 绝对路径：直接使用
    // 相对路径：相对于项目根目录
    // 注意：上游会先把该路径转为 GBK 再拼接，请避免在路径中使用中文
    'document_root' => '',

    // 缓存配置
    'cache' => [
        // 验证码答案等缓存键的命名空间前缀
        // 留空：不添加前缀，与项目其他缓存键共用命名空间
        // 填写后：poster 相关键统一追加 poster:命名空间长度:命名空间: 前缀
        // 注意：启用后物理缓存键会变化，已有缓存不会自动迁移
        'namespace' => '',
    ],

    // 验证码配置
    'captcha' => [
        // 验证码驱动，可选：gd、imagick
        // 留空：沿用上面的 driver
        'driver' => '',

        // 所有验证码类型共用的配置
        // 支持 kkokk/poster 策略 config() 的任意键，例如 font_size、font_family、im_type、quality
        // 注意：不要开启上游的 debug，debug 会写入 vendor/kkokk/poster/tests 目录，
        // 以 Composer 方式安装后该目录并不存在，会导致生成失败
        'common' => [],

        // 滑块验证码
        // 可用键：src、im_width、im_height、im_type、quality、type、bg_width、bg_height、
        //         slider_width、slider_height、slider_border、slider_bg
        'slider' => [],

        // 点击验证码
        // 可用键：src、im_width、im_height、im_type、quality、font_family、font_size、
        //         char_count、line_count 等
        'click' => [],

        // 旋转验证码
        // 可用键：src、im_width、im_height、im_type、quality、font_family、font_size 等
        'rotate' => [],

        // 输入验证码
        // 可用键：src、im_width、im_height、im_type、quality、type、font_family、
        //         font_size、font_count、line_count、char_count
        'input' => [],
    ],

    // HTML 转图片配置
    'html' => [
        // wkhtmltoimage / wkhtmltopdf 可执行文件所在目录
        // 留空：从系统 PATH 查找
        // 填写后：该目录会被放到当前进程 PATH 的最前面，仅对当前请求生效
        // 支持绝对路径或相对项目根目录的路径
        'binary_path' => '',
    ],
];