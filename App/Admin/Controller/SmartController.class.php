<?php
/**
 * 后台智能标签管理
 * Enter description here ...
 * @author Administrator
 *
 */
namespace Admin\Controller;

class SmartController extends BaseController {
    /**
     * 
     * Enter description here ...
     */
    public function __construct()
    {
        parent::__construct("Smart",'Config');
    }

    /**
     * 查看智能标签
     * Enter description here ...
     */
    public function view()
    {
        $where = 'group_id = 2';
        $result = $this->_model->where($where)->select();
        $info = $result[0];

        $smart_list = $result;
        $this->assign('info', $info);
        $this->assign('smart_list', $smart_list);
        $this->display();
    }

    public function baidu()
    {
        $switch_mbaidu = $this->config('switch_mbaidu');
        $this->assign('switch_mbaidu',$switch_mbaidu);
        $this->display();
    }

    public function m_pc()
    {
        if($_POST){
            $url=trim(I('post.url'));
            if(!empty($url)&&!is_numeric($url)){
                if(strstr($url,'.qftouch.com')||strstr($url,'.qftouch.cn'))$this->error('不能将QFTouch测试域名作为手机站正式地址!');
                $conf=load_config(CONF_PATH.'api'.CONF_EXT);
                $conf['M_URL']=$url;
                \Think\Storage::put(CONF_PATH.'api'.CONF_EXT,'<?php return  '.var_export($conf,true).';','F');
                //开启移动互联
                M('config')->where('`key`="switch_m_pc"')->setField('value',1);
                $this->success('保存成功');
            }
            $this->error('保存失败');
        }
        $switch_m_pc = $this->config('switch_m_pc');
        $this->assign('switch_m_pc',$switch_m_pc);
        $this->display();
    }

    public function api_switch()
    {
        $switch_api = $this->config('switch_api');
        $this->assign('switch_api',$switch_api);
        $this->assign('api_token',$this->makeToken());
        $this->display();
    }

    public function mini_program()
    {
        //检测是否开启数据接口并获取token
        $switch_api = $this->config('switch_api');
        if($switch_api == 0){
            $this->error('请先开启数据接口！', __GROUP__."/Smart/api_switch");
        }
        $token = $this->makeToken();
        //获取接口测试地址
        $api_txt_path = './api.txt';
        if( !file_exists($api_txt_path) ){
            $this->error('没有找到数据接口访问地址！');
        }
        $mini_program_url = file_get_contents($api_txt_path);
        //获取客户端合同cookie
        $mini_program_ht = cookie('min_program_ht') ?  cookie('min_program_ht') : '';
        $data = array(
            'mini_program_url'  =>  $mini_program_url,
            'token'             =>  $token,
            'ht_cookie'         =>  $mini_program_ht
        );
        $data = base64_encode(json_encode($data));
        $this->assign('parm',$data);
        $this->display();
    }

    public function ajax()
    {
        $state = 1;
        switch ($_REQUEST['t'])
        {
            case 'tag': //获取模板标签
                $info = M('config')->find($_REQUEST['id']);
                if($info['value'])
                {
                    $result['status'] = 1;
                    $result['data'] = $info['value'];
                    $this->ajaxReturn($result);
                }else {
                    $this->error('');
                }
                break;
            case 'baidu':
                $data = M('config')->where('`key`="switch_mbaidu"')->select();
                $info = $data[0];
                $info['value'] = $_GET['v'];
                M('config')->save($info);
                break;
            case 'm_pc':
                $data = M('config')->where('`key`="switch_m_pc"')->select();
                $info = $data[0];
                $info['value'] = $_GET['v'];
                M('config')->save($info);
                break;
            case 'switch_api':
                $data = M('config')->where('`key`="switch_api"')->select();
                $info = $data[0];
                $info['value'] = $_GET['v'];
                M('config')->save($info);
                break;
            case 'qfjob':
                $data = M('config')->where('`key`="qfjob"')->select();
                $info = $data[0];
                $info['group_id'] = $_GET['v'];
                M('config')->save($info);
                break;
            case 'repair': //修复
                $repair = explode(',', ltrim($_GET['name'], ','));
                foreach($repair as $key=>$value)
                {
                    $flag = M()->execute("REPAIR TABLE `".$value."`");
                    if(!$flag)
                    {
                        $state = '0';
                    }
                }
                if($state)
                {
                    $this->success('修复成功');
                }else {
                    $this->error('修复失败');
                }
                break;
            case 'optimize': //优化
                $optimize = explode(',', ltrim($_GET['name'], ','));

                foreach($optimize as $key=>$value)
                {
                    $flag = M()->execute("OPTIMIZE TABLE `".$value."`");
                    if(!$flag)
                    {
                        $state = '0';
                    }
                }
                if($state)
                {
                    $this->success('优化成功');
                }else {
                    $this->error('优化失败');
                }
                break;
        }
        exit;
    }
    
