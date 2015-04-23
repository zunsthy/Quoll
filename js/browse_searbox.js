var sa_row = [ 
	[ 'sa', [ 0, 1, 3, 4 ] ],
	[ 'sm', [ 0, 1, 2 ] ], 
	[ 'all', [ 0, 1, 2 ] ], 
	[ 'state', [ 0, 1, 2, 3, 4, 5, 6, 7 ] ], 
	[ 'type',  [ 'normal', 'hot', 'classic', 'recommended' ] ],
	[ 'marked', [ 0, 1, 2 ] ],
	[ 'cat', [ 401, 402, 405, 404, 403, 407, 406, 413, 410, 411, 409 ] ],
	[ 'sou', [] ],
	[ 'order', [ 'desc', 'asc'] ],
	[ 'orderby', [ 'title', 'born', 'comment', 'seeder', 'leecher', 'completed', 'size' ] ]
];


var sa_timer;
searchbox();

function searchbox(){
	if(!language){
		sa_timer = setInterval(searchbox, 200);
		return;
	} else {
		//console.log('the language file loaded');
		clearInterval(sa_timer);
	}
	//console.log(sa_row);
	for(var i = 0; i < sa_row.length; i++){
		//console.log(sa_row[i]);
		var list = $('#' + sa_row[i][0]  + '[class^=searchbox_]')[0];
		if(list == undefined)
			continue;
		var values = sa_row[i][1],
				list_items = "";
		list.dataset.field = sa_row[i][0];
		//list.innerHTML = language.sa_row[i][0];
		list.innerHTML = "";
		for(var j = 0; j < values.length; j++)
			list_items += (language.sa_row[i][j]) ? ('<div data-v="' + values[j] + '">' + language.sa_row[i][j] + '</div>') : '';
		list.innerHTML += list_items; 
	}
	var args = tl.options;
	for(var k in args){
		if(k == 's'){
			$('input#ss')[0].value = args[k];
		} else if(k == 'cat' || k == 'sou'){
			if(Array.isArray(args[k])){
				var arr = args[k];
				for(var i = 0; i < arr.length; i++){
					var eles = $('#' + k + '>div[data-v=' + arr[i] + ']');
					if(eles.length > 0) eles[0].className = 'active';
				}
			} else {
				var eles = $('#' + k + '>div[data-v=' + args[k] + ']');
				if(eles.length > 0) eles[0].className = 'active';
			}
		} else if($.inArray(k, ['sa', 'sm', 'marked', 'all']) >= 0){
			$('#' + k + '>div').css('display: none');
			$('#' + k + '>div[data-v=' + args[k] + ']').css('display: block');
		} else if($.inArray(k, ['state', 'type', 'order', 'orderby']) >= 0){
			var eles = $('#' + k + '>div[data-v=' + args[k] + ']');
			if(eles.length > 0) eles[0].className = 'active';
		}
	}
}

function clickSearchButton(){
	tl.setOption('page', 1);
	tl.setOption('page');
	var categories = [], sources = [];
	$('#cat>div.active').each(function(){ categories.push(this.dataset.v); });
	if(categories.length > 0)
		tl.setOption('cat', categories);
	else 
		tl.setOption('cat');
	$('#sou>div.active').each(function(){ sources.push(this.dataset.v); });
	if(sources.length > 0)
		tl.setOption('sou', sources);
	else
		tl.setOption('sou');
	var s = $('input#ss')[0].value;
	if(tl.options == undefined || tl.options.s != s){
		if(s != undefined && s.trim())
			tl.setOption('s', s);
	}
}

$('input#ss').keypress(function(e){
	if(e.which == 13 || e.which == 10){
		clickSearchButton();
		tl.genSection();
	}
});

// TODO: 'active' replace / / 
 
$(document).on('click', '#searchbox_go', function(){
	clickSearchButton();
	tl.genSection();
}).on('click', '#state>div,#type>div,#orderby>div,#order>div', function(){
	if(this.className.indexOf('active') >= 0){
		this.className = '';
		tl.setOption(this.parentNode.dataset.field);
	} else {
		var activated_element = this.parentNode.getElementsByClassName('active')[0];
		if(activated_element)
			activated_element.className = '';
		this.className += 'active';
		tl.setOption(this.parentNode.dataset.field, this.dataset.v);
	}
}).on('click', '#cat>div,#sou>div', function(){
	if(this.className.indexOf('active') >= 0){
		this.className = '';
	} else {
		this.className += 'active';
	}
}).on('click', '#sa>div,#sm>div,#marked>div,#all>div', function(){
	//var cec = this.parentNode.childNodesCount;
	this.style.display = 'none';
	var ele = this.nextElementSibling;
	if(ele){
		ele.style.display = 'block';
		if(ele.dataset.v)
			tl.setOption(ele.parentNode.dataset.field, ele.dataset.v);
	} else {
		ele = this.parentNode.firstChild;
		ele.style.display = 'block';
		tl.setOption(ele.parentNode.dataset.field);
	}
});
