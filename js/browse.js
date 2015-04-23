/**
 * ZunSThy@QingyingPT
 * 2015 designed
 * 'browse.html'
 * 2015-04-15 created
 */


var language;
var require_lang_file = setInterval(function(){
	if(language == undefined){
		$.ajax({
			url: 'lang/chs/browse.json',
			dataType: 'json',
		}).success(function(data, st, jqXHR){
			if(jqXHR.st == 304)
				language = $.parseJSON(jqXHR.responseText);
			else
				language = data;
		});
	} else {
		//console.log(language);
		clearInterval(require_lang_file);
		initPage();
	}
}, 100);

// Data Cache
function TorrentListCache(){
	this.c = {};
}

TorrentListCache.prototype.get = function(key){
	if(key) var k = JSON.stringify(key);
	return this.c[k];
};

TorrentListCache.prototype.cache = function(key, value){
	this.c[JSON.stringify(key)] = value;
};

// Torrents List Object
function TorrentList(){
	this.cache = new TorrentListCache();
}

TorrentList.prototype.link = {
	source: 'web/browse.php',
	user: '/user.php?id=',
	details: '/details.php?id=',
	bookmark: '/bookmark.php?torrentid=',
	download: '/download.php?id=',
	edit: '/edit.php?id='
};

TorrentList.prototype.keys = [
	'cat[]', 'sou[]', 's', 'sa', 'sm', 'nothotword', 
	'state'/* TODO: state and type maybe ARRAY */, 'type', 'banned', 'all', 'marked',
	'orderby', 'order', 'page'
];

TorrentList.prototype.options = {};

TorrentList.prototype.setOption = function(key, value){
	if(value == undefined || value === -1){
		if(this.options[key] != undefined)
			delete this.options[key];
	} else {
		this.options[key] = value;
	}
};

TorrentList.prototype.clearOptions = function(){
	for(var key in this.options)
		delete this.options[key];
};

TorrentList.prototype.getList = function(callback){
	var self = this;
	var ret = self.cache.get(self.options);
	if(ret){
		callback(ret);
	} else {
		$.ajax({
			url: self.link.source,
			dataType: 'json',
			type: 'GET',
			data: self.options,
		}).success(function(data){
			self.cache.cache(self.options, data);
			callback(data);
		}).fail(function(jqXHR, textStatus){
			console.log(textStatus);
			callback({});
		});
	}
};

TorrentList.prototype.init = function(){
	for(var i in this.keys){
		var k = this.keys[i];
		var v = getParamValue(k);
		//console.log(k, v);
		if(/\[\]/.test(k)){
			if(v != undefined && v != null){
				this.setOption(k.replace(/\[\]/, ''), v);
				continue;
			} else
				v = getParamValue(k.replace(/\[\]/, ''));
		} 			
		if(v != undefined && v != null)
			this.setOption(k.replace(/\[\]/, ''), v);
	}
};

TorrentList.prototype.genSection = function(){
	var self = this;
	var newurl = window.location.protocol + '//' 
		+ window.location.host + window.location.pathname + '?' + $.param(self.options);
	window.history.pushState({path: newurl}, '', newurl);

	this.getList(function(data){
		if(data){
			self.genPager(document.getElementsByClassName('pager'), data.page);
			self.genList(document.getElementById('list'), data.field, data.data);
		} else {
			/* empty ! */
		}
	});
};

TorrentList.prototype.genPager = function(eles, data){
	if(!eles || !data)
		return;
	var at = data[2];
	for(var i = 0; i < eles.length; i++){
		eles[i].innerHTML = "";
		var div = document.createElement('div');
		div.id = 'result';
		div.innerHTML = language.result_number + data[0];
		eles[i].appendChild(div);

		var ul = document.createElement('ul');
		ul.id = 'pagers';
		var flag = 0;
		for(var j = 1; j <= data[1]; j++){
			if((j - at > 3 || at - j > 3) && j != 1 && j != data[1]){
				if(flag == 0){
					var li = document.createElement('li');
					li.className = 'page_null';
					li.innerHTML = '...';
					ul.appendChild(li);
					flag = 1;
				}
					continue;
			} 
			flag = 0;
			var li = document.createElement('li');
			if(j == at + 1)
				li.className = 'page_at';
			else
				li.className = 'page';
			li.innerHTML = j;
			li.dataset.page = j-1;
			ul.appendChild(li);
		}
		eles[i].appendChild(ul);
	}
};

