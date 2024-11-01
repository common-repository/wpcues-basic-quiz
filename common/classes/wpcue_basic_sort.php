<?php 
 if(!class_exists('WpCueBasicSort'))
{
    class WpCueBasicSort extends WpCueBasicQuestion
    {
		protected function save_questiondata($questmeta,$output,$questionstatus){
			$questmeta=$this->save_answertabdata(1,'a',$output,$questmeta,$questionstatus);
			if(empty($questmeta['p'])){$questmeta['p']=0;}
			if(!(empty($output['coranswer']))){
				$questmeta['c']=$output['coranswer'];
				$coranswer=explode(',',$questmeta['c']);
				foreach($coranswer as $key=>$answer){
					$questmeta['coranswer'][$key]=$questmeta['a']['id'][intval($answer)-1];
				}
				$questmeta['p']=$output['points'];
				if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
			}else{$questmeta['p']=0;$questmeta['partialpoint']=0;}
			if(!empty($output['markinputbox'])){$questmeta['markinputbox']=1;}
			if(!empty($output['sorthelpmsg'])){$questmeta['sorthelpmsg']=1;}
			return $questmeta;
		}
		protected function addinitial_main($questmeta){
			if(!empty($questmeta['partialpoint'])){$partialpoint=$questmeta['partialpoint'];}else{$partialpoint=0;}
			$j=2;$status=1;
			if(!(empty($questmeta))){$j=count($questmeta['a']['id']);}
			echo '<div id="answersbox-'.$status.'" class="innertabcontent"><div class="answeraddtools">';
			echo '<div class="button add_answer_button">'.__('Add Answer','wpcues-basic-quiz').'</div></div><div id="answersboxtab-'.$status.'">';
			echo '<ul class="innernumtabs">';
			for($i=1;$i<=$j;$i++){
				echo '<li class="activetab"><a href="#'.$status.'answereditortab-'.$i.'">'.$i.'</a></li>';
			} 
			echo '</ul>';
			for($i=1;$i<=$j;$i++){
				$point=0;$corstatus=0;$answerid=$i;$ansdesc='';
				if(!(empty($questmeta))){
					$k=$i-1;
					$answerid=$questmeta['a']['id'][$k];$ansdesc=$questmeta['a'][$answerid]['desc'];	
				}
				$this->answer_form($i,$answerid,$ansdesc,$status,$point,$corstatus);
			}
			echo '</div></div>';
			if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
			if(!(empty($questmeta['c']))){$coranswer=$questmeta['c'];}else{$coranswer=array();}
			if(empty($coranswer)){$coranswer='';$disabledstatus=1;}else{$disabledstatus=0;}
			echo '<div id="sortquestionbox" class="questdepbox"><table class="widefat fixed"><tr><td>'.__('Points','wpcues-basic-quiz').'</td>';
			echo '<td><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td>';
			echo '<td><input type="text" name="coranswer" value="'.$coranswer.'"  class="questioncorrectanswer" /></td></tr>';
			echo '<tr><td colspan="2"><div class="entitymsg">'.__('Please enter whatever your correct sorting order as 1,2,3,4 or 2,4,3,1 i.e. separated by comma.','wpcues-basic-quiz').'</div></td></tr>';
			echo '</table></div>';
			echo '<div id="partialmarkbox"><ul>';
			echo '<li><input type="checkbox" name="partialpoint" class="partialpoint" value="1"';
			if($partialpoint == 1){echo 'checked';}
			echo '>'.__('Award weighted point to partial correct answers','wpcue-basic-quiz').'</li>';
			echo '</ul></div>';
		}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){
			echo "<div id='".$boxstatus."answereditortab-".$i."'><div class='answerclosetools'><div class='button answer_close_button'>X</div></div>";
				wp_editor($ansdesc,$boxstatus.'answeredittab-'.$answerid,array('wpautop'=>true,'default_editor'=>'tinymce','textarea_rows'=>40,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
				echo '<input type="hidden" name="'.$boxstatus.'answerid[]" value="'.$answerid.'">';
				echo '<input type="hidden" name="'.$boxstatus.'tabids[]" value="'.$i.'">';
				echo '</div>';
		
		}
		public function get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber){
			$report='<div class="rowquest"><div class="questnumber';
			if(empty($showquestsymbol)){$report.=' hiddendiv';}
				$report.='">';
				if(!empty($questionsymbol)){
					$report.='<span class="questionsymbol">'.$questionsymbol.'</span>';
				}
				if(!empty($showquestnumber)){$report.=$questnum;}
			$report.='</div><div class="mainquest"><div class="questdesc"> '.$questmeta['desc'].'</div><div class="answerdesc"><ul class="sortans">';
			foreach($answer as $key=>$answerid){
				$report.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';
			}
			$report.='</ul>';
			$discloseans=$this->disclose_status($discloseans,$status);
			if($status != 4){
				$report.='<div class="sortrepans"><p class="repliedanstext">Replied Ans</p><ul class="sortrepans">';
				foreach($reply as $key=>$answerid){
					$report.='<li';
					if(!empty($discloseans)){
						if(!empty($questmeta['coranswer'])){
							$class=$this->get_class($status,$answerid,$key,$questmeta['coranswer']);
						}else{$class="ansreplied";}
						$report.=' class="'.$class.'"';
					}
					$report.='>'.$questmeta['a'][$answerid]['desc'].'</li>';
				}
				$report.='</ul></div>';
			}
			if(!empty($discloseans) && ($status != 3)){
				$report.='<div class="sortcorans"><p class="correctanstext">Correct Ans</p><ul class="sortcorans">';
				foreach($questmeta['coranswer'] as $key=>$answerid){
					$report.='<li>'.$questmeta['a'][$answerid]['desc'].'</li>';
				}
				$report.='</ul></div>';
			}
			$report.='</div></div></div>';
			return $report;
		}
		private function disclose_status($discloseans,$status){
			if(!(empty($discloseans))){
				if(($discloseans==3)&&($status !=1)){$discloseans=0;}
				if(($discloseans==2)&&($status ==4)){$discloseans=0;}
			}
			return $discloseans;
		}
		private function get_class($status,$answerid,$key,$correctanswer){
			switch($status){
				case 0:
				case 2:
					if($answerid==$correctanswer[$key]){$class="correctansreplied";}else{$class="wrongansreplied";}
					break;
				case 1:
					$class="correctansreplied";
					break;
			}
			return $class;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$fontcolor){
			$report='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
			foreach($answer as $key=>$answerid){
				$report.='<tr><td></td><td><table><tr><td>'.($key+1).'</td><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr></table></d></tr>';
			}
			$discloseans=$this->disclose_status($discloseans,$status);			
			if($status != 4){
				$report.='<tr><td></td><td><table><tr><td>Replied Answer</td><td></td></tr>';
				foreach($reply as $key=>$answerid){
					if(!empty($discloseans)){
						if(!empty($questmeta['coranswer'])){
							$class=$this->get_class($status,$answerid,$key,$questmeta['coranswer']);
						}else{$class="ansreplied";}
					}
					$report.='<tr><td>';
					if(!empty($class)){$report.='<font color="'.$fontcolor[$class].'">';};
					$report.=($key+1);
					if(!empty($class)){$report.='</font>';}
					$report.='</td><td>';
					if(!empty($class)){$report.='<font color="'.$fontcolor[$class].'">';};
					$report.=$questmeta['a'][$answerid]['desc'];
					if(!empty($class)){$report.='</font>';}
					$report.='</td>';
					if($class=='correctansreplied'){
						$report.= '<td><img src="'.WPCUES_BASICQUIZ_URL.'/public/img/correctans.png"></td>';
					}elseif($class=='wrongansreplied'){
						$report.= '<td><img src="'.WPCUES_BASICQUIZ_URL.'/public/img/wrongans.png"></td>';
					}else{$report.='<td></td>';}
					$report.='</tr>';
				}
				$report.='</table></td></tr>';
			}
			if(!empty($discloseans) && !(empty($questmeta['coranswer']))){
				$disstatus=1;
				if(($discloseans==3)&&($status !=1)){$disstatus=0;}
				if(!empty($disstatus)){
					$report.='<tr><td></td><td><table><tr><td>Correct Answer</td><td></td></tr>';
					foreach($questmeta['coranswer'] as $key=>$answerid){
						$report.='<tr><td>'.($key+1).'</td><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr>';
					}
					$report.='</table></td></tr>';
				}
			}
			return $report;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			if(!(empty($output['sortquestionstatus']))){
				if(!(empty($entitymeta['coranswer']))){
					$misans=array_diff_assoc($entitymeta['coranswer'],$output['answer-'.$entityid]);
					if($entitymeta['partialpoint']==0){
						if(empty($misans)){$point=$entitymeta['p'];$correct=1;$percent+=100;$pointscored+=$point;}else{$correct=0;$point=0;}
					}else{
						$cor=(count($entitymeta['coranswer'])-count($misans))/count($entitymeta['coranswer']);
						$point=$cor*$entitymeta['p'];$pointscored+=100;$percent+=$cor*100;
						if($cor == count($entitymeta['coranswer'])){$correct=1;}elseif($cor != 0){$correct=2;}else{$correct=0;}
					}	
				}else{$point=0;$correct=3;}
				$reply=serialize($output['answer-'.$entityid]);
			}else{$reply=NULL;$point=0;$correct=4;}
			$answer=$output['answerids-'.$entityid];
			$resultmeta=array('answer'=>$answer,'reply'=>$reply,'correct'=>$correct,'point'=>$point,'pointscored'=>$pointscored,'percent'=>$percent);
			return $resultmeta;
		}
		protected function getquest_det($questmeta,$questionid,$randomans,$prevquestdet = false,$rejectprev=false){
			$content='';$disabled=0;
			if($prevquestdet && (!empty($prevquestdet->disabled))){$disabled=1;}
			$content.='<div class="questdesc"><p>';
			if($questmeta['t'] !=5){$content.=$questmeta['desc'];}
			$content.="<input type='hidden' name='disablestatus-".$questionid."' class='disablestatus' value='".$disabled."'>";
			$content.="<input type='hidden' name='questiontype-".$questionid."' value='".$questmeta['t']."'>";
			if(($prevquestdet)&&(empty($rejectprev))){
							$answerids=$reply=maybe_unserialize($prevquestdet->reply);
							$origansids=maybe_unserialize($prevquestdet->answer);
							if(empty($answerids)){$answerids=$origansids;}
					}else{
					$answerids=$questmeta['a']['id'];
					if(!(empty($randomans))){shuffle($answerids);}
						$origansids=$answerids;
					}
					$content.='</p><p class="entitymsg">Sort the answers in correct order and select the checkbox when done</p></div><div class="answerdesc"><ul class="sortquestion">';
					$i=1;
					foreach($answerids as $answer){
						$content.='<li class="ui-state-default" id="anslist-'.$questionid.'"><input type="hidden" name="answer-'.$questionid.'[]" value="'.$answer.'"> '.$i.'. '.$questmeta['a'][$answer]['desc'].'</li>';
						$i++;
					}
					$content.='</ul><input type="checkbox" name="sortquestionstatus" value="1" ';
					if(!(empty($reply))){$content.='checked';}
					if($disabled){$content.=' disabled';}
					$content.='>Mark as correct';
					$content.="<input type='hidden' name='answerids-".$questionid."' value='".serialize($origansids)."'>";
					$content.='</div>';
			return $content;
		}
	}
}
/* EOF */