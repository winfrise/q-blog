<?php
/**
 * [decription] : api接口类
 * [use] : 用于同步Qtouch
 */
namespace Home\Controller;

use Think\Controller;

class ApiController extends Controller {

    private $error; // 接口错误消息
    private $version; // 同步版本列表
    public $debug = false; // 调试模式

    // API入口函数
    public function index($action) {
        header("Content-Type: text/html; charset=utf-8");
        $_request = I('request.');
        if ($action == 'check') {
            $result = $this->check();
        } else {
            // $this->debug = true;
            // 非调试模式，权限验证
            if ($this->debug == false) {
                $api_secret = C('API_SECRET');
                if (md5($api_secret) != $_request['signature']) {
                    exit('签名错误');
                }
            }
            if ($action == 'data') {
                $result = $this->excute($_request);
            } else if ($action == 'synchro') {
                $result = $this->synchro() ? 'ok' : $this->error;
                $this->debug = false;
            }
        }
        // 调试模式不输出只返回
        if ($this->debug) {
            return $result;
        }
        //返回数据
        echo $result;
    }

    /**
     *智美云接收添加信息同步的接口
     */
    public function addMessageInfo() {
        //智美云接收留言信息接口
        if(!isset($action)){
            if(_request('type')=='sendMessage'){
                $this->postMessage(_request());
                exit();
            }
        }
    }

    /**
     * 智美云客户管理 留言客户信息同步接口 每次请求添加一个留言客户 可以是qftouch juqi360 站点的数据 客户保持唯一性
     * 需要标识客户来源渠道 便于管理该智美云网站手机站相关的留言客户信息汇总
     * @param $request
     */
    public function postMessage($request){
        //是否需要同步 如果没有开通qftouch站点不需要同步直接取消
        $api_id = C('API_ID');
        if ($api_id!=$request['api_id']) {
            echo json_encode(array('status'=>3,'msg'=>'该智美云没有开通qftouch站点，不需要同步信息'));
            exit('该智美云没有开通qftouch站点，不需要同步信息');
        }
        // 非调试模式，权限验证
        if ($this->debug == false) {
            $api_secret = C('API_SECRET');
            if (md5($api_secret) != $request['signature']) {
                echo json_encode(array('status'=>0,'msg'=>'签名错误'));
                exit('签名错误');
            }
        }
        if ($_POST) {
            $signature = $request['signature'];
            $time =decrypt($signature, 'qftouch');
            if (time() - $time < 3600) { //时间误差小于3600秒
                echo json_encode(array('status'=>-1,'msg'=>'请求时间限制'));
                exit();
            }
            //Z($request);
            //添加留言 数据到智美云
            $message['name'] =$request['link_man'];
            $message['tel'] =$request['tel'];
            $message['add'] =$request['company_address'];
            $message['email'] =$request['e_mail'];
            $message['content'] =$request['remark'];
            $message['ip'] = get_client_ip();
            $message['time'] =dateTime();
            $message['reply'] ='';
            $message['is_show'] =1;
            if(webResource()=='聚企360'){
                $message['is_mobile'] =0;
            }else if(webResource()=='QFTouch'){
                $message['is_mobile'] =1;
            }else if(webResource()=='官网'){
                $message['is_mobile'] =0;
            }else{
                $message['is_mobile'] =0;
            }
            $message['order'] =0;
            $message['resource'] =webResource();
            //Z($message);
            $where['name']=$message['name'];
            $where['content']=$message['content'];
            $is_message=M('message')->where($where)->find();
            $id=0;
            if(!$is_message){
                $id=M('message')->add($message);
            }
            /**
             * 留言成功自动添加默认客户 该方法新加的 每次留言都会调用
             * 同步留言客户  2019-5-5注释
             */
            //if($id!=0){
            //    $this->addCustomer($request['type'],$message,$id);
            //}
            //Z(json_decode(json_encode(array('status'=>1,'data'=>$request))));
            echo json_encode(array('status'=>1,'data'=>$request,'msg'=>'接口调用成功'));
            exit();
        }
        echo json_encode(array('status'=>2,'msg'=>'接口接收数据失败'));
        exit('接口接收数据失败');
    }

