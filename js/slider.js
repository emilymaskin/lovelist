if(typeof(HU) == "undefined")
	var HU = { $:function(id) { return document.getElementById(id); } };

// Slider Class
HU.Slider = Class.create(
	{
		// Depends: Protoype, Scriptaculous
		// Assume you start in the first div
		initialize: function(obj)
		{
			if(! obj)
				obj = {};
			this.div_id = typeof(obj.div_id) != "undefined" ? obj.div_id : '';
			this.total_slots = typeof(obj.total_slots) != "undefined" ? parseInt(obj.total_slots) : 1;
			this.list_size = typeof(obj.list_size) != "undefined" ? parseInt(obj.list_size) : 1;
			this.first_slot = typeof(obj.first_slot) != "undefined" ? parseInt(obj.first_slot) : 1;
			this.slide_by = typeof(obj.slide_by) != "undefined" ? obj.slide_by : 120;
			this.duration = typeof(obj.duration) != "undefined" ? obj.duration : 1.0;
			this.transition = typeof(obj.transition) != "undefined" ? obj.transition : Effect.Transitions.sinoidal;
			this.effect = typeof(obj.effect) != "undefined" ? obj.effect : 'slide';
			this.jump_effect = typeof(obj.jump_effect) != "undefined" ? obj.jump_effect : this.effect;
			this.onChange = typeof(obj.onChange) != "undefined" ? obj.onChange : null;
			this.active = false;
			if(this.effect == 'slideVertical')
				this.setHeight();
			else
				this.setWidth();			
		},
		setWidth: function()
		{
			$(this.div_id).style.width = (this.list_size*this.slide_by+20)+'px';
		},
		setHeight: function()
		{
			$(this.div_id).style.height = (this.list_size*this.slide_by+20)+'px';
		},
		next: function($num)
		{
			if(this.active || ! $(this.div_id) || (this.total_slots + this.first_slot > this.list_size))
				return false;
			this.active = true;
			$num = $num ? $num : 1;
			this.first_slot = parseInt(this.first_slot)+$num;
			this._onChange(this.first_slot);
		
			this.doEffect(this.effect,-$num);
		},
		prev: function($num)
		{
			if(this.active || ! $(this.div_id) || (this.first_slot<=1))
				return false;

			this.active = true;
			$num = $num ? $num : 1;
			this.first_slot -= parseInt($num);
			this._onChange(this.first_slot);
		
			this.doEffect(this.effect,$num);
		},
		end: function()
		{
			var adjustment = this.list_size - this.first_slot + 1 - this.total_slots;
			if(adjustment>0)
				this.next(adjustment);
		},
		start: function()
		{
			var adjustment = this.first_slot - 1;
			if(adjustment>0)
				this.prev(adjustment);
		},
		jumpTo: function($num)
		{
			if(this.active || !$(this.div_id) || $num == this.first_slot - 1 || this.list_size < this.total_slots)
				return false;
			this.active = true;
			$num2 = this.first_slot;
			if($num <= this.list_size - this.total_slots + 1)
				this.first_slot = parseInt($num);
			else
				this.first_slot = this.list_size - this.total_slots + 1;
			this._onChange(this.first_slot);
			
			this.doEffect(this.jump_effect, -(this.first_slot-$num2));
		},
		updateListSize: function($change)
		{
			if(this.list_size + $change >= 0)
			{
				this.list_size += $change;
				if(this.effect == 'slideVertical')
					this.setHeight();
				else
					this.setWidth();
			}
		},
		
		updateNumbers: function ($to,$slider_name){
			$('item_shown').innerHTML = $to;
			$('total_items').innerHTML = '/'+this.list_size;
		},
		
		_onChange: function($to,$total)
		{
			if(this.onChange)
				eval(this.onChange+'('+$to+','+$total+');');
		},
		
		/*	Effects: */
		doEffect: function($effect, $num)
		{
			eval('this._'+$effect+'('+$num+');');
		},
		_slide: function($num)
		{
			new Effect.Move(this.div_id, { x: (this.slide_by*$num), duration: this.duration, transition: this.transition, that: this, afterFinish: function() { this.that.active = false; } });
		},
		_slideVertical: function($num){
			new Effect.Move(this.div_id, { y: (this.slide_by*$num), duration: this.duration, transition: this.transition, that: this, afterFinish: function() { this.that.active = false; } });			
		},
		_instant: function($num)
		{
			var $offset = $(this.div_id).style.left ? parseInt($(this.div_id).style.left.replace('px','')) : 0;
			$offset += this.slide_by*$num;
			$(this.div_id).style.left = $offset+'px';
			this.active = false;
		}
	}
);