<?php
//decode by http://www.yunlu99.com/
namespace Admin\Controller;
use Common\Controller\CommonController;

class BaseController extends CommonController {

    /**
     * 执行操作的ACTION名称
     * ACTION初始化时需要设置动作名称
     * @var unknown_type
     */
    protected $_action_name;

    /**
     * 和ACTION关联的MODEL名称
     * ACTION初始化时需要设置关联的模型名称
     * @var unknown_type
     */
    protected $_model_name;

    /**
     * MODEL模型
     * @var unknown_type
     */
    protected $_model;

    /**
     * 基础分类名称
     * 主要目的是获取分类列表使用
     * @var unknown_type
     */
    protected $_categroy = array('Article' => '1', 'Goods' => '2', 'Jobs' => '3');
    /**
     * 条件查询
     * @var unknown_type
     */
    protected $_where;

    /**
     * 构造函数
     * @param unknown_type $classs_name 子类名称
     */
    function __construct($action_name = null, $model_name = null) {
        parent::__construct();
        //个别路由器无法登陆后台
        error_reporting(E_ALL);

        // uploadify插件修复session问题
        $session_name = session_name();
        if (isset($_POST[$session_name])) {
            session_id($_POST[$session_name]);
            session_start();
        }

        //如果存在安装文件，删除
        $this->del_install();

        //检测用户是否登录
        $this->check_user();
        $this->assign('username', $_SESSION['username']);
        $this->assign('empty', '<tr><td  align="center" colspan="10">本栏目暂时没有数据</td></tr>');

        //初始化
        if ($action_name == null) die('Forbidden'); else {
            $this->_action_name = $action_name;
            $this->_model_name = $model_name;
            $this->_model = D($this->_model_name);
            $this->assign('action', $this->_action_name);
            //读取栏目开关
            $this->assign('switch_order', $this->config('switch_order'));
            $this->assign('switch_message', $this->config('switch_message'));
            $this->assign('switch_jobs', $this->config('switch_jobs'));
        }
    }

    /**
     * 动态设置控制器
     * @param unknown_type $actionName
     */
    function setController($actionName) {
        $this->_action_name = $actionName;
    }

    /**
     * 设置关联模型
     * @param unknown_type $modelnName
     */
    function setModel($modelnName) {
        $this->_model_name = $modelnName;
        $this->_model = D($this->_model_name);
    }

    /**
     * 默认列表页面
     */
    function index($template = null, $cate = 0) {
        $v = $_GET['v'];
        if ($v != null) {
            $where = 'pc_m = ' . $v;
            $this->_where = $where;
        }
        import('ORG.Util.Page');
        $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
        $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('theme', '<span>%totalRow% %header% %nowPage%/%totalPage% 页</span>  %first%  %upPage% %linkPage%  %downPage% %end% %select%');// = array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%    %nextPage% %end%');
        $show = $Page->show();// 分页显示输出

        $list = $this->_model->where($this->_where)->limit($Page->firstRow . ',' . $Page->listRows)->order('`order` desc,id desc')->select();

        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        if ($template == null) {
            $this->display();
        } else {
            $this->display($template);
        }
    }

