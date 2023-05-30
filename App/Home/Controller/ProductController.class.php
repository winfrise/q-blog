<?php
namespace Home\Controller;

class ProductController extends PublicController {

    public function index() {
        //  import("ORG.Util.Page");
         import("Vendor.Page");
        $current_page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
        $page_size = $_REQUEST['size'] ? $_REQUEST['size'] : 9;
        $type = $_REQUEST['type'];

        $pid = $type ? $type : $this->getTypeID(PRODUCT);

        $where = "`pid` in ($pid)";
        if ($keyword) {
            $where .= " and `title` like '%$keyword%'";
        }
        $count = M('goods')->where($where)->count();

        $firstRow = ($current_page - 1) * $page_size;
        if ($count) {
            $page = new \Page($count, $page_size);
            $product = M('goods')->where("`pid` in ($pid)")->order('`order` desc,`id` desc')->limit($firstRow . ',' . $page_size)->select();
        }


        foreach ($product as $key => $value) {
            $product[$key]['url'] = __APP__ . '/product/' . $value['pid'] . '_' . $value['id'];
        }
        
        $categories = M('type')->where("`parent` = 2")->field('id,name')->order('`order` desc,`id` asc')->select();
        foreach($categories as $key=>$value)
        {
            $categories[$key]['url'] = '/product?type=' . $value['id'];
        }

        $this->assign('list', $product);
        $this->assign('paginationStr', json_encode(array(
            'total' => $count, 
            'pageSize' => $page_size,
            'currentPage' => $current_page,
        )));
        $this->assign('page', $page->show());
        $this->assign('currentCategory', $type ? $type : '');
        $this->assign('keyword', $keyword);
        $this->assign('sidebarMenus', $categories);
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