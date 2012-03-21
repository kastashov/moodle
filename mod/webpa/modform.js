function reloadForms(url)
{
	var dropdown = $("#id_form_select");
	$.get(url,function(data){
		for(var k in data)
		{
			var v = data[k];
			var x = $(dropdown).find("option[value='"+k+"']");
			if(x.length)
				x.html(v);
			else
				$(dropdown).append('<option value="'+k+'" selected="selected">'+v+'</option>');
		}
	},'json');
}

function someValidation(value) {
	alert(value);
	return false;
}