    public function addCustomer($type,$data,$id){
        $data['id']=$id;
        //Z($data);
        $customer=array();
        if($_SESSION['user_id']){
            $customer['uid']=$_SESSION['user_id'];
        }
        switch ($type){
            case 'message':
                $customer['link_man']=$data['name'];
                $customer['mobile']=$data['tel'];
                $customer['tel']=$data['tel'];
                $customer['e_mail']=$data['email'];
                $customer['company_address']=$data['add'];
                $customer['remark']=$data['content'];
                $customer['mid']=$data['id'];
                break;
            case 'order':
                $customer['link_man']=$data['name'];
                $customer['mobile']=$data['tel'];
                $customer['tel']=$data['tel'];
                $customer['e_mail']=$data['email'];
                $customer['company_address']=$data['add'];
                $customer['remark']=$data['notes'];
                $customer['mid']=$data['id'];
                break;
            case 'sendMessage':
                $customer['link_man']=$data['name'];
                $customer['mobile']=$data['tel'];
                $customer['tel']=$data['tel'];
                $customer['e_mail']=$data['email'];
                $customer['company_address']=$data['add'];
                $customer['remark']=$data['content'];
                $customer['mid']=$data['id'];
                break;
        }
        //$customer['source']=$data['ip'];
        $customer['source']=webResource();
        $customer['create_time']=date('Y-m-d H:i:s',time());
        //Z($customer);
        $where['link_man']=$customer['link_man'];
        $isExist=M('Customer')->where($where)->find();
        //存在的客户不用重复添加 只记录第一次留言客户的信息 要查这个客户留言记录 可以通过名字去信息表查询 此处根据业务可以继续开发
        if(!$isExist){
            M('Customer')->add($customer);
        }
    }

