## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# youvision/think-poster

基于 [kkokk/poster](https://github.com/kkokk/poster) 的 ThinkPHP 8 适配包。

本包不复制上游源码，而是通过 Composer 依赖 `kkokk/poster`，在其上提供 ThinkPHP 8 的服务注册、容器绑定、门面、配置、缓存适配与路径处理，用于快速生成海报、二维码、输入/滑块/点击/旋转验证码，以及把 HTML 转为图片。

## 环境要求

- PHP >= 8.0
- ThinkPHP 8
- 必需扩展：`gd`、`mbstring`、`iconv`（由 kkokk/poster 声明）
- 可选扩展：`imagick`（使用 imagick 驱动时）
- 可选程序：`wkhtmltoimage` / `wkhtmltopdf`（把 HTML 转为图片或 PDF 时）

## 安装

```bash
composer require youvision/think-poster
```

本包已在 `composer.json` 的 `extra.think.services` 中声明服务，安装后 ThinkPHP 8 会自动发现并注册 `Youvision\ThinkPoster\Service`。

如需修改配置，把包内的 `config/poster.php` 复制到项目的 `config` 目录：

```bash
copy vendor\youvision\think-poster\config\poster.php config\poster.php
```

配置项均有中文说明，未复制的配置项使用包内默认值。

## 快速开始

### 生成海报

```php
use Youvision\ThinkPoster\Facade\Poster;

$result = Poster::buildIm(750, 1334, [255, 255, 255])
    ->buildBg(750, 1334, ['color' => ['#1677ff', '#36cfc9'], 'to' => 'bottom'])
    ->buildText('ThinkPoster', 'center', 120, 48, [255, 255, 255], 600)
    ->buildImage(poster_path('uploads/avatar.jpg'), 'center', 300, 0, 0, 120, 120, true, 'circle')
    ->buildText('PHP 海报生成', 'center', 460, 32, [255, 255, 255], 600)
    ->buildQr('https://www.thinkphp.cn', 'center', 1000, 0, 0, 200, 200)
    ->getPoster(poster_path('poster/invite.png'));

// $result = ['url' => '.../poster/invite.png']
```

`getPoster()` 不传路径时，输出到配置的 `save_path` 目录，文件名为 `poster.png`。

### 生成验证码

```php
use Youvision\ThinkPoster\Facade\Captcha;

// 生成输入型验证码
$data = Captcha::type('input')->get();
// $data = ['key' => '...', 'img' => 'data:image/png;base64,...']

// 校验，答案一次性消费
$passed = Captcha::type('input')->check($data['key'], (string) $request->post('code'));

// 滑块验证码
$slider = Captcha::type('slider')->get();
// $slider = ['key' => '...', 'img' => 'data:image/png;base64,...', 'y' => 120]
$passed = Captcha::type('slider')->check($slider['key'], (int) $request->post('x'));
```

验证码答案保存在 ThinkPHP 缓存中。缓存不可用时，上游会在返回结果中附带 `secret` 字段，此时需要业务层自行保存并在 `check()` 的第四个参数中回传。

### 生成二维码

```php
use Youvision\ThinkPoster\Facade\Poster;

// 直接输出二进制流
$stream = Poster::Qr('https://www.thinkphp.cn');

// 保存到文件
$result = Poster::Qr('https://www.thinkphp.cn', poster_path('poster/qrcode.png'));
```

### HTML 转图片

需要系统可执行 `wkhtmltoimage`。可在配置中指定程序目录：

```php
// config/poster.php
'html' => [
    'binary_path' => 'C:/Program Files/wkhtmltopdf/bin',
],
```

```php
use Youvision\ThinkPoster\Facade\Html;

$result = Html::load('<html><body><h1>Hello Poster</h1></body></html>')
    ->tempDirectory(runtime_path('poster'))
    ->timeout(30)
    ->output(poster_path('poster/html.png'), 'png')
    ->render();
```

`tempDirectory()` 必须指向已存在且可写的目录，建议使用 `runtime_path()` 下的目录。

### 缓存

```php
use Youvision\ThinkPoster\Facade\Cache;

Cache::put('poster:demo', 'value', 60);
Cache::get('poster:demo');
Cache::forget('poster:demo');
```

## 门面与助手函数

| 门面 | 容器标识 | 助手函数 | 说明 |
| --- | --- | --- | --- |
| `Youvision\ThinkPoster\Facade\Poster` | `poster` | `poster()` | 海报、二维码 |
| `Youvision\ThinkPoster\Facade\Captcha` | `poster.captcha` | `poster_captcha()` | 验证码 |
| `Youvision\ThinkPoster\Facade\Html` | `poster.html` | `poster_html()` | HTML 转图片 |
| `Youvision\ThinkPoster\Facade\Cache` | `poster.cache` | `poster_cache()` | 缓存仓库 |

路径助手：

```php
poster_path('uploads/a.png'); // 返回 public/uploads/a.png 的绝对路径
poster_path();                // 返回 public 目录
```

`poster_path()` 会把相对路径解析到项目 `public` 目录；盘符路径、UNC 路径、协议地址和 Phar 地址原样返回。以 `/` 开头的路径按 ThinkPHP 习惯同样解析到 `public` 目录，例如 `poster_path('/uploads/a.png')` 返回 `public/uploads/a.png`。上游对相对路径的解析依赖 `$_SERVER['DOCUMENT_ROOT']`，在 CLI 和队列环境中不可靠，因此建议在传入图片、字体和输出路径时统一使用 `poster_path()`。

## 配置说明

`config/poster.php` 中的配置项：

| 配置 | 默认值 | 说明 |
| --- | --- | --- |
| `driver` | `gd` | 默认图片驱动，可选 `gd`、`imagick`；同时作用于海报和验证码 |
| `save_path` | 空 | 默认输出目录；空表示 `public/poster`，相对路径相对项目根目录 |
| `document_root` | 空 | 上游解析相对输出路径的基准目录；空表示项目 `public` 目录 |
| `cache.namespace` | 空 | 验证码缓存键命名空间；启用后物理键会变化，旧缓存不会迁移 |
| `captcha.driver` | 空 | 验证码驱动；空表示沿用 `driver` |
| `captcha.common` | `[]` | 所有验证码类型共用的策略配置 |
| `captcha.slider` | `[]` | 滑块验证码配置 |
| `captcha.click` | `[]` | 点击验证码配置 |
| `captcha.rotate` | `[]` | 旋转验证码配置 |
| `captcha.input` | `[]` | 输入验证码配置 |
| `html.binary_path` | 空 | `wkhtmltoimage` / `wkhtmltopdf` 所在目录；空表示从系统 PATH 查找 |

验证码各类型的配置会合并到上游策略的默认值中，可用键与 kkokk/poster 对应策略一致：

- `slider`：`src`、`im_width`、`im_height`、`im_type`、`quality`、`type`、`bg_width`、`bg_height`、`slider_width`、`slider_height`、`slider_border`、`slider_bg`
- `click`：`src`、`im_width`、`im_height`、`im_type`、`quality`、`font_family`、`font_size`、`char_count`、`line_count` 等
- `rotate`：`src`、`im_width`、`im_height`、`im_type`、`quality`、`font_family`、`font_size` 等
- `input`：`src`、`im_width`、`im_height`、`im_type`、`quality`、`type`、`font_family`、`font_size`、`font_count`、`line_count`、`char_count`

验证码有效期通过 `get($expire)` 传入（单位秒），例如：

```php
$data = Captcha::type('input')->get(300);
```

## 与直接使用 kkokk/poster 的差异

本包只做框架适配，不修改上游实现：

1. **显式缓存适配**：用 `ThinkCacheAdapter` 把 `think\Cache` 接入验证码，避免上游通过 `class_exists` 自动判断缓存后端时受运行环境影响。
2. **默认驱动**：`buildIm()` 等便捷方法默认使用配置中的 `driver`，也可继续用 `extension('gd')` 显式指定。
3. **默认输出目录**：`getPoster()` 不传路径时输出到 `save_path`，而不是上游 vendor 包内的 `tests` 目录。
4. **路径兜底**：CLI、队列和常驻进程中自动补齐 `$_SERVER['DOCUMENT_ROOT']`，并可用 `poster_path()` 显式解析相对路径。
5. **HTML 程序路径**：通过 `html.binary_path` 把程序目录加入当前进程 PATH，无需修改系统环境变量。
6. **验证码配置分组**：按 `slider`、`click`、`rotate`、`input` 分组注入配置，避免不同验证码类型的同名字段（如 `type`）互相覆盖。

## 多应用模式与站点隔离

在多应用模式下，建议按应用或站点把输出文件放到子目录，避免不同站点互相覆盖：

```php
use Youvision\ThinkPoster\Facade\Poster;

$path = poster_path('poster/' . $appName . '/' . $siteId . '/' . uniqid() . '.png');

Poster::buildIm(750, 1334)
    ->buildText('站点海报', 'center', 100, 40)
    ->getPoster($path);
```

也可以使用 Builder 的 `path()` 方法在链式调用中指定输出位置：

```php
Poster::buildIm(750, 1334)
    ->path($path)
    ->buildText('站点海报', 'center', 100, 40)
    ->getPoster();
```

## 常见问题

**vendor 目录只读**

默认把图片写到 `public/poster`。若项目 `public` 目录不可写，请把 `save_path` 改为 `runtime_path('poster')` 等可写目录，并通过业务代码把文件复制或上传到需要的位置。

**imagick 驱动报错**

`imagick` 需要 PHP 安装 imagick 扩展。未安装时请保持 `driver` 为 `gd`。

**路径包含中文**

上游 `get_document_root()` 会把 `$_SERVER['DOCUMENT_ROOT']` 转为 GBK 后再拼接路径。请尽量避免在输出路径中使用中文；必须使用时建议显式传入绝对路径，并确认运行环境的编码设置。

**验证码没有写入缓存**

检查项目的 `config/cache.php` 是否配置了可用的缓存驱动。缓存不可用时上游会在返回结果中附带 `secret`，需要业务层自行保存。

**HTML 转换失败**

确认 `wkhtmltoimage --version` 能在命令行执行。若程序不在 PATH 中，请配置 `html.binary_path`，并确认 PHP 的 `proc_open`、`proc_get_status`、`proc_terminate`、`proc_close` 未被禁用。

**不要开启上游 debug**

kkokk/poster 验证码策略的 `debug` 会把图片写入 `vendor/kkokk/poster/tests` 目录。以 Composer 方式安装后该目录不存在，开启后会导致生成失败。本包配置未暴露该选项。

## 测试

```bash
composer install
php tests/verify.php
```

测试脚本会初始化 ThinkPHP 8 容器、注册服务、生成海报与验证码，并校验配置、缓存和路径解析是否生效。

## License

MIT。上游 `kkokk/poster` 的版权与许可声明见 `vendor/kkokk/poster/LICENSE`。