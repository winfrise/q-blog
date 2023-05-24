<?php
namespace Home\Controller;

class PublicController extends BaseController {
	public function __construct()
	{
		parent::__construct();
		switch (CONTROLLER_NAME) {
			case 'Jobs':
				$title="人才招聘";
				break;
			case 'Order':
				$title="在线订单";
				break;
			case 'Message':
				$title="在线留言";
				break;
			case 'Contact':
				$title="联系我们";
				break;
			case 'Search':
				$title="产品搜索";
				break;
			default:				
				break;
		}
		$honor=M('Article')->where(array('pid'=>5))->order('`order` desc,id desc')->select();
		$case=M('Article')->where(array('pid'=>13))->order('`order` desc,id desc')->select();
		$wenti=M('Article')->where(array('pid'=>12))->order('`order` desc,id desc')->select();
		$this->assign('wenti', $wenti);
		$this->assign('case', $case);
		$this->assign('honor', $honor);
		$this->header_seo($title);
		$this->page_location($title,true);
		$this->catname($title,true);
		$this->friend_links();
	}

	/**
	 * 设置上一条下一条
	 * @param   $id     文章id
	 * @param   $module 模型与表名对应 article、goods、jobs
	 */
	protected function prev_next($id,$module)
	{
	    $_url = __ROOT__ . strtolower(__CONTROLLER__); // 当前控制器地址
		$info=M($module)->field('pid,order')->where("`id`=$id")->find();
		if (!$info) { //未找到当前分类返回执行
			return false;
		}
		if (strtolower($module)=='jobs') {
			$fields='id,pid,job'; 
		}else{
			$fields= 'id,pid,title';
		}
		$next=M($module)->field($fields)->where("`pid`=".$info['pid']." and `order`=".$info['order']." and `id`<".$id)->order("`id` desc")->find();
		$prev=M($module)->field($fields)->where("`pid`=".$info['pid']." and `order`=".$info['order']." and `id`>".$id)->order("`id` asc")->find();
		if (!$next) {
			$next=M($module)->field($fields)->where("`pid`=".$info['pid']." and `order`<".$info['order'])->order("`order` desc,`id` desc")->find();
		}
		if (!$prev) {
			$prev=M($module)->field($fields)->where("`pid`=".$info['pid']." and `order`>".$info['order'])->order("`order` asc,`id` asc")->find();
		}
		if (!$next) {
			$next_page="下一篇：没有了;";
		}else{
			$title=strtolower($module)=="jobs"?$next['job']:$next['title'];
			$url=strtolower($module)=="jobs"?$_url.'/'.$next['id']:$_url.'/'.$next['pid'].'_'.$next['id'];
			$next_page="下一篇：<a href=\"$url\">$title</a>";
		}
		if (!$prev) {
			$prev_page="上一篇：没有了;";
		}else{
			$title=strtolower($module)=="jobs"?$prev['job']:$prev['title'];
			$url=strtolower($module)=="jobs"?$_url.'/'.$prev['id']:$_url.'/'.$prev['pid'].'_'.$prev['id'];
			$prev_page="上一篇：<a href=\"$url\">$title</a>";
		}

		$this->assign("prev_page",$prev_page);
		$this->assign("next_page",$next_page);
	}

	/**
	 * 页面当前位置
	 * @param   $id 分类id 或者分类名称
	 * @return  首页 > 产品中心 > 产品分类
	 */
	protected function page_location($typeid, $is_catname=false)
	{
		if ($is_catname) { //参数是栏目名称
			$location='<a href="'.U("/").'">首页</a> > '.$typeid;
		}else{  //参数是分类ID
			$code_path=M("type")->where("`id`=$typeid")->getField("code");
			if (!$code_path) {
				return false;
			}
			$location='<a href="'.U("/").'">首页</a>';		
			$code_path=trim(ltrim($code_path,'1'),',');
			$code_arr=split(',', $code_path);
			foreach ($code_arr as $v) {
				$type=M('type')->where("id=$v")->find();
				if (CONTROLLER_NAME=='Custom') {
					$url_type='/t/'.$v;
				}else{
					if ($type['parent']!=0&&$type['parent']!=1) { // 0、1的为顶级分类，去掉type参数
						$url_type='/'.$v;
					}
				}				
				$url=U('/'.strtolower(CONTROLLER_NAME).$url_type);
				$location=$location.' > <a href="'.$url.'" title="'.$type['name'].'">'.($v==$typeid?$type['name']:$type['name']).'</a>';
			}
		}
		
		$this->assign("location",$location);
	}

	protected function friend_links() {
		$links = M('article')->field('`title`,`img`,`content` as `link`')->where('`pid`=6')->order('`order` desc,`id` desc')->select();
		$this->assign('links', $links);
	}

	/* 定制扩展位置 */
	
		
}