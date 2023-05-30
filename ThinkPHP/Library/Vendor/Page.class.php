<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------
class Page{

  public $firstRow; // 起始行数

  public $listRows; // 列表每页显示行数

  public $parameter; // 分页跳转时要带的参数

  public $totalRows; // 总行数

  public $totalPages; // 分页总页面数

  public $rollPage  = 11;// 分页栏每页显示的页数

  public $lastSuffix = true; // 最后一页是否显示总页数

  private $p    = 'page'; //分页参数名

  private $url   = ''; //当前链接URL

  private $nowPage = 1;

  // 分页显示定制

  private $config = array(

    'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',

    'prev'  => '上一页',

    'next'  => '下一页',

    'first' => '1...',

    'last'  => '...%TOTAL_PAGE%',

    'theme' => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',

  );

  /**

   * 架构函数

   * @param array $totalRows 总的记录数

   * @param array $listRows 每页显示记录数

   * @param array $parameter 分页跳转的参数

   */

  public function __construct($totalRows, $listRows=20, $parameter = array()) {

    C('VAR_PAGE') && $this->p = C('VAR_PAGE'); //设置分页参数名称

    /* 基础设置 */

    $this->totalRows = $totalRows; //设置总记录数

    $this->listRows  = $listRows; //设置每页显示行数

    $this->parameter = empty($parameter) ? $_GET : $parameter;

    $this->nowPage  = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);

    $this->firstRow  = $this->listRows * ($this->nowPage - 1);

  }

  /**

   * 定制分页链接设置

   * @param string $name 设置名称

   * @param string $value 设置值

   */

  public function setConfig($name,$value) {

    if(isset($this->config[$name])) {

      $this->config[$name] = $value;

    }

  }

  /**

   * 生成链接URL

   * @param integer $page 页码

   * @return string

   */

  private function url($page){

    return str_replace(urlencode('[PAGE]'), $page, $this->url);

  }

  /**

   * 组装分页链接

   * @return string

   */

  public function show() {
    if(0 == $this->totalRows) return '';

    /* 生成URL */

    $this->parameter[$this->p] = '[PAGE]';

    $this->url = U('', $this->parameter, true);

    /* 计算分页信息 */

    $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数

    /* 当前页不能大于总页数 */
    if(!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
      $this->nowPage = $this->totalPages;

    }


    $pagerCount = 6;
    $halfPagerCount = ($pagerCount - 1) / 2;
    $currentPage = $this -> nowPage;
    $pageCount = $this->totalPages;

    $showPrevMore = false;
    $showNextMore = false;

    if ($pageCount > $pagerCount) {
      if ($currentPage > $pagerCount - $halfPagerCount) {
        $showPrevMore = true;
      }

      if ($currentPage < $pageCount - $halfPagerCount) {
        $showNextMore = true;
      }
    }

    $pagerArr = array();
    var_dump($showPrevMore);
    var_dump($showNextMore);

    if ($showPrevMore == true && $showNextMore == false) {
      $startPage = $pageCount - ($pagerCount - 2);
      for ($i = $startPage; $i < $pageCount; $i++) {
        array_push($pagerArr, '<li class="page-num ' . ($this->nowPage == $i ? 'active' : '') . '"><a href="' . $this -> url($i) . '">' . $i . '</a></li>');
      }
    } else if ($showPrevMore == false && $showNextMore == true) {
      for ($i = 2; $i < $pagerCount; $i++) {
       array_push($pagerArr, '<li class="page-num ' . ($this->nowPage == $i ? 'active' : '') . '"><a href="' . $this -> url($i) . '">' . $i . '</a></li>');
      }
    } else if ($showPrevMore == true && $showNextMore == true) {
      $offset = floor($pagerCount / 2) - 1;
      for ($i = $currentPage - $offset ; $i <= $currentPage + $offset; $i++) {
        array_push($pagerArr, '<li class="page-num ' . ($this->nowPage == $i ? 'active' : '') . '"><a href="' . $this -> url($i) . '">' . $i . '</a></li>');
      }
    } else {
      for ($i = 2; $i < $pageCount; $i++) {
        array_push($pagerArr, '<li class="page-num ' . ($this->nowPage == $i ? 'active' : '') . '"><a href="' . $this -> url($i) . '">' . $i . '</a></li>');
      }
    }



    /* 计算分页零时变量 */

    $now_cool_page   = $this->rollPage/2;

    $now_cool_page_ceil = ceil($now_cool_page);

    $this->lastSuffix && $this->config['last'] = $this->totalPages;

    //上一页

    $prev = $this->nowPage - 1;
    $prev_page = '<a ' . ($prev > 0 ? 'href="' . $this->url($prev) . '"' : '') . '>' . $this->config['prev']  . '</a>';

    //下一页

    $next = $this->nowPage + 1;
    $next_page = '<a ' . ($next <= $this->totalPages ? 'href="' . $this->url($next) . '"' : '') . '>' . $this->config['next']  . '</a>';

    //第一页

    $first_page = '';
    if($this-> totalPages > 1){
    
      $first_page = '<li class="number ' . ($this -> nowPage == 1 ? 'acitve' : '') . '">1</li>';

    }

    //最后一页

    $last_page = '';

    if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
      $last_page = '<li class="number">' . $this -> totalPages . '</li>';

    }


    //替换分页内容


      var_dump($pagerArr);
    $page_str = str_replace(

      array('%PREV_PAGE%', '%NEXT_PAGE%', '%FIRST_PAGE%', '%LAST_PAGE%', '%TOTAL_ROW%', '%PAGER%', '%PREV_MORE%', '%NEXT_MORE%'),

      array( $prev_page, $next_page, $first_page, $last_page, $this->totalRows, join('', $pagerArr), $showPrevMore ? '...' : '', $showNextMore ? '...' : ''),

      '<span>共%TOTAL_ROW%条</span>
      <li class="prev">%PREV_PAGE%</li>
    <li>%FIRST_PAGE%</li>
    <li>%PREV_MORE%</li>
    <li>%PAGER%</li>
    <li>%NEXT_MORE%</li>
    <li>%LAST_PAGE%</li>
    <li class="next">%NEXT_PAGE%</li>');

    return "<ul>{$page_str}</ul>";

  }

}