    /**
     * 执行
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function excute($data) {
        //类似解析表名
        $type = $this->parseType($data['type'], $data['obj']);
        //解析where条件
        $where = $this->parseWhere($data['type'], $data['obj']);
        //取出数据
        if ($type == 'Article' && $data['obj'] == -1) { //公司简介特殊处理
            $result = M($type)->field('title,content')->where($where)->find();
            $result['id'] = -1;
        } else if ($type == 'Article' && $data['type'] == 5) {
            $result = M($type)->field('id,title,content')->where($where)->find();
        } else if ($type == 'Article' || $type == 'Goods') {
            $db_pre = C("DB_PREFIX");
            $table_name = $db_pre . strtolower($type);
            $id = $where['id'];
            $where["$table_name.id"] = $where['id'];
            unset($where['id']);
            // 与type表联合查询
            $result = M($type)->field("{$table_name}.*,{$db_pre}type.code")->join("{$db_pre}type on {$table_name}.pid={$db_pre}type.id")->where($where)->find();
        } else if ($type == 'Config') {
            $result = M($type)->where($where)->select();
        }else if($type == 'Jobs'){
            $result = M($type)->field('id,job,experience,type,salary,address,area_id,education,scale,property,catid,content,num,time,end_time')->where($where)->find();
        }
        else if($type == 'Apply'){
            $result =M($type)->field('id,a_id,name,sex,email,tel,note,addtime')->where($where)->find();
        }
        else {
            $result = M($type)->where($where)->find();
        }
        //如果没有数据，返回字信息
        if (is_null($result)) {
            return 'no find';
        }
        //格式化数据
        $final_data = array();
        $content = $this->parseResult($result, $data['type']);
        $final_data['check'] = md5(json_encode($content));
        $final_data['content'] = $content;
        //返回数据
        return json_encode($final_data);
    }

    /**
     * 格式化数据
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    private function parseResult($result, $type) {
        // 修正内容中图片路径 本地->完整路径
        if ($result['content']) {
            $content = $result['content'];
            $domain = 'http://' . $_SERVER['HTTP_HOST'] . '/';
            preg_match_all('/(src|SRC)=["|\'| ]{0,}((?!http:\/\/|\'|")(.*)\\.(gif|jpg|jpeg|bmp|png))/isU', $content, $img_arr);
            $img_arr = array_unique($img_arr[2]);
            foreach ($img_arr as $img_url) {
                $new_url = $domain . ltrim($img_url, '/');
                $result['content'] = str_replace($img_url, $new_url, $content);
                $content = $result['content'];
            }
        }
        $data = array();
        if ($type == 1 || $type == 6) {
            // 新闻、自助
            $data['id'] = $result['id'];
            $data['pid'] = $result['pid'];
            $data['title'] = $result['title'];
            $data['img'] = empty($result['img']) ? '' : 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/' . C('UPLOAD_DIR') . $result['img'];
            $data['content'] = $result['content'];
            $data['price'] = empty($result['price']) ? '' : $result['price'];
            $data['date'] = $result['time'];
            $data['order'] = $result['order'];
        }elseif($type == 2){
            //产品
            $data['id'] = $result['id'];
            $data['pid'] = $result['pid'];
            $data['cateid'] = $result['catid'];//新增同步行业分类字段，2019/4/18
            $data['title'] = $result['title'];
            $data['img'] = empty($result['img']) ? '' : 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/' . C('UPLOAD_DIR') . $result['img'];
            $data['content'] = $result['content'];
            $data['price'] = empty($result['price']) ? '' : $result['price'];
            $data['date'] = $result['time'];
            $data['order'] = $result['order'];
        }elseif ($type == 3) {
            // 分类
            $data['id'] = $result['id'];
            $data['name'] = $result['name'];
            $data['parent'] = $result['parent'];
            $data['flag'] = 0;
            if ($result['id'] == '2') { // 产品
                $data['flag'] = 1;
            } else if ($result['id'] == '4') { //新闻
                $data['flag'] = 2;
            }
        } elseif ($type == 4) {
            // 网站参数
            foreach ($result as $key => $val) {
                $data[$val['key']] = $val['value'];
            }
            $data['logo'] = empty($data['logo']) ? '' : 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/' . C('UPLOAD_DIR') . $data['logo'];
            $data['map'] = $data['baidu_map_x'] . ',' . $data['baidu_map_y'];
            unset($data['baidu_map_x'], $data['baidu_map_y']);
        } elseif ($type == 5) {
            // 公司简介、联系我们
            if (isset($result['content'])) { //有content字段，代表是公司简介
                $data['id'] = $result['id'];
                $data['title'] = $result['title'];
                $data['content'] = $result['content'];
            } else {
                $data['id'] = -2;
                $data['title'] = '联系我们';
                foreach ($result as $key => $val) {
                    $result[$val['key']] = $val['value'];
                }
                $data['content'] .= $result['linkman'] ? sprintf('<p>联系人：%s</p>', $result['linkman']) : '';
                $data['content'] .= $result['tel'] ? sprintf('<p>联系电话：%s</p>', $result['tel']) : '';
                $data['content'] .= $result['mobile'] ? sprintf('<p>手机号码：%s</p>', $result['mobile']) : '';
                $data['content'] .= $result['fax'] ? sprintf('<p>传真：%s</p>', $result['fax']) : '';
                $data['content'] .= $result['email'] ? sprintf('<p>邮箱：%s</p>', $result['email']) : '';
                $data['content'] .= $result['address'] ? sprintf('<p>地址：%s</p>', $result['address']) : '';
            }
        }elseif ($type == 7){
            //人才招聘 智美字段id,job,experience,type,salary,address,area_id,education,scale,property,catid,num,end_time
            //job、scale、catid、num 和聚企字段名称不一样
            $data['id'] = $result['id'];
            $data['job'] = $result['job'];
            $data['experience'] = $result['experience'];
            $data['type'] = $result['type'];
            $data['salary'] = $result['salary'];
            $data['address'] = $result['address'];
            $data['area_id'] = $result['area_id'];
            $data['education'] = $result['education'];
            $data['property'] = $result['property'];
            $data['scale'] = $result['scale'];
            $data['cateid'] = $result['catid'];
            $data['content'] = $result['content'];
            $data['num'] = $result['num'];
            $data['add_time'] = $result['time'];
            $data['end_time'] = $result['end_time'];
        }elseif ($type == 8){
            //应聘
            $data['id'] = $result['id'];
            $data['name'] = $result['name'];
            $data['sex'] = $result['sex'];
            $data['phone'] = $result['tel'];
            $data['email'] = $result['email'];
            $data['a_id'] = $result['a_id'];
            $data['remark'] = $result['note'];
            $data['add_time'] = $result['addtime'];
        }
        //返回数据
        return $data;

    }

    /**
     * 解析数据模型
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function parseType($type, $obj) {
        switch ($type) {
            case '1':
                $type = 'Article';
                break;
            case '2':
                $type = 'Goods';
                break;
            case '3':
                $type = 'Type';
                break;
            case '4':
                $type = 'Config';
                break;
            case '5': // 公司简介Article、联系我们Config
                $type = $obj == -2 ? 'Config' : 'Article';
                break;
            case '6':
                $type = 'Article';
                break;
            case '7':
                $type = 'Jobs';
                break;
            case '8':
                $type = 'Apply';
                break;
        }
        //返回数据模型类别
        return $type;
    }

    /**
     * 解析语句条件
     * @param  [type] $obj [description]
     * @return [type]      [description]
     */
    private function parseWhere($type, $obj) {
        if ($type == 4) {
            $key = 'key';
            $where[$key] = array('in', array('web_name', 'logo', 'baidu_map_x', 'baidu_map_y'));
        } else if ($type == 5) {
            if ($obj == -2) { // 联系我们特殊处理         
                $key = 'key';
                $where[$key] = array('in', array('linkman', 'tel', 'mobile', 'fax', 'email', 'address'));
            } elseif ($obj == -1) {  // 公司简介特殊处理
                $key = 'id';
                $where = array($key => 1);
            } else {
                $key = 'id';
                $where = array($key => $obj);
            }
        } else if (intval($obj) > 0) {
            $key = 'id';
            $where = array($key => $obj);
        } else {
            $where = '1=2'; // 永假条件，不返回数据
        }
        return $where;
    }

