<?php
/**
 * 后台招聘管理
 * Enter description here ...
 * @author Administrator
 *
 */

namespace Admin\Controller;

class JobsController extends BaseController {
    /**
     *
     * Enter description here ...
     */
    public function __construct() {
        parent::__construct("Jobs", 'jobs');
    }

    public function index() {
        $type = $this->_get('type');

        $_SESSION['check_type'] = $type;

        if (!$type) {
            import('ORG.Util.Page');
            $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
            $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('theme', '<span>%totalRow% %header% %nowPage%/%totalPage% 页</span>  %first%  %upPage% %linkPage%  %downPage% %end% %select%');
            $show = $Page->show();// 分页显示输出
            if($count){
                $list = $this->_model->where($this->_where)->limit($Page->firstRow . ',' . $Page->listRows)->order('`order` desc,`id` desc')->select();
            }
            $result = D('Type')->select();
            foreach ($result as $key => $value) {
                $category[$value['id']] = $value['name'];
            }
            $salary = C('ZPCONFIG.ZP_SALARY');
            foreach ($list as $key => $value) {
                $list[$key]['cate_name'] = $category[$value['pid']];
                $list[$key]['salary'] = $salary[$value['salary']];
            }
            $this->select_category();

            $search = array('value' => 'job');
            $this->assign('search', $search);
            $this->assign('list', $list);// 赋值数据集
            $this->assign('page', $show);// 赋值分页输出

            $this->display('index');
        } else {
            //显示该类别下所有产品
            import('ORG.Util.Page');
            $w['code'] = array('like', '%,' . $type . ',%');
            $result = M("Type")->where($w)->order('code')->select();
            foreach ($result as $key => $value) {
                $pidarray .= $value['id'] . ',';
            }
            $pidarray = substr($pidarray, 0, -1);
            $where['pid'] = array('in', $pidarray);

            $count = M('Jobs')->where($where)->count();// 查询满足要求的总记录数
            $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('theme', '<span>%totalRow% %header% %nowPage%/%totalPage% 页</span>  %first%  %upPage% %linkPage%  %downPage% %end% %select%');
            $show = $Page->show();// 分页显示输出
            if($count){
                $list = M('Jobs')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('`order` desc,id desc')->select();
            }
            //类别、工资
            $salary = C('ZPCONFIG.ZP_SALARY');
            foreach ($list as $key => $value) {
                $list[$key]['cate_name'] = M('Type')->where("`id`={$value['pid']}")->getField('name');
                $list[$key]['salary'] = $salary[$value['salary']];
            }
            $this->select_category();
            $search = array('name' => 'code', 'value' => 'title');
            $this->assign('search', $search);
            $this->assign('list', $list);// 赋值数据集
            $this->assign('page', $show);// 赋值分页输出
            $this->display('index');
        }


    }

