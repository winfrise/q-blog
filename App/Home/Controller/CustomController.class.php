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

        switch ($category['type']) {
            case "1":
                $type = "article";
                switch ($_REQUEST['t']) {
                    //自定义指定文章分类的模板。设计扩充使用。
                    /*
                    case "ID": //文章分类ID
                        $tpl = "/News/image";自定义指定模板位置
                        break;
                    */
                    case "5":
                        $tpl = "/News/image";
                        $title_h1 = "资质荣誉";
                        $this->assign('title_h1', $title_h1);
                        break;
                    case "13":
                        $tpl = "/News/case";
                        $title_h1 = "资质荣誉";
                        $this->assign('title_h1', $title_h1);
                        break;
                    default:
                        $tpl = "/News/index";
                        $page_num = $this->config('page_default');

                }
                break;
            case "2":
                $type = "goods";
                $tpl = "/Product/index";
                $page_num = $this->config('page_product');
                break;
            case "3":
                $type = "jobs";
                $tpl = "/Jobs/index";
                $page_num = $this->config('page_jobs');
                break;
        }
        $count = M($type)->where("`pid` in ($category_id)")->count();
        $page = new \Page($count, $page_num);

        $list = M($type)->where("`pid` in ($category_id)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        if (!$count) {
            $this->assign('exist', false);
        } else {
            $this->assign('exist', true);
        }
        if ($type == 'goods') {
            $type = 'product';
        } elseif ($type == 'article') {
            $type = 'news';
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
        $this->display($tpl);
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
        $this->display($tpl);
    }
}