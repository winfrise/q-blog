<?php
namespace Home\Controller;

class JobsController extends PublicController {
    public function index() {
        import("ORG.Util.Page");

        $job_id = $this->getTypeID(JOBS);

        $count = M('jobs')->where("`pid` in ($job_id)")->count();

        $page_jobs = $this->config('page_jobs');
        $page = new \Page($count, $page_jobs ? $page_jobs : $this->config('page_default'));
        if($count){
            $jobs = M('jobs')->where("`pid` in ($job_id)")->order(array('order'=>'desc','id'=>'desc'))->limit($page->firstRow . ',' . $page->listRows)->select();
        }
        $salary = C('ZPCONFIG.ZP_SALARY');
        foreach ($jobs as $key => $value) {
            $jobs[$key]['url'] = __APP__ . '/jobs/' . $value['id'];
            $jobs[$key]['salary'] = $salary[$value['salary']];
        }

        $this->assign('page', $page->show());
        $this->assign('list', $jobs);
        $this->display();
    }

    public function jobs_info() {
        $id = intval($_GET['id']);

        $jobs = M('jobs')->where("`id`=$id")->find();
        $salary = C('ZPCONFIG.ZP_SALARY');
        $jobs['salary'] = $salary[$jobs['salary']];
        if (!$jobs) $this->_404();
        $this->prev_next($jobs['id'], 'jobs');

        $this->assign('jobs', $jobs);
        $this->display();
    }

    public function seek_job() {
        $id = intval($_GET['id']);
        $job = M('jobs')->where("`id`=$id")->find();
        if (!$job) {
            $this->_404();
        }
        $this->assign('job', $job);
        $this->display();
    }

    public function add_job() {
        if ($_SESSION['verify'] != md5($_POST['captcha'])) {
            $this->error('验证码错误');
        }

        $order = D('Admin/Apply');
        $this->safe();
        if ($order->add($_POST)) {
            $this->success('信息提交成功');
        } else {
            $this->error('信息提交失败,请稍候再试');
        }
    }
}