    /**
     * 基础添加方法
     */
    function add($template = null) {
        $l_id = $this->_get('l_id');
        if ($_POST) {
            if ($this->_action_name == 'Article' || $this->_action_name == 'Goods') {
                if (($this->_action_name == 'Article') && ($_POST['pid'] == 5 || $_POST['pid'] == 6)) {
                    if ($_FILES['img']['size'] != 0) {
                        $img_name = $this->UploadFile();
                        $_POST['img'] = $img_name[0];
                    }
                } else {
                    if ($_FILES['img1']['size'] != 0 or $_FILES['img2']['size'] != 0 or $_FILES['img3']['size'] != 0 or $_FILES['img4']['size'] != 0 or $_FILES['img5']['size'] != 0 or $_FILES['img6']['size'] != 0) {
                        $img_name_arr = array();
                        for ($i = 1; $i < 7; $i++) {
                            if ($_FILES['img' . $i] != null) {
                                if ($_FILES['img' . $i]['size'] != 0) {
                                    $img_name_n = $this->UploadFile($_FILES['img' . $i]);

                                    $img_name_arr[] = $img_name_n[0]['savename'];
                                }
                            }
                        }

                        for ($i = 1; $i < 7; $i++) {
                            if ($img_name_arr[$i - 1] != null) {
                                $_POST['img' . $i] = $img_name_arr[$i - 1];
                            } else {
                                $_POST['img' . $i] = '';
                            }
                        }

                        $_POST['img'] = $_POST['img1'];
                    }
                }
            } else {
                if ($_FILES['img']['size'] != 0) {
                    $img_name = $this->UploadFile();
                    $_POST['img'] = $img_name[0];
                }
            }
            if(in_array($this->_action_name, array('Goods'))){
                $cateid = $_POST['cate'];
                cookie('last_cate_id', $cateid,3600);
                $_POST['catid'] = $cateid;
                unset($_POST['cate']);
                unset($_POST['cate1']);
                unset($_POST['cate2']);

            }
            if ($this->_model->create()) {
                if ($_POST['batch'] == '1') {

                    foreach ($_POST['content'] as $value) {
                        $id = $this->_model->add($value);
                    }
                } else {
                    $id = $this->_model->add($_POST);
                }
                if ($id) {
                    //新闻产品添加记录后保留类别
                    if ($this->_action_name == 'Article' and $l_id == 6) {
                        $url = __GROUP__ . '/' . $this->_action_name . '/add/l_id/' . $l_id;
                    } else {
                        if ($this->_action_name == 'Goods' or $this->_action_name == 'Article') {
                            $w['id'] = $id;
                            $classid = $this->_model->where($w)->getField('pid');
                            $url = __GROUP__ . '/' . $this->_action_name . '/add/classid/' . $classid;
                        } else {
                            $url = __GROUP__ . '/' . $this->_action_name . '/Index';
                        }
                    }
                    if ($this->_action_name == 'Flash') {
                        if ($_POST['pc_m'] == 1) {
                            $url = __GROUP__ . '/' . $this->_action_name . '/m';
                        } else {
                            $url = __GROUP__ . '/' . $this->_action_name . '/pc';
                        }
                    }
                    //批量添加
                    if ($_POST['batch'] == '1') {
                        $url = __GROUP__ . '/' . $this->_action_name . '/batch/classid/' . $classid;
                    }
                    $this->success('添加成功', $url, C('JUMP_TIME'));
                } else {
                    var_dump($_POST);
                    // $this->error('添加失败');
                }
            } else {
                $this->error($this->_model->getError());
            }
            exit;
        }
        $v = $this->_get('v');
        $classid = $this->_get('classid');
        $search = array('value' => 'title');
        $this->assign('search', $search);

        if ($this->_action_name == 'Article') {
            $this->select_category_1();
        } else if (in_array($this->_action_name, array('Goods'))) {
            //行业类别
            $last_cate_id =cookie('last_cate_id');
            if (empty($last_cate_id)) {
                $last_cate_id = 0;
            }
            $data_row['cate'] = $last_cate_id;
            $industry = getCatelist();
            foreach ($industry as $k => $v) {
                if($v['pid'] == 0){
                    $cate1_list[$k] = $v;
                }
            }
            $cate = $data_row['cate'];
            if ($cate > 0) {
                $cate2_info = $industry[$industry[$cate]['pid']];
                $cate1_info = $industry[$cate2_info['pid']];
                $cate2_list = $this->getCateListbyParm($cate2_info['pid']); ;
                $cate_list = $this->getCateListbyParm($industry[$cate]['pid']);
            } else {
                $cate_list = $cate2_list = array();
            }
            $data_row['cate1'] = $cate1_info['id'];
            $data_row['cate2'] = $cate2_info['id'];
            $this->assign('cate1_list',$cate1_list);
            $this->assign('cate2_list', $cate2_list);
            $this->assign('cate', $cate_list);
            $this->assign('data_row',$data_row);
            $this->select_category();
        }
        $this->assign('v', $v);
        $this->assign('title_type', '新增');
        if (is_null($info)) {
            $info['open'] = 1;
            $info['order'] = 99;//默认排序99
            $info['time'] = date('Y-m-d H:i:s', time());
            $this->assign('info', $info);
        }
        if ($template == null) {
            $this->assign('classid', $classid);
            if ($l_id == 6) {
                $this->assign('l_id', $l_id);
                $this->display('edit_links');
                exit;
            }
            $this->display('edit');
        } else {
            $this->display($template);
        }
    }


