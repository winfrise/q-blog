<?php
namespace Home\Controller;

class CompanyController extends PublicController {
    public function index() {
        $_REQUEST['id'] = intval($_REQUEST['id']);
        $article = M('article')->where('`id`="' . $_REQUEST['id'] . '"')->find();
        $this->assign('article', $article);
        $this->display();
    }
}