<?php
namespace Home\Controller;

class IndexController extends PublicController {
    public function index() {
        	$news_id  = $this->getTypeID(NEWS);
			$product_id = $this->getTypeID(PRODUCT);
			$article = M('article')->where('`id`=1')->find();
			// $qywh = M('article')->where('`id`=7')->find(); //企业文化
			// $shfw = M('article')->where('`id`=9')->find(); //售后服务
			// $fzlc = M('article')->where('`id`=10')->find(); //发展历程

			// 产品列表
			$product = M('goods')->where("`pid` in ($product_id)")
							   	 ->order('`order` desc,`id` desc')
								 ->limit(0, 20)
							   	 ->select();
			foreach ($product as $key => $value) {
					if($value['img'] != null){
						if(strpos($value['img'], ',')){
							$arr = explode(',', $value['img']);
							$product[$key]['img'] = $arr[0];
						}
					}
				}
			$this->assign('product',$product);

			//新闻中心
			$news = M('article')->where("`pid` in (4)")
							   	 ->order('`order` desc,`id` desc')
								 ->limit(0, 14)
							   	 ->select();
			$this->assign('news',$news);	

			// //发货通知
			// $fhtz = M('article')->where("`pid` in (21)")
			// 				   	 ->order('`order` desc,`id` desc')
			// 				   	 ->select();
			// $this->assign('fhtz',$fhtz);

			//疑问解答
			$ywjd = M('article')->where("`pid` in (12)")
							   	 ->order('`order` desc,`id` desc')
							   	 ->select();
			$this->assign('ywjd',$ywjd);

			// 友情链接
			// $friendlink = M('article')->where("`pid` in (6)")
			// 				   	 ->order('`order` desc,`id` desc')
			// 				   	 ->select();
			// $this->assign('friendlink',$friendlink);

			$this->header_seo();

			// $article['content'] = mb_substr(strip_tags($article['content']),0,580,'UTF-8').'...';
			// $qywh['content'] = mb_substr(strip_tags($qywh['content']),0,130,'UTF-8').'......';
			// $shfw['content'] = mb_substr(strip_tags($shfw['content']),0,130,'UTF-8').'...';
			// $fzlc['content'] = mb_substr(strip_tags($fzlc['content']),0,130,'UTF-8').'...';
			// $this->assign('intro',$article);
			// $this->assign('qywh',$qywh);
			// $this->assign('shfw',$shfw);
			// $this->assign('fzlc',$fzlc);



		
			// $goodlist =  M('Type')->where("`parent`=2")->select();
            // foreach ($goodlist as $key => $value) {
            //  $goodlist[$key] = M('goods')->where("`pid` = {$value['id']}")->order('`order` desc,`id` desc')->limit(0, 10)->select();
            //  $this->assign('goodslist', $goodlist);
            // }
		
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