    /**
     * 基础添加方法
     * Enter description here ...
     */
    function add($template = null) {
        if ($_POST) {
            if ($_FILES['img']['size'] != 0) {
                $img_name = $this->UploadFile();
                //z($img_name);
                $_POST['img'] = $img_name[1];
            }
            $cateid = $_POST['cate'];
            $_POST['catid'] = $cateid;
            unset($_POST['cate']);
            unset($_POST['cate1']);
            unset($_POST['cate2']);
            //生成cookie
            cookie('last_jobcate_id', $cateid,3600);
            cookie('area_id',$_POST['area_id'],3600);
            cookie('property',$_POST['property'],3600);
            cookie('scale',$_POST['scale'],3600);
            if ($this->_model->create()) {
                $id = $this->_model->add($_POST);
                if ($id) {
                    $url = __GROUP__ . '/' . $this->_action_name . '/Index';
                    $this->success('添加成功', $url, C('JUMP_TIME'));
                } else {
                    $this->error('添加失败');
                }
            } else {
                $this->error($this->_model->getError());
            }
            exit;
        }
        $search = array('value' => 'job');
        $this->assign('search', $search);
        $last_cate_id = cookie('last_jobcate_id');
        if (empty($last_cate_id)) {
            $last_cate_id = 0;
        }
        //获取行业分类
        $data_row['cate'] = $last_cate_id;
        $industry = getZhaopinlist();
        foreach ($industry as $k => $v) {
            if($v['pid'] == 0){
                $cate1_list[$k] = $v;
            }
        }
        $cate = $data_row['cate'];
        if ($cate > 0) {
            $cate2_info = $industry[$industry[$cate]['pid']];
            $cate1_info = $industry[$cate2_info['pid']];
            $cate2_list = $this->getZPListbyParm($cate2_info['pid']); ;
            $cate_list = $this->getZPListbyParm($industry[$cate]['pid']);
        } else {
            $cate_list = $cate2_list = array();
        }
        $data_row['cate1'] = $cate1_info['id'];
        $data_row['cate2'] = $cate2_info['id'];
        $this->assign('cate1_list',$cate1_list);
        $this->assign('cate2_list', $cate2_list);
        $this->assign('cate', $cate_list);
        $this->assign('data_row',$data_row);
        //获取招聘配置
        $zp_config = getZhaopinConfig();
        $info =array(
                'area_id' => cookie('area_id')?cookie('area_id'):'',
                'property' => cookie('property')?cookie('property'):'',
                'scale' => cookie('scale')?cookie('scale'):'',
        );
        //获取当前城市信息和地区信息
        $area = getArealist();
        if($info['area_id']>0){
            foreach($area['area'] as $k=>$v){
                if($v['id'] == $info['area_id']){
                    $info['city_id'] = $v['city_id'];
                    continue;
                }else{
                    $area_list = $city_list = array();
                }
            }
            if(!empty($info['city_id'])){
                foreach($area['area'] as $k=>$v){
                    if($v['city_id'] == $info['city_id']){
                        $area_list[] = $v;
                    }
                }
                foreach($area['city'] as $k=>$v){
                    if($v['id'] == $info['city_id']){
                        $info['province_id'] = $v['province_id'];
                        continue;
                    }else{
                        $city_list = array();
                    }
                }
                if(!empty($info['province_id'])){
                    foreach($area['city'] as $k=>$v){
                        if($v['province_id'] == $info['province_id']){
                            $city_list[] = $v;
                        }
                    }
                }
            }
        }else{
            $area_list = $city_list = array();
        }

        $this->select_category();
        $this->assign('zpconfig',$zp_config);
        $this->assign('province_list', $area['province']);
        $this->assign('city_list', $city_list);
        $this->assign('area_list', $area_list);
        $this->assign('data_row', $data_row);
        $this->assign('info',$info);
        $this->assign('title_type', '新增');
        if ($template == null) {
            $this->display('edit');
        } else {
            $this->display($template);
        }
        //$this->display('edit');
    }

