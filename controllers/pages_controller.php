<?php
class PagesController extends AppController
{
	var $name = 'Pages';
	var $uses = array('Fbuser','Event','Lovelist','Product','Brand','Tip');

	function lovelist()
	{
		$this->set('brand', array('id' => 0));
		$this->pageTitle = 'Lovelist';
		$this->_loadCSS('lists.css');
		$this->_loadCSS('lovelist.css');
		
		if($this->fbuser_id){
			$url = $this->Lovelist->productsYouLove($this->fbuser_id);
			if($url)
				$this->set('url', $url);
		}
		$this->set('product_list', json_encode(array()));
		$this->set('in_list', json_encode($this->Lovelist->getItemsInList($this->fbuser_id)));
		
		$this->_loadJS('slider.js');
		$this->_loadJS('lovelist.js');
	}
	function special_offers()
	{
		$this->pageTitle = 'Special Offers';
		$this->_loadCSS('special_offers.css');
		$this->Brand->unbindModel(array('hasMany' => array('Product')));
		$this->set('offer',$this->Brand->findAll(array("Brand.special_offer <> ''"), array('Brand.id','Brand.special_offer','Brand.offer_url'), 'Brand.order_id'));
	}
	function sweepstakes()
	{
		$this->pageTitle = 'Sweepstakes';
		$this->_loadCSS('sweepstakes.css');
	}
	function rewards()
	{
		$this->pageTitle = 'Rewards';
		$this->_loadCSS('rewards.css');
	}
	function shopping()
	{
		$this->pageTitle = 'Shopping Tip Of The Day';
		$this->_loadCSS('shopping.css');
		$this->_loadJS('slider.js');
		$current_date = getdate();
		if(strlen($current_date['mon'])==1)
			$current_date['mon'] = intval('0' . $current_date['mon']);
		$formatted_date = $current_date['year'] . '-' . $current_date['mon'] . '-' . $current_date['mday'];
		$this->set('today',$current_date['mon'] . $current_date['mday']);
		$tips = $this->Tip->findAll(array("Tip.date <= '$formatted_date'"));
		rsort($tips);
		$this->set('tips', $tips);
	}
	function faqs()
	{
		$this->pageTitle = 'FAQs';
		$this->_loadCSS('info.css');
	}
		
	function privacy()
	{
		$this->pageTitle = 'Privacy Policy';
		$this->_loadCSS('info.css');
	}
	
	function terms()
	{
		$this->pageTitle = 'Terms Of Service';
		$this->_loadCSS('info.css');
	}
	
	function official_rules()
	{
		$this->pageTitle = 'Official Rules';
		$this->_loadCSS('info.css');
	}
	
	function books()
	{
		$this->pageTitle = 'Books';
		$this->_loadCSS('lists.css');
		$this->_loadJS('slider.js');
		$this->_loadJS('tmat.rotator.js');		
	}
	function fb_refresh()
	{
		$this->layout = 'fb_refresh';
	}
	
	/*	AJAX Lovelist Actions */
	function lovelist_add()
	{
		if(isset($this->data['Product']))
		{
			header('Content-type: text/javascript');
			Configure::write('debug',0);
			$this->autoRender = false;
			
			$lovelist_added = $this->Lovelist->addToList($this->fbuser_id, $this->data['Product']['id']);
			$this->Event->addEvent($this->fbuser_id, 'love_list', $this->data['Product']['id']);
			$products_you_love = $this->Lovelist->productsYouLove($this->fbuser_id);
			$response = array(
				'points' => $this->Event->totalPoints($this->fbuser_id), 
				'valid' => $lovelist_added, 
				'product_id' => $this->data['Product']['id'], 
				'product_name' => $this->Product->field('name', array('Product.id' => $this->data['Product']['id'])),
				'next_empty_slot' => $this->data['Product']['next_empty_slot'],
				'products_you_love' => '/stylefeeder/view/#/' . $products_you_love['name'] . '/u/' . $products_you_love['brands'] . '//2-11////variety-none/////'
			);
			echo json_encode($response);
		}
	}
	function lovelist_remove()
	{
		if(isset($this->data['Product']))
		{
			header('Content-type: text/javascript');
			Configure::write('debug',0);
			$this->autoRender = false;

			$this->Lovelist->removeFromList($this->fbuser_id, $this->data['Product']['id']);
			$this->Event->removeEvent($this->fbuser_id, 'love_list', $this->data['Product']['id']);
			$products_you_love = $this->Lovelist->productsYouLove($this->fbuser_id);
			$response = array(
				'points' => $this->Event->totalPoints($this->fbuser_id),
				'products_you_love' => '/stylefeeder/view/#/' . $products_you_love['name'] . '/u/' . $products_you_love['brands'] . '//2-11////variety-none/////'
			);
			echo json_encode($response);
		}
	}
}
?>