    /**
     * 基础编辑方法
     */
    function edit($template = null) {
        $p = $this->_get('p');
        $l_id = $_GET['l_id'];
        if ($_POST) {
            $check_type = $_SESSION['check_type'];
            unset($_SESSION['check_type']);
            if ($this->_action_name == 'Article' || $this->_action_name == 'Goods') {
                if (($this->_action_name == 'Article') && ($_POST['pid'] == 5 || $_POST['pid'] == 6)) {
                    if ($_FILES['img']['size'] != 0) {
                        if ($_POST['img'] != null) {
                            $this->del_image($_POST['img']);
                        }

                        $img_name = $this->UploadFile();
                        $_POST['img'] = $img_name[0];
                    }
                } else {
                    if ($_FILES['img1']['size'] != 0 or $_FILES['img2']['size'] != 0 or $_FILES['img3']['size'] != 0 or $_FILES['img4']['size'] != 0 or $_FILES['img5']['size'] != 0 or $_FILES['img6']['size'] != 0) {
                        $img_name_arr = array();

                        for ($i = 1; $i < 7; $i++) {
                            if ($_FILES['img' . $i] != null) {
                                if ($_FILES['img' . $i]['size'] != 0) {
                                    if ($_POST['img' . $i] != null) {
                                        $this->del_image($_POST['img' . $i]);
                                    }
                                    $img_name_n = $this->UploadFile($_FILES['img' . $i]);
                                    $img_name_arr[] = $img_name_n[0]['savename'];
                                } else {
                                    if ($_POST['img' . $i] != null) {
                                        $img_name_arr[] = $_POST['img' . $i];
                                    }
                                }
                            }
                        }

                        for ($i = 1; $i < 7; $i++) {
                            if ($img_name_arr[$i - 1] != null) {
                                $_POST['img' . $i] = $img_name_arr[$i - 1];
                            } else {
                                $_POST['img' . $i] = '';
                            }
                        }

                        $_POST['img'] = $_POST['img1'];
                    }
                }
            } else {
                if ($_FILES['img']['size'] != 0) {
                    if ($_POST['img'] != null) {
                        $this->del_image($_POST['img']);
                    }
                    $img_name = $this->UploadFile();
                    $_POST['img'] = $img_name[0];
                }
            }
            if(in_array($this->_action_name, array('Goods'))){
                $cateid = $_POST['cate'];
                cookie('last_cate_id', $cateid,3600);
                $_POST['catid'] = $cateid;
                unset($_POST['cate']);
                unset($_POST['cate1']);
                unset($_POST['cate2']);
            }
            if ($this->_model->create()) {

                $id = $this->_model->save($_POST);
                if ($id) {
                    if ($l_id == 6 and $p == null) {
                        $url = __GROUP__ . '/' . $this->_action_name . '/links';
                    } elseif ($_POST['pc_m'] != null) {
                        if ($_POST['pc_m'] == 1) {
                            $url = __GROUP__ . '/' . $this->_action_name . '/m';
                        } else {
                            $url = __GROUP__ . '/' . $this->_action_name . '/pc';
                        }
                    } else {
                        if ($check_type != null) {
                            $url = __GROUP__ . '/' . $this->_action_name . '/Index/type/' . $check_type;
                        } else {
                            $newp = $this->_post('p');
                            $url = __GROUP__ . '/' . $this->_action_name . '/Index/p/' . $newp;
                        }
                    }

                    if ($this->_action_name == 'Goods') {
                        $message = array('msg' => '更新成功', 'status' => 'true', 'back_list_url' => $url, 'msg_name' => 'back_edit_msg', 'back_edit_msg' => '返回编辑', 'url_name' => 'back_edit_url', 'back_edit_url' => __GROUP__ . '/' . $this->_action_name . '/edit/id/' . $_POST['id'] . '/p/' . $p);
                    } else {
                        $message = '更新成功';
                    }
                    $this->success($message, $url);
                } else {
                    $this->error('数据没有保存或没有修改');
                }
            } else {
                $this->error($this->_model->getError());
            }
            exit;
        }
        if ($this->_action_name == 'Article') {
            $this->select_category_1();
        } else if (in_array($this->_action_name, array('Goods'))) {
            $this->select_category();
        }

        $search = array('value' => 'title');
        $this->assign('search', $search);
        $this->assign('title_type', '修改');

        $info = D($this->_model_name)->find($_REQUEST['id']);
        if(in_array($this->_action_name, array('Goods'))){
            //更新时间
            $info['time'] = date('Y-m-d H:i:s', time());
            //行业类别
            if(empty($info['catid'])){
                $info['catid'] = 0;
            }
            $industry = getCatelist();
            foreach ($industry as $k => $v) {
                if($v['pid'] == 0){
                    $cate1_list[$k] = $v;
                }
            }
            $cate = $info['catid'];
            $data_row['cate'] = $cate;
            if ($cate > 0) {
                $cate2_info = $industry[$industry[$cate]['pid']];
                $cate1_info = $industry[$cate2_info['pid']];
                $cate2_list = $this->getCateListbyParm($cate2_info['pid']);
                $cate_list = $this->getCateListbyParm($industry[$cate]['pid']);
            } else {
                $cate_list = $cate2_list = array();
            }
            $data_row['cate1'] = $cate1_info['id'];
            $data_row['cate2'] = $cate2_info['id'];
            $this->assign('cate1_list',$cate1_list);
            $this->assign('cate2_list', $cate2_list);
            $this->assign('cate', $cate_list);
            $this->assign('data_row',$data_row);
        }
        $img_arr = array();
        for ($i = 1; $i < 7; $i++) {
            if ($info['img' . $i] != null) {
                $img_arr[] = $info['img' . $i];
            }
        }

        if (count($img_arr) == 0) {
            $img_arr = null;
        }

        // 数据库中记录的域名
        $config_domain = M("config")
            ->where(array(
                'key' => array('eq', 'web_url'),
            ))
            ->find()["value"];
        $this->assign('domain',$config_domain);

        $this->assign('img_arr', $img_arr);
        $this->assign('info', $info);
        $this->assign('p', $p);
        if ($template == null) {
            if ($l_id == 6) {
                $this->assign('l_id', $l_id);
                $this->display('edit_links');
                exit;
            }
            $this->display();
        } else {
            $this->display($template);
        }
    }

