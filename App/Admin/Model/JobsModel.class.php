<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/04/18
 * Time: 下午 04:34
 */

namespace Admin\Model;


class JobsModel extends BaseModel {
    function __construct()
    {
        parent::__construct();
    }

    protected $_validate = array(
            array('job','require','职位名称不允许为空！'), //默认情况下用正则进行验证
    );
}