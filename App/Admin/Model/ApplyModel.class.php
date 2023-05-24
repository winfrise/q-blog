<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/04/26
 * Time: 上午 08:35
 */

namespace Admin\Model;


class ApplyModel extends BaseModel{
    function __construct()
    {
        parent::__construct();
    }

    protected $_validate = array(
            array('name','require','名字不允许为空！'), //默认情况下用正则进行验证
    );
}