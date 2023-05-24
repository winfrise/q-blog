<?php
namespace Home\Controller;

class CompanyController extends PublicController {
    public function index() {
        $article = M('article')->where('`id`=1')->find();
        $this->assign('article', $article);
        $this->display();
        // $this->show($article);
    }
}