jQuery(document).ready(function($){
function questtablealternate(){
$('#questiontable >tbody > tr:odd').css("background-color","#eee");
$('#questiontable > tbody > tr:even').css("background-color","#fff");
$('.secquestadded > tbody > tr:even').css("background-color","#fff");
$('.secquestadded > tbody > tr:odd').css("background-color","#eee");
}
questtablealternate();
(function ($){
    $.fn.extend({ 
        addTemporaryClass: function (className, duration) {
            var elements = this;
            setTimeout(function() {
                elements.removeClass(className);
            }, duration);

            return this.each(function() {
                $(this).addClass(className);
            });
        }
    });})(jQuery);
function loginrequired(){
if($('#loginrequired').is(':checked')){
	$('.tempmsg').css('display','none');
	$('.logindep').show();
	}
}
loginrequired();
$(document).on('change','#sectionvalues',function(){
	if(($('#original_post_status').val()==createquizL10n.publish)&&($('input[name="savequestion_status"]').val()==1)){$('#questionchanged').val($questionchanged);}
});
$(document).on("click","#loginrequired",function(){
if(this.checked){
	$('.logindep').show();
	$('.intermediatescreen').show();
	$('#quizintermediate').addClass('requiredvar');
	$('.completedscreen').show();
	$('#quizcomplete').addClass('requiredvar');
}else{
	$('.logindep').hide();
	$('.intermediatescreen').hide();
	$('#quizintermediate').removeClass('requiredvar');
	$('.completedscreen').hide();
	$('#quizcomplete').removeClass('requiredvar');
}
});
$('#category-add').find(':input').not(':button').each(function(){$(this).prop('disabled',true);});

$("#tabs").tabs({heightStyle: "content"},{active:0},
	{create:function(event,ui){
		$('#tabs-1').siblings().each(function(){
			var panelindex=$(this).index();
			$(this).find(':input').not(':button').each(function(i, elem){
				var input = $(elem);
				if(panelindex==3){
					input.data('disableState',1);
				}else if(panelindex !==1){if(input.prop('disabled')){input.data('disableState',1);}else{input.data('disableState',0);}}
				input.prop('disabled',true);
			});
		});
		$('#postdivrich').tabs({heightStyle: "content"});
		$('#emailnotificationtab').tabs();
		$('#dialogtabs').tabs();
		$('#tabs-1').find(':input').not(':button').each(function(i, elem){
				var input = $(elem);
				if(!(input.prop('disabled'))){input.data('disableState',0);}
				else{input.data('disableState',1);}
			});
		}
	},{activate:function(event,ui){
		ui.oldPanel.find(':input').not(':button').each(function(i, elem){
				var input = $(elem);
				if(!(input.prop('disabled'))){input.data('disableState',0);input.prop('disabled',true);}
				else{input.data('disableState',1);}
			});
		ui.newPanel.find(':input').not(':button').each(function(i, elem){
				var input = $(elem);
				if(!(input.data('disableState'))){$(this).prop('disabled',false);}
		});
	}}
);
$('#tabs-1').tabs();
$("#progressscreen").tabs();
$('#emailnotificationtab').tabs();
$('#quiz_options_section').tabs();
$('#settab-4').tabs();

$(document).on("click","#publish,#save",function (event){
event.preventDefault();
tinyMCE.triggerSave();
var $quizname=$('input[name=quizname]').val();
if((typeof $quizname === 'undefined') || ($.trim($quizname).length ===0)){alert(createquizL10n.quiztit);return false;}
if(!($('#quizeditor').is(':hidden'))){alert(createquizL10n.savequest);return false;}
if(!($('#gradeeditor').is(':hidden'))){alert(createquizL10n.savegrade);return false;}
$('form#quizax').find(':input.requiredvar').each(function(i, elem){
	var input = $(elem);
	if(input.prop('disabled')){
		input.data('initialState',1);
		input.prop('disabled',false);
	}else{input.data('initialState',0);}
});
$butid=$(this).attr('id');
if($butid=='publish'){
$('#publishing-action').children('.spinner').addClass('is-active ');
$('#publishing-action').children('.spinner').show();
}else{
$('input[name="poststatustype"]').val(0);	
$('#savedraft-action').children('.spinner').addClass('is-active ');
$('#savedraft-action').children('.spinner').show();	
}
var myformdata=$('form#quizax').serialize();
$.ajax({
            type:'POST',
            dataType:'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuequizsavequiz_action', 
                'myformdata':myformdata
				},
			success: function (data){
			$('form#quizax').find(':input.requiredvar').each(function(i, elem){
				var input = $(elem);
				if(input.data('disableState')){$(this).prop('disabled',true);}
			});
			$('input[name="poststatustype"]').val(1);	
			if($butid=='save'){
				$('#savedraft-action').children('.spinner').removeClass('is-active');
				$('#savedraft-action').children('.spinner').hide();
				$('.autodraftlockmsg').hide();
			}else{
				$('#publishing-action').children('.spinner').removeClass('is-active');
				$('#publishing-action').children('.spinner').hide();
			}
			if(data.msg=='saved'){
				$('input[name="questionschanges"]').val(0);
				if($butid=='save'){
					if($('#original_post_status').val()==createquizL10n.autodraft){
						$("#tabs").tabs("option","disabled",[]);
						$('#embedquiz').children('a').removeClass('disabled');
					}
					$('#original_post_status').val('draft');
					$('#message').html('<p>'+createquizL10n.quizsaved+'</p>');
					$('#message').show();
				}else{
					$('#publish').val('Update');
					if($('input[name="tax_input[wpcuebasicquiz][]"]:checked').length===0){
						$('input[name="tax_input[wpcuebasicquiz][]"][value="1"]').attr('checked', 'checked');
					}
				if($('#original_publish').val() ==createquizL10n.update){
					$('#message').html('<p>'+createquizL10n.quizupdated+'</p>');
					var $gradegroupid=data.gradegroupid;
					$('input[name=gradegroupid]').val($gradegroupid);
					$('input[name=inheritgradegroupid]').val($gradegroupid);
					$('#questiontable').find('tr').each(function(){
						var newinstanceid=$(this).find('input[name="entityid[]"]').val();
						$(this).find('input[name="instanceid[]"]').val(newinstanceid);
					});
				}else{
					$('#original_publish').val('Update');
					$('#original_post_status').val('publish');
					$('#message').html('<p>'+createquizL10n.quizpublished+'</p>');
				}
				$('#message').show();
				}
				if($('#questiontable tr').length){
					$('#questiontable tr').each(function(){
						$(this).find('input[name="instanceid[]"]').val($(this).find('input[name="entityid[]"]').val());
					});
				}
				
			}else{alert(createquizL10n.savepost);}	
			$('form#quizax').find(':input.requiredvar').each(function(i, elem){
				var input = $(elem);
				if(input.data('initialState')===1){
					input.prop('disabled',true);
				}
			});
			}
});
});
$(document).on('click','.gradegroupremove',function(e){
e.preventDefault();
var $target=$(this).closest('tr');
var $gradegroupid=$('input[name=inheritgradegroupid]').val();
$.ajax({
    type: 'POST',
    dataType: 'json',
	url:ajaxurl,
    data: { 
        'action': 'wpcuequizremovegradegroup_action',
		'quizid':$('#quizid').val(),
		'gradegroupid':$gradegroupid},
	success: function(data){
		if(data.msg=='success'){
			$('input[name=inheritgradegroupid]').val(0);
			if(data.quizstatus===0){$('input[name=gradegroupid]').val(0);}
			$('#add_grade_button').removeClass('disabled');
			$target.empty();
		}else{alert(createquizL10n.errormsg);}
	}
});
});
$(document).on('click','.gradegroupedit',function(e){
e.preventDefault();
var $gradegroupid=$('input[name=inheritgradegroupid]').val();
$.ajax({
    type: 'POST',
    dataType: 'html',
	url:ajaxurl,
    data: { 
        'action': 'wpcuequizeditgradegroup_action',
		'gradegroupid':$gradegroupid,},
	success: function(response){
		$('#gradeeditor').show();
		$('#gradeeditor').append(response);
		$('#gradeeditor').find('textarea').each(function(){
			var id=$(this).attr('id');
			$('#gradeeditor').find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
		});
		$('#gradeeditortabs').tabs();	
	}
});
});
$(document).on('click','#add_grade_button',function(e){
if($(this).hasClass('disabled')){
if($('#original_post_status').val()==createquizL10n.autodraft){alert(createquizL10n.draftmsg);}
	else{alert(createquizL10n.gradeadded);}
	return;
}
if(!($('#gradeeditor').is(':hidden'))){alert(createquizL10n.gradeeditor);return;}
	$autodraftsavestatus=$('#original_post_status').val();
	if($autodraftsavestatus == createquizL10n.autodraft){alert(createquizL10n.quiztitle);return false;}
$.ajax({
    type: 'POST',
    dataType: 'html',
	url:ajaxurl,
    data: { 'action': 'wpcuequizaddgradegroup_action',},
	success: function(response){
		$('#gradeeditor').show();
		$('#gradeeditor').append(response);
		$('#gradeeditor').find('textarea').each(function(){
			var id=$(this).attr('id');
			$('#gradeeditor').find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
		});
		$('#gradeeditortabs').tabs();
	}
});
});
$(document).on("click",".add_grade_button",function (){
$gradebase=$('#gradebasis').val();
var $tablist=$('#gradeeditortabs').children('ul');
var $lastanchor=$tablist.children('li.activetab:last').children('a').attr('href');
var $index=parseInt($lastanchor.split('-')[1],10)+1;
var $newitemindex=parseInt($tablist.find('li.activetab').length,10)+1;
var $lastanchorhref=$lastanchor.split('-')[0];
$.ajax({
    type: 'POST',
    dataType: 'html',
	url:ajaxurl,
    data: { 'action': 'wpcuequizaddgrade_action','index':$index,'gradebase':$gradebase},
	success: function(response){
		$tablist.append('<li class="activetab"><a href="'+$lastanchorhref+'-'+$index+'">'+$newitemindex+'</a></li>');
		$('#gradeeditortabs').append(response);
		$newdivid=$lastanchorhref.substring(1);
		$newtabid=$tablist.find('li.activetab:last').index();
		$('#'+$newdivid+'-'+$index).find('textarea').each(function(){
			var id=$(this).attr('id');
			$('#'+$newdivid+'-'+$index).find('a[data-editor="'+id+'"]').bind('click',function(){window.wpActiveEditor=id;});
			tinymce.execCommand('mceAddEditor',true,id);
			quicktags({id:id});
			QTags._buttonsInit();
		});
		$('#gradeeditortabs').tabs('refresh');
		$('#gradeeditortabs').tabs({active :$newtabid});
		if($('.gradeclosetools').is(':hidden')){$('.gradeclosetools').show();}
	}
});

});
$(document).on("click",".grade_close_button",function(){
var $activeanchorhref=$(this).parent().parent().attr('id');
var $activetab=$('#gradeeditortabs').children('ul').children('li').find('a[href="#'+$activeanchorhref+'"]').parent('li');
var $firstactive=$('#gradeeditortabs').children('ul').children('li.activetab:visible:first').index();
var $lastactive=$('#gradeeditortabs').children('ul').children('li.activetab:visible:last').index();
var $currentindex=$activetab.index();var $activetabindex;
if($currentindex==$firstactive){$activetabindex=$activetab.next('li.activetab:visible').index();}
else if($currentindex==$lastactive){$activetabindex=$activetab.prev('li.activetab:visible').index();}
else{$activetabindex=$activetab.next('li.activetab:visible').index();}
$('#'+$activetab.children('a').attr('href').substring(1)).find(':input').not(':button').each(function(i,elem){
var input=$(elem);
input.data('disableState',1);
input.prop('disabled',true);
});
$activetab.hide();
$activetab.nextAll('li.activetab').each(function(){
var $activetabanchor=$(this).children('a');
var $activetabanchortext=parseInt($(this).text(),10)-1;
$activetabanchor.text($activetabanchortext);
});
$activetab.removeClass('activetab');
$('#gradeeditortabs').tabs( "option", "active",$activetabindex);
});
$(document).on("click",".cancel_gradegroup_button",function(){
$('#gradeeditor').find('textarea').each(function(){
	tinymce.execCommand('mceRemoveEditor',false,$(this).attr('id'));
});
$('#gradeeditor').empty().hide();
});
$(document).on("click",".save_gradegroup_button",function(){
	tinyMCE.triggerSave();
	var $intialgradegroupid=$('input[name=gradegroupid]').val();
	var $posttitle=$('input[name="gradegrouptitle"]').val();
	if((typeof $posttitle === 'undefined') || ($.trim($posttitle).length===0)){alert(createquizL10n.gradename);return false;}
	var $gradebasis=$('#gradebasis option:selected').val();
	var $i=0;var $error=0;var $j;
	$('#gradeeditortabs').find('li.activetab:visible').each(function(){
		var $activetabid=$(this).children('a').attr('href').substring(1);
		$('#'+$activetabid).find('textarea').each(function(){
		var $textid=$(this).attr('id');
		var $gradeid=$textid.split('-')[1];
		var $gradetitle=$('#gradetitle-'+$gradeid).val();$j=$i+1;
		if((typeof $gradetitle === 'undefined') || ($.trim($gradetitle).length===0)){$error=1;alert(createquizL10n.gradetitle+$j);return false;}
		var $gradedesc=$('#grade-'+$gradeid).val();
		if((typeof $gradedesc === 'undefined') || ($.trim($gradedesc).length===0)){$error=1;alert(createquizL10n.gradedesc+$j);return false;}
		var $gradebasefrom=$('input[name=gradebasefrom-'+$gradeid+']').val();
		if((typeof $gradebasefrom === 'undefined') || ($.trim($gradebasefrom).length===0)){$error=1;alert(createquizL10n.gradebasis+$j);return false;}
		var $gradebaseto=$('input[name=gradebaseto-'+$gradeid+']').val();
		if((typeof $gradebaseto === 'undefined') || ($.trim($gradebaseto).length===0)){$error=1;alert(createquizL10n.gradebasis+$j);return false;}
		$i++;
	});});
	if($error == 1){return;}
	var myformdata=$('form#quizax').serialize();
	$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'wpcuequizsavegradegroup_action',
				'myformdata':myformdata},
			success: function(data){
				if(data.msg == 'success'){
					var $gradegroupid=data.gradegroupid;var $gradegrouptitle=data.gradegrouptitle;
					var $quizstatus=data.quizstatus;
					if($quizstatus===1){
						$intialgradegroupid=parseInt($intialgradegroupid,10);
						if($intialgradegroupid === 0){
							$('input[name=gradegroupid]').val($gradegroupid);
							$('input[name=inheritgradegroupid]').val($gradegroupid);
						}else{$('input[name=inheritgradegroupid]').val($gradegroupid);}
					}else{
						$('input[name=gradegroupid]').val($gradegroupid);
						$('input[name=inheritgradegroupid]').val($gradegroupid);}
					var $htmlcontent='<td>'+$gradegrouptitle+'<div class="row-actions"><span><a href="#" class="gradegroupedit">'+createquizL10n.edit+'</a> | </span><span><a href="#" class="gradegroupremove">'+createquizL10n.remove+'</a></div></td>';
					$('.gradegroupaddedrow').html($htmlcontent);
					$('.gradegroupadded').show();
					$('#add_grade_button').addClass('disabled');
				}else{alert(createquizL10n.errormsg);}
				$('#gradeeditor').find('textarea').each(function(){
					tinymce.execCommand('mceRemoveEditor',false,$(this).attr('id'));
				});
				$('#gradeeditor').empty().hide();
			}
		});

});
$(document).on('focus','#gradebasis',function(){
$(this).data('previousval',$(this).val());
});
$(document).on('change','#gradebasis',function(){
$gradebasis=parseInt($(this).val(),10);
$previousval=$(this).data('previousval');
if($gradebasis !== 0){
	if($gradebasis===1){
		$('.gradebase').html(createquizL10n.points);
	}else{$('.gradebase').html('%'+createquizL10n.corans);}
	$(this).data('previousval',$gradebasis);
}else{$('#gradebasis option[value="'+$previousval+'"]').attr('selected','selected');return false;}

});
$(document).on('click','#category-add-toggle',function(e){
e.preventDefault();
if($('#category-add').is(':hidden')){
$('#category-add').find(':input').not(':button').each(function(){$(this).prop('disabled',false);});
}else{
$('#category-add').find(':input').not(':button').each(function(){$(this).prop('disabled',true);});
}
$('#category-add').toggle();
});
$(document).on('click','#category-add-submit',function(e){
e.preventDefault();
var $quizcategory=$('#newcategory').val();
var $parentcategory=$('#parent_category').val();
var $quizcatnonce=$('#_ajax_nonce-add-category').val();
$.ajax({
	type:'POST',
	dataType:'json',
	url:ajaxurl,
	data:{'action':'wpcuequizsavequizcategory_action',
		'quizcategory':$quizcategory,
		'parentcategory':$parentcategory,
		'quizcatnonce':$quizcatnonce},
	success:function(data){
		if(data.msg === 'success'){
			var $returnval=data.returnval;
			$('#category-add').hide();
			if($parentcategory == -1){
				$('#categorychecklist').append($returnval);
			}else{
				var $target=$('#in-wpcuebasicquizcat-'+$parentcategory).parent().siblings('.children');
				if($target.length >0 ){
					$target.append($returnval);
				}else{
					$('#categorychecklist').append($returnval);
				}
			}
			$('#newcategory').val('New Category Name');
			$('#parent_category').prop('selectedIndex',0);
			$('#category-add').find(':input').not(':button').each(function(){$(this).prop('disabled',true);});
		}else{alert(createquizL10n.catadd);}
	}
	});
});
$("#newcategory").focus(function() { $(this).addClass("active"); if($(this).attr("value") === createquizL10n.newcat) $(this).attr("value", ""); });
$("#newcategory").blur(function() { $(this).removeClass("active"); if($(this).attr("value") === '') $(this).attr("value", createquizL10n.newcat); });

$('.procontent').attr('title', 'This is WpCues Pro Quiz feature, the premium plugin of which this plugin is light version.');
$('.procontent').tooltip({tooltipClass:'withoutcolor'});
});
