<?php
/**
 * 公用函数库
 */

/* 调试快捷函数 */
function debug($var, $end = true)
{
	header ( "Content-type:text/html;charset=utf-8" );
        echo '<pre>';
	echo '<hr>';
	//var_dump($var);
	print_r ( $var );
	echo '<hr>';
	echo '</pre>';
	if ($end)
	{
		exit ();
	}
}

/* 调试快捷函数别名 */
function Z($var, $end = true)
{
	debug ( $var, $end );
}

/* 空模块处理 */
function __hack_module(){
    A('Home/Base')->_404();
}

/* 空操作处理 */
function __hack_action(){
    A('Home/Base')->_404();
}


/* 提交过滤 */
if (get_magic_quotes_gpc ())
{
	function stripslashes_deep($value)
	{
		$value = is_array ( $value ) ? array_map ( 'stripslashes_deep', $value ) : stripslashes ( $value );
		return $value;
	}
	$_POST = array_map ( 'stripslashes_deep', $_POST );
	$_GET = array_map ( 'stripslashes_deep', $_GET );
	$_COOKIE = array_map ( 'stripslashes_deep', $_COOKIE );
}

/**
 * [http_transport description]
 * @param  [type] $url    [description]
 * @param  array  $params [description]
 * @param  string $method [description]
 * @return [type]         [description]
 */
function http_transport($url, $params = array(), $method = 'POST')
{
    $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url .'?'. http_build_query($params);
            break;
        case 'POST':
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
    }       
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);
    if ($err > 0) {     
        // $this->error = $errmsg; 
        return false;
    }else {
        return $data;
    }
}

/**
 * 加密算法
 * @param  string $data 加密字符串
 * @param  string $key  密钥
 * @return string       
 */
function encrypt($data, $key)
{
    $key = md5($key);
    $x  = 0;
    $len = strlen($data);
    $l  = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
         $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}

/**
 * 解密算法
 * @param  string $data 待解密字符串
 * @param  string $key  密钥
 * @return string       明文
 */
function decrypt($data, $key)
{
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l)
        {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

function _request($var = '', $filter = '', $default = '') {
    return I('request.'.$var, $filter, $default);
}

function dateTime(){
    return date('y-m-d H:i:s',time());
}

/**
 * 请求的来源判断
 * @param $url
 * @return string
 */
function webResource(){
    $url=getenv("HTTP_REFERER");
    if(strpos($url,'juqi360.com')){
        return '聚企360';
    }else if(strpos($url,'qftouch.com')){
        return 'QFTouch';
    }else{
        return '官网';
    }
}

/**
 * //函数解释：
 *msubstr($str, $start=0, $length, $charset=”utf-8″, $suffix=true)
 *$str:要截取的字符串
 * $start=0：开始位置，默认从0开始
 * $length：截取长度
 * $charset=”utf-8″：字符编码，默认UTF－8
 * $suffix=true：是否在截取后的字符后面显示省略号，默认true显示，false为不显示
 * 模版使用：{$vo.title|msubstr=0,5,'utf-8',true}
 * @param $str
 * @param int $start
 * @param $length
 * @param string $charset
 * @param bool $suffix
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
        if($suffix)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if($suffix)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
                  [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}


/**
 * 获取需要过滤的违禁词
 */
function get_badwords(){
    $cache_badwords=S('badwords');
    if(empty($cache_badwords)){
        //得到广告法违禁词，生成缓存文件
        $filter_url=decrypt(C('FILTER_URL'),C('FILTER_KEY'));
        $data=http($filter_url);
        if($data['status_code']==200){
            $badwords =json_decode($data['content'],true);
            S('badwords',$badwords,array('type'=>'file','expire'=>3600*48 ));
            update_list($badwords,'BADWORDS');
        }else{
            S('badwords',C('BADWORDS'),array('type'=>'file','expire'=>3600*48 ));
        }
    }
    $badword=C('BADWORDS');
    return $badword;
}
/**
 *  判断是否违禁词过滤
 */
function is_filter(){
    $validate =M('config')->where('`key`="filter_badwords"')->getField('value');
    $flag=($validate=="1")?1:0;
    return $flag;
}

function http($url, $params = array(), $method = 'POST')
{
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url .'?'. http_build_query($params);
            break;
        case 'POST':
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data['content']  = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $data['status_code']= curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err > 0) {
        // $this->error = $errmsg;
        return false;
    }else {
        return $data;
    }
}