     /**
         * 清除缓存操作
     * Enter description here ...
     */
    public function clearcache()
    {
        import('ORG.Util.File');
        $temp_dir = RUNTIME_PATH;
        session('left_menu', null);
        if(is_dir($temp_dir))
        {
            \File::unlinkDir($temp_dir);
            $this->success('清除缓存成功！', __GROUP__."/Index/status");
        }else 
        {
            $this->error('清除缓存失败！');
        }
    }
    
    /**
     * 数据优化
     * Enter description here ...
     */
    public function optimization()
    {
        $table_name = M()->query('SHOW TABLES');
        foreach($table_name as $key=>$value)
        {
             $list[$key]['name'] = implode(',',$value);
             $table = $list[$key]['name'];
             $table_state = M()->query("CHECK TABLES $table");
             $list[$key]['state'] = $table_state[0][Msg_text];
        }
        $this->assign('list',$list);
        $this->display();
    }

    /**
     * qftouch页面
     * @return [type] [description]
     */
    public function qftouch()
    {
        if($_POST)
        {   
            // 已经激活就不再执行
            if (C('API_STATUS')=='ok') {
                return false;
            }
            set_time_limit(0);
            //参数
            $api_url    = "http://www.qftouch.com/api.php";
            $api_id     = $_POST['api_id'];
            $api_secret = $_POST['api_secret'];

            //拼接数据
            $params = array(
                    'action'     => 'check',
                'appid'      => $api_id,
                'signature'  => md5($api_secret),
            );

            //发送数据
            $response = http_transport($api_url, $params);    

            //验证API信息成功
            if($response === 'ok'){
                //动态将参数加入到配置文件里面
                $config_path = CONF_PATH . 'api.php';
                $params['action'] = 'url';                
                // 合并数组数据
                $config_data['API_URL'] = $api_url;
                //$config_data['M_URL'] = 'http://' . http_transport($api_url, $params); // 查询qftouch的地址
                $config_data['M_URL']=empty(trim(C('M_URL')))?NULL:C('M_URL');
                $config_data['API_ID'] = $api_id;
                $config_data['API_SECRET'] = $api_secret;
                $config_data['API_VERSION'] = '1.1';
                $config_data['API_STATUS'] = 'ok';
                $config_data = var_export($config_data, true);
                //拼接字符串
                $str_data = "<?php\n return ".$config_data.";";
                // 将数据存入到文件里面                
                if(file_put_contents($config_path, $str_data))
                {
                    //首次同步数据  
                    $api = A('Home/Api');
                    $api->debug = true;                   
                    $api->index('synchro');
                    $this->success('激活成功！');                     
                }else{
                    $this->error('写入配置文件失败，请检查Conf文件夹权限');
                }
            } else {
                $this->error('请检查填写信息是否正确！');
            }

        }
        if (C('API_STATUS')=='ok') {
            //读取配置项数据
            $api_status = C('API_STATUS');
            $api_id = C('API_ID');
            $api_secret = C('API_SECRET');
            // 生产token
            $token=encrypt(strval(time()),$api_secret);
            $this->assign('api_status',$api_status);
            $this->assign('appid',$api_id);
            $this->assign('token',$token);
        }
        
        //显示
        $this->display();
    }


}