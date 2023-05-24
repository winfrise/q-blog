<?php
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 网站根目录
define('ROOT', dirname(__FILE__));

// 定义项目路径
define('APP_PATH', ROOT . '/App/');
define('RUNTIME_PATH', ROOT . '/Runtime/');

// 调试开关开启
define('APP_DEBUG', true);

// 定义基础常量
define('ARTICLE', 1);
define('PRODUCT', 2);
define('JOBS', 3);
define('NEWS', 4);

require(ROOT . '/ThinkPHP/ThinkPHP.php');