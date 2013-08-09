<?php
class TrendsController extends AppController
{
	var $name = 'Trends';
	
	function index()
	{
		return $this->redirect('/trend/fashion');
	}
	function detail($slug=NULL)
	{
		$this->set('all_trends', $this->Trend->findAll());
		$trend = $this->Trend->findByCategory($slug);
		if(! $trend)
			return $this->redirect('/trend/fashion');
			
		$this->set('trend',$trend['Trend']);
		
		$this->pageTitle = '12 Trends To Try';
		$this->_loadCSS('trends.css');
		$this->_loadJS('tmat.rotator.js');
		$this->set('preroll',$this->getPreroll());
	}
	
	function getPreroll()
	{
		$videos = array('/edit_videos/preroll_dove.f4v','/edit_videos/preroll_covergirl.f4v','/edit_videos/preroll_loreal.f4v','/edit_videos/preroll_olay_body.f4v','/edit_videos/preroll_olay_skin1.f4v','/edit_videos/preroll_olay_skin2.f4v');
		$selection = $videos[mt_rand(0,5)];
		return $selection;
	}
}
?>