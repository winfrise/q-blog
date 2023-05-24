<?php
namespace Home\Controller;

class CustomController extends PublicController {

    /**
     *
     * Enter description here ...
     */
    public function __construct() {
        parent::__construct("Article", 'Article');
    }

    /**
     * 公用文章列表页
     * (non-PHPdoc)
     * @see BaseController::index()
     */
    public function index() {
        import("ORG.Util.Page");
        $_REQUEST['t'] = intval($_REQUEST['t']);

        $category_id = $this->getTypeID($_REQUEST['t']);

        $category = M('type')->find($_REQUEST['t']);

        $count = M($type)->where("`pid` in ($category_id)")->count();
        $page = new \Page($count, $page_num);

        $list = M($type)->where("`pid` in ($category_id)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        if (!$count) {
            $this->assign('exist', false);
        } else {
            $this->assign('exist', true);
        }


        foreach ($list as $key => $value) {
            if ($_REQUEST['t'] == '5') {
                $list[$key]['url'] = __APP__ . '/custom/' . $value['id'];
            } else {
                $list[$key]['url'] = __APP__ . '/' . $type . '/' . $value['id'] . '_' . $value['id'];
            }
        }
        $title = $category['name'];
        $keywords = $category['keywords'];
        $description = $category['description'];
        $this->header_seo($title, $keywords, $description);
        $this->page_location($_REQUEST['t']);
        $this->catname($_REQUEST['t']);

        $this->assign('category', $category['name']);
        $this->assign('page', $page->show());
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 公用文章单页(non-PHPdoc)
     * @see BaseController::info()
     */
    public function info() {
        $_REQUEST['id'] = intval($_REQUEST['id']);
        switch ($_REQUEST['id']) {
            /*
            case ID:
                $tpl = "info";
                break;
            */
            default:
                $tpl = "";
        }
        $user = M('user')->field('username')->find();
        M('article')->where('`id`="' . $_REQUEST['id'] . '"')->setInc('click');
        $article = M('article')->where('`id`="' . $_REQUEST['id'] . '"')->find();
        $title = $article['title'];
        $keywords = $article['keywords'];
        $description = $article['description'];
        $this->header_seo($title, $keywords, $description);
        $this->page_location($title, true);
        $this->catname($title, true);

        $this->assign('article', $article);
        $this->assign('user', $user);
        $this->display();
    }
}