    /**
     * 基本获取信息方法
     * @param unknown_type $template
     */
    function info($template = null) {
        $info = $this->_model->find($_REQUEST['id']);
        $this->select_category();
        $this->assign('info', $info);
        if ($template == null) {
            $this->display();
        } else {
            $this->display($template);
        }
    }

    /**
     * 批量添加
     * @return [type] [description]
     */
    public function batch() {
        if ($_FILES['img']['name'] != '') {
            $up_name = $_FILES['img']['name'];
            $up_name_ext = pathinfo($up_name, PATHINFO_EXTENSION);
            $up_name = str_replace('.' . $up_name_ext, '', $up_name); //去掉后缀

            $save_arr = $this->UploadFile();
            $save_name = $save_arr[0];

            $pid = $this->_post('pid');
            $titleBy = $this->_post('titleby');
            $title = $titleBy == 0 ? $this->_post('title') : $up_name;
            $data = array(
                'pid' => $pid, 'title' => $title, 'img' => $save_name, 'img1' => $save_name
            );
            if($this->_action_name == 'Goods'){
                //产品模块加入行业分类
                $cateid = $this->_post('cate','',0);
                cookie('last_cate_id',$cateid,3600);
                $data = array(
                        'pid' => $pid, 'title' => $title, 'img' => $save_name, 'img1' => $save_name,'catid'=>$cateid
                );
            }
            if (!$this->_model->add($data)) {
                $this->del_image($save_name);
            }
        } else {
            $last_cate_id = cookie('last_cate_id');
            if (empty($last_cate_id)) {
                $last_cate_id = 0;
            }
            $data_row['cate'] = $last_cate_id;
            $industry = getCatelist();
            foreach ($industry as $k => $v) {
                if($v['pid'] == 0){
                    $cate1_list[$k] = $v;
                }
            }
            $cate = $data_row['cate'];
            if ($cate > 0) {
                $cate2_info = $industry[$industry[$cate]['pid']];
                $cate1_info = $industry[$cate2_info['pid']];
                $cate2_list = $this->getCateListbyParm($cate2_info['pid']); ;
                $cate_list = $this->getCateListbyParm($industry[$cate]['pid']);
            } else {
                $cate_list = $cate2_list = array();
            }
            $data_row['cate1'] = $cate1_info['id'];
            $data_row['cate2'] = $cate2_info['id'];
            $this->assign('cate1_list',$cate1_list);
            $this->assign('cate2_list', $cate2_list);
            $this->assign('cate', $cate_list);
            $this->assign('data_row',$data_row);
            $this->select_category_1();
            $this->display();
        }
    }

