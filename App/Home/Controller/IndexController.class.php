<?php
namespace Home\Controller;

class IndexController extends PublicController {
    public function index() {
			// 产品分类列表
			$productCategories = M('type')->where('`parent`=2')
											->order('`order` desc, `id` asc')
											->select();
			$this->assign('productCategories', $productCategories);
			
			//新闻列表
			$news = M('article')->where("`pid` in (4)")
							   	 ->order('`order` desc,`id` desc')
								 ->limit(0, 5)
							   	 ->select();
			$this->assign('news',$news);	


			// 公司简介
			$companyIntro = M('article')->where('`id`=2')->find();
			$this->assign('companyIntro',$companyIntro);
			$this->assign('companyIntro',$companyIntro);

			// 推荐产品
			$productPid = $this->getTypeID(PRODUCT);
			$recommendProductList = M('Goods')->where("`pid` in ($productPid)")
												->order('`order` desc,`id` desc')
												->limit(0, 16)
												->select();
			$this->assign('recommendProductList',$recommendProductList);

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
