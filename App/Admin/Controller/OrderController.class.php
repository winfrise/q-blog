<?php
/**
 * 后台订单管理
 * Enter description here ...
 * @author Administrator
 *
 */
namespace Admin\Controller;

class OrderController extends BaseController {
    function __construct()
    {
    	parent::__construct('Order','Order');
    }
	
	function index()
	{
		import('ORG.Util.Page');
		$count      = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
		$Page       = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->setConfig('theme','<span>%totalRow% %header% %nowPage%/%totalPage% 页</span>  %first%  %upPage% %linkPage%  %downPage% %end% %select%');
		$show       = $Page->show();// 分页显示输出

		$list = $this->_model->where($this->_where)->limit($Page->firstRow.','.$Page->listRows)->order('`order` desc,id desc')->select();
		foreach ($list as $key => $value) {
			$info = M('Goods')->where("`id` = {$value['cp_id']}")->select();
			$list[$key]['title'] = $info[0]['title'];
		}
		//z($list);
		$search = array('value'=>'name');
		$this->assign('search', $search);
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		if($template == null)
		{
			$this->display();
		}else{
			$this->display($template);
		}
	}
	
	function search()
	{
		foreach($_POST as $key=>$value)
    	{
    		$value = trim($value);
    		$this->_where[$key] = array('like','%'.$value.'%');
    	}
    	
    	//z($this->_where);
    	
    	import('ORG.Util.Page');
		$count      = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
		$Page       = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		$list = $this->_model->where($this->_where)->order('`order` desc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach ($list as $key => $value) {
			$info = M('Goods')->where("`id` = {$value['cp_id']}")->select();
			$list[$key]['title'] = $info[0]['title'];
		}
		$search = array('value'=>'name');
		$this->assign('search', $search);
		$this->assign('set_title', $_REQUEST['name']);
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		if($template == null)
		{
			$this->display('index');
		}else{
			$this->display($template);
		}
	}

	function more($template = null)
    {
    	$info = D($this->_model_name)->find($_REQUEST['id']);
    	$this->assign('info',$info);

    	$re = M('Goods')->where("`id` = {$info['cp_id']}")->select();
    	$this->assign('title',$re[0]['title']);
    	if($template == null)
		{
			$this->display();
		}else{
			$this->display($template);
		}
    }
}