    /**
     * 批量移动
     * @return [type] [description]
     */
    public function move() {
        $totype = (int)$_POST['totype'];
        //判断是否选择目标栏目
        if ($totype <= 0) {
            $this->error('请选择移动栏目');
        }
        $data['pid'] = $totype;
        $ids = ltrim($_POST['ids'], ',');
        $where = 'id in (' . $ids . ')';
        $res = $this->_model->where($where)->save($data);
        // 移动失败
        if ($res === false) {
            $this->error('移动失败');
        }
        // 移动成功
        $this->success('移动成功');

    }

    /**
     * 基础详细方法
     */
    function more($template = null) {
        $info = D($this->_model_name)->find($_REQUEST['id']);
        $this->assign('info', $info);
        if ($template == null) {
            $this->display();
        } else {
            $this->display($template);
        }
    }

    /**
     * 带分类的列表页面
     */
    function index_cate($template = null) {
        import('ORG.Util.Page');

        if ($this->_action_name == 'Article') {
            $where = "`pid` != 0 and `pid` != 6";
            $this->setWhere($where);
            $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
        } else {
            $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
        }

        $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('theme', '<span>%totalRow% %header% %nowPage%/%totalPage% 页</span>  %first%  %upPage% %linkPage%  %downPage% %end% %select%');
        $show = $Page->show();// 分页显示输出
        $list = $this->_model->where($this->_where)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();

        $result = D('Type')->select();

        foreach ($result as $key => $value) {
            $category[$value['id']] = $value['name'];
        }

        foreach ($list as $key => $value) {
            $list[$key]['cate_name'] = $category[$value['pid']];
        }
        if ($this->_action_name == 'Article') {
            $this->select_category_1();
        } else {
            $this->select_category();
        }

        $search = array('name' => 'code', 'value' => 'title');
        //print_r($search);
        $this->assign('search', $search);

        $input_hidden = '<input type="hidden" name="initialization" value="1" id="initialization">';
        $this->assign('input_hidden', $input_hidden);

        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        if ($template == null) {
            $this->display('index');
        } else {
            $this->display($template);
        }

    }

    /**
     * 基础后台搜索
     */
    public function search($template = null) {

        foreach ($_REQUEST as $key => $value) {
            if ($key !== '_URL_') {
                $value = trim($value);
                $this->_where[$key] = array('like', '%' . $value . '%');
            }
        }

        import('ORG.Util.Page');
        $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
        $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = $this->_model->where($this->_where)->limit($Page->firstRow . ',' . $Page->listRows)->order('`order` desc,id desc')->select();

        $result = D('Type')->select();
        foreach ($result as $key => $value) {
            $category[$value['id']] = $value['name'];
        }
        foreach ($list as $key => $value) {
            $list[$key]['cate_name'] = $category[$value['pid']];
        }
        $this->select_category();
        $search = array('name' => 'pid', 'value' => 'title');
        $this->assign('search', $search);
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        if ($template == null) {
            $this->display('index');
        } else {
            $this->display($template);
        }

    }