    // 绑定验证
    private function check() {
        return 'ok';
    }

    // 一键同步更新
    private function synchro() {
        // 取消脚本时间限制、忽略用户终止
        ignore_user_abort(true);
        set_time_limit(0);

        if (!file_exists(CONF_PATH . 'api.php')) {
            $this->error = '接口配置错误';
            return false;
        }
        $API = include CONF_PATH . 'api.php';
        $api_status = $API['API_STATUS'];
        $api_url = $API['API_URL'];

        // 未验证API成功，直接退出
        if ($api_status != 'ok') {
            $this->error = '接口配置错误';
            return false;
        }
        //同步网站参数
        $params = $this->setParams(4);
        $params && $ret = http_transport($api_url, $params);

        // 同步分类
        unset($where);
        $where['id'] = array('neq', 1); // 排除文章根栏目
        $where['code'] = array('notlike', array('3,%', '1,6,%'), 'and'); //排除人才招聘、友情链接
        $list = M('Type')->where($where)->order('`code` asc,`order` desc')->getField('id', true);
        $params = $this->analyse(3, $list);

        // 同步公司简介、联系我们、自助单页
        unset($where);
        $where['id'] = array('neq', 1);
        $where['pid'] = 0;
        $list = M('Article')->where($where)->getField('id', true);
        $list = array_merge(array(-1, -2), (array)$list);
        $params = array_merge((array)$params, $this->analyse(5, $list));

        // 同步新闻
        unset($where);
        $news_types = M('Type')->where("code like '1,4,%'")->getField('id', true);
        $where['pid'] = array('in', $news_types);
        $list = M('article')->where($where)->getField('id', true);
        $params = array_merge((array)$params, $this->analyse(1, $list));

        // 同步产品
        unset($where);
        $goods_types = M('Type')->where("code like '2,%'")->getField('id', true);
        $where['pid'] = array('in', $goods_types);
        $list = M('goods')->where($where)->getField('id', true);
        $params = array_merge((array)$params, $this->analyse(2, $list));
        // 同步招聘
        unset($where);
        $list = M('Jobs')->getField('id',true);
        $params = array_merge((array)$params, $this->analyse(7, $list));

        // 同步应聘
        unset($where);
        $list = M('Apply')->getField('id',true);
        $params = array_merge((array)$params, $this->analyse(8, $list));

        // 自助栏目内容
        unset($where);
        $where['id'] = array('neq', 1);
        $link_types = M('Type')->where("code like '1,6,%'")->getField('id', true);
        $page_types = array(0);
        $where['pid'] = array('not in', array_merge($news_types, $link_types, $page_types));
        $list = M('article')->where($where)->getField('id', true);
        $params = array_merge((array)$params, $this->analyse(6, $list));

        // 统一发送同步
        foreach ($params as $p) {
            $ret = http_transport($api_url, $p);
            if ($ret == 'ok') {
                // 如果成功记录同步版本
                if ($p['option'] == 2) {
                    // 删除同步 删除同步版本
                    $where['type'] = $p['type'];
                    $where['obj'] = array('in', $p['obj']);
                    D('synchro')->where($where)->delete();
                } else {
                    // 添加、编辑同步 保存同步版本                    
                    $p['action'] = 'data';
                    foreach (explode(',', $p['obj']) as $id) {
                        $synchro['obj'] = $id;
                        $synchro['type'] = $p['type'];
                        $p['obj'] = $id;
                        $_REQUEST = $p;
                        $this->debug = true;
                        $result = $this->index('data');
                        $this->debug = false;
                        if ($result) {
                            $data = json_decode($result);
                            $synchro['check'] = $data->check;
                        }
                        $where = $synchro;
                        unset($where['check']);
                        if (M('synchro')->where($where)->find()) {
                            M('synchro')->where($where)->setField('check', $synchro['check']);
                        } else {
                            M('synchro')->add($synchro);
                        }
                    }
                }
            }
        }

        return true;
    }

