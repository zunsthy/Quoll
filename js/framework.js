var cat_a = [ 
	['电影', 401, [ ['合集', 57], ['原盘', 60], ['1080P',3 ], ['720P', 6 ], ['480P', 5 ], ['其他', 7 ] ]],
	['剧集', 402, [ ['大陆', 23], ['港台', 24], ['欧美', 25], ['日韩', 26], ['完结', 28]               ]], 
	['动漫', 405, [ ['连载', 45], ['完结', 51], ['漫画', 46], ['音乐', 52], ['OVA',  84], ['其他', 47] ]], 
	['纪录', 404, [ ['自然', 61], ['科技', 64], ['历史', 63], ['人文', 62], ['其他', 34]               ]],
	['综艺', 403, [ ['娱乐', 68], ['访谈', 69], ['赛事', 70], ['其他', 18] ]],
	['体育', 407, [ ['篮球', 20], ['足球', 21], ['其他', 22], ['教学', 65] ]],
	['音乐', 406, [ ['华语', 2 ], ['欧美', 1 ], ['日韩', 8 ], ['古典', 9 ], ['新世纪', 76], ['演唱会', 17], ['M V', 16], ['其他', 10] ]],
	['软件', 413, [ ['Win' , 42], ['*nix', 50], ['手机', 43], ['其他', 44] ]],
	['游戏', 410, [ ['电脑', 11], ['主机', 12], ['手机', 14], ['其他', 13] ]],
	['学习', 411, [ ['考研', 29], ['外语', 58], ['专业', 30], ['讲座', 31], ['文选', 66], ['书刊', 67], ['其他', 32] ]],
	['其他', 409, [ ['哈工大', 77] ]]
];

(function(){
	var header = document.getElementsByTagName('header')[0];
	if(header.innerHTML.trim() == ''){
		header.innerHTML = '<div class="logo"><img src="styles/logo/logo-black.png" alt="清影PT"></div><div class="top">分享精彩,其乐无穷</div>';
	}
	var navbar = document.getElementsByTagName('nav')[0];
	if(navbar.innerHTML.trim() == ""){
		var l1 = 'browse.html?cat=';
		var l2 = 'browse.html?sou=';
		var menu = document.createElement('ul');
		menu.className = 'menu';

		var menu_item = document.createElement('li');
		menu_item.className = 'menu';
		menu_item.innerHTML = '<a class="link" href="/">首页</a>';
		menu.appendChild(menu_item);
		menu_item = document.createElement('li');
		menu_item.className = 'menu';
		menu_item.innerHTML = '<a class="link" href="/forums.php">论坛</a>';
		menu.appendChild(menu_item);

		for(var i in cat_a){
			menu_item = document.createElement('li');
			menu_item.className = 'menu';
			var inner = '<a class="link" href="' + l1 + cat_a[i][1] + '">' + cat_a[i][0] + '</a>';
			inner += '<ul class="menu_child">';
			var tmp = cat_a[i][2];
			for(var j in tmp){
				inner += '<li class="menu_child"><a href="' + l2 + tmp[j][1] + '">' + tmp[j][0] + '</a></li>';
			}
			inner += '</ul>';
			menu_item.innerHTML = inner;
			menu.appendChild(menu_item);
		}
		navbar.appendChild(menu);

		var searchbox = document.createElement('div');
		searchbox.id = 'searchboxplus';
		var form = document.createElement('form');
		form.id = 'searchboxplus';
		form.className = 'clearfix';
		form.setAttribute('action', 'browse.html');
		form.setAttribute('method', 'GET');
		form.innerHTML = '<input id="searchboxplus_search" type="text" placeholder="搜..." name="s"></input><input id="searchboxplus_incldead" type="text" style="display:none;" value="1" name="all"></input><input id="searchboxplus_submit" type="submit" style="display:none;"></input>';
		searchbox.appendChild(form);
		navbar.appendChild(searchbox);
	}
})();
