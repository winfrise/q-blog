<?php
/**
 * 系统内容管理
 * Enter description here ...
 * @author Administrator
 *
 */
namespace Admin\Controller;

class SystemController extends BaseController {
    function __construct() {
        parent::__construct('System', 'Config');
    }

    /**
     * 系统配置项管理
     * @see BaseController::index()
     */
    public function index() {

        if ($_POST) {
            $result = $this->_model->where('`type`="file"')->select();
            //有文件上传操作处理
            foreach ($result as $key => $value) {
                if ($_FILES[$value['key']]['name']) {
                    $img_name = $this->UploadFile($_FILES[$value['key']], 0, 1);
                    $data['value'] = $img_name['0']['savename'];
                    $this->_model->create();
                    $id = $this->_model->where('`key`="' . $value['key'] . '"')->save($data);
                }
            }

            $result = $this->_model->where('`type`!="file" and `group_id`!=0')->select();
            foreach ($result as $key => $value) {
                if (array_key_exists($value['key'], $_POST)) {
                    $data['value'] = $_POST[$value['key']];
                    $this->_model->create();
                    $id = $this->_model->where('`key`="' . $value['key'] . '"')->save($data);
                }
            }
            $url = __GROUP__ . '/' . $this->_action_name . '/Index/t/' . $_GET['t'];
            $this->success('更新成功', $url);
            exit;
        }


        $group_id = $_REQUEST['t'];
        if (!$group_id) {
            $group_id = 1;
        }
        $where = 'group_id = ' . $group_id;
        $data = $this->_model->where($where)->select();

        //特殊处理模板
        $path = APP_PATH . 'Home/View';
        $dirname = sdir($path);
        foreach ($data as $key => $value) {
            if ($value['key'] == 'current_theme') {
                $data[$key]['type'] = 'select';
                $data[$key]['options'] = implode(',', $dirname);
            }
        }

        foreach ($data as $key => $value) {
            switch ($value['type']) {
                case 'text':
                    $list[$key]['name'] = $value['name'];
                    $list[$key]['key'] = $value['key'];
                    if ($value['options'] == 'number') {
                        $list[$key]['input'] = "<input type='number' class='txt' min='1' id='" . $value['key'] . "' name='" . $value['key'] . "' size='35' value='" . $value['value'] . "' />\r\n";
                    } else {
                        $list[$key]['input'] = "<input type='text' class='txt' id='" . $value['key'] . "' name='" . $value['key'] . "' size='35' value='" . $value['value'] . "' />\r\n";
                    }

                    break;
                case 'textarea':
                    $list[$key]['name'] = $value['name'];
                    $list[$key]['input'] = "<textarea name='" . $value['key'] . "' id='" . $value['key'] . "' cols='88' rows='10' class='txt' >" . $value['value'] . "</textarea>\r\n";
                    break;
                case 'file':
                    $list[$key]['name'] = $value['name'];
                    $list[$key]['input'] = "<input type='file' name='" . $value['key'] . "' class='txt'>\r\n";
                    if ($value[value] != '') {
                        $list[$key]['input'] .= '<a href="'.__ROOT__.'/' . C('UPLOAD_DIR') . $value[value] . '" target="_black">查看图片</a>';
                    }
                    break;
                case 'checkbox':
                    $list[$key]['name'] = $value['name'];
                    $arr = explode(',', $value['options']);
                    foreach ($arr as $k => $val) {
                        if ($value['value'] == $key) {
                            $list[$key]['input'] .= "<label><input type='checkbox' name='checkbox' value='" . $key . " checked '>$value </label>\r\n";
                        } else {
                            $list[$key]['input'] .= "<label><input type='checkbox' name='checkbox' value='" . $key . " '>$value </label>\r\n";
                        }
                    }
                    break;
                case 'select':
                    $list[$key]['name'] = $value['name'];
                    $list[$key]['input'] = "<select name='$value[key]' >\r\n";
                    $arr = explode(',', $value['options']);
                    if ($value['key'] == 'current_theme') //特殊处理选择模板
                    {
                        foreach ($arr as $k => $val) {
                            if ($value['value'] == $val) {
                                $list[$key]['input'] .= "<option value='$val' selected >$val</option>\r\n";
                            } else {
                                $list[$key]['input'] .= "<option value='$val'>$val</option>\r\n";
                            }
                        }
                    } else {
                        foreach ($arr as $k => $val) {
                            if ($value['value'] == $key) {
                                $list[$key]['input'] .= "<option value='$k' selected >$val</option>\r\n";
                            } else {
                                $list[$key]['input'] .= "<option value='$k'>$val</option>\r\n";
                            }
                        }
                    }
                    $list[$key]['input'] .= "</select>";
                    break;
                case 'radio':
                    $list[$key]['name'] = $value['name'];
                    $arr = explode(',', $value['options']);
                    foreach ($arr as $k => $val) {
                        if ($value['value'] == $k) {
                            $list[$key]['input'] .= "<label><input type='radio' name='$value[key]' value='$k' checked > $val</label>\r\n";
                        } else {
                            $list[$key]['input'] .= "<label><input type='radio' name='$value[key]' value='$k'> $val</label>\r\n";
                        }
                    }
                    break;
            }
        }

        $this->assign('list', $list);
        $this->assign("group", $group_id);
        switch ($group_id) {

            case '3':
                $this->assign('Xpoint', $this->config('baidu_map_x'));
                $this->assign('Ypoint', $this->config('baidu_map_y'));
                $this->assign('linkman', $this->config('linkman'));
                $this->assign('tel', $this->config('tel'));
                $this->assign('address', $this->config('address'));
                $this->assign('web_name', $this->config('web_name'));
                $this->display('baidu_map');
                break;
            default:
                $this->display();
        }
    }
}