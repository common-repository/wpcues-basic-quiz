<?php
if(!class_exists('WpCueBasicQuestionPost'))
{
    /**
     * WpCueBasicQuestion class
     */
    class WpCueBasicQuestionPost extends WpCueQuiz_Base
    {
        const POST_TYPE = "wpcuebasicquestion";
        public $questionid;
		/**
		* hook into WP's init action hook
		*/
		protected function init()
		{
			// Initialize Post Type
			$this->add_action('init','create_post_type');
			add_action( 'wp_ajax_wpcuequizsavequestion_action', array(&$this,'save_question'));
			add_action('wp_ajax_wpcuequizeditquestion_action',array(&$this,'quiz_form'));
			add_action('wp_ajax_wpcuequizaddquestion_action',array(&$this,'quiz_form'));
			add_action('wp_ajax_wpcuequizaddinitialanswer_action',array(&$this,'addinitial_answer'));
			add_action('wp_ajax_wpcuequizaddsecondaryanswer_action',array(&$this,'addinitial_secondary'));
			add_action('wp_ajax_wpcuequizaddanswer_action',array(&$this,'add_answer'));
			add_action('wp_ajax_wpcuequizremovequestion_action',array(&$this,'ajaxremove_question'));
			add_action('wp_ajax_wpcuequizchangequestorder_action',array(&$this,'changeorder_question'));
			add_action('wp_ajax_wpcuequizchangeansorder_action',array(&$this,'changeorder_answer'));
			add_filter('get_edit_post_link',array(&$this,'edit_question_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type(){
			$labels = array(
			'name'               => _x( 'Questions', 'post type general name', 'wpcues-basic-quiz' ),
			'singular_name'      => _x( 'Question', 'post type singular name', 'wpcues-basic-quiz' ),
			'menu_name'          => _x( 'Questions', 'admin menu', 'wpcues-basic-quiz' ),
			'name_admin_bar'     => _x( 'Question', 'add new on admin bar', 'wpcues-basic-quiz' ),
			'add_new'            => _x( 'Add New', 'question', 'wpcues-basic-quiz' ),
			'add_new_item'       => __( 'Add New Question', 'wpcues-basic-quiz' ),
			'new_item'           => __( 'New Question', 'wpcues-basic-quiz' ),
			'edit_item'          => __( 'Edit Question', 'your-plugin-textidomain' ),
			'view_item'          => __( 'View Question', 'wpcues-basic-quiz' ),
			'all_items'          => __( 'All Questions', 'wpcues-basic-quiz' ),
			'search_items'       => __( 'Search Questions', 'wpcues-basic-quiz' ),
			'parent_item_colon'  => __( 'Parent Questions:', 'wpcues-basic-quiz' ),
			'not_found'          => __( 'No Questions found.', 'wpcues-basic-quiz' ),
			'not_found_in_trash' => __( 'No Questions found in Trash.', 'wpcues-basic-quiz' )
			);
		
		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'capability_type'    => 'post',
			'show_ui'=>false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'editor', 'author','excerpt')
		);
		register_post_type(self::POST_TYPE,$args);
		$labels = array(
			'name'              => _x( 'Question Categories', 'taxonomy general name','wpcues-basic-quiz' ),
			'singular_name'     => _x( 'Question Category', 'taxonomy singular name','wpcues-basic-quiz' ),
			'search_items'      => __( 'Search Question Categories','wpcues-basic-quiz' ),
			'all_items'         => __( 'All Question Categories','wpcues-basic-quiz' ),
			'parent_item'       => __( 'Parent Question Category','wpcues-basic-quiz' ),
			'parent_item_colon' => __( 'Parent Question Category:','wpcues-basic-quiz' ),
			'edit_item'         => __( 'Edit Question Category','wpcues-basic-quiz' ),
			'update_item'       => __( 'Update Question Category','wpcues-basic-quiz' ),
			'add_new_item'      => __( 'Add New Question Category','wpcues-basic-quiz' ),
			'new_item_name'     => __( 'New Question Category Name','wpcues-basic-quiz' ),
			'menu_name'         => __( 'Question Category','wpcues-basic-quiz' ),
		);

		$arges = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'public'=>false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => array( 'slug' => 'questioncategory' ),
		);

		register_taxonomy('wpcuebasicquestcat','wpcuebasicquestion',$arges);
		
		}
		
		/**
		* Set question id
		*/
		public function set_questionid(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			$this->questionid=$post->ID;
		}
		/**
		* Save the post
		*/
		public function save_post()
		{
			
		} // END public function save_post
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicquestion' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion';
		}
		public function edit_question_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicquestion'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequiznewquestion'. $action.$posting));
			}
			return $url;
		}
		
		/**
		* Ajax Handler to save Question
		*/
		public function save_question(){
			global $wpdb;
			ob_start();
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			if(!empty($_POST['entityorder'])){$entityorder=(float)$_POST['entityorder'];}else{$entityorder=0;}
			$questiontype=$output['origquestiontype'];
			$wpcuequestion=$this->get_questobject($questiontype);
			$jsonencoded_msg=$wpcuequestion->save_question($output,$entityorder);
			echo json_encode($jsonencoded_msg);
			echo ob_get_clean();
			die();
		}
		/**
		* Ajax handler to edit question
		*/
		public function quiz_form(){
			ob_start();
			if(!(empty($_POST['myformdata']))){
				if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
					$myformdata=stripslashes($_POST['myformdata']);
				}else{$myformdata=$_POST['myformdata'];}
				parse_str($myformdata,$output);
				if(!(empty($output['quizid']))){$quizid=$output['quizid'];}
				if(!(empty($output['sectionid']))){$sectionid=$output['sectionid'];}
				if(!(empty($output['questionchanged']))){$questionchanged=$output['questionchanged'];}
				$questionid=$output['entityid'][0];
				$instanceid=$output['instanceid'][0];
				$instance=get_post($instanceid);
				$questmeta=unserialize($instance->post_content);
			}else{
				$questmeta=array();$questionid=0;$sectionids=array();
				if(!empty($_POST['poststatus'])){$poststatus=$_POST['poststatus'];}
				if(!(empty($_POST['quizid']))){
					$quizid=$_POST['quizid'];
				}
				if(!(empty($_POST['sectionid']))){$sectionid=$_POST['sectionid'];}
				
			}
			if(!empty($_POST['sectionids'])){$sectionids=$_POST['sectionids'];}else{$sectionids=array();}
			if(empty($quizid)){$quizid=0;}if(empty($sectionid)){$sectionid=0;}
			if(empty($questionchanged)){$questionchanged=0;}
			if((empty($quizid)) && ((empty($sectionid)))){$butstatus=0;}else{$butstatus=1;}
			echo '<input type="hidden" name="curquestionid" value="'.$questionid.'">';
			if(empty($questmeta['t'])){$questiontype=0;}else{$questiontype=$questmeta['t'];}
			$wpcuequestion=$this->get_questobject($questiontype);
			$wpcuequestion->question_form($questmeta,$sectionids,$butstatus,$questionchanged);
			if(!($quizid)){
				echo '<input type="hidden" name="entityid[]" value="'.$questionid.'">';
				echo '<input type="hidden" name="instanceid[]" value="'.$questionid.'">';
			}
			echo ob_get_clean();
			die();
		}
		public function addinitial_answer(){
			ob_start();
			$questiontype=$_POST['questiontype'];
			$questmeta=array();
			$wpcuequestion=$this->get_questobject($questiontype);
			$wpcuequestion->answereditor($questiontype,$questmeta);
			echo ob_get_clean();
			die();
		}
		public function add_answer(){
			ob_start();
			$questiontype=$_POST['questiontype'];
			$tabid=$_POST['tabid'];
			$index=$_POST['index'];
			$wpcuequestion=$this->get_questobject($questiontype);
			$wpcuequestion->answer_form($index,$index,'',$tabid,0,0);
			echo ob_get_clean();
			die();
		}
		public function ajaxremove_question(){
			global $wpdb;$error=0;
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
				$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			parse_str($myformdata,$output);
			$entityid=$output['entityid'][0];
			$quizid=$output['quizid'];
			$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$resultid=$wpdb->query($wpdb->prepare("DELETE from $table_name  where quizid=%d and entityid=%d",$quizid,$entityid));
			if($resultid===false){$error=1;};
			if($error == 0){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			die();
		}
	public function changeorder_question(){
		global $wpdb;
		$questionid=$_POST['questionid'];
		$quizid=$_POST['quizid'];
		$newsection=$_POST['newsectionid'];$instanceid=$_POST['instanceid'];
		$newquestposition=$_POST['newquestpostion'];
		$publishstatus=$_POST['publishstatus'];
		$error=0;
		$question=get_post($instanceid);
		$questmeta=unserialize($question->post_content);
		$prevparent=$questmeta['s'];
		if($prevparent != $newsection){
			$questmeta['s']=$newsection;
			if($instanceid==$questionid){
				$instanceid=$wpdb->insert($wpdb->posts,array('post_title'=>$question->post_title,'post_content'=>serialize($questmeta),'post_status'=>'inherit','post_parent'=>$questionid),array('%s','%s','%s','%d'));
			}else{
				$instanceid=$wpdb->update($wpdb->posts,array('post_title'=>$question->post_title,'post_content'=>serialize($questmeta),'post_status'=>'publish'),array('ID'=>$instanceid),array('%s','%s','%s'),array('%d'));
			}
		}
		if(empty($publishstatus)){
			$resultid=$wpdb->update($wpdb->prefix.'wpcuequiz_quizinfo',array('entityorder'=>$newquestposition),array('quizid'=>$quizid,'entityid'=>$entityid),array('%s'),array('%d'));
		}
		echo json_encode(array('msg'=>'success','instanceid'=>$instanceid));
		die();
	}
	public static function getadded_questions($entityids,$quizid,$sectionstatus=false){
		if(!empty($entityids)){
			$entityids = esc_sql( $entityids );
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			if($sectionstatus){
				$entityorder=$wpdb->get_results($wpdb->prepare("select entityid,entityorder from $table_name where parentid=%d order by entityorder asc",$quizid),OBJECT_K);
			}else{
				$entityorder=$wpdb->get_results($wpdb->prepare("select entityid,entityorder from $table_name where quizid=%d order by entityorder asc",$quizid),OBJECT_K);
			}
			$secent=$wpdb->get_results($wpdb->prepare("select parentid,count(*) as counter from $table_name where quizid=%d and parentid != -1 group by parentid",$quizid),OBJECT_K);
			if($sectionstatus){
				WpCueBasicQuestionPost::addedquestions_display($entityids,$secent,$entityorder,$sectionstatus);
			}else{
				WpCueBasicQuestionPost::addedquestions_display($entityids,$secent,$entityorder);
			}
			
		}
	}
	
	public static function addedquestions_display($entityids,$secent,$entityorder,$sectionstatus=false){
		$args = array( 'post__in'=>$entityids,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
					$query1 = new WP_Query($args);
					$ansnum=0;$sectionstat=0;$lastquest=0;$sectionid=0;$rownum=1;$entnum=1;$secnum=1;$i=0;
					while ($query1->have_posts()){
					$query1->the_post();
					$quest=$query1->post;
					$questionid=$quest->ID;
						if($quest->post_type=='wpcuebasicsection')	{
							$sectionstat=1;
							$sectionid=$quest->ID;
							if(!(empty($secent)) && (!(empty($secent[$sectionid])))){$count=$secent[$sectionid]->counter;}else{$count=0;}
							$ent=$i+$count;
							?>
						<tr id='rowsec-<?php echo $sectionid; ?>' class='rowdet closed sectentity <?php if($count==0){echo 'secnoquest'; }else{echo 'secwithquest';} ?>'>
						<td class='row-number-wrapper secnum'>S. <?php echo $secnum;?></td>
						<td class='rowtitle'>
						<div class='rowshort'><div class='sectionname' style="float:left;"><p>
						<?php echo  $quest->post_title; ?></p></div><div class='questcounttext' style="float:right;">
						<?php if($count != 0){$text='Q.';$text.=$rownum;if($count >1 ){$text.='-Q.';$text.=($rownum+$count-1);}echo $text; }else{echo 'No Question'; } ?>
						</div></div><div class='rowfull'><div class="sectioncontent">
							<p class='sectitle'><?php echo  $quest->post_title; ?></p>
							<p class='secdesc'>
						<?php echo $quest->post_content; ?></p>
						<input type="hidden" name="entityid[]" value="<?php echo $sectionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="instanceid[]" value="<?php echo $sectionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="parentid[]" value="-1" disabled class="requiredvar">
						<input type="hidden" name="point[]" value="0" disabled class="requiredvar">
						<input type="hidden" name="category[]" value="0" disabled class="requiredvar">
						<input type="hidden" name="entityorder[]" value="<?php echo $entityorder[$sectionid]->entityorder; ?>" disabled class="requiredvar">
						<input type="hidden" name="questionchangedstat[]" value="0" disabled class="requiredvar">
						</div>
							<?php if(isset($ent)){if($i==$ent){$sectionstat=0;
								echo '</div>';
								echo "<div class='rowactions'><span><a href='#' class='sectionedit'>";
								_e('Edit Section','wpcues-basic-quiz');
								echo "</a> | </span><span><a href='#' class='sectionremove'>";
								_e('Remove Section','wpcues-basic-quiz');
								echo "</a> | </span><span><a href='#' class='sectiondelete'>";
								_e('Delete Section','wpcues-basic-quiz');echo "</a></span></div>";
								echo '</td><td class="handlerow"></td></tr>';}else{echo '<table class="secquestadded"><tbody>';}
							$entnum++;$secnum++;
							}}
						elseif($quest->post_type=='wpcuebasicquestion'){ $questionid=$quest->ID;?>
						<tr id='rowquest-<?php echo $questionid; ?>' class='rowdet closed questentity'>
						<td class='row-number-wrapper questnum'>Q. <?php echo $rownum; ?></td>
						<td class='rowtitle'>
						<div class='rowshort'><p><?php $questcontent=unserialize($quest->post_content);
						echo WpCueQuiz_Admin::summary($questcontent['desc'],100,true);?></p></div>
						<div class='rowfull'><p><?php echo $questcontent['desc']; ?></p>
						<input type="hidden" name="questiontype-<?php echo $questionid; ?>" value="<?php echo $questcontent['t']; ?>" disabled class="requiredvar">
						<input type="hidden" name="entityid[]" value="<?php echo $questionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="instanceid[]" value="<?php echo $questionid; ?>" disabled class="requiredvar">
						<input type="hidden" name="parentid[]" value="<?php echo $questcontent['s'];?>" disabled class="requiredvar">
						<?php if(in_array($questcontent['t'],array(2,7))){$point=$questcontent['totalpoint'];}else{$point=$questcontent['p'];}?>
						<input type="hidden" name="point[]" value="<?php echo $point;?>" disabled class="requiredvar">
						<input type="hidden" name="category[]" value="<?php echo $questcontent['qc'];?>" disabled class="requiredvar">
						<input type="hidden" name="entityorder[]" value="<?php echo $entityorder[$questionid]->entityorder; ?>" disabled class="requiredvar">
						<input type="hidden" name="questionchangedstat[]" value="0" disabled class="requiredvar">
							<?php
							if(in_array($questcontent['t'],array(1,2,3,4))){
							if($questcontent['t'] != 3){
								if(!(empty($questcontent['a']['id']))){
									
									echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
									foreach($questcontent['a']['id'] as $answerid){
										echo '<li>'.$questcontent['a'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'"  disabled class="requiredvar"></li>'; 
									}
									echo '</ol>';
								}
							}else{
								if(!(empty($questcontent['la']['id']))){
								echo '<h3>Left Column</h3>';
								echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
								foreach($questcontent['la']['id'] as $answerid){
									echo '<li class="answersort-'.$questionid.'">'.$questcontent['la'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'"  disabled class="requiredvar"></li>'; 
								}
								echo '</ol>';
								}
								if(!(empty($questcontent['ra']['id']))){
								echo '<h3>Right Column</h3>';
								echo '<ol class="createquestlist answersort-'.$questionid.'">'; 
								foreach($questcontent['ra']['id'] as $answerid){
									echo '<li class="answersort-'.$questionid.'">'.$questcontent['ra'][$answerid]['desc'].'<input type="hidden" name="finanswerid-'.$questionid.'[]" value="'.$answerid.'" disabled class="requiredvar"></li>'; 
								}
								echo '</ol>';
								}
								
							}
							}
							echo '</div><div class="questrowactions"><span><a href="#" class="questedit">';_e('Edit','wpcues-basic-quiz');echo '</a> | </span><span><a href="#" class="questremove">';_e('Remove','wpcues-basic-quiz');echo '</a>';
							if($sectionstatus){echo '</span></div>';
							}else{echo '| </span><span><a href="#"  class="changequestorder">';_e('Change Question Order','wpcues-basic-quiz');echo '</a> </span></div>';}
								echo "</td><td class='handlerow'></td></tr>";
							
							if($sectionstat != 0){
							if(isset($ent)){
								if($i==$ent){
									$sectionstat=0;
									echo '</tbody></table></div>';
									echo "<div class='rowactions'><span><a href='#' class='sectionedit'>";
									_e('Edit Section','wpcues-basic-quiz');echo "</a> | </span><span><a href='#' class='sectionremove'>";
									_e('Remove Section','wpcues-basic-quiz');echo "</a> | </span><span><a href='#' class='sectionremove'>";_e('Remove Section','wpcues-basic-quiz');echo "</a></span></div>";
									echo '</td><td class="handlerow"></td></tr>';
								}
							}}
							$rownum++;
						}
						$i++;
					}wp_reset_postdata();
				
		
	}
	private function get_questobject($questiontype){
			switch($questiontype){
				case 0: $wpcuequestion=new WpCueBasicQuestion();break;
				case 1: $wpcuequestion=new WpCueBasicSinglecor();break;
				case 2: $wpcuequestion=new WpCueBasicMultiplecor();break;
				case 3: $wpcuequestion=new WpCueBasicMatch();break;
				case 4: $wpcuequestion=new WpCueBasicSort();break;
				case 5: $wpcuequestion=new WpCueBasicFillgaps();break;
				case 6: $wpcuequestion=new WpCueBasicTruefalse();break;
				case 7: $wpcuequestion=new WpCueBasicOpenend();break;
			}
			return $wpcuequestion;	
		}
} // END if(!class_exists('WpCueBasicQuestionPost'))
}
/* EOF */