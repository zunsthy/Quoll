/**
 * ZunSThy@QingyingPT 
 * 2015 designed 
 * 'calendar.html'
 * 2015-03-31 created
 */

var permission;
$.ajax({
	url: 'web/calendar.php?action=permission',
	async: true,
	dataType: 'json'
}).done(function(d, s, jqXHR){
	permission = $.parseJSON(jqXHR.responseText);
	if(jQuery.inArray('edit', permission) >= 0){
		$("div.for_mod").show();
	}
});
var language;
var require_lang_file = setInterval(function(){
	if(language === undefined){
	$.ajax({
		url: 'lang/chs/calendar.json',
		async: true,
		dataType: 'json'
	}).success(function(data, status, jqXHR){ 
		if(jqXHR.status == 304)
			language = $.parseJSON(jqXHR.responseText);
		else
			language = data;
	});
	} else {
		initPage();
		clearInterval(require_lang_file);
	}
}, 100);

var calendar = new CalendarList("", 0, true);

var searchlink = "/torrents.php?search=";

function CalendarList(date, type, all){
	this.date = date ? new Date(date) : new Date();
	this.date.setHours(this.date.getHours() + 8);
	this.type = parseInt(type);
	this.all = all ? true : false;
	this.url = "web/calendar.php";
	this.cover = "../styles/20131220Logo.png";
	this.arr = new Array();
	this.cache = new Array();
}

CalendarList.prototype.setDate = function(date){
	this.date = new Date(date);
};

CalendarList.prototype.changeDate = function(days){
	this.date.setDate(this.date.getDate() + parseInt(days));
};

CalendarList.prototype.setType = function(type){
	this.type = parseInt(type);
	this.all = false;
};

CalendarList.prototype.setAll = function(all){
	this.all = all ? true : false;
};

CalendarList.prototype.genList = function(ele){
	if(!ele)
		return;
	var d = this.date.toISOString().slice(0,10);
	//console.log(d);
	if(this.all)
		query = { date: d };
	else 
		query = { date: d, type: this.type };
	var self = this;
	var i = -1;
	if((i = jQuery.inArray(d, this.arr)) >= 0){
		if(this.cache[i] != "empty")
			showList(ele, this.cache[i]);
	} else {
		$.get(this.url, query, 
			function(data){
				if(data == "empty"){
					self.arr.push(d);
					self.cache.push(data);
					showList(ele, "");
				} else if(data == "error"){
					alert("error");
				} else {
					self.arr.push(d);
					self.cache.push(data);
					showList(ele, data);
				}
			}	,'json');
	}
};

function showList(ele, objs){
	if(!objs){ // no list
		div = document.createElement('div');
		div.className = 'empty';
		div.innerHTML = 'EMPTY';
		return;
	}
	var default_cover = calendar.cover;
	for(var i in objs){
		var component = document.createElement('div');
		var div = document.createElement('div');
		div.title = objs[i].name;
		div.id = 'item_' + objs[i].id;
		div.className = 'items';
		//div.setAttribute('data-keywords', objs[i].keywords);
		//div.setAttribute('data-category', objs[i].type);
		//div.setAttribute('data-episode', objs[i].ep);
		//div.setAttribute('data-status', objs[i].status);
		//console.log(div);
		div.dataset.keywords = objs[i].keywords;
		div.dataset.id = objs[i].id;
		div.dataset.ep = objs[i].ep;
		var cover = document.createElement('div');
		cover.className = 'cover';
		cover.innerHTML = '<div><img src="' + (objs[i].cover ? objs[i].cover : default_cover ) + '"></div>';
		div.appendChild(cover);
		var title = document.createElement('div');
		title.className = 'title';
		title.innerHTML = '<p>' + objs[i].name + '</p>'
		 + '<p>' + language.ep[objs[i].type][0] + objs[i].ep + language.ep[objs[i].type][1] + '</p>';
		div.appendChild(title);
		component.appendChild(div);
		
		if(jQuery.inArray('edit', permission) >= 0){
			div = document.createElement('div');
			div.className = 'mod';
			var mod = document.createElement('span');
			mod.className = 'mod mod_edit';
			mod.innerHTML = language.mod_edit;
			div.appendChild(mod);
			if(objs[i].status == 'air'){
				mod = document.createElement('span');
				mod.className = 'mod mod_fin';
				mod.innerHTML = language.mod_setfinal;
				div.appendChild(mod);
			}
			mod = document.createElement('span');
			mod.className = 'mod mod_delete';
			mod.innerHTML = language.mod_delete;
			div.appendChild(mod);
			component.appendChild(div);
		}
		ele.appendChild(component);
	}
}

function getParameterByName(name) {
	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
	return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function changeHref(objs){
	var newurl = window.location.protocol + '//' + window.location.host + window.location.pathname + '?';
	//console.log(objs);
	for(var i in objs)
		newurl += encodeURIComponent(i) + '=' + encodeURIComponent(objs[i]) + '&';
	//console.log(newurl);
	window.history.pushState({path: newurl}, '', newurl);
	initPage();
}

function initPage(){
	//var nav = window.location.href.match(/#.*$/);
	$("div.side_r>div").remove();
	var d = getParameterByName('d');
	var t = getParameterByName('t');
	if(d) calendar.setDate(d);
	if(t) calendar.setType(t);
	$("div#date_enter")[0].innerHTML = calendar.date.toISOString().slice(0,19).replace('T',' ');
	var span = $("div.date_bar>span");
	calendar.genList($("li#today div.side_r")[0]);
	span[2].innerHTML = calendar.date.toISOString().slice(5,10);
	calendar.changeDate(-1);
	calendar.genList($("li#yesterday div.side_r")[0]);
	span[1].innerHTML = calendar.date.toISOString().slice(5,10);
	calendar.changeDate(-1);
	span[0].innerHTML = calendar.date.toISOString().slice(5,10);
	calendar.changeDate(3);
	calendar.genList($("li#tomorrow div.side_r")[0]);
	span[3].innerHTML = calendar.date.toISOString().slice(5,10);
	calendar.changeDate(1);
	span[4].innerHTML = calendar.date.toISOString().slice(5,10);
	calendar.changeDate(-2);
}


$(document).on('click', "div.items", function(){
	//console.log(this.dataset);
	//location.href = searchlink + encodeURIComponent(this.dataset.keywords);
	window.open(searchlink + encodeURIComponent(this.dataset.keywords));
}).on('click', "div.date_previous", function(){
	calendar.changeDate(-1);
	changeHref({d: calendar.date.toISOString().slice(0,10)});
}).on('click', "div.date_next", function(){
	calendar.changeDate(1);
	changeHref({d: calendar.date.toISOString().slice(0,10)});
});

