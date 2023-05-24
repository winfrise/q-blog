<?php
/**
 * 后台留言管理
 * Enter description here ...
 * @author Administrator
 *
 */
namespace Admin\Controller;

class MessageController extends BaseController {
	/**
	 * 
	 * Enter description here ...
	 */
	public function __construct()
	{
		parent::__construct("Message",'message');
	}
}