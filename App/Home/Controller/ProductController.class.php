<?php
namespace Home\Controller;

class ProductController extends PublicController {

    public function index() {
        import("ORG.Util.Page");
        
        $pid = $this->getTypeID(PRODUCT);
        $count = M('goods')->where("`pid` in ($pid)")->count();
        $page_size = $this->config('page_default');
        $pagination = new \Page($count, $page_size);
        if ($count) {
            $product = M('goods')->where("`pid` in ($pid)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        }


        foreach ($product as $key => $value) {
            $product[$key]['url'] = __APP__ . '/product/' . $value['pid'] . '_' . $value['id'];
        }
        
        $categories = M('type')->find(PRODUCT);

        $this->assign('list', $product);
        $this->assign('pagination', array('total' => $count, 'pageSize' => $page_size));
        $this->assign('categories', $categories);
        $this->display();
    }

    public function product_info() {
        //商品ID
        $id = intval($_GET['id']);
        if ($id > 0) {
            $list = M('goods')->where("`id`=$id")->find();
            M('goods')->where("`id`=$id")->setInc('click');
        }

        if (!$list) {
            $this->_404();
            exit;
        } else {
            $this->assign('list', $list);
        }


        $this->prev_next($list['id'], 'goods');
        $this->display();
    }

    public function product_type() {
        import("ORG.Util.Page");
        //商品类型
        $type = intval($_GET['type']);

        if ($type > 0) {
            $pid = $this->getTypeID($type);
            $count = M('goods')->where("`pid` in ($pid)")->count();
            if ($count) {
                $page_product = 24;
                $page = new \Page($count, $page_product ? $page_product : $this->config('page_default'));

                $list = M('goods')->where("`pid` in ($pid)")->order('`order` desc,`id` desc')->limit($page->firstRow . ',' . $page->listRows)->select();
            }
        } else {
            $this->_404();
            exit;
        }

        if (!$count) {
            $this->assign('exist', false);
        } else {
            foreach ($list as $key => $value) {
                $list[$key]['url'] = __APP__ . '/product/' . $value['pid'] . '_' . $value['id'];
            }
            $this->assign('exist', true);
            $this->assign('list', $list);
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
    public function product_order() {
        $this->display();

    }
}