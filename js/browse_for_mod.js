


var permission;

$.ajax({
	url: 'web/browse.php?action=permission',
	dataType: 'json'
}).success(function(d, s, jqXHR){
	permission = $.parseJSON(jqXHR.responseText);
	if(jQuery.inArray('edit', permission) >= 0){
		addCSSRule(document.styleSheets[0], 'li.torrent:hover div.t_edit', 'display: block');
	}
});