function filter_badwords($data,$badword=array()){
    $arr=array_pad(array("***"),count($badword),"***");
    $data = str_replace($badword,$arr,$data);
    return $data;
}
function is_exist_badword($data){
    $badword=get_badwords();
    $data_str=implode(",",$data);
    foreach($badword as $k=>$v){
       if(strpos($data_str,$v) !== false ){
           return $result=array(
               'status'=>1,
               'badword'=>$badword
           );
       }
    }
    return $result=array(
        'status'=>0,
    );
}

/*
 * 获取行业分类
 */
function getCatelist(){
    $cache_catelist=S('catelist');
    if(empty($cache_catelist)){
        //得到行业分类，生成缓存文件
        $filter_url=decrypt(C('QTAPI_URL'),C('QTAPI_KEY'))."/cate_list";
        $data=http($filter_url);
        if($data['status_code']==200){
            $catelist =json_decode($data['content'],true);
            $list = $catelist['data'];
            S('catelist',$list,array('type'=>'file','expire'=>3600*24*7 ));
            update_list($list,'CATELIST');
        }else{
            S('catelist',C('CATELIST'),array('type'=>'file','expire'=>3600*24*7));
        }
    }
    $cate=C('CATELIST');
    return $cate;
}
/*
 * 获取招聘分类
 */
function getZhaopinlist(){
    $cache_zhaopinlist=S('zhaopinlist');
    if(empty($cache_zhaopinlist)){
        //得到招聘分类，生成缓存文件
        $filter_url=decrypt(C('QTAPI_URL'),C('QTAPI_KEY'))."/zhaopin_list";
        $data=http($filter_url);
        if($data['status_code']==200){
            $zhaopinlist =json_decode($data['content'],true);
            $list = $zhaopinlist['data'];
            S('zhaopinlist',$list,array('type'=>'file','expire'=>3600*24*7 ));
            update_list($list,'ZHAOPINLIST');
        }else{
            S('zhaopinlist',C('ZHAOPINLIST'),array('type'=>'file','expire'=>3600*24*7));
        }
    }
    $cate=C('ZHAOPINLIST');
    return $cate;
}

/*
 * 获取地区信息
 */
function getArealist(){
    $cache_arealist=S('arealist');
    if(empty($cache_arealist)){
        //得到地区信息，生成缓存文件
        $filter_url=decrypt(C('QTAPI_URL'),C('QTAPI_KEY'))."/area_list";
        $data=http($filter_url);
        if($data['status_code']==200){
            $arealist =json_decode($data['content'],true);
            $list = $arealist['data'];
            S('arealist',$list,array('type'=>'file','expire'=>3600*24*7));
            update_list($list,'AREALIST');
        }else{
            S('arealist',C('AREALIST'),array('type'=>'file','expire'=>3600*24*7));
        }
    }
    $cate=C('AREALIST');
    return $cate;
}

/**
 * 获取招聘配置
 */
function getZhaopinConfig(){
    $cache_zhaopinlist=S('zhaopinconfig');
    if(empty($cache_zhaopinlist)){
        //得到招聘配置，生成缓存文件
        $filter_url=decrypt(C('QTAPI_URL'),C('QTAPI_KEY'))."/zhaopin_config";
        $data=http($filter_url);
        if($data['status_code']==200){
            $zhaopinlist =json_decode($data['content'],true);
            $list = $zhaopinlist['data'];
            S('zhaopinconfig',$list,array('type'=>'file','expire'=>3600*24*7 ));
            update_list($list,'ZPCONFIG');
        }else{
            S('zhaopinconfig',C('ZPCONFIG'),array('type'=>'file','expire'=>3600*24*7));
        }
    }
    $cate=C('ZPCONFIG');
    return $cate;
}

/*
 * 更新配置文件中分类配置
 *
 */
function update_list($data,$config){
    $conf=load_config(CONF_PATH.'api_url'.CONF_EXT);
    $conf[$config]=$data;
    \Think\Storage::put(CONF_PATH.'api_url'.CONF_EXT,'<?php return  '.var_export($conf,true).';','F');
}
