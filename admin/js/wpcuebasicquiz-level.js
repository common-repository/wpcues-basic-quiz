jQuery(document).ready(function($){
$.ajax({
            type: 'POST',
            dataType: 'json',
			url:ajaxurl,
            data: { 
                'action': 'addlevel_action', //calls wp_ajax_nopriv_ajaxlogin
				},
			success: function(data){
			msg=data.msg;
			finmsg="<a href='"+msg+"' class='add-new-h2 newer'>"+levelL10n.addnew+"</a>"
			$('.wrap').find('h2').append(finmsg)

}
});


});