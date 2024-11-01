<?php
global $wpdb,$wp;
$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
if(isset($_GET['tab'])){$activetab=$_GET['tab']-1;}else{$activetab=0;}
if(isset($_GET['paged'])){$paged=$_GET['paged'];}else{$paged=1;}
if(isset($_GET['action'])){$action=$_GET['action'];}
?>
<div class="wrap">
<?php
if(!empty($action) && ($action=='trashed')){
	$instanceid=$_GET['instance'];
	$msg='<div id="message" class="updated"><p>Deleted Instance id '.$instanceid.'</p></div>';
	echo $msg;
}
?>
<h2><?php _e('Statistics','wpcues-basic-quiz');?></h2>
<div id="tabs" class="quizstattab">
		<ul class="outertabs">
            <li><a href="#tabs-1"><?php _e('Logs','wpcues-basic-quiz');?></a></li>
			<li <?php if($activetab != 1){echo ' style="display:none;"';}?>><a href="#tabs-2"><?php _e('Detailed Report','wpcues-basic-quiz');?></a></li>
        </ul>
		<div id='tabs-1'>
			<div id="bulkactionstool">
			<div class="alignleft actions bulkactions">
					<label for='bulk-action-selector-top' class='screen-reader-text'><?php _e('Select bulk action','wpcues-basic-quiz'); ?></label><select name='action' id='bulk-action-selector-top'>
					<option value='-1' selected='selected'><?php _e('Bulk Actions','wpcues-basic-quiz'); ?></option>
					<option value='delete' class="hide-if-no-js"><?php _e('Delete','wpcues-basic-quiz'); ?></option>
				</select>
				<?php
					$deletebulkurl=add_query_arg(array('action'=>'delete','tab'=>1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
				?>
				<input type="hidden" name="deletebulkurl" value="<?php echo $deletebulkurl; ?>">
				<input type="submit" name="doaction" id="doaction" class="button action" value="Apply"  />
			</div>
			</div>
			<div id="logtablestat">
			<?php		
			$countrows=$wpdb->get_results("select sql_calc_found_rows instanceid from $table_name order by endtime desc",ARRAY_A);
			$count = $wpdb->get_var('SELECT FOUND_ROWS()');
			$i=($paged-1)*5;$j=$paged*5;
			$statlogs=$wpdb->get_results("select sql_calc_found_rows instanceid,userid,quizid,status,starttime,endtime from $table_name order by endtime desc LIMIT $i,$j",ARRAY_A);	
		if(!(empty($statlogs))){
			$pagenum=ceil($count/5);
			if($pagenum > 1){
			$firstpage=add_query_arg(array('tab'=>1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
			if($paged>1){$prevpage=add_query_arg(array('tab'=>1,'paged'=>$paged-1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			else{$prevpage=add_query_arg(array('tab'=>1,'page'=>$paged),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			if($paged==$pagenum){$nextpage=add_query_arg(array('tab'=>1,'paged'=>$paged),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			else{$nextpage=add_query_arg(array('tab'=>1,'paged'=>$paged+1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));}
			$lastpage=add_query_arg(array('tab'=>1,'paged'=>$pagenum),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
			echo '<div class="tablenav top">';
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">'.$count.__('items','wpcues-basic-quiz').'</span>';
			echo '<span class="pagination-links"><a class="first-page';if($paged==1){echo ' disabled';}echo '" title="Go to the first page" href="'.$firstpage.'">&laquo;</a>';
			echo '<a class="prev-page';if($paged==1){echo ' disabled';}echo '" title="Go to the previous page" href="'.$prevpage.'">&lsaquo;</a>';
			echo '<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Select Page</label><input class="current-page" id="current-page-selector" title="Current page" type="text" name="paged" value="'.$paged.'" size="1" /> of <span class="total-pages">'.$pagenum.'</span></span>';
			echo '<a class="next-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the next page"	href="'.$nextpage.'">&rsaquo;</a>';
			echo '<a class="last-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the last page" href="'.$lastpage.'">&raquo;</a></span>';
			echo '</div></div>';
			}
			echo '<table class="wp-list-table widefat fixed posts">';
			echo '<thead><tr><th scope="col" class="manage-column column-cb check-column"  style=""><label class="screen-reader-text" for="cb-select-all-2">'.__('Select All','wpcues-basic-quiz').'</label><input id="cb-select-all-2" class="cb-select" type="checkbox" /></th><th scope="col" class="manage-column">'.__('Quiz','wpcues-basic-quiz').'</th><th  scope="col" class="manage-column">'.__('User Id','wpcues-basic-quiz').'</th><th  scope="col" class="manage-column">'.__('Status','wpcues-basic-quiz').'</th><th scope="col" class="manage-columng">'.__('Started on','wpcues-basic-quiz').'</th><th scope="col" class="manage-columng">'.__('Completed on','wpcues-basic-quiz').'</th></tr></thead>';
			echo '<tfoot><tr><th scope="col" class="manage-column column-cb check-column"  style=""><label class="screen-reader-text" for="cb-select-all-2">'.__('Select All','wpcues-basic-quiz').'</label><input id="cb-select-all-2" class="cb-select" type="checkbox" /></th><th scope="col" class="manage-column">'.__('Quiz','wpcues-basic-quiz').'</th><th  scope="col" class="manage-column">'.__('User Id','wpcues-basic-quiz').'</th><th  scope="col" class="manage-column">'.__('Status','wpcues-basic-quiz').'</th><th scope="col" class="manage-columng">'.__('Started on','wpcues-basic-quiz').'</th><th scope="col" class="manage-columng">'.__('Completed on','wpcues-basic-quiz').'</th></tr></tfoot>';
			echo '<tbody>';
			$userids=array();$quizids=array();$i=0;
			foreach($statlogs as $statlog){
				$userids[$i]=$statlog['userid'];
				$quizids[$i]=$statlog['quizid'];$i++;
			}
			$args = array('include'=>$userids,'number'=>25,'offset' => 0);
			$user_query=new WP_User_Query($args);
			$userdesc=array();
			if ( ! empty( $user_query->results ) ) {
					foreach ( $user_query->results as $user ) {
						$userdesc['i'.$user->ID]=$user->display_name;
					}
			}
			$args = array('post__in'=>$quizids,'post_type'=>array('wpcuebasicquiz'),'orderby'=>'post__in','posts_per_page' => -1);
			$quizquery = get_posts($args);
			$quizdesc=array();
			foreach($quizquery as $quiz){
				
				$quizdesc['i'.$quiz->ID]=$quiz->post_title;
			}
			$i=0;
			foreach($statlogs as $statlog){
				$viewurl=add_query_arg(array('instance'=>$statlog['instanceid'],'action'=>'view','tab'=>2),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
				$deleteurl=add_query_arg(array('instance'=>$statlog['instanceid'],'action'=>'delete','trashed'=>1,'tab'=>1),admin_url('edit.php?post_type=wpcuebasicquiz&page=wpcuequizstatistics'));
				echo '<tr ';
				if(!($i%2)){echo 'class="alternate"';}
				if($statlog['status']==1){$statusdesc='Completed';}else{$statusdesc='Incomplete';}
				echo '><th scope="row" class="check-column">
								<label class="screen-reader-text" for="cb-select-'.$statlog['instanceid'].'">Select truth</label>
				<input class="cb-select" id="cb-select-'.$statlog['instanceid'].'" type="checkbox" name="post[]" value="'.$statlog['instanceid'].'" />
				<div class="locked-indicator"></div><td><a href="'.$viewurl.'">'.$quizdesc['i'.$statlog['quizid']].'</a><div class="row-actions"><span class="view"><a href="'.$viewurl.'">';
				_e('View','wpcues-basic-quiz');echo '</a>|</span><span class="delete"><a href="'.$deleteurl.'">';
				_e('Delete','wpcues-basic-quiz');echo '</a></span></div></td><td>'.$userdesc['i'.$statlog['userid']].'</td><td>'.$statusdesc.'</td><td>'.$statlog['starttime'].'</td><td>'.$statlog['endtime'].'</td></tr>';
				$i++;
			}
			echo '</tbody></table>';
			if($pagenum>1){
				
			echo '<div class="tablenav bottom">';
			echo '<div class="tablenav-pages">';
			echo '<span class="displaying-num">'.$pagenum.' items</span>';
			echo '<span class="pagination-links"><a class="first-page';if($paged==1){echo ' disabled';}echo '" title="Go to the first page" href="'.$firstpage.'">&laquo;</a>';
			echo '<a class="prev-page';if($paged==1){echo ' disabled';}echo '" title="Go to the previous page" href="'.$prevpage.'">&lsaquo;</a>';
			echo '<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Select Page</label><input class="current-page" id="current-page-selector" title="Current page" type="text" name="paged" value="'.$paged.'" size="1" /> of <span class="total-pages">'.$pagenum.'</span></span>';
			echo '<a class="next-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the next page"	href="'.$nextpage.'">&rsaquo;</a>';
			echo '<a class="last-page';if($paged==$pagenum){echo ' disabled';}echo '" title="Go to the last page" href="'.$lastpage.'">&raquo;</a></span>';
			echo '</div></div>';
			}
		}
		
		?>
		</div>
		</div>
		<div id='tabs-2'>
		<div id="detailedreportstat">
		<?php
			if($activetab==1){
			global $wpdb;
			$trial=array();
			$WpCueBasicQuiz=new WpCueQuiz_Admin(new WpCueQuiz_Config($trial));
			$instanceid=$_GET['instance'];
			$table_name=$wpdb->prefix.'wpcuequiz_quizstat';
			$table_name4=$wpdb->prefix.'wpcuequiz_quizinfo';
			$quizstat=$wpdb->get_row($wpdb->prepare("select quizid,UNIX_TIMESTAMP(endtime) as endtime,status from $table_name where instanceid=%d",$instanceid),ARRAY_A);
			if(isset($_GET['quiz'])){$quizid=$_GET['quiz'];}else{$quizid=$quizstat['quizid'];}
			$entitystat=$wpdb->get_results($wpdb->prepare("SELECT entityid,questionchange,UNIX_TIMESTAMP(questionchangedate) as questionchangedate from $table_name4 where quizid=%d",$quizid),OBJECT_K);
			if(isset($_GET['quiz'])){$quizid=$_GET['quiz'];}else{$quizid=$quizstat['quizid'];}
			if(isset($_GET['user'])){$userid=$_GET['user'];}
			$displaysetting=get_post_meta($quizid,'displaysetting',true);
			if(!empty($displaysetting['showquestnumber'])){$showquestnumber=$displaysetting['showquestnumber'];}else{$showquestnumber=0;}
			if(!empty($displaysetting['showquestsymbol'])){$showquestsymbol=$displaysetting['showquestsymbol'];}else{$showquestsymbol=0;}
			$wpprocuesetting=get_option('wpcuequiz_setting');
			if(!empty($wpprocuesetting['text']['questionsymbol'])){$questionsymbol=$wpprocuesetting['text']['questionsymbol'];}else{$questionsymbol='';}
			$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$entities=$wpdb->get_results($wpdb->prepare("SELECT entityid,answer,reply,status from $table_name where instanceid=%d order by id asc",$instanceid),OBJECT_K);
			$i=1;$entityids=array_keys($entities);
			if(empty($entityids)){
				$entityids=$WpCueBasicQuiz->entityids($quizid);
			}
			$args = array( 'post__in'=>$entityids,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
			$entityquery = new WP_Query($args);$report='<div class="quizreport clearfix">';
			while ($entityquery->have_posts()){
				$entityquery->the_post();
				$entitypost=$entityquery->post;
				$entityid=$entitypost->ID;
				if($entitypost->post_type=='wpcuebasicquestion'){
				$entitymeta=unserialize($entitypost->post_content);
					if(!empty($entities[$entityid]) && ((empty($entitystat[$entityid]->questionchange)) || ($quizstat['endtime'] > $entitystat[$entityid]->questionchangedate))){
						$answer=$entities[$entityid]->answer;
						$reply=$entities[$entityid]->reply;
						$status=$entities[$entityid]->status;
					}else{
						switch($entitymeta['t']){
							case 1:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 2:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 3:
								$answerar['la']=maybe_unserialize($entitymeta['la']['id']);
								$answerar['ra']=maybe_unserialize($entitymeta['ra']['id']);
								$leftcount=count($questmeta['la']['id']);$rightcount=count($questmeta['ra']['id']);
								if($leftcount <= $rightcount){
									$matchcount=$leftcount;$column='rightcolumn';
								}else{
									$matchcount=$rightcount;$column='leftcolumn';
								}
								$answerar['column']=$column;
								$answerar['count']=$matchcount;
								$answer=serialize($answerar);
								break;
							case 4:
								$answer=serialize($entitymeta['a']['id']);
								break;
							case 5:
								if(!(empty($entitymeta['c']))){$answer=serialize($entitymeta['c']);}else{$answer='';}
								break;
							case 6:
								
								$answer=serialize(array(1,0));
								break;
						}
						$reply='';$status=4;
					}
					$report.=$WpCueBasicQuiz->wpcue_report($answer,$reply,$status,$entitymeta,1,$i,0,$showquestsymbol,$questionsymbol,$showquestnumber);
					$i++;
				}
			}
			echo $report.'</div>';
			}
		?>
		</div>
		</div>
	</div>
<?php

?>
<script>
jQuery(document).ready(function($){
var activetab=<?php echo $activetab; ?>;
$( "#tabs" ).tabs({active: activetab});
$("#quiztabs").tabs(<?php if(isset($activelog) && ($activelog !=0)){echo '{active : 2}';}?>);
$("#usertabs").tabs(<?php if(isset($activelog) && ($activelog !=0)){echo '{active : 1}';}?>);
$(document).on('click','.cb-select',function(e){
	if($(this).closest('thead').length || $(this).closest('tfoot').length){
		if($(this).prop('checked')){$('.cb-select').each(function(){$(this).prop('checked',true);});}else{
			$('.cb-select').each(function(){$(this).prop('checked',false);});	
		}
	}
});
$(document).on('click','#doaction',function(){
var bulkaction=$('#bulk-action-selector-top').val();
if(bulkaction==-1){alert('First select any bulk action');return false;}
var instance=[];var count=0;
$('input[name="post[]"]').each(function(){if($(this).prop('checked')){count++;instance.push($(this).val());}});
if(count==0){alert('First Select Entries to be deleted');return false;}
var instanceids=instance.join(",");
console.log(instanceids);
var deleteurl=$('input[name="deletebulkurl"]').val();
deleteurl=deleteurl+'&instance='+instanceids+'&trashed='+count;
window.location.href=deleteurl;	
});
});
</script>
<style>
#bulkactionstool{margin:1em 1em;padding-bottom:2em;}
</style>