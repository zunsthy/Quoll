
var mouse_interval;

$(document).on('mouseover', "li.menu", function(){
	$("ul.menu_child").hide();
	clearInterval(mouse_interval);
	$(this).find("ul").show();
}).on('mouseout', "li.menu", function(){
	var self = this;
	mouse_interval = setTimeout(function(){
		clearInterval(mouse_interval);
		$(self).find("ul").slideUp(100);
	}, 200);
});
