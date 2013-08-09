if(typeof(HU) == "undefined")
	var HU = { $:function(id) { return document.getElementById(id); } };
// Requires Prototype
HU.EventTracker = {
	add: function(category,value){
		new Ajax.Updater('points','/events/add',{ method: 'post', parameters: { 'data[Event][category]': category, 'data[Event][value]': value} });
	}
};