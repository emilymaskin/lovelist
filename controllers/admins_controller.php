<?php 
class AdminsController extends AppController
{
	var $name = 'Admins';
	var $uses = array('Event','Fbuser');
	
	function beforeFilter()
	{
		parent::beforeFilter();
		
		$admins = array("100001259954788","100000478238828","100001450303070");
		$user = $this->Fbuser->findById($this->getFbuser('id'));
		$user = $user['Fbuser']['facebook_id'];
		if(! in_array($user,$admins))
			$this->redirect('/');
		
	}
	function beforeRender()
	{
		parent::beforeRender();
		$this->layout = 'admin';
		$this->_loadCSS('admin.css');
	}
	
	function index()
	{
		$users = $this->Event->findAll(array('OR' => array('category' => 'return', 'category' => 'sign_up')));
		$this->set('users', $users);
	}
	
	function search()
	{
		if(isset($this->data['Search']))
		{
			$date = intval($this->data['Search']['y']) . '-' . intval($this->data['Search']['m']) . '-' . intval($this->data['Search']['d']);
			$users = $this->Event->findAll(array("Event.created  >= '" . $date. " 00:00:00'", "Event.created  <= '" . $date. " 23:59:59'", array('OR' => array('category' => 'return', 'category' => 'sign_up'))));
		}
		else
		{
			$users = $this->Event->findAll(array('OR' => array('category' => 'return', 'category' => 'sign_up')));
		}
		$this->set('users', $users);
	}
	
	function fb_email($userid)
	{
		vendor('facebook');

		$facebook = new Facebook(array(
		  'appId'  => FACEBOOK_APP_ID,
		  'secret' => FACEBOOK_APP_SECRET,
		  'cookie' => false
		));		
		
		if(isset($this->data['Email']) && $this->data['Email']['subject'] != '' && $this->data['Email']['message'] != '')
		{
			$facebook->api(array('method' => 'notifications.sendEmail', 'recipients' => array($userid), 'subject' => $this->data['Email']['subject'], 'text' => $this->data['Email']['message']));
			$this->set('message', 'Your message has been sent. ');
		}
		else
		{
			$response = $facebook->api('/' . $userid);

			$this->set('user', array(
					'id' => $userid,
					'name' => @$response['name'],
					'email' => @$response['email']
				));
		}
	}
}
?>