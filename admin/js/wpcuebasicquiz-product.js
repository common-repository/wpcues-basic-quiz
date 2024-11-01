jQuery(document).ready(function($){
function itemtablealternate(){
$('#itemtable >tbody > tr:odd').css("background-color","#eee");
$('#itemtable > tbody > tr:even').css("background-color","#fff");
$('.itemtable > tbody > tr:even').css("background-color","#fff");
$('.itemtable > tbody > tr:odd').css("background-color","#eee");
}
itemtablealternate();
function rename_itemtablerows(){
	var $i=1;
	$('#itemtable').find('.itemnum').each(function(){
		$(this).text($i);
		$i++;
	});
}
$('#postdivrich').tabs();
$(document).on('click','.submitdelete',function(e){
	e.preventDefault();
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuequiztrashproduct_action',
				'postid':$('#postid').val()
				},
			success: function(data){
			msg=data.msg;
			if(data.msg=='success'){
				document.location.href=data.redirecturl;
			}else{
				alert(productL10n.producttrash);
			}

		}
	});
});
$(document).on('click','.handlediv',function(){
	$(this).siblings('.inside').toggle();
});
$(document).on('click','#add_quizitem_button ,#add_quizcatitem_button , .pageitemlink',function(e){
	e.preventDefault();
	if($(this).attr('id') == 'add_quizitem_button'){
		if($('#producteditor').is(':visible')){alert(productL10n.itemeditor);return false;}
		$itemtype=1;$page=1;
	}else if($(this).attr('id') == 'add_quizcatitem_button'){
		if($('#producteditor').is(':visible')){alert(productL10n.itemeditor);return false;}
		$itemtype=2;$page=1;
	}else{
		$page=parseInt($(this).data('value'),10);
		$itemtype=$('#itemtype').val();	
	}
	if($('input[name="selectallstatus"]').length){
		$selectall=$('input[name="selectallstatus"]').val()
	}else{$selectall=0;}
	$productid=$('#postid').val();
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuefetchitemlist_pageaction',
				'itemtype':$itemtype,
				'page':$page,
				'selectall':$selectall,
				'productid':$productid,
			},
			success: function(data){
				if(data.msg == 'success'){
					$('#producteditor').html(data.content);
					$('#producteditor').show();
				}else{
					alert(productL10n.errormsg);
				}
			}
	});
});
$(document).on('click','.saveimportitem',function(){
$importitems=[];$i=0;$importitemtitles=[];
$('input[name="importitems[]"]:checked').each(function(){$importitems[$i]=$(this).val();$i++;});
$i=0;
$('input[name="importitemtitles[]"]').each(function(){$importitemtitles[$i]=$(this).val();$i++;});
if(typeof $importitems =='undefined'){return false;}
$productid=$('#postid').val();
$itemtype=$('#itemtype').val();
var newindex=$('#itemtable').find('.addeditemrow').length+1;
if($('#original_post_status').val() !=productL10n.publish){
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuesaveitemlist_pageaction',
				'items':$importitems,
				'productid':$productid,
				'itemtype':$itemtype
			},
			success: function(data){
				if(data.msg == 'success'){
					var content='';
					jQuery.each($importitems,function(index,value){
						content+='<tr id="rowitem-'+value+'" class="addeditemrow"><td class="itemnum">'+(newindex+index)+'<td>'+$importitemtitles[index]+'</td><td>';
						content+='<input type="hidden" name="addeditem[]" value="'+value+'">';
						content+='<input type="hidden" name="addeditemtype[]" value="'+$itemtype+'">';
						if($itemtype==1){
							content+='Quiz';
						}else{
							content+='Quiz Category';
						}
						content+='</td><td class="removeitem"></td></tr>';
					});
					$('#itemtable').append(content);
					$('#itemtable').show();
					itemtablealternate();
					$('#producteditor').empty();
					$('#producteditor').hide();
				}else{
					alert(productL10n.errormsg);
				}
			}
	});
}else{
	var content='';
	jQuery.each($importitems,function(index,value){
		content+='<tr id="rowitem-'+value+'" class="addeditemrow"><td class="itemnum">'+(newindex+index)+'<td>'+$importitemtitles[index]+'</td><td>';
		content+='<input type="hidden" name="addeditem[]" value="'+value+'">';
		content+='<input type="hidden" name="addeditemtype[]" value="'+$itemtype+'">';
		if($itemtype==1){
			content+='Quiz';
		}else{
			content+='Quiz Category';
		}
		content+='</td><td class="removeitem"></td></tr>';
	});
	$('#itemtable').append(content);
	$('#itemtable').show();
	itemtablealternate();
	$('#producteditor').empty();
	$('#producteditor').hide();
}
});
$(document).on('click','.cancelimportitem',function(){
	$('#producteditor').empty();
	$('#producteditor').hide();
});
$(document).on('click','.removeitem',function(){
	$itemid=$(this).siblings().find('input[name="addeditem[]"]').val();
	$productid=$('#postid').val();
	if($('#original_post_status').val() !=productL10n.publish){
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcueremove_item',
				'itemid':$itemid,
				'productid':$productid
			},
			success: function(data){
				if(data.msg == 'success'){
					$('#rowitem-'+$itemid).remove();
					rename_itemtablerows();
					itemtablealternate();
					if(!($('#itemtable').find('tr').length)){
						$('#itemtable').hide();
					}
				}else{
					alert(productL10n.errormsg);
				}
			}
	});
	}else{
		$('#rowitem-'+$itemid).remove();
		rename_itemtablerows();
		itemtablealternate();
	}
});
$(document).on('change','#productcurrency',function(){
$productcurrency=$(this).val();
if($productcurrency == -1){
$('#customCurrency').show();
}else{
$('#customCurrency').hide();
}
});
$(document).on('click','#publish',function(e){
	e.preventDefault();
	$status=0;
	$producttitle=$('#title').val();
	if(($producttitle=== 'undefined')|| ($.trim($producttitle).length ===0)){
		$('#messagesubmit').html('<p>'+productL10n.addproducttitle+'</p>');$status=1;	
	}
	if($('#itemtable').find('.addeditemrow').length ==0 && $status==0){
		$('#messagesubmit').html('<p>'+productL10n.additemmsg+'</p>');$status=1;
	}
	if($status==1){$('#messagesubmit').show();return false;}
	$('#quizax').submit();
});
});