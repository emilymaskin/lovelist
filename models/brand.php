<?php
class Brand extends AppModel
{
	var $name = 'Brand';
	var $hasMany = array('Product' => array('order' => 'order_id, id'));
	var $belongsTo = array('Silo','Department');
	
	function beforeSave()
	{
		if(isset($this->data['Brand']['name']))
			$this->data['Brand']['slug'] = $this->__getSlug($this->data['Brand']['name'], $this->id);
		return true;
	}
}
?>