<?php
class Event extends AppModel
{
	var $name = 'Event';
	var $belongsTo = array('Fbuser');
	var $allowedCategories = array('video_load', 'video_played', 'love_list', 'sign_up', 'share', 'return', 'click');
	
	function totalPoints($fbuser_id)
	{
		$rows = $this->query("SELECT SUM(`points`) AS `total` FROM `events` WHERE `fbuser_id` = " . $fbuser_id);
		return isset($rows[0][0]['total']) ? $rows[0][0]['total'] : 0;
	}
	
	function _checkvalidity($category,$value)
	{
		$this->__loadModel('Brand');
		$this->__loadModel('Product');
		if(in_array($category,$this->allowedCategories))
		{
			$valid = false;
			switch($category)
			{
				case 'video_played':
				case 'video_load':
					$valid = $this->Brand->findById($value);
					break;
				case 'love_list':	
				case 'click':
					$valid = $this->Product->findById($value);
					break;
				case 'share':
					$valid = $this->Brand->findById($value);
					break;
			}
		}
		return $valid;
	}
	
	function addEvent($fbuser_id,$category,$product_id)
	{
		if(! $this->find(array('fbuser_id' => $fbuser_id, 'category' => $category, 'value' => $product_id)) && $this->_checkvalidity($category,$product_id))
		{
			return $this->save(array(
				'fbuser_id' => $fbuser_id, 
				'category' => $category, 
				'value' => $product_id, 
				'points' => 1
				));
		}
		return false;
	}
	
	function removeEvent($fbuser_id,$category,$dragged_id)
	{
		$out = '';
		
		$exists = $this->find(array('fbuser_id'=>$fbuser_id, 'category'=>$category,'value'=>$dragged_id));
		if($exists)
		{
				$table_id = $this->query("SELECT `id` FROM `events` WHERE `fbuser_id` = $fbuser_id AND `value` = $dragged_id ");
				$this->remove($table_id,true);
				$out = "The item has been removed from your list.";
		}
		else
			$out = "Invalid action.";
			
		return array('points' => $this->totalPoints($fbuser_id), 'message' => $out);
	}
}
?>