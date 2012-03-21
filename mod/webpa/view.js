function showMarkSheets(id)
{
	var container = $("#marks");
	var xtant = container.html();
	container.html("Loading"+xtant);
	container.show('fast');
	$.getJSON("marks.php",{'id':id,'getsheets':1},function(data){
		var dropdown = $('<select name="sheet" id="marks-dropdown" onchange="markSheetSelect(this.selectedIndex)" />')
		var ctr = 1;
		for(sheet in data.sheets)
		{
			var dt = new Date(parseInt(sheet))
			var opt = $('<option value="'+dt.getTime()+'">Mark Sheet '+ctr+'</option>');
			opt.data('table',data.sheets[sheet]);
			dropdown.append(opt);
			ctr++;
		}
		if(dropdown.children().length==0)
		{
			container.html('No mark sheets found. <a href="login.php?id='+id+'" target="_blank">Login</a> to WebPA to mark this assignment.');
		}
		else
		{
			container.html(xtant);
			$("#marks-table").show('fast');
			var form = $('<form action="marks.php" method="get"><input type="hidden" name="id" value="'+id+'"><small>Mark Sheet:</small></form>');
			form.append(dropdown);
			form.append('&nbsp;<input type="submit" value="Submit to Gradebook" />');
			form.insertBefore($("#marks-table"));
			markSheetSelect(0);
		}
	})
}

function markSheetSelect(index)
{
	var _opt = $("#marks-dropdown").get(0).options[index];
	var opt = $(_opt).data('table');
	$("#marks-weighting").html(opt.weighting);
	$("#marks-algorithm").html(opt.algorithm);
	$("#marks-grading-type").html(opt.grading);
	$("#marks-penalty").html(opt.penalty+opt.penalty_type);
}