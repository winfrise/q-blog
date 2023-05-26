<?php
namespace Home\Controller;

class NewsController extends PublicController {

    public function index() {
        $current_page = $_REQUEST['current_page'] ? $_REQUEST['current_page'] : 1;
        $page_size = $_REQUEST['page_size'] ? $_REQUEST['page_size'] : 9;

        $news_id = 4;

        $count = M('article')->where("`pid` in ($news_id)")->count();

        $firstRow = ($current_page - 1) * $page_size;
        $news = M('article')->where("`pid` in ($news_id)")->order('`order` desc,`id` desc')->limit($firstRow . ',' . $page_size)->select();


        foreach ($news as $key => $value) {
            $news[$key]['url'] = __APP__ . '/news/' . $value['pid'] . '_' . $value['id'];
        }


        $this->assign('list', $news);
        $this->assign('paginationStr', json_encode(array(
            'total' => $count, 
            'pageSize' => $page_size,
            'currentPage' => $current_page,
        )));

        $sidebarMenus[0] = array('name' => '新闻动态', 'url' => '/news');
        $sidebarMenus[1] = array('name' => '资质荣誉', 'url' => '/honor');
        $sidebarMenus[2] = array('name' => '公司简介', 'url' => '/company');
        $sidebarMenus[3] = array('name' => '联系我们', 'url' => '/contact');

        $this->assign('sidebarMenus', $sidebarMenus);
        $this->display();

    }

    public function news_info() {
        //新闻ID
        $id = intval($_GET['id']);

        if ($id > 0) {
            $news = M('article')->where("`id`=$id")->find();
            M('article')->where("`id`=$id")->setInc('click');
        } else {
            $this->_404();
        }

        $this->prev_next($news['id'], 'article');

        $this->assign('news', $news);

        $this->display();
    }

    public function news_type() {
        import("ORG.Util.Page");
        //新闻类型
        $type = intval($_GET['type']);
        if ($type > 0) {
            $news_id = $this->getTypeID($type);
            $count = M('article')->where("`pid` in ($news_id)")->count();
            $page = new \Page($count, $this->config('page_default'));

            $news = M('article')->where("`pid` in ($news_id)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        } else {
            $this->_404();
            exit;
        }

        if (!$count) {
            $this->assign('exist', false);
        } else {
            foreach ($news as $key => $value) {
                $news[$key]['url'] = __APP__ . '/news/' . $value['pid'] . '_' . $value['id'];
            }
            $this->assign('exist', true);
            $this->assign('list', $news);
            $this->assign('page', $page->show());
        }

        $category = M('type')->find($type);
        $title = $this->getTypeName($category['id']);
        $keywords = $category['keywords'];
        $description = $category['description'];
        $this->header_seo($title, $keywords, $description);
        $this->page_location($category['id']);
        $this->catname($category['id']);

        $this->display('index');

    }
}