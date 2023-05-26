<?php
namespace Home\Controller;

class CaseController extends PublicController {

    public function index() {
        import("ORG.Util.Page");

        $category_id = 13;
        $page_num = 12;
        $count = M($type)->where("`pid` in ($category_id)")->count();
        $page = new \Page($count, $page_num);

        $list = M('article')->where("`pid` in ($category_id)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        if (!$count) {
            $this->assign('exist', false);
        } else {
            $this->assign('exist', true);
        }

        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }

    public function details() {
        $_REQUEST['id'] = intval($_REQUEST['id']);

        M('article')->where('`id`="' . $_REQUEST['id'] . '"')->setInc('click');
        $article = M('article')->where('`id`="' . $_REQUEST['id'] . '"')->find();

        $this->assign('article', $article);
        $this->display();
    }
}