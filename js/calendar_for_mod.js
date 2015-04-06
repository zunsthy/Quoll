/**
 * ZunSThy@QingyingPT 
 * 2015 designed 
 * 'calendar.html'
 */

var query_url = 'web/calendar.php';

function clearForm(){
	$("div#edit_action")[0].innerHTML = "";
	$("input[id][id^=mod_]").each(function(){
		if(this.id != "mod_submit")
			this.value = "";
		else
			this.style.display = "none";
	});
}

function newCalendar(){
	$.get(query_url, {
		action: 'new',
		ep: $("input#mod_ep")[0].value,
		name: $("input#mod_name")[0].value,
		keywords: $("input#mod_keywords")[0].value,
		type: $("input#mod_type")[0].value,
		cover: $("input#mod_cover")[0].value,
		status: $("input#mod_status")[0].value,
		time: $("input#mod_time")[0].value
	}, function(data){
		if(data == "success")
			location.reload();
	}, 'json');
	clearForm();
}

function editCalendar(cfield, cdata){
	//console.log($("input#mod_ep")[0].value);
	if(cdata === undefined || cdata == ''){
		clearForm();
		return;
	}
	
	cid = $("input#mod_id")[0].value;
	cep = $("input#mod_ep")[0].value;
	$.get(query_url, { action: 'edit', id: cid, ep: cep, field: cfield, data: cdata },
	function(data){
		if(data == 'empty'){
			$("div#edit_action")[0].innerHTML = "new";
			$("div#mod_submit").click();
		} else{
			clearForm();
			alert(data);
			if(data == "success")
				initPage();
		}
	}, 'json');
}

$("div#date_enter").keypress(function(e){
	if(e.keyCode == 10 || e.keyCode == 13){
		$("div#create_new").click();
		return false;
	}
});

$("input#mod_id,input#mod_ep").keypress(function(e){
	if(e.keyCode == 10 || e.keyCode == 13){
		return false;
	}
});

$("div#edit_calendar>input[id^=mod_]").keypress(function(e){
	if(e.keyCode == 10 || e.keyCode == 13){	
		$("input#mod_ep")[0].value = '';
		if($("input#mod_id")[0].value != ''){
			//editCalendar(this.id.replace('mod_', ''), this.innerHTML.replace('<br>', ''));
			editCalendar(this.id.replace('mod_', ''), this.value);
		} else
			newCalendar();
		return false;
	}
});

$("div#edit_item>input[id^=mod_]").keypress(function(e){
	if(e.keyCode == 10 || e.keyCode == 13){	
		if($("input#mod_id")[0].value != '' && $("input#mod_ep")[0].value != ''){
			//editCalendar(this.id.replace('mod_', ''), this.innerHTML.replace('<br>', ''));
			editCalendar(this.id.replace('mod_', ''), this.value);
		} else
			newCalendar();
		return false;
	}
});

$(document).on('click', "div#create_new", function(){
	var d = $("div#date_enter")[0].innerHTML;
	if(d && /^\d{4}\-\d{2}\-\d{2}(\s?\d{2}:\d{2}:\d{2})?($|<)/.test(d)){
		clearForm();
		$("div#mod_submit")[0].style.display = "block";
		//console.log(this);
		$("div#edit_action")[0].innerHTML = "new";
		$("input#mod_id")[0].value = "";
		$("input#mod_time")[0].value = d;
	}
}).on('click', "span.mod_edit", function(){
	clearForm();
	//console.log(this.parentNode.previousSibling);
	var ds = this.parentNode.previousSibling.dataset;
	$("div#edit_action")[0].value = "edit";
	cid = $("input#mod_id")[0].value = ds.id;
	cep = $("input#mod_ep")[0].value = ds.ep;
	$.get(query_url, { action: 'query', id: cid, ep: cep }, function(data){
		$("input#mod_name")[0].value = data.name;
		if(data.keywords) $("input#mod_keywords")[0].value = data.keywords;
		if(data.cover) $("input#mod_cover")[0].value = data.cover;
		$("input#mod_type")[0].value = data.type;
		$("input#mod_status")[0].value = data.status;
		$("input#mod_time")[0].value = data.time;
	}, 'json');
}).on('click', "span.mod_delete", function(){
	var ds = this.parentNode.previousSibling.dataset;
	cid = ds.id;
	cep = ds.ep;
	$.get(query_url, { action: 'delete', id: cid, ep: cep }, function(data){
		alert(data);
		if(data == "success")
				initPage();
	}, 'json');
}).on('click', "span.mod_fin", function(){
	var ds = this.parentNode.previousSibling.dataset;
	cid = ds.id;
	cep = ds.ep;
	// console.log(ds);
	$.get(query_url, { action: 'edit', id: cid, ep: cep, field: 'status', data: 'fin' },
	function(data){
		alert(data);
		if(data == "success")
			initPage();
	}, 'json');
}).on('click', "div#mod_submit", function(){
	newCalendar();
});


