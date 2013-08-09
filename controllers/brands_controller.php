<?php
class BrandsController extends AppController
{
	var $name = 'Brands';
	var $uses = array('Fbuser','Event','Lovelist','Product','Brand','Silo','Department');

	function detail($slug=NULL)
	{
		$brand = $this->Brand->find(array('Brand.slug' => $slug));
		if(! $brand)
			return $this->redirect('/');
		$this->set('brand',$brand['Brand']);
		if($brand['Silo']['id'])
			$this->set('silo',$brand['Silo']);
		$this->pageTitle = $brand['Brand']['name'];
		$this->set('department',$brand['Department']);
		$this->set('product_list', json_encode($brand['Product']));
		$this->set('products', $brand['Product']);
		$this->set('product_count',count($brand['Product']));
		
		$this->set('in_list', json_encode($this->Lovelist->getItemsInList($this->fbuser_id)));
		
		if($this->fbuser_id){
			$url = $this->Lovelist->productsYouLove($this->fbuser_id);
			$this->set('url', $url ? $url : array('name' => $brand['Brand']['name'], 'brands' => ''));
		}
				
		$this->Brand->unbindModel(array('hasMany' => array('Product')));
		$this->set('offer',$this->Brand->findAll(array("Brand.special_offer <> ''"), array('Brand.id','Brand.special_offer')));
		$this->set('image_url',$brand['Brand']['image_url']);
		$this->_loadCSS('lists.css');
		$this->_loadCSS('brands.css');
		$this->_loadJS('slider.js');
		$this->_loadJS('swfaddress.js');
		$this->_loadJS('lovelist.js');
		$this->_loadJS('tmat.rotator.js');
	}
}
?>