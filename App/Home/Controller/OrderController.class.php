<?php
namespace Home\Controller;

class OrderController extends PublicController {

    public function add_order() {
        if ($_SESSION['verify'] != md5($_POST['captcha'])) {
            $result = array(
                    'status' =>0,
                    'msg'    =>'验证码错误'
            );
            $this->ajaxReturn($result);
        }
        $order = M('order');
        $this->safe();
        $data = $_POST;
        // 验证电话正确性
        if (!preg_match('/^[1][0-9]{10}$/', $data['tel']) && !preg_match('/^(([0\+]\d{2,3}-)?(0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/', $data['tel'])) {
            $result = array(
                    'status' =>0,
                    'msg'    =>'电话格式错误，请输入正规的手机号码或固话'
            );
            $this->ajaxReturn($result);
        }
        $id=M('order')->add($data);
        /**
         * 留言成功自动添加默认客户 该方法新加的 每次留言都会调用
         * 同步留言客户 ,2019-5-5注释
         */
        //$customer=A('Home/Api');
        //$customer->addCustomer('order',$data,$id);
        if ($id) {
            $this->sendWxMsg($data); // 发送微信消息
            $result = array(
              'status' =>1,
              'msg'    =>'订单提交成功，请等待回复'

            );
            $this->ajaxReturn($result);
        } else {
            $result = array(
                    'status' =>0,
                    'msg'    =>'订单提交失败，请重新填写'
            );
        }
        $this->ajaxReturn($result);
    }

    public function product_order(){
        $id =I('get.id');
        $product = M('goods')->find($id);
        if(!$product)$this->error('未找到对应产品');
        $this->assign('product',$product);
        //dump($product);
        $this->display();
    }
}