    /**
     * Ajax获取数据
     */
    public function ajax() {
        switch ($_REQUEST['t']) {
            //获取不同类型栏目的ajax数据
            case 'del':
                $where = array('id' => $_REQUEST['id']);
                //删除上除图片
                //$info = $this->_model->find($_REQUEST['id']);
                $result = $this->_model->where($where)->select();

                $this->del_images($result);

                $data = $this->_model->where($where)->delete();
                if ($data) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
                break;
            case 'batch_del':
                $where = array('id' => array('in', $_REQUEST['ids']));
                $result = $this->_model->where($where)->select();

                //删除上除图片
                $this->del_images($result);

                $data = $this->_model->where($where)->delete();

                if ($data) {
                    $this->success('删除成功');
                } else {
                    $this->error('删除失败');
                }
                break;
            case 'order':
                $where = array('id' => $_REQUEST['id']);
                //删除上除图片
                $info = $this->_model->find($_REQUEST['id']);
                $info['order'] = (int)$_REQUEST['value'];
                $id = $this->_model->save($info);
                if ($id) {
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
                break;
        }
    }


    /**
     * 设置添加查询
     * @param unknown_type $where
     */
    protected function setWhere($where) {
        $this->_where = $where;
    }

    /**
     *
     */

    /**
     * 上传图片封装
     * 直接调用就可以返回保存的图片名称,可以上传多个图片返回数组格式
     * @param unknown_type $file 上传文件名
     * @param unknown_type $is_watermark 是否开启水印
     * @param unknown_type $is_thumb 是否开启缩率图
     */
    protected function UploadFile($file = '', $is_watermark = '1', $is_thumb = '1') {
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();// 实例化上传类
        $upload->maxSize = C('UPLOAD_SIZE');// 设置附件上传大小
        $upload->allowExts = C('UPLOAD_TYPE');// 设置附件上传类型
        $upload->savePath = C('UPLOAD_DIR');// 设置附件上传目录

        import('ORG.Util.Image');
        $Image = new \Image();

        $dir = ROOT . '/' . C('UPLOAD_DIR');
        $dir_thumb = $dir; // 原图同目录下

        if ($file == '') {
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
            }
            //水印开关检测
            $switch_watermark = $this->config('switch_watermark');
            //是否有水印图片
            $web_watermark = $this->config('web_watermark');

            foreach ($info as $key => $value) {
                $result[] = $value['savename'];
                //上传图片水印处理
                if ($is_watermark && $switch_watermark) {
                    //处理图片水印
                    //$Image->water($dir.$value['savename'], $dir.$web_watermark);
                    //设置文字水印位置
                    $img_info = getimagesize($dir . $value['savename']);
                    $x = $img_info[0] / 2 - 50;
                    $y = $img_info[1] / 2 - 10;
                    //处理文字水印
                    $Image->showImg($dir . $value['savename'], $web_watermark, $x, $y);
                }
                //是否生成缩率图
                if ($is_thumb) {
                    $s = $Image->thumb($dir . $value['savename'], $dir_thumb . 'm_' . $value['savename'], '', 300, 300);
                }
            }
            return $result;
        } else {
            $info = $upload->uploadOne($file);

            if (!$info) { // 上传错误提示错误信息
                return null;
            } else { // 上传成功 获取上传文件信息
                //水印开关检测
                $switch_watermark = $this->config('switch_watermark');
                //是否有水印图片
                $web_watermark = $this->config('web_watermark');
                if ($is_watermark && $switch_watermark) {
                    //处理图片水印
                    //$Image->water($dir.$value['savename'], $dir.$web_watermark);
                    //设置文字水印位置
                    $img_info = getimagesize($dir . $info[0]['savename']);
                    $x = $img_info[0] / 2 - 50;
                    $y = $img_info[1] / 2 - 10;
                    //处理文字水印
                    $Image->showImg($dir . $info[0]['savename'], $web_watermark, $x, $y);
                }
                //是否生成缩率图
                if ($is_thumb) {
                    $s = $Image->thumb($dir . $info[0]['savename'], $dir_thumb . 'm_' . $info[0]['savename'], '', 300, 300);
                }
                return $info;
            }
        }


    }

    /**
     * 检测管理员是否登录
     */
    private function check_user() {
        if ($_SESSION['username'] == '') {
            //没有登录直接跳转登录页面
            header("Content-type:text/html;charset=utf-8");
            redirect(__GROUP__ . '/Login');
            exit;
        }
    }

    /**
     * 生成API数据接口调用TOKEN的方法
     */
    public function makeToken()
    {
        // token规则
        // 1.数据库取config表里的web_url ===>>> http://www.Zm.com
        // 2.web_url替换协议头为空,转小写 ===>>> www.zm.com
        // 3.加盐 ===>>> www.zm.comqzf_Gi7Co0
        // 4.2次MD5
        $url = $this->config("web_url");
        if(empty($url)){
            $url = "http://www.I0DolZ.com";
        }
        $url = str_replace(array("http://", "https://"), "", $url);
        $url = strtolower($url);
        $salt = 'qzf_Gi7Co0';
        return (md5(md5($url . $salt)));
    }

    /**
     * 删除上传的图片
     * @param unknown_type $img_name
     */
    public function del_image($img_name) {
        import('ORG.Util.File');
        $imgage_dir_name = C('UPLOAD_DIR') . $img_name;
        $imgage_dir_name_m = C('UPLOAD_DIR') . "m_" . $img_name;
        \File::unlinkFile($imgage_dir_name);
        \File::unlinkFile($imgage_dir_name_m);
    }

