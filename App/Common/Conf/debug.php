<?php
defined('THINK_PATH') or exit();
// 调试模式下面默认设置 可以在项目配置目录下重新定义 debug.php 覆盖
return  array(
    'LOG_RECORD'			=>	false, // 进行日志记录
    'LOG_EXCEPTION_RECORD'  => 	false, // 是否记录异常信息日志
    'LOG_LEVEL'       		=>  'EMERG,ALERT,CRIT,ERR,WARN',  // 允许记录的日志级别
    'DB_FIELDS_CACHE'		=> 	false, // 字段缓存信息
    'DB_DEBUG'				=>  false, // 开启调试模式 记录SQL日志
    'DB_SQL_LOG'			=>	false, // 记录SQL信息
    'APP_FILE_CASE'  		=>  false, // 是否检查文件的大小写 对Windows平台有效
    'TMPL_CACHE_ON'    		=> 	false, // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_STRIP_SPACE'      => 	false, // 是否去除模板文件里面的html空格与换行
    'SHOW_ERROR_MSG'        => 	true, // 显示错误信息
    'URL_CASE_INSENSITIVE'  =>  true,  // 默认false 表示URL区分大小写 true则表示不区分大小写
);