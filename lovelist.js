if(typeof(HU) == "undefined")
	var HU = { $:function(id) { return document.getElementById(id); } };

//depends upon Slider, Prototype, Scriptaculous dragdrop, effects
HU.LoveList = {
	points_timer: null, //timer for displaying points to user
	next_empty_slot: 1, //the integer position of the next slot an item can be dropped into
	end_slot: 1, //the integer position of the next slot to be added
	total_list_slots: 8, //the total number of slots in the list 
	total_item_slots: 8, //the total number of slots in the items section
	in_list_array: [], //all products already in the list
	logged_in: false, //is user logged in?
	all_products: [], // all products belonging to brand
	
	load: function(brand_items)
	{
		HU.LoveList.all_products = brand_items;
		HU.LoveList.setUpBrand();
		HU.LoveList.setUpLovelist();
	},
	
	setUpBrand: function(){
		//create slider for items and details
		HU.LoveList.items_slider = new HU.Slider({ div_id: 'items', total_slots: HU.LoveList.total_item_slots, slide_by: 123, duration: 0.3, list_size: HU.LoveList.all_products.length});
		HU.LoveList.details_slider = new TMAT.Rotator({img_id_prefix: 'details_', total: HU.LoveList.all_products.length, width:270, duration: 0.3, effect: 'slideLeft', onChange: 'HU.LoveList.sliderOnChange'});
		var j=1;		
		HU.LoveList.all_products.each(function(k){
			if(HU.LoveList.logged_in)
				new Draggable('item'+j, {revert:'failure', ghosting:true});
				j++;
		});
		if(SWFAddress.getPath() != '/' && $('product_'+SWFAddress.getPath())){
			var $swf_to = $('product_'+SWFAddress.getPath()).parentNode.id.replace('item','')-1;
			HU.LoveList.items_slider.jumpTo($swf_to);
			HU.LoveList._updatePosition($swf_to+1);
			HU.LoveList.details_slider.switchTo($swf_to);
		}
		else
			HU.LoveList._updatePosition(1);
	},
	
	setUpLovelist: function (){
		
		//Create slider for lovelist if logged in
		if(HU.LoveList.logged_in){
			$('lovelist').style.display = 'block';
			Droppables.add( 'lovelist_wrapper', { onDrop: HU.LoveList.addToList } );
			HU.LoveList.lovelist_slider = new HU.Slider({ div_id: 'lovelist', total_slots: HU.LoveList.total_list_slots, slide_by: 120, duration: 0.3, list_size: HU.LoveList.in_list_array.length});

			for(var i=0;i<HU.LoveList.in_list_array.length;i++){
				HU.LoveList._addEmptySlot();
				//if the item is in this brand
				if($('product_'+$(HU.LoveList.in_list_array[i]).id))
					HU.LoveList._fillSlot($('product_'+$(HU.LoveList.in_list_array[i]).id).parentNode,true);
				else
					HU.LoveList._fillSlot(HU.LoveList.in_list_array[i],false, HU.LoveList.in_list_array[i].Brand.slug);
			}
			if(HU.LoveList.in_list_array.length<HU.LoveList.total_list_slots){
				for(var j=HU.LoveList.in_list_array.length;j<HU.LoveList.total_list_slots;j++)
					HU.LoveList._addEmptySlot();
				HU.LoveList.lovelist_slider.updateListSize(HU.LoveList.total_list_slots - HU.LoveList.in_list_array.length);
			}
			else{
				HU.LoveList._addEmptySlot();
				HU.LoveList.lovelist_slider.updateListSize(1);
			}
			HU.LoveList.lovelist_slider.end();
		}
	},
	
	lovelist_next: function()
	{
		if(HU.LoveList.logged_in)
			HU.LoveList.lovelist_slider.next();
	},
	
	lovelist_prev: function()
	{
		if(HU.LoveList.logged_in)
			HU.LoveList.lovelist_slider.prev();
	},
	
	addToList: function (dragged,dropped,event){
		new Ajax.Request('/pages/lovelist_add', 
			{ method: 'post', parameters: { 'data[Product][id]': dragged.down(0).id.replace('product_',''), 'data[Product][next_empty_slot]': HU.LoveList.next_empty_slot },
				onSuccess: HU.LoveList._success });
		
		//replace original item image (with itself)
		$(dragged.id).style.left="0px";
		$(dragged.id).style.top="0px";
		
		//if item is already in list, return
		for(var i=0;i<HU.LoveList.in_list_array.length;i++){
			if((HU.LoveList.in_list_array[i]).id == dragged.down(0).id.replace('product_',''))
				return;}
		
		//otherwise, add item to list and update variables as necessary
		HU.LoveList._fillSlot(dragged,true);
		HU.LoveList.in_list_array.push({'id': dragged.down(0).id.replace('product_',''), 'name': dragged.alt});
		
		if(HU.LoveList.in_list_array.length>=HU.LoveList.total_list_slots){
			HU.LoveList.lovelist_slider.updateListSize(1);
			HU.LoveList._addEmptySlot();
			HU.LoveList.lovelist_slider.end();
		}
	},

	removeFromList: function (product_to_add_back,slot_to_remove_from){
		new Ajax.Request('/pages/lovelist_remove', 
			{ method: 'post', parameters: { 'data[Product][id]': product_to_add_back.toString().replace('product_','') },
				onSuccess: HU.LoveList._success });
		
		$('slot'+slot_to_remove_from).style.background = 'white';
		$('slot'+slot_to_remove_from).style.height = '120px';
		$('slot'+slot_to_remove_from).shrink({ duration: 0.5, afterFinish: function() { 
			if(HU.LoveList.lovelist_slider.list_size <= HU.LoveList.lovelist_slider.total_slots)
				HU.LoveList._addEmptySlot();		
			else
				HU.LoveList.lovelist_slider.updateListSize(-1);
	
			for(var i=0;i<HU.LoveList.in_list_array.length;i++){
				if((HU.LoveList.in_list_array[i]).id == (product_to_add_back.toString()).replace('product_',''))
					HU.LoveList.in_list_array.splice(i,1);
			}
			HU.LoveList.lovelist_slider.prev();
		}});
	},
	
	sliderOnChange: function($to)
	{
		HU.LoveList._updatePosition($to+1);
		HU.LoveList._setLink($to+1);
		HU.LoveList._setVideo($to+1);
		HU.LoveList._setImage($to+1);
	},
	_success: function(r)
	{
		$r = eval('(' + r.responseText + ')');
		$$('div#more a.more_products')[0].href=$r.products_you_love;
		//alert($r.products_you_love);
	},
	
	_showItems: function(){
		return $('items');
	},
	
	_addEmptySlot: function(){
		$('lovelist').insert({bottom: '<a id="slot'+HU.LoveList.end_slot+'" href="javascript:;" class="items"><img src="/img/brands/slot.jpg" alt="" /></a>'});
		HU.LoveList.end_slot++;
	},
	
	_fillSlot: function(item_to_add, in_brand, brand_slug){
		if(in_brand){
			var m=item_to_add.id.replace("item","");
			$('slot'+HU.LoveList.next_empty_slot).down(0).src = item_to_add.down(0).src;
			$('slot'+HU.LoveList.next_empty_slot).down(0).alt = item_to_add.down(0).alt;
			$('slot'+HU.LoveList.next_empty_slot).className += ' filledslot';
			$('slot'+HU.LoveList.next_empty_slot).insert({bottom: '<p class="name">'+HU.LoveList.all_products[m-1].name+'</p><p>'+HU.LoveList.all_products[m-1].price+'</p><p class="view" onclick="HU.LoveList.details_slider.switchTo('+(m-1)+');Effect.ScrollTo(\'product_detail\');">VIEW PRODUCT DETAIL</p><p class="remove" onclick="HU.LoveList.removeFromList(\'product_'+HU.LoveList.all_products[m-1].id+'\','+HU.LoveList.next_empty_slot+');">REMOVE FROM LOVELIST</p>'});
		}
		else{
			$('slot'+HU.LoveList.next_empty_slot).down(0).src = '/product_images/thumb/'+(item_to_add).id+'.jpg';
			$('slot'+HU.LoveList.next_empty_slot).down(0).alt = item_to_add.name;
			$('slot'+HU.LoveList.next_empty_slot).className += ' filledslot';
			$('slot'+HU.LoveList.next_empty_slot).insert({bottom: '<p class="name">'+item_to_add.name+'</p><p class="price">'+item_to_add.price+'</p><p class="view" onclick="document.location=\'/brand/'+brand_slug+'#'+item_to_add.id+'\';">VIEW PRODUCT DETAIL</p><p class="remove" onclick="HU.LoveList.removeFromList(\'product_'+item_to_add.id+'\','+HU.LoveList.next_empty_slot+');">REMOVE FROM LOVELIST</p>'});
		}
		HU.LoveList.next_empty_slot++;
	},

	_setLink: function(featured){
		$('buy').setAttribute('href',(HU.LoveList.all_products[featured-1]).buy_url);
	},
	_setVideo: function($id){
		if(HU.LoveList.all_products[$id-1].video && HU.LoveList.all_products[$id-1].video.length > 0)
		{
			try
			{
				$$('#video object')[0].videoLoader('/brand_videos/' + HU.LoveList.all_products[$id-1].video + '.f4v');
			}
			catch(err)
			{
				//alert(err);
				try
				{
					$$('#video object')[1].videoLoader('/brand_videos/' + HU.LoveList.all_products[$id-1].video + '.f4v');
				}
				catch(err2)
				{
					//alert(err2);
				}
			}
			$$('#brand_logo img')[0].src = '/brand_logos/' + HU.LoveList.all_products[$id-1].video + '.jpg';
		}
	},
	_setImage: function($id){
		if(HU.LoveList.all_products[$id-1].image && HU.LoveList.all_products[$id-1].image.length > 0)
		{
			$$('#video img')[0].src = '/brand_videos/' + HU.LoveList.all_products[$id-1].image + '.jpg';
			$$('#brand_logo img')[0].src = '/brand_logos/' + HU.LoveList.all_products[$id-1].image + '.jpg';
		}
	},
	_updatePosition: function ($to){
		$('item_shown').innerHTML = $to;
		$('total_items').innerHTML = '/'+HU.LoveList.all_products.length;
		HU.LoveList.items_slider.jumpTo($to);
		for(var i=0;i<HU.LoveList.all_products.length;i++)
			$('item'+(i+1)).style.borderColor="#FFFFFF";
		$('item' + $to).style.borderColor="#CCCCCC";
	},
	_lovelistSwitchTo: function($to,$id){
		HU.LoveList.details_slider.switchTo($to);
		HU.EventTracker.add('click',$id);
	}
};