TorrentList.prototype.genList = function(ele, field, data){
	if(!ele || !field || !data)
		return;
	ele.innerHTML = "";
	var ul = document.createElement('ul');
	ul.id = 'torrents';
	ul.className = 'torrents';
	for(var i = 0; i < data.length; i++){
		var li = document.createElement('li');
		li.className = 'torrent';
		if(data[i][4] == 'yes')
			li.className += ' banned';
		
		var div = document.createElement('div');
		div.className = 't_ico_' + data[i][7];
		li.appendChild(div);

		div = document.createElement('div');
		div.className = 't_main';
		var div_c = document.createElement('div');
		div_c.className = 't_name';
		div_c.innerHTML = data[i][11];
		div.appendChild(div_c);
		var ms = data[i][12].match(/([\S\s]+?)(\*[\S\s]+)?$/);
		if(ms){
		div_c = document.createElement('div');
		div_c.className = 't_descr';
		div_c.innerHTML = ms[1];
		div.appendChild(div_c);
		if(ms[2]){
			div_c = document.createElement('div');
			div_c.className = 't_descr_s';
			div_c.innerHTML = ms[2] ? ms[2] : ''; //TODO: segment
			div.appendChild(div_c);
		}
		}
		li.appendChild(div);
		
		div = document.createElement('div');
		div.className = 't_tool';
		div_c = document.createElement('div');
		if(this.options.mark == '1')
			div_c.className = 't_mark_no';
		else
			div_c.className = 't_mark';
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_edit';
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_download';
		div.appendChild(div_c);
		li.appendChild(div);

		div = document.createElement('div');
		div.className = 't_right';
		div_c = document.createElement('div');
		div_c.className = 't_time';
		div_c.innerHTML = data[i][15];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_size';
		div_c.title = data[i][14];
		div_c.innerHTML = data[i][14].formatSize();
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_uploader';
		div_c.innerHTML = '<a class="user_link UC_' + (data[i][17] == 'yes' ? '_NULL' : data[i][21]) 
				+ '" href="' + this.link.user + data[i][18] + '">' 
				+ data[i][20] + '</a>';
		div.appendChild(div_c);
		li.appendChild(div);

		div = document.createElement('div');
		div.className = 't_top';
		div_c = document.createElement('div');
		div_c.className = 't_seeders';
		div_c.title = language.tip_seeders;
		div_c.innerHTML = data[i][10];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_leechers';
		div_c.title = language.tip_leechers;
		div_c.innerHTML = data[i][9];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_completed';
		div_c.title = language.tip_completed;
		div_c.innerHTML = data[i][13];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_comments';
		div_c.title = language.tip_comments;
		div_c.innerHTML = data[i][16];
		div.appendChild(div_c);
		li.appendChild(div);

		div = document.createElement('div');
		div.className = 't_addition';
		div_c = document.createElement('div');
		div_c.className = 't_picktype';
		div_c.innerHTML = data[i][5];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_source';
		div_c.innerHTML = data[i][8];
		div.appendChild(div_c);
		div_c = document.createElement('div');
		div_c.className = 't_state';
		div_c.innerHTML = data[i][1];
		div.appendChild(div_c);
		li.appendChild(div);

		li.dataset.id = data[i][0];
		li.dataset.pos = data[i][6];
		li.dataset.state = data[i][1];
		li.dataset.url = data[i][19];
		ul.appendChild(li);
	}
	ele.appendChild(ul);
};

var tl = new TorrentList();

function initPage(){
	tl.init();
	tl.genSection();
}


$(document).on('click', 'div.t_descr', function(){
	var id = this.parentNode.parentNode.dataset.id;
	window.open(tl.link.details + id);
}).on('click', 'div.t_download', function(){
	var id = this.parentNode.parentNode.dataset.id;
	window.open(tl.link.download + id);
}).on('click', 'div.t_edit', function(){
	var id = this.parentNode.parentNode.dataset.id;
	window.open(tl.link.edit + id);
}).on('click', 'div[class^=t_mark]', function(){
	var id = this.parentNode.parentNode.dataset.id;
	if(this.className == 't_mark')
		this.className = 't_mark_no';
	else
		this.className = 't_mark';
	//window.open(tl.link.bookmark + id);
}).on('click', 'div[class^=t_ico]', function(){
	tl.clearOptions();
	tl.setOption('cat', [ parseInt(this.className.match(/\d+/)) ]);
	tl.genSection();
}).on('click', 'li.page', function(){
	var page = parseInt(this.dataset.page);
	if(page)
		tl.setOption('page', page);
	else 
		tl.setOption('page');
	tl.genSection();
});
