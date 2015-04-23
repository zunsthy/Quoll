
function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

var getParamValue = (function(){
	var params;
	var resetParams = function(){
		var query = window.location.search;
		var regex = /[?&;](.+?)=([^&;]+)/g;
		var match;
		params = {};
		if(query){
			while(match = regex.exec(query)){
				if(params[match[1]] == undefined)
					params[match[1]] = [];
				params[match[1]].push(decodeURIComponent(match[2]));
			}
		}    
	};
	window.addEventListener
		&& window.addEventListener('popstate', resetParams);

	resetParams();
	return function(param){
		if(param) return params.hasOwnProperty(param) ? (params[param].length == 1 ? params[param][0] : params[param]) : null;
		else return params;
	}
})();

function addCSSRule(sheet, selector, rules, index) {
	if("insertRule" in sheet) {
		sheet.insertRule(selector + "{" + rules + "}", index);
	}
	else if("addRule" in sheet) {
		sheet.addRule(selector, rules, index);
	}
}

/*
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
*/

String.prototype.formatNum = function(fixed, x, separetor, delimiter){
	var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (fixed > 0 ? '\\D' : '$') + ')',
			num = Number(this).toFixed(Math.max(0, ~~fixed));
	return (delimiter ? num.replace('.', delimiter) : num).replace(new RegExp(re, 'g'), '$&' + (separetor || ','));
};

String.prototype.formatSize = function(si, fixed, space){
	var n = Number(this),
			s = si ? [1000, 'kMGTPEZY', 'B'] : [1024, 'KMGTPEZY', 'iB'],
			x = Math.log(n) / Math.log(s[0]) | 0;
	return ((n / Math.pow(s[0], x)).toFixed(fixed || 2) + (space || ' ') + (x ? (s[1][--x] + s[2]) : 'Bytes'));
};