    private function analyse($type, $obj) {
        $db = M('synchro');
        $where['type'] = $type;
        $synchro = $db->where($where)->getField('obj,check');
        $old_obj = array_keys($synchro);

        // 交集 比较版本check 判断编辑
        $list = array_intersect((array)$obj, (array)$old_obj);
        // 组包拉取一下信息获取check
        $url = $_SERVER['HTTP_HOST'] . __ROOT__ . '/api';
        foreach ($list as $id) {
            $old_check = $synchro[$id];
            $sent = $this->setParams($type, $id);
            $sent['action'] = 'data';
            $_REQUEST = $sent;
            $this->debug = true;
            $result = $this->index('data');// 访问自己接口获取check
            $data = json_decode($result);
            $check = $data->check;
            if ($check != $old_check) {
                $ids[] = $id;
            }
        }
        $list = $ids;
        $params_edit = $this->setParams($type, implode(',', $list), 3);
        // 差集 添加 新-旧
        $list = array_diff((array)$obj, (array)$old_obj);
        $params_add = $this->setParams($type, implode(',', $list), 1);
        // 补集 删除 旧-新
        $list = array_diff((array)$old_obj, (array)$obj);
        $params_del = $this->setParams($type, implode(',', $list), 2);

        $this->version = array_merge((array)$version, (array)$this->version);
        return array_filter(array($params_add, $params_del, $params_edit));
    }

    /**
     * 构建发送数据数组
     * @param  int $type type取值1,2,3,4
     * @param  string $result obj取值1或者1,2,3字符串
     * @return array
     */
    private function setParams($type, $obj = '', $option = 3) {
        if (!file_exists(CONF_PATH . 'api.php')) {
            return null;
        }
        if ($type != 4 && $obj == '') {
            return null;
        }

        $API = include CONF_PATH . 'api.php';
        // 批量编辑 
        $data = array(
            'action'    => 'notice', //操作类型
            'type'      => $type,   //数据类型
            'option'    => $option, //数据库操作方式
            'obj'       => $obj, //当前ID
            'appid'     => $API['API_ID'], //接口编号
            'signature' => md5($API['API_SECRET']), //签名
            'version'   => $API['API_VERSION'], //版本号
        );

        return $data;
    }
}