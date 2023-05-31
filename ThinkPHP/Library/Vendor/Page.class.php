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

  public $perPageRows; // 列表每页显示行数

  public $parameter; // 分页跳转时要带的参数

  public $totalRows; // 总行数

  public $totalPage; // 分页总页面数

  private $p    = 'page'; //分页参数名

  private $url   = ''; //当前链接URL

  private $currentPage = 1;

  // 分页显示定制

  private $config = array(
    'prev_text'  => '上一页',

    'next_text'  => '下一页',

    'theme' => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
    'page_sizes' => array(12, 24, 36, 48, 72),

  );

  /**

   * 架构函数

   * @param array $totalRows 总的记录数

   * @param array $listRows 每页显示记录数

   * @param array $parameter 分页跳转的参数

   */

  public function __construct($totalRows, $perPageRows=20, $parameter = array(), $pagerCount=6) {

    C('VAR_PAGE') && $this->p = C('VAR_PAGE'); //设置分页参数名称

    /* 基础设置 */

    $this->totalRows = $totalRows; //设置总记录数

    $this->perPageRows  = $perPageRows; //设置每页显示行数

    $this->currentPage  = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);

    $this->pagerCount = $pagerCount;

    $this->firstRow  = $this->perPageRows * ($this->currentPage - 1);

    $this->parameter = empty($parameter) ? $_GET : $parameter;

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

    // 总页数
    $this->totalPage = ceil($this->totalRows / $this->perPageRows); 

    /* 当前页不能大于总页数 */
    if(!empty($this->totalPage) && $this->currentPage > $this->totalPage) {
      $this->currentPage = $this->totalPage;
    }

    $pagerCount = $this->pagerCount;
    $halfPagerCount = ($pagerCount - 1) / 2;
    $currentPage = $this -> currentPage;
    $totalpage = $this->totalPage;

    $showPrevMore = false;
    $showNextMore = false;

    if ($totalpage > $pagerCount) {
      if ($currentPage > $pagerCount - $halfPagerCount) {
        $showPrevMore = true;
      }

      if ($currentPage + $halfPagerCount < $totalpage ) {
        $showNextMore = true;
      }
    }

    $pagerArr = array();
    if ($showPrevMore == true && $showNextMore == false) {
      $startPage = $totalpage - ($pagerCount - 2);
      for ($i = $startPage; $i < $totalpage; $i++) {
        $currPager = $currentPage == $i ? '<li class="active">' . $i . '</li>' : '<li><a href="' . $this -> url($i) . '">' . $i . '</a></li>';
        array_push($pagerArr, $currPager);
      }
    } else if ($showPrevMore == false && $showNextMore == true) {
      for ($i = 2; $i < $pagerCount; $i++) {
        $currPager = $currentPage == $i ? '<li class="active">' . $i . '</li>' : '<li><a href="' . $this -> url($i) . '">' . $i . '</a></li>';
        array_push($pagerArr, $currPager);
      }
    } else if ($showPrevMore == true && $showNextMore == true) {
      $offset = floor($pagerCount / 2) - 1;
      for ($i = $currentPage - $offset ; $i <= $currentPage + $offset; $i++) {
        $currPager = $currentPage == $i ? ('<li class="active">' . $i . '</li>') : ('<li><a href="' . $this -> url($i) . '">' . $i . '</a></li>');
        array_push($pagerArr, $currPager);
      }
    } else {
      for ($i = 2; $i < $totalpage; $i++) {
        $currPager = $currentPage == $i ? '<li class="active">' . $i . '</li>' : '<li><a href="' . $this -> url($i) . '">' . $i . '</a></li>';
        array_push($pagerArr, $currPager);
      }
    }




    //上一页
    $prev = $this->currentPage - 1;
    $prev_page = '';
    if ($prev > 0) {
      $prev_page = '<a href="' . $this->url($prev) . '">' . $this->config['prev_text']  . '</a>';
    } else {
      $prev_page = '<span class="disabled">' . $this->config['prev_text'] . '</span>';
    }

    //下一页
    $next = $this->currentPage + 1;
    $next_page = '';
    if ($next <= $this->totalPage) {
      $next_page = '<a href="' . $this->url($next) . '">' . $this->config['next_text']  . '</a>';
    } else {
      $next_page = '<span class="disabled">' . $this->config['next_text'] . '</span>';
    }

    //第一页
    $first_page = '';
    if($this-> totalPage > 1){
      if ($this -> currentPage == 1) {
        $first_page = '<li class="active">1</li>';
      } else {
        $first_page = '<li><a href="' . $this->url(1) . '">1</a></li>';
      }
    }

    //最后一页
    $last_page = '';
    if($this->totalPage > 2){
      if ($this -> currentPage == $this->totalPage) {
        $last_page = '<li class="active">' . $this->totalPage . '</li>';
      } else {
        $last_page = '<li><a href="' . $this->url($this->totalPage) . '">' . $this->totalPage . '</a></li>';
      }
    }

    // 每页显示条数
    $page_sizes_str = '<select>';
    foreach ($this->config['page_sizes'] as $value){
        $page_sizes_str .= '<option value="' . $value . '">' . $value . '条/页</option>';
    }
    $page_sizes_str .= '</select>';

    // 跳转到指定页数
    $jumper_str = '<span>跳转到第<input type="text" value="' . $currentPage . '" />页</span> <button type="button">确定</button>';



    //替换分页内容
    $page_str = str_replace(

      array('%PREV_PAGE%', '%NEXT_PAGE%', '%FIRST_PAGE%', '%LAST_PAGE%', '%TOTAL_ROW%', '%PAGER%', '%PREV_MORE%', '%NEXT_MORE%', '%PAGE_SIZES%', '%JUMPER%'),

      array( $prev_page, $next_page, $first_page, $last_page, $this->totalRows, join('', $pagerArr), $showPrevMore ? '<li>...</li>' : '', $showNextMore ? '<li>...</li>' : '', $page_sizes_str, $jumper_str),

      '<span>共%TOTAL_ROW%条</span>
      <li class="prev">%PREV_PAGE%</li>
    %FIRST_PAGE%
    %PREV_MORE%
    %PAGER%
    %NEXT_MORE%
    %LAST_PAGE%
    <li class="next">%NEXT_PAGE%</li>
    %PAGE_SIZES%
    %JUMPER%
    ');

    return '<ul class="pagination">' . $page_str . '</ul>';

  }

}