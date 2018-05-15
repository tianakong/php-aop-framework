<?php
/**
 * 入口文件
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */


// 0: 线上模式; 1: 调试模式; 2: 插件开发模式;
!defined('DEBUG') AND define('DEBUG', 2);
define('APP_PATH', dirname(__FILE__) . '/'); // __DIR__
require APP_PATH . '../vendor/autoload.php';
\think\Db::setConfig(parse_ini_file(APP_PATH . '../config/config.ini'));
require APP_PATH . '../route/route.php';




