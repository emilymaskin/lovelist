<?php
class Product extends AppModel
{
	var $name = 'Product';
	var $belongsTo = array('Brand');
	
	function getProducts(){
		$rows = $this->findAll();
		$products = array();
		foreach($rows as $e)
			$products[] = $e['Product'];
		return $products;
	}
}
?>