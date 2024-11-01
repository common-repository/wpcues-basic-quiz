<?php
/**
 * WpCueBasicQuiz class
*/
	 if(!class_exists('WpCueBasicQuiz'))
{
    class WpCueBasicQuiz extends WpCueQuiz_Base
    {
        const POST_TYPE = "wpcuebasicquiz";
        public $quizid;
		/**
		* hook into WP's init action hook
		*/
		protected function init()
		{
			// Initialize Post Type
			$this->add_action('init','create_post_type');
			$this->add_action('before_delete_post','delete_quiz');
			$this->add_action('wp_ajax_wpcuequizsavequiz_action','save_quiz');
			$this->add_action('wp_ajax_wpcuequizadddepgrade_action','addgrade_group');
			$this->add_action('wp_ajax_wpcuequizremdepgrade_action','remgrade_group');
			$this->add_action('wp_ajax_wpcuequizsavequizcategory_action','save_quizcategory');
		} // END public function init()
		public function showPosttype() {
			return self::POST_TYPE ;
		}
		/**
		* Create the post type
		*/
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Quizzes', 'post type general name', 'wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Quiz', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Quizzes', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Quiz', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'quiz', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Quiz', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Quiz', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Quiz', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Quiz', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Quizzes', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Quizzes', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Quizzes:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Quizzes found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Quizzes found in Trash.', 'wpcues-basic-quiz' ),
		
			);
			$args = array(
				'labels'             => $labels,
				'publicly_queryable' => true,
				'show_ui'            => false,
				'capability_type'    => 'post',
				'taxonomies'=>array('wpcuebasicquizcat'),
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt')
		
			);
			$wpprocuesetting=$this->_config->setting;
			$status=0;
			$quiztype=$wpprocuesetting['basic']['quiztype'];
			if($quiztype !=1){
				$args['public']=true;
				$quizslug=$wpprocuesetting['basic']['quizslug'];
				$args['rewrite']=array('slug'=>$quizslug,'with_front'=>false);
			}else{$args['public']=false;}
			register_post_type(self::POST_TYPE,$args);
			if(!empty($wpprocuesetting['basic']['quizchanged'])){
				flush_rewrite_rules();
				$wpprocuesetting['basic']['quizchanged']=0;
				update_option('wpcuequiz_setting',$wpprocuesetting);
			}
			$labels = array(
				'name'              => _x( 'Quiz Categories', 'taxonomy general name','wpcues-basic-quiz' ),
				'singular_name'     => _x( 'Quiz Category', 'taxonomy singular name','wpcues-basic-quiz'  ),
				'search_items'      => __( 'Search Quiz Categories','wpcues-basic-quiz'  ),
				'all_items'         => __( 'All Quiz Categories','wpcues-basic-quiz'  ),
				'parent_item'       => __( 'Parent Quiz Category','wpcues-basic-quiz'  ),
				'parent_item_colon' => __( 'Parent Quiz Category:','wpcues-basic-quiz'  ),
				'edit_item'         => __( 'Edit Quiz Category','wpcues-basic-quiz'  ),
				'update_item'       => __( 'Update Quiz Category','wpcues-basic-quiz'  ),
				'add_new_item'      => __( 'Add New Quiz Category','wpcues-basic-quiz'  ),
				'new_item_name'     => __( 'New Quiz Category Name','wpcues-basic-quiz'  ),
				'menu_name'         => __( 'Quiz Category','wpcues-basic-quiz' ),
			);
			$arges = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'public'=>false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => false
			);
			register_taxonomy('wpcuebasicquizcat','wpcuebasicquiz',$arges);
			$uncategorized=__('Uncategorized','wpcues-basic-quiz');
			wp_insert_term($uncategorized,'wpcuebasicquizcat');
		}
		/**
		* Set quiz id
		*/
		public function set_quizid(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			$this->quizid=$post->ID;
		}
		
		private function update_quizmeta($quizid,$metakey,$metavalue=false){
			if(isset($metavalue)){update_post_meta($quizid,$metakey,$metavalue);}else{delete_post_meta($quizid,$metakey);}
		}
		/**
		*Save Quiz
		*/
		public function save_quiz(){
			global $wpdb;
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_POST['myformdata']);
			}else{$myformdata=$_POST['myformdata'];}
			$error=0;$postid=0;
			parse_str($myformdata,$output);
			$butval=$output['original_publish'];
			$poststatustype=$output['poststatustype'];
			$poststatus=$output['original_post_status'];
			$quiz['ID']=$output['quizid'];
			if(isset($output['quizdesc'])){$quiz['post_content']=wp_kses_post($output['quizdesc']);}else{$quiz['post_content']='';}
			if(empty($poststatustype)){
				$quiz['post_status']='draft';
				if($poststatus=='auto-draft'){$quiz['post_name']=wp_unique_post_slug($output['quizname'],$quiz['ID'],'publish','wpcuebasicquiz',0);}
			}else{$quiz['post_status']='publish';}
			$quiz['post_title']=$output['quizname'];
				$post_category=array();		
			if(isset($output['tax_input']['wpcuebasicquizcat'])){$post_category=$output['tax_input']['wpcuebasicquizcat'];}
			if(empty($output['tax_input'])){
				$term=get_term_by('name','Uncategorized','wpcuebasicquizcat');
				$post_category[]=$term->term_id;
			}
			$quiz['tax_input']=array('wpcuebasicquizcat'=>$post_category);
			wp_update_post($quiz);
			$allowed_html=wp_kses_allowed_html('post');
			$this->update_quizmeta($quiz['ID'],'quizfinal',wp_kses($output['quizfinal'],$allowed_html));
			if(!empty($output['quizintermediate'])){$this->update_quizmeta($quiz['ID'],'quizintermediate',wp_kses_post($output['quizintermediate']));}
			if(!empty($output['quizcomplete'])){$this->update_quizmeta($quiz['ID'],'quizcomplete',wp_kses_post($output['quizcomplete']));}
			if(!empty($output['adminemailsubject'])){$adminemail['subject']=$output['adminemailsubject'];}
			if(!empty($output['adminemail'])){$adminemail['mail']=$output['adminemail'];}
			if(!empty($output['useremailsubject'])){$useremail['subject']=$output['useremailsubject'];}
			if(!empty($output['useremail'])){$useremail['mail']=$output['useremail'];}
			if(!empty($output['customcss'])){
						$this->update_quizmeta($quiz['ID'],'customcss',wp_slash($output['customcss']));
				}else{delete_post_meta($quiz['ID'],'customcss');}
			if(isset($output['discloseans'])){$basicsetting['discloseans']=$output['discloseans'];}
			$this->update_quizmeta($quiz['ID'],'quizadminemail',$adminemail);
			$this->update_quizmeta($quiz['ID'],'quizuseremail',$useremail);
			if(isset($output['quizduration'])){$basicsetting['duration']=$output['quizduration']*60;}
			if(isset($output['questloaded'])){$basicsetting['questloaded']=$output['questloaded'];}
			if(isset($output['questperpage'])){$basicsetting['questperpage']=$output['questperpage'];}
			if(!empty($output['quizmode'])){$basicsetting['mode']=$output['quizmode'];}
			if(isset($output['loginrequired'])){$basicsetting['login']=$output['loginrequired'];}else{$basicsetting['login']=0;}
			if(!empty($output['lognum'])){$basicsetting['lognum']=$output['lognum'];}
			if(!empty($output['loggap'])){$basicsetting['loggap']=$output['loggap'];}
			if(!empty($output['notifyadmin'])){$basicsetting['notifyadmin']=$output['notifyadmin'];}
			if(!empty($output['notifyuser'])){$basicsetting['notifyuser']=$output['notifyuser'];}
			if(!empty($output['autosubmit'])){$basicsetting['autosubmit']=$output['autosubmit'];}
			if(isset($output['discloseans'])){$basicsetting['discloseans']=$output['discloseans'];}
			if(isset($output['addcaptcha'])){
				$basicsetting['addcaptcha']=$output['addcaptcha'];
				$basicsetting['captchalocation']=$output['captchalocation'];
			}
			$prevbasicsetting=get_post_meta($quiz['ID'],'basicsetting',true);
			if(isset($prevbasicsetting['login'])&&(isset($basicsetting['login'])) && ($prevbasicsetting['login'] != $basicsetting['login'])){
				$basicsetting['changedquiztimestamp']=time();
			}
			if(!(empty($basicsetting))){$this->update_quizmeta($quiz['ID'],'basicsetting',$basicsetting);}
			if(!empty($output['randomquest'])){$randomizsetting['randomquest']=$output['randomquest'];}
			if(!empty($output['randomans'])){$randomizsetting['randomans']=$output['randomans'];}
			if(!empty($output['randomquestcat'])){$randomizsetting['randomquestcat']=$output['randomquestcat'];}
			if(!empty($output['randsecexc'])){$randomizsetting['randsecexc']=$output['randsecexc'];}
			if(!empty($output['randsecexcans'])){$randomizsetting['randsecexcans']=$output['randsecexcans'];}
			if(!empty($randomizsetting)){$this->update_quizmeta($quiz['ID'],'randomizsetting',$randomizsetting);}
			if(!empty($output['timer'])){$displaysetting['timer']=$output['timer'];}
			if(!empty($output['showquestsymbol'])){$displaysetting['showquestsymbol']=$output['showquestsymbol'];}
			if(!empty($output['showquestnumber'])){$displaysetting['showquestnumber']=$output['showquestnumber'];}
			if(!empty($output['disablequizdesc'])){$displaysetting['disablequizdesc']=$output['disablequizdesc'];}
			if(!empty($output['disableintermediate'])){$displaysetting['disableintermediate']=$output['disableintermediate'];}
			if(!empty($output['disablestartbutton'])){$displaysetting['disablestartbutton']=$output['disablestartbutton'];}
			if(!empty($output['intermediatecontrol'])){$displaysetting['intermediatecontrol']=$output['intermediatecontrol'];}
			if(!empty($output['savebuttonstat'])){$displaysetting['savebuttonstat']=$output['savebuttonstat'];}
			if(!empty($output['submitbuttonstat'])){$displaysetting['submitbuttonstat']=$output['submitbuttonstat'];}
			if(!(empty($displaysetting))){$this->update_quizmeta($quiz['ID'],'displaysetting',$displaysetting);}
			if(!empty($output['showanswer'])){$questtools['showanswer']=$output['showanswer'];}
			if(!empty($output['showhint'])){$questtools['showhint']=$output['showhint'];}
			if(!empty($output['reportquest'])){$questtools['reportquest']=$output['reportquest'];}
			if(!empty($output['disabledentity'])){$disabledentities=$output['disabledentity'];}else{$disabledentities=array();}
			if(!(empty($questtools))){$this->update_quizmeta($quiz['ID'],'questtools',$questtools);}
			$gradegroupid=0;
			if($poststatus=='publish'){
				if(isset($output['gradegroupid'])){$gradegroupid=$output['gradegroupid'];}
				if(isset($output['inheritgradegroupid'])){$inheritgradegroupid=$output['inheritgradegroupid'];}
				if(empty($gradegroupid)&& !(empty($inheritgradegroupid))){
					wp_delete_post($inheritgradegroupid);
				}
				if(!(empty($gradegroupid)) && !(empty($inheritgradegroupid)) &&($inheritgradegroupid != $gradegroupid)){
					$newgradegroup=get_post($inheritgradegroupid);
					wp_update_post(array('ID'=>$gradegroupid,'post_title'=>$newgradegroup->post_title,'post_content'=>$newgradegroup->post_content,'post_status'=>'publish'));
				}
				$this->update_quizmeta($quiz['ID'],'quizgrade',$gradegroupid);
				if(!(empty($output['questionschanges']))){
					$table_name=$wpdb->prefix.'posts';
					$wpdb->delete($wpdb->prefix.'wpcuequiz_quizinfo',array('quizid'=>$quiz['ID']),array('%d'));
					if(!(empty($output['entityid']))){
						$entityid=$output['entityid'];$parentid=$output['parentid'];$flippedentityids=array_flip($entityid);
						$instanceid=$output['instanceid'];$updateents=array();
						$updateents=$diffent=array_diff($entityid,$instanceid);$combent=array_combine($entityid,$instanceid);
						foreach($diffent as $entity){
							array_push($updateents,$combent[$entity]);
						}
						if(!(empty($updateents))){$updateentids='('.implode(',',$updateents).')';
						$questions=$wpdb->get_results("select ID,post_title,post_content,post_type from $table_name where ID in $updateentids",OBJECT_K); 
						foreach($diffent as $entity){
							$instanceid=$combent[$entity];
							$entitykey=$flippedentityids[$entity];
							if(!empty($questionchangedstatus)){array_push($updatequestinfo,$entity);}
							if($questions[$instanceid]->post_type=='wpcuebasicsection'){$instancemeta=$questions[$instanceid]->post_content;}else{
							$instancemeta=unserialize($questions[$instanceid]->post_content);}
							if(!(empty($instancemeta))){
								$questionid=$entity;
								if($questions[$questionid]->post_type=='wpcuebasicsection'){$questmeta=$questions[$questionid]->post_content;}else{
									$questmeta=unserialize($questions[$questionid]->post_content);}
								if($questions[$questionid]->post_type=='wpcuebasicquestion'){if($questmeta['qc'] != $instancemeta['qc']){
									if($instancemeta['qc'] != -1){
										wp_set_object_terms($questionid,$instancemeta['qc'],'wpcuebasicquestcat');
									}
								}}
								$questmeta=$instancemeta;
								$questtitle=$questions[$instanceid]->post_title;
								if($questions[$questionid]->post_type=='wpcuebasicsection'){$questcontent=$questmeta;}else{
									$questcontent=serialize($questmeta);}
								$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>$questcontent,'post_status'=>'publish'),array('ID'=>$questionid),array('%s','%s','%s'),array('%d'));
							}
							array_push($disabledentities,$instanceid);
						}
						}
						$point=$output['point'];$category=$output['category'];
						$totalquest=count($entityid);$value='';$totalquestcount=0;
						$questionchangedstat=$output['questionchangedstat'];
						for($i=0;$i<$totalquest;$i++){
							$value.='('.$quiz['ID'].','.$entityid[$i].','.$parentid[$i].','.$point[$i].','.$category[$i].','.($i+1).','.$questionchangedstat[$i].')';
							if(($totalquest>1)&&($i != ($totalquest-1))){$value.=',';}
							if($parentid[$i] != -1){$totalquestcount++;}
						}
						$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
						$wpdb->query("INSERT INTO $table_name (quizid,entityid,parentid,point,category,entityorder,questionchange) VALUES $value");
					}
				}
			}
			foreach($disabledentities as $entityid){wp_delete_post($entityid);}
			echo json_encode(array('msg'=>'saved','gradegroupid'=>$gradegroupid));
			die();
		}
		public function delete_quiz($postid){
			ob_start();
			global $post_type;  
			if($post_type != 'wpcuebasicquiz'){return;}
			global $wpdb;	
			$table_name[0] = $wpdb->prefix.'wpcuequiz_quizinfo';	
			$table_name[1] = $wpdb->prefix.'wpcuequiz_quizstat';
			$table_name[2] = $wpdb->prefix.'wpcuequiz_quizstatinfo';	
			foreach($table_name as $tablename){
				$wpdb->query($wpdb->prepare("Delete from $tablename where quizid=%d",$postid));
			}
		}
		public static function entityids($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d order by entityorder asc",$quizid));
			return $entityids;
		}
		public static function getquestions($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d and parentid != -1 order by entityorder asc",$quizid));
			return $entityids;
		}
		/**
		* Add Grade Group to Quiz
		*/
		public function addgrade_group(){
			$quizid=$_POST['quizid'];
			$gradegroup=$_POST['gradegroup'];
			if(add_post_meta($quizid,'quizgrade',$gradegroup,true)){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			die();
		}
		public function save_quizcategory(){
			check_ajax_referer('wpprocue-wpcuebasicquizcat-nyspecial','quizcatnonce');
			$quizcategory=$_POST['quizcategory'];
			$parentcategory=$_POST['parentcategory'];
			if($parentcategory != -1){
				$quizcat=wp_insert_term($quizcategory,'wpcuebasicquizcat',array('parent'=>$parentcategory));
			}else{
				$quizcat=wp_insert_term($quizcategory,'wpcuebasicquizcat');
			}
			if(is_wp_error($quizcat)){
			echo json_encode(array('msg'=>'failed'));
			}else{
				$returnval='<li id="wpcuebasicquizcat-'.$quizcat['term_id'].'"><label class="selectit"><input value="'.$quizcat['term_id'].'" type="checkbox" name="tax_input[wpcuebasicquizcat][]" id="in-wpcuebasicquizcat-'.$quizcat['term_id'].'" checked/>'.$quizcategory.'</label></li>';
				echo json_encode(array('msg'=>'success','returnval'=>$returnval));
			}
			die();
		}
	
		/**
		* Remove Grade Group from Quiz
		*/
		public function remgrade_group(){
			$quizid=$_POST['quizid'];
			if(delete_post_meta($quizid,'quizgrade')){
				echo json_encode(array('msg'=>'success'));
			}else{echo json_encode(array('msg'=>'failed'));}
			die();
		}
		public function quizinfo($quizid){
			global $wpdb;
			$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
			$quizinfo=$wpdb->get_row($wpdb->prepare("select count(id) as totalquestions, sum(point) as totalpoint from $quizinfotable where quizid=%d and parentid != -1",$quizid),ARRAY_A);
			if(empty($quizinfo['totalquestions'])){$quizinfo['totalquestions']=0;}
			if(empty($quizinfo['totalpoint'])){$quizinfo['totalpoint']=0;}
			return $quizinfo;
		}
    } // END class WpCueBasicQuiz
} // END if(!class_exists('WpCueBasicQuiz'))
/* EOF */
