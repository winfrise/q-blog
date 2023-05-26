<?php
namespace Home\Controller;

class IndexController extends PublicController {
    public function index() {
			// 产品分类列表
			$productCategories = M('type')->where('`parent`=2')
											->order('`order` desc, `id` asc')
											->limit(0, 8)
											->select();
			$this->assign('productCategories', $productCategories);
			
			//新闻列表
			$news = M('article')->where("`pid` in (4)")
							   	 ->order('`order` desc,`id` desc')
								 ->limit(0, 5)
							   	 ->select();
			$this->assign('news',$news);	



			$companyIntro = M('article')->where('`id`=2')->find();
			$this->assign('companyIntro',$companyIntro);
			
			$this->display();
    }

    public function sitemap() {
        $single = M('article')->where("`pid`=0 and `system`='0'")->select();
        $listpage = M('type')->where("`parent`='1' and `id` != '4' and `id` !='5'")->select();
        $this->assign('single', $single);
        $this->assign('listpage', $listpage);
        $this->display();
    }


}