    //判断有多张图片时候，删除多张图片
    public function del_images($result) {
        if ($result != null) {
            foreach ($result as $key => $value) {
                if ($value['img'] != null && $value['img1'] != null) {
                    for ($i = 1; $i < 7; $i++) {
                        if ($value['img' . $i] != null) {
                            $this->del_image($value['img' . $i]);
                        }
                    }
                }
                if ($value['img'] != null && ($value['img1'] == null)) {
                    $this->del_image($value['img']);
                }
            }
        }
    }

    /**
     * 清除安装包
     */
    private function del_install() {
        $install_path = APP_PATH . 'Install';
        if (is_dir($install_path)) {
            import("ORG.Util.File");
            $file = new \File();
            $file->unlinkDir($install_path);
        }
    }

    protected function select_category() {
        $category = D('Type')->select_column(C($this->_action_name), true);
        $this->assign('category', $category);
        $selected = M($this->_model_name)->field('pid')->find($_REQUEST['id']);
        $this->assign('selected', $selected);
        return $category;
    }

    protected function select_category_1() {
        $category = D('Type')->select_column_1(C($this->_action_name), true);
        $this->assign('category', $category);
        $selected = M($this->_model_name)->field('pid')->find($_REQUEST['id']);
        $this->assign('selected', $selected);
        return $category;
    }

    protected function select_category_2() {
        $category = D('Type')->select_column_2(C($this->_action_name), true);
        $this->assign('category', $category);
        $selected = M($this->_model_name)->field('pid')->find($_REQUEST['id']);
        $this->assign('selected', $selected);
        return $category;
    }

    // 以下代码 for 微信互联
    public function weixin()
    {
        $step = $_GET['step'];
        if ($step == 1) {
            $wx_code = $this->config('wx_code');
            if (!$wx_code) {
                $wx_code = substr(md5(time().rand(100, 999)), 8, 16);
                $this->config('wx_code', $wx_code);
            }
            $this->assign('wx_code',$wx_code);
        }else{
            $wx_user = $this->config('wx_user');
            if ($wx_user) {
                $user = json_decode($wx_user, true);
                $this->assign('user',$user);
            }else{
                $this->redirect('Message/weixin', 'step=1');
            }
        }
        $this->display();
    }

    public function qrcode()
    {
        $wx_code = $this->config('wx_code');
        if (!$wx_code) {
            $wx_code = substr(md5(time().rand(100, 999)), 8, 16);
            $this->config('wx_code', $wx_code);
        }
        $parmas['action'] = 'qrcode';
        $parmas['code'] = $wx_code;
        $parmas['from'] = 1;
        $res =  http_transport('http://mswx.myqingfeng.cn/api.php',$parmas,'GET');
        header('Content-type: image/jpeg');
        echo $res;
        exit();
    }

    public function wxuser()
    {
        $wx_code = $this->config('wx_code');
        $parmas['action'] = 'user';
        $parmas['code'] = $wx_code;
        $parmas['url'] = urlencode('http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/');
        $parmas['from'] = 1;
        $ret = http_transport('http://mswx.myqingfeng.cn/api.php',$parmas,'GET');
        $data = json_decode($ret,true);
        if ($data['status'] == 1) {
            $this->config('wx_user', $data['msg']);
            echo "ok";
        }else{
            echo $data['msg'];
        }
    }

    public function unlink()
    {
        $openid = $_GET['id'];
        $wx_code = $this->config('wx_code');

        $parmas['action'] = 'unlink';
        $parmas['userid'] = $openid;
        $parmas['code'] = $wx_code;
        $ret = http_transport('http://mswx.myqingfeng.cn/api.php',$parmas,'GET');
        $data = json_decode($ret,true);
        if ($data['status'] == 1) {
            $this->config('wx_user', $data['msg']);
            $this->success('取消绑定成功', U('message/weixin'));
        }else{
            $this->error($data['msg'], U('message/weixin'));
        }
        exit();
    }
    // 以上代码 for 微信互联


    public function getCateListbyParm($pid)
    {
        $cate_list_id = $pid ? $pid : 0;
        $cate = getCatelist();
        foreach ($cate as $k => $v) {
            if($v['pid'] == $cate_list_id){
                $rows[$k] = $v;
            }
        }
        if (empty($rows)) {
            $rows = array();
        }
        return $rows;
    }


}