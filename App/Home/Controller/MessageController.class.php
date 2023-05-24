<?php
namespace Home\Controller;

class MessageController extends PublicController {
    public function index() {
        $this->display();
    }

    public function add_message() {
        if ($_SESSION['verify'] != md5($_POST['captcha'])) {
            $this->error('验证码错误！');
        }
        $this->safe();
        $data = $_POST;
        // 验证电话正确性
        if (!preg_match('/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/', $data['tel']) && !preg_match('/^(([0\+]\d{2,3}-)?(0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/', $data['tel'])) {
            $this->error('电话格式错误，请输入正规的手机号码或者固话');
        }
        // 验证邮箱正确性
        if (!preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/', $data['email'])) {
            $this->error('邮箱格式错误，请输入合法的邮箱地址');
        }
        $data['ip'] = get_client_ip();
        /**
         * 过滤垃圾数据
         * 每天每IP只能留言3次
         */
        $count = M('message')->where(array('ip' => $data['ip'], 'time' => array('like', date('Y-m-d') . '%')))->count();
        if ($count > 3) {
            //$this->error('同IP每天只能留言3次');
        }
        $id=M('message')->add($data);
        /**
         * 留言成功自动添加默认客户 该方法新加的 每次留言都会调用
         * 同步留言客户  2019-5-5注释
         */
        //$customer=A('Home/Api');
        //$customer->addCustomer('message',$data,$id);
        if ($id) {
            $this->sendWxMsg($data); // 发送微信消息
            $this->success('留言成功');
        } else {
            $this->error('留言失败,请稍候再试');
        }
    }
}