    /**
     * 基础编辑方法
     * Enter description here ...
     */
    function edit($template = null) {
        if ($_POST) {
            if ($_FILES['img']['size'] != 0) {
                $img_name = $this->UploadFile();
                $_POST['img'] = $img_name[1];
            }
            $cateid = $_POST['cate'];
            $_POST['catid'] = $cateid;
            unset($_POST['cate']);
            unset($_POST['cate1']);
            unset($_POST['cate2']);
            //生成cookie
            cookie('last_jobcate_id', $cateid,3600);
            cookie('area_id',$_POST['area_id'],3600);
            cookie('property',$_POST['property'],3600);
            cookie('scale',$_POST['scale'],3600);
            if ($this->_model->create()) {
                $id = $this->_model->save($_POST);
                if ($id) {
                    $check_type = $_SESSION['check_type'];
                    unset($_SESSION['check_type']);

                    if ($check_type != null) {
                        $url = U('Jobs/Index', 'type=' . $check_type);
                    } else {
                        $url = U('Jobs/Index');
                    }
                    $this->success('更新成功', $url);
                } else {
                    $this->error('数据没有保存或没有修改');
                }
            } else {
                $this->error($this->_model->getError());
            }
            exit;
        }
        $this->select_category();

        $search = array('value' => 'job');
        $this->assign('search', $search);
        $this->assign('title_type', '修改');

        $info = D($this->_model_name)->find($_REQUEST['id']);
        if(empty($info['catid'])){
            $info['catid'] = 0;
        }
        $industry = getZhaopinlist();
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
            $cate2_list = $this->getZPListbyParm($cate2_info['pid']); ;
            $cate_list = $this->getZPListbyParm($industry[$cate]['pid']);
        } else {
            $cate_list = $cate2_list = array();
        }
        $data_row['cate1'] = $cate1_info['id'];
        $data_row['cate2'] = $cate2_info['id'];
        $this->assign('cate1_list',$cate1_list);
        $this->assign('cate2_list', $cate2_list);
        $this->assign('cate', $cate_list);
        $this->assign('data_row',$data_row);
        //获取招聘配置
        $zp_config = getZhaopinConfig();
        //获取当前城市信息和地区信息
        $area = getArealist();
        if($info['area_id']>0){
             foreach($area['area'] as $k=>$v){
                 if($v['id'] == $info['area_id']){
                     $info['city_id'] = $v['city_id'];
                     continue;
                 }else{
                     $area_list = $city_list = array();
                 }
             }
             if(!empty($info['city_id'])){
                 foreach($area['area'] as $k=>$v){
                     if($v['city_id'] == $info['city_id']){
                         $area_list[] = $v;
                     }
                 }
                 foreach($area['city'] as $k=>$v){
                     if($v['id'] == $info['city_id']){
                         $info['province_id'] = $v['province_id'];
                         continue;
                     }else{
                         $city_list = array();
                     }
                 }
                 if(!empty($info['province_id'])){
                     foreach($area['city'] as $k=>$v){
                         if($v['province_id'] == $info['province_id']){
                             $city_list[] = $v;
                         }
                     }
                 }
             }
        }else{
            $area_list = $city_list = array();
        }
        $this->assign('zpconfig',$zp_config);
        $this->assign('province_list', $area['province']);
        $this->assign('city_list', $city_list);
        $this->assign('area_list', $area_list);
        $this->assign('info', $info);
        if ($template == null) {
            $this->display();
        } else {
            $this->display($template);
        }
    }

    public function search() {
        //z($_POST);
        //print_r($_REQUEST);
        foreach ($_REQUEST as $key => $value) {
            if ($key !== '_URL_') {
                $value = trim($value);
                $this->_where[$key] = array('like', '%' . $value . '%');
            }
        }

        //z($this->_where);

        import('ORG.Util.Page');
        $count = $this->_model->where($this->_where)->count();// 查询满足要求的总记录数
        $Page = new \Page($count);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = $this->_model->where($this->_where)->order('`order` desc,id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $result = D('Type')->select();
        foreach ($result as $key => $value) {
            $category[$value['id']] = $value['name'];
        }
        foreach ($list as $key => $value) {
            $list[$key]['cate_name'] = $category[$value['pid']];
        }
        $this->select_category();

        $search = array('value' => 'job');
        $this->assign('search', $search);
        $this->assign('set_title', $_REQUEST['job']);
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display('index');
    }

    public function qfjob() {
        $w['key'] = 'qfjob';
        if ($this->_post()) {
            $qfcode = $this->_post('qfcode');
            $data['value'] = $qfcode;
            $r = M('config')->where($w)->save($data);
            if ($r) {
                $this->success('修改成功！', __GROUP__ . '/Jobs/qfjob', C('JUMP_TIME'));
            } else {
                $this->error('添加失败！', __GROUP__ . '/Jobs/qfjob');
            }
        }
        $qfcode = M('config')->where($w)->getField('value');
        $status = M('config')->where($w)->getField('group_id');
        $this->assign('qfcode', $qfcode);
        $this->assign('status', $status);
        $this->display();

    }

    public function getZPListbyParent(){
        $cate_list_id = I('cate_list_id', 0);
        $cate = getZhaopinlist();
        foreach ($cate as $k => $v) {
            if($v['pid'] == $cate_list_id){
                $rows[$k] = $v;
            }
        }
        if (empty($rows)) {
            $rows = array();
        }
        $rows =  array_values($rows);
        $this->ajaxReturn($rows);
    }

    public function getCityListByProvince(){
        $province_id = I('province_id', 0);
        $citylist = C('AREALIST.city');

        $rows = array();
        if(!empty($citylist)){
            foreach($citylist as $k=>$v){
                if($v['province_id'] == $province_id){
                    $rows[] = $v;
                }
            }
        }
        $this->ajaxReturn($rows);
    }

    public function getAreaListByCity(){
        $city_id = I('city_id', 0);
        $arealist = C('AREALIST.area');
        $rows = array();
        if(!empty($arealist)){
            foreach($arealist as $k=>$v){
                if($v['city_id'] == $city_id){
                    $rows[] = $v;
                }
            }
        }
        $this->ajaxReturn($rows);
    }

    public function getZPListbyParm($pid)
    {
        $cate_list_id = $pid ? $pid : 0;
        $cate = getZhaopinlist();
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