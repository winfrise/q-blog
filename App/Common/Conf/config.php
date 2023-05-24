<?php
return array(
    // 'MODULE_ALLOW_LIST'         => array('Home','Admin','Install','Zmapi','M'),
    'MODULE_ALLOW_LIST'         => array('Home','Admin'),
    'DEFAULT_MODULE'            => 'Home',
    'URL_CASE_INSENSITIVE'      =>  true,
    'UPLOAD_DIR'				=>  'Uploads/',
    'URL_MODEL'                 =>  2,
    'TMPL_PARSE_STRING'         => array(
        '__UPLOAD__' => 'Uploads', // 增加新的上传路径替换规则
		'__APP_MOBILE__'=>'/m'
    ),
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR'   => 'Public:jump',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Public:jump',
    'URL_ROUTER_ON'       =>  true, //开启路由
    'URL_ROUTE_RULES'     =>  array(   //路由规则
        '/^culture$/'              =>  'custom/info?id=17',
        '/^news\/(\d+)_(\d+)$/'    =>  'news/news_info?id=:2',
        '/^news\/(\d+)$/'          =>  'news/news_type?type=:1',
        '/^product\/(\d+)_(\d+)$/' =>  'product/product_info?id=:2',
        '/^product\/(\d+)$/'       =>  'product/product_type?type=:1',
        '/^custom\/(\d+)$/'        =>  'custom/info?id=:1',
        '/^custom\/t\/(\d+)$/'     =>  'custom/index?t=:1',
        '/^honor$/'                =>  'custom/index?t=5',
        '/^case$/'                 =>  'custom/index?t=13',
    ),
    'URL_HTML_SUFFIX' => '',  // URL伪静态后缀设置
    'LOAD_EXT_CONFIG' => 'db,api,api_url',
    'LOG_RECORD'      => false, // 默认不记录日志

);