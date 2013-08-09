<?php
class Lovelist extends AppModel
{
	var $name = 'Lovelist';
	var $belongsTo = array('Fbuser','Product');
	
	function addToList($fbuser_id, $product_id)
	{
		$exists = $this->find(array('fbuser_id'=>$fbuser_id, 'product_id'=>$product_id));
		if(! $exists && $this->Product->findById($product_id))
		{
			$info = array('Lovelist'=>array('fbuser_id'=>$fbuser_id, 'product_id'=>$product_id));
			$this->create();
			return $this->save($info);
		}
		return false;
	}
	
	function removeFromList($fbuser_id, $product_id)
	{
		$exists = $this->find(array('fbuser_id'=>$fbuser_id, 'product_id'=>$product_id));
		if($exists)
			$this->remove($exists['Lovelist']['id'],true);
	}
	
	function getItemsInList($fbuser_id)
	{
		$fbuser_id = intval($fbuser_id);
		$in_list = array();
		$this->unbindModel(array(
			'belongsTo' => array('Fbuser')
			));
		$this->bindModel(array(
			'belongsTo' => array(
				'Product' => array(
					'fields' => array('id','brand_id','name','price','price_text','description','silo_id')
				))
			));
		$this->Product->bindModel(array(
			'belongsTo' => array(
				'Brand' => array(
					'fields' => array('slug')
				))
			));
		$rows = $this->findAll(array('Lovelist.fbuser_id' => $fbuser_id), NULL, 'Lovelist.created ASC', NULL, NULL, 2);
		foreach($rows as $e)
			$in_list[] = $e['Product'];
		return $in_list;
	}
	
	function productsYouLove($fb_user_id)
	{
		$rows = $this->query("SELECT `Product`.`silo_id`, COUNT(`silo_id`) `total` FROM `products` `Product` WHERE `Product`.`id` IN (SELECT product_id FROM lovelists WHERE fbuser_id = ".intval($fb_user_id).") GROUP BY `Product`.`silo_id` ORDER BY `total` DESC");
		if(count($rows) <= 0)
			return false;
		
		//Find Brands
		$brands = $this->query("SELECT `Brand`.`name` FROM `brands` `Brand` WHERE `Brand`.`id` IN (SELECT `brand_id` FROM `products` WHERE `products`.`id` IN (SELECT product_id FROM lovelists WHERE fbuser_id = ".intval($fb_user_id).") GROUP BY `products`.`brand_id`) ORDER BY `Brand`.`name`");
		
		$brands_you_love = array();
		foreach($brands as $e)
			$brands_you_love[] = $e['Brand']['name'];
		
		$this->__loadModel('Silo');
		return array('name' => $this->Silo->field('slug', array('Silo.id' => $rows[0]['Product']['silo_id'])), 'brands' => implode(',', $brands_you_love));
	}
}
?>