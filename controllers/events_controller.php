<?php
class EventsController extends AppController
{
	var $name = 'Events';
	var $uses = array('Event','Fbuser','Product');
	
	function add()
	{
		$this->autoRender = false;
		if(isset($this->data))
		{
			$category = $this->data['Event']['category'];
			$value = $this->data['Event']['value'];
			if(isset($this->fbuser_id))
				echo $this->Event->addEvent($this->fbuser_id, $category, $value);
			else
				echo '0';
		}
		else
			echo 'Invalid data.';
	}
}
?>