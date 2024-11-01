<?php
if(!class_exists('WpCueBasicQuestion'))
{
    /**
     * WpCueBasicQuestion class
     */
    class WpCueBasicQuestion
    {
		protected $questiontype;
		/**
		* The Constructor
		*/
		public function __construct()
		{
			// register actions
			$this->questiontype=array(
								0=>__('Select Question type','wpcues-basic-quiz'),
								1=>__('Multiple Choice : Single Correct','wpcues-basic-quiz'),
								2=>__('Multiple Choice : Multiple Correct','wpcues-basic-quiz'),
								3=>__('Match the answers','wpcues-basic-quiz'),
								4=>__('Sort the values','wpcues-basic-quiz'),
								5=>__('Fill the gaps','wpcues-basic-quiz'),
								6=>__('True False','wpcues-basic-quiz'),
								7=>__('Open Ended','wpcues-basic-quiz')
								);
			$this->init();
		}
		protected function init(){}
		public function save_question($output,$entityorder){
			global $wpdb;
			$questiondesc=wp_kses_post($output['newquestion']);
			if(isset($output['sectionid'])){$sectionid=intval($output['sectionid']);}
			if(isset($output['quizid'])){$quizid=intval($output['quizid']);}
			if(isset($output['entityid'])){$questionid=intval($output['entityid'][0]);}
			if(isset($output['instanceid'])){$instanceid=intval($output['instanceid'][0]);}
			if(isset($output['category'])){$prevcategoryid=intval($output['category'][0]);}
			if(isset($output['questionchanged'])){$questionchanged=$output['questionchanged'];}else{$questionchanged=0;}
			$questionstatus=intval($output['savequestion_status']);
			$newquestion=0;
			if(!(empty($quizid)) || (!(empty($sectionid)))){
				$poststatus=$output['original_post_status'];
				if($poststatus =='publish'){
					if(!(empty($questionid))){if($instanceid==$questionid){$newquestion=1;$inherited=1;}}else{$newquestion=1;}
				}else{if(empty($questionid)){$newquestion=1;}}
			}else{if(empty($questionid)){$newquestion=1;}}
			if(!empty($newquestion)){
				$question=get_default_post_to_edit('wpcuebasicquestion',true);
				if(!(empty($questionid))){$prevquestionid=$questionid;$questionid=$question->ID;}else{$prevquestionid=$questionid=$question->ID;}
			}else{$prevquestionid=$questionid;$questionid=$instanceid;}
			$questmeta=array();
			$questmeta['t']=$output['origquestiontype'];
			if(empty($sectionid)){$questmeta['s']=$output['sectionvalues'];$sectionstatus=1;}else{$questmeta['s']=$sectionid;$sectionstatus=0;}
			$questmeta['qc']=intval($output['questcatform']);
			$questmeta['desc']=$questiondesc;
			$questmeta=$this->save_questiondata($questmeta,$output,$questionstatus);
			$questtitle=WpCueQuiz_Admin::summary($questmeta['desc'],100,true);
			if(isset($output['anshint'])){$questmeta['anshint']=$output['anshint'];}
			if(isset($output['correctansdesc'])){$questmeta['correctansdesc']=$output['correctansdesc'];}
			if(!(empty($inherited))){
				$postid=$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>serialize($questmeta),'post_status'=>'inherit','post_parent'=>$prevquestionid),array('ID'=>$questionid),array('%s','%s','%s','%d'),array('%d'));
			}else{
				$postid=$wpdb->update($wpdb->posts,array('post_title'=>$questtitle,'post_content'=>serialize($questmeta),'post_status'=>'publish'),array('ID'=>$questionid),array('%s','%s','%s'),array('%d'));
			}
			if(empty($newquestion)){if(!(empty($prevcategoryid)) && ($prevcategoryid != -1)){wp_remove_object_terms($questionid,$prevcategoryid,'questcategory');}}
			if($questmeta['qc'] != -1){wp_set_object_terms( $questionid, $questmeta['qc'], 'wpcuebasicquestcat');}
			if(!(empty($quizid)) || (!(empty($sectionid)))){
				if(empty($quizid)){$quizid=0;}
				if(empty($sectionid)){$sectionid=intval($questmeta['s']);}
				if(in_array($questmeta['t'],array(2,7))){
					$totalpoint=$questmeta['totalpoint'];
				}else{$totalpoint=$questmeta['p'];}
				$questioncat=intval($questmeta['qc']);
				$table_name = $wpdb->prefix.'wpcuequiz_quizinfo';	
				if($newquestion){
					if($poststatus != 'publish'){
						$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,entityid,parentid,entityorder,category,point) VALUES (%d,%d,%d,%f,%d,%d)",$quizid,$questionid,$sectionid,$entityorder,$questioncat,$totalpoint));
						if(!(empty($quizid))){
							$totalpoint=$wpdb->get_results($wpdb->prepare("select sum(point) as point,count(*) as counter from $table_name where quizid=%d and parentid != -1",$quizid),ARRAY_A);
						}
					}
					
				}else{
					if($poststatus != 'publish'){
						$wpdb->update($table_name,array('parentid'=>$sectionid,'point'=>$totalpoint,'category'=>$questioncat,'entityorder'=>$entityorder),array('quizid'=>$quizid,'entityid'=>$questionid),array('%d','%d','%d','%f'),array('%d','%d'));
						if(!(empty($quizid))){
							$totalpoint=$wpdb->get_results($wpdb->prepare("select sum(point) as point,count(*) as counter from $table_name where quizid=%d and parentid != -1",$quizid),ARRAY_A);
						}
					}
				}
				if(in_array($questmeta['t'],array(2,7))){$point=$questmeta['totalpoint'];}else{$point=$questmeta['p'];}
				$content=$this->getsave_question($questmeta,$questtitle,$questionid,$prevquestionid,$sectionid,$point,$questioncat,$entityorder,$sectionstatus,$questionchanged);
			}else{$content='';}if(empty($prevsectionid)){$prevsectionid=$questmeta['s'];}
			$jsonencoded_msg=array('msg'=>'saved','content'=>$content,'questionid'=>$prevquestionid,'instanceid'=>$questionid,'sectionid'=>$questmeta['s'],'prevsectionid'=>$prevsectionid);
			return $jsonencoded_msg;
		}
		protected function getsave_question($questmeta,$questtitle,$questionid,$prevquestionid,$sectionid,$point,$category,$entityorder,$sectionstatus,$questionchanged){
			$content="<td class='rowtitle'><div class='rowshort'><p>";
			$content.=$questtitle."</p></div><div class='rowfull' id='rowfull-".$prevquestionid."'><p>".$questmeta['desc']."</p>";
			$content.='<input type="hidden" name="entityid[]" value="'.$prevquestionid.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="instanceid[]" value="'.$questionid.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="parentid[]" value="'.$sectionid.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="point[]" value="'.$point.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="category[]" value="'.$category.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="entityorder[]" value="'.$entityorder.'" disabled class="requiredvar">';
			$content.='<input type="hidden" name="questionchangedstat[]" value="'.$questionchanged.'" disabled class="requiredvar">';
			if(in_array($questmeta['t'],array(1,2,4))){
				$content.="<ol  class='createquestlist'>";
				foreach($questmeta['a']['id'] as $answerid){$content.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';}
				$content.="</ol>";
			}elseif($questmeta['t']==3){
				$content.="<h3>Left Column</h3><ol class='createquestlist'>";
				foreach($questmeta['la']['id'] as $answerid){$content.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';}
				$content.="</ol><h3>Right Column</h3><ol class='createquestlist'>";
				foreach($questmeta['ra']['id'] as $answerid){$content.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';}
			$content.="</ol>";
			}
			$content.="</div><div class='questrowactions'><span><a href='#' class='questedit'>";$content.=__('Edit','wpcues-basic-quiz');$content.="</a> | </span><span><a href='#' class='questremove'>".__('Remove','wpcues-basic-quiz')."</a> ";
			if(!empty($sectionstatus)){
				$content.="| </span><span><a href='#'  class='changequestorder'>".__('Change Question Order','wpcues-basic-quiz')."</a></span></div>";
			}else{$content.='</span></div>';}
			$content.="</td>";
			return $content;
		}
		protected function save_answertabdata($i,$j,$output,$questmeta,$questionstatus){
			ob_start();
			if(!empty($output[$i.'answerid'])){
			foreach($output[$i.'answerid'] as $key=>$value){
					if(!empty($output[$i.'answeredittab-'.$value])){
					if($questionstatus){
						$answerid=$value;
						if($answerid==$output[$i.'tabids'][$key]){
							$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
						}
					}else{
						$answerid=substr(str_shuffle(MD5(microtime())), 0, 10);
					}
					if(!(empty($questmeta[$j]['id']))){
						array_push($questmeta[$j]['id'],$answerid);
					}else{$questmeta[$j]['id']=array($answerid);}
					$questmeta[$j][$answerid]['desc']=wp_kses_post($output[$i.'answeredittab-'.$value]);
					}
				}
			}
			echo ob_get_clean();
			return $questmeta;
		}
		protected function save_questiondata($questmeta,$output,$questionstatus){}
		public function question_form($questmeta,$sectionids,$butstatus=false,$questionchanged=false){
			echo '<div id="questioneditor"><div class="question_top"><h2 class="question_title">'.__('Question:','wpcues-basic-quiz').'</h2>';
			echo '<input type="hidden" name="questionchanged" id=questionchanged" value="'.$questionchanged.'"></div>';
			echo '<div class="questiontools"><div class="questiontypetool"><select name="questiontype" id="questiontype">';
			foreach($this->questiontype as $key=>$value){
				echo '<option value="'.$key.'" ';
				if(!empty($questmeta['t']) && ($questmeta['t']==$key)){echo 'selected';}
				echo '>'.$value.'</option>';
			}
			echo '</select></div>';
			echo '<div class="sectool">';
			$divheader='<div id="addedsectvalues" ';
			if(!empty($sectionids)){ $divheader.='style="display:block">';}else{$divheader.='style="display:none">';}
			echo $divheader;
			echo '<select name="sectionvalues" id="sectionvalues"><option value=0>'.__('Select Section','wpcues-basic-quiz').'</option>';
			if(!empty($sectionids)){
				$args = array( 'post__in'=>$sectionids,'post_type'=>'wpcuebasicsection','orderby'=>'post__in','posts_per_page' => -1);
					$query1 = new WP_Query($args);
					while ($query1->have_posts()){
						$query1->the_post();
						$entity=$query1->post;
						echo "<option value='".$entity->ID."'";
						if(!(empty($questmeta['s'])) && ($entity->ID==$questmeta['s'])){echo ' selected';}
						echo ">".$entity->post_title."</option>";
					}
				wp_reset_postdata();
			}
			echo '</select></div></div>';
			echo '<div class="questioncattool">';
			$catarg=array('name'=>'questcatform',
							'show_option_none'=>__('Select Question Category','wpcues-basic-quiz'),
							'id'=>'questcatform',
							'hide_empty'=>false,
							'taxonomy'=>'wpcuebasicquestcat');
			if(!(empty($questmeta)) &&($questmeta['qc'] != -1)){$catarg['selected']=$questmeta['qc'];}
			wp_dropdown_categories($catarg);
			echo '</div></div><div class="item_section">';
			if(!empty($questmeta)){
				$questdesc=$questmeta['desc'];$savequestion_status=1;
				$anshint=$questmeta['anshint'];$correctansdesc=$questmeta['correctansdesc'];
			}else{
				$questdesc='';$savequestion_status=0;$anshint='';$correctansdesc='';
			}
			wp_editor($questdesc,'newquestion',array('wpautop'=>false,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			if(empty($questmeta['t'])){$questiontype=0;}else{$questiontype=$questmeta['t'];}
			echo '</div><input type="hidden" name="origquestiontype" value="'.$questiontype.'"><input type="hidden" name="savequestion_status"  value="'.$savequestion_status.'">';
			echo "</div><div id='answereditor' class='innertabcontainer'>";	
			if(!(empty($questmeta))){
				$this->answereditor($questmeta['t'],$questmeta);
			}
			echo '</div>';
			if(!empty($butstatus)){echo '<div class="savequestion"><div class="save_question_button button button-primary">'.__('Save Question','wpcues-basic-quiz').'</div>';
			echo '<div class="button button-primary cancel_question_button">'.__('Cancel','wpcues-basic-quiz').'</div></div>';}
		}
		public function answereditor($questiontype,$questmeta){
			echo '<div id="answereditorinner">';
			if($questiontype==7){
				echo "<ul  class='inneranstabs'><li><a href='#answertab-1'>".__('Correct Answers','wpcues-basic-quiz')."</a></li>";
			}else{
			echo "<ul  class='inneranstabs'><li><a href='#answertab-1'>".__('Answer','wpcues-basic-quiz')."</a></li>";}
			echo "<li><a href='#answertab-2'>".__('Hint','wpcues-basic-quiz')."</a></li><li><a href='#answertab-3'>".__('Correct Answer Description','wpcues-basic-quiz')."</a></li></ul>";
			echo "<div id='answertab-1' class='innertabcontent'>";
			$this->addinitial_main($questmeta);
			echo "</div><div id='answertab-2' class='innertabcontent'>";
			if(empty($questmeta['anshint'])){$anshint='';}else{$anshint=$questmeta['anshint'];}
			if(empty($questmeta['correctansdesc'])){$correctansdesc='';}else{$correctansdesc=$questmeta['correctansdesc'];}
			wp_editor($anshint,'anshint',array('wpautop'=>false,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			echo "</div><div id='answertab-3' class='innertabcontent'>";
			wp_editor($correctansdesc,'correctansdesc',array('wpautop'=>false,'default_editor'=>'tinymce','textarea_rows'=>50,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			echo '</div>';
			echo '</div>';
		}
		protected function addinitial_main($questmeta){}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){}
		public function get_question($entitypost,$settings,$iterators,$instance=false,$prevent=false,$entitystat=false,$quizstat=false){
			$displaysetting=array();$textsettings=array();$randomizsetting=array();
			$i=$iterators['i'];$j=$iterators['j'];$rownum=$iterators['rownum'];$pagenum=$iterators['pagenum'];
			if(!empty($settings['displaysetting'])){$displaysetting=$settings['displaysetting'];}
			if(!empty($settings['text'])){$textsettings=$settings['text'];}
			if(!empty($settings['randomizsetting'])){$randomizsetting=$settings['randomizsetting'];}
			$entitymeta=unserialize($entitypost->post_content);
			$section=0;
			$content='<div id="rowquest-'.$entitypost->ID.'" class="rowquest"><div class="questnumber';
			if(empty($displaysetting['showquestsymbol'])){$content.=' hiddendiv';}
			$content.='">';
			if(!empty($textsettings['questionsymbol'])){
				$content.='<span class="questionsymbol">'.$textsettings['questionsymbol'].'</span>';
			}
			if(!empty($displaysetting['showquestnumber'])){$content.=$rownum;}
			$content.='</div><div class="mainquest">';
			$content.='<input type="hidden" name="questionid[]" value="'.$entitypost->ID.'">';
			if(empty($randomizsetting['randomans'])){$randomizsetting['randomans']=0;}
			if($instance && !(empty($prevent))){
				$rejectprev=0;
				if(!(empty($entitystat[$entitypost->ID]->questionchange)) && ($quizstat['endtime'] < $entitystat[$entitypost->ID]->questionchangedate)){
					$rejectprev=1;
				}
				$content.=$this->getquest_det($entitymeta,$entitypost->ID,$randomizsetting['randomans'],$prevent,$rejectprev);
			}else{
				$content.=$this->getquest_det($entitymeta,$entitypost->ID,$randomizsetting['randomans']);
			}
			$content.='</div></div>';
			//$questdata=array('content'=>$content,'i'=>$i,'pagenum'=>$pagenum,'rownum'=>$rownum);
			return $content;
		}
		protected function getquest_det($questmeta,$questionid,$randomans,$prevquestdet = false,$rejectprev=false){}
	}
}
/* EOF */
