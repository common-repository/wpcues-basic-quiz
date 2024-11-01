<?php 
 if(!class_exists('WpCueBasicOpenend'))
{
    class WpCueBasicOpenend extends WpCueBasicQuestion
    {
		protected function save_questiondata($questmeta,$output,$questionstatus){
			$questmeta=$this->save_answertabdata(1,'a',$output,$questmeta,$questionstatus);
			$totalpoint=0;
			foreach($output['1answerid'] as $key=>$value){
				if(!empty($output['1answeredittab-'.$value])){
					$answerid=$questmeta['a']['id'][$key];
					$questmeta['p'][$answerid]=$output['points-'.$output['1tabids'][$key]];
					$totalpoint+=$questmeta['p'][$answerid];
				}
			}
			if(empty($questmeta['p'])){$questmeta['p']=0;}
			$questmeta['totalpoint']=$totalpoint;
			if(!empty($questmeta['a'])){
				$questmeta['matchingmode']=$output['matchingmode'];
				$questmeta['casesensitivity']=$output['casesensitivity'];
			}
			return $questmeta;
		}
		protected function addinitial_main($questmeta){
			$j=1;$status=1;
			if(!(empty($questmeta['a']))){$j=count($questmeta['a']['id']);}
			echo '<div id="answersbox-'.$status.'" class="innertabcontent"><div class="answeraddtools">';
			echo '<div class="button add_answer_button">'.__('Add Answer','wpcues-basic-quiz').'</div></div><div id="answersboxtab-'.$status.'">';
			echo '<ul class="innernumtabs">';
			for($i=1;$i<=$j;$i++){
				echo '<li class="activetab"><a href="#'.$status.'answereditortab-'.$i.'">'.$i.'</a></li>';
			} 
			echo '</ul>';
			for($i=1;$i<=$j;$i++){
				$point=0;$corstatus=0;$answerid=$i;$ansdesc='';
				if(!(empty($questmeta['a']))){
					$k=$i-1;
					$answerid=$questmeta['a']['id'][$k];$ansdesc=$questmeta['a'][$answerid]['desc'];
					if((!empty($questmeta['a']))){$point=$questmeta['p'][$answerid];$corstatus=1;}
				}
				$this->answer_form($i,$answerid,$ansdesc,$status,$point,$corstatus);
			}
			echo '</div></div>';
		}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){
			echo "<div id='".$boxstatus."answereditortab-".$i."'><div class='answerclosetools'><div class='button answer_close_button'>X</div></div>";
			wp_editor($ansdesc,$boxstatus.'answeredittab-'.$answerid,array('wpautop'=>true,'default_editor'=>'tinymce','textarea_rows'=>40,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
			echo '<input type="hidden" name="'.$boxstatus.'answerid[]" value="'.$answerid.'">';
			echo '<input type="hidden" name="'.$boxstatus.'tabids[]" value="'.$i.'">';
			
				echo '<div id="openendedbox-<?php echo $i;?>" class="questdepbox openendedbox"><table class="widefat fixed">';
				echo '<tr><td>'.__('Matching Mode','wpcues-basic-quiz').' :</td><td> <select name="matchingmode"><option value="1"';
			if(!(empty($questmeta['matchingmode'])) && ($questmeta['matchingmode']==1)){echo ' selected';
			}elseif(empty($questmeta['matchingmode'])){echo 'selected';}
			echo '>'.__('Loose','wpcues-basic-quiz').'</option>';
			echo '<option value="2"';
			if(!(empty($questmeta['matchingmode'])) && ($questmeta['matchingmode']==2)){echo ' selected';}
			echo '>'.__('User Answer Text contains correct answer','wpcues-basic-quiz').'</option>
			<option value="3"';
			if(!(empty($questmeta['matchingmode'])) && ($questmeta['matchingmode']==3)){echo ' selected';}
			echo '>'.__('Correct Answer contains whole user answer','wpcues-basic-quiz').'</option>
			<option value="4"';
			if(!(empty($questmeta['matchingmode'])) && ($questmeta['matchingmode']==4)){echo ' selected';}
			echo '>'.__('Exact match','wpcues-basic-quiz').'</option></select>';
			echo '<select name="casesensitivity"><option value="0"';
			if(isset($questmeta['casesensitivity']) && ($questmeta['casesensitivity']==0)){echo 'selected';
			}elseif(empty($questmeta['casesensitivity'])){echo 'selected';}
			echo '>'.__('Case Insensitive','wpcues-basic-quiz').'</option><option value="1"';
			if(isset($questmeta['casesensitivity']) && ($questmeta['casesensitivity']==1)){echo 'selected';}
			echo '>'.__('Case Sensitive','wpcues-basic-quiz').'</option></select></td></tr>';
				echo '<tr><td>'.__('Points','wpcues-basic-quiz').'</td><td><input type="text" name="points-'.$i.'" class="questionpoint" value="'.$point.'"></td></tr>';
				echo '</table></div>';
				echo '</div>';
		
		}
		public function get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber){
			$correctcontent=$this->get_correctcontent($status,$discloseans);
				$report='<div class="rowquest"><div class="questnumber';
				if(empty($showquestsymbol)){$report.=' hiddendiv';}
				$report.='">';
				if(!empty($questionsymbol)){
					$report.='<span class="questionsymbol">'.$questionsymbol.'</span>';
				}
				if(!empty($showquestnumber)){$report.=$questnum;}
				$report.='</div><div class="mainquest"><div class="questdesc">';
				$report.=$questmeta['desc'].'</div><div class="answerdesc"><ul class="reportans openendedrepans">';
				if(!(empty($reply))){$report.='<li class="repopenendlist">Replied Answer : <span class="openendedreply';
				if($status==1){$report.=' correctansreplied'; }elseif($status==0){$report.=' wrongansreplied'; 
				}else{$report.=' ansreplied'; }
				$report.='">'.$reply.'</span></li>';}
				if(!(empty($correctcontent))){
					if(count($questmeta['a']['id']) >1){
						$report.='<li class="correctopenendlist">Correct Answers : <ul>';
						foreach($questmeta['a']['id'] as $answerid){
							$report.='<li >'.$questmeta['a'][$answerid]['desc'].'</li>';
						}
						$report.='</ul></li>';
					}else{
						foreach($questmeta['a']['id'] as $answerid){
							$report.='<li class="correctopenendlist">Correct Answer : '.$questmeta['a'][$answerid]['desc'].'</li>';
						}
					}
				}
				$report.='</ul></div></div></div>';
			return $report;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$bgcolor){
			$correctcontent=$this->get_correctcontent($status,$discloseans);
			$report='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
			if(!(empty($reply))){$report.='<tr><td></td><td><table><tr><td>Replied Answer : </td><td>'.$reply.'</td></tr></table></td></tr>';}
			if(!(empty($correctcontent))){
				if(count($questmeta['a']['id']) >1){
					$report.='<tr><td></td><td><table><tr><td>Correct Answers : </td><td><table>';
					foreach($questmeta['a']['id'] as $answerid){
						$report.='<tr><td>'.$questmeta['a'][$answerid]['desc'].'</td></tr>';
					}
					$report.='</table></td></tr></table></td></tr>';
				}else{
					$report.='<tr><td></td><td><table><tr><td>Correct Answer : </td>';
					foreach($questmeta['a']['id'] as $answerid){
						$report.='<td>'.$questmeta['a'][$answerid]['desc'].'</td>';
					}
					$report.='</tr></table></td></tr>';
				}
			}
			return $report;
		}
		private function get_correctcontent($status,$discloseans=false){
			$correctcontent=0;
			if(!(empty($discloseans))){
				switch($discloseans){
					case 1:
						if($status !=3){$correctcontent=1;}
						break;
					case 2:
						if(!in_array($status,array(3,4))){$correctcontent=1;}
						break;
					case 3:
						if($status==1){$correctcontent=1;}
						break;
				}
			}
			return $correctcontent;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			$answer=NULL;$point=0;$reply=NULL;
			if(isset($output['answer-'.$entityid])){
				$reply=$output['answer-'.$entityid];
				$correct=3;
				if(!empty($entitymeta['a'])){
					$matchingmode=$entitymeta['matchingmode'];
					$correct=0;
					$casesensitivity=$entitymeta['casesensitivity'];
					foreach($entitymeta['a']['id'] as $answerid){
						$answerdesc=$entitymeta['a'][$answerid]['desc'];
						$compval=strcasecmp($reply,$answerdesc);
						switch($matchingmode){
							case 1:
								if($compval >= 0){
									if(empty($casesensitivity)){
										if(!empty($reply) && stristr($reply,$answerdesc)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}else{
										if(!empty($reply) && strstr($reply,$answerdesc)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}
								}else{
									if(empty($casesensitivity)){
										if(!empty($reply) && stristr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}else{
										if(!empty($reply) && strstr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}
								}
								break;
							case 2:
								if($compval >=0){
									if(empty($casesensitivity)){
										if(!empty($reply) && stristr($reply,$answerdesc)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}else{
										if(!empty($reply) && strstr($reply,$answerdesc)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}
								}
								break;
							case 3:
								if($compval <= 0){
									if(empty($casesensitivity)){
										if(!empty($reply) && stristr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}else{
										if(!empty($reply) && strstr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}
								}
								break;
							case 4:
								if($compval==0){
									if(empty($casesensitivity)){
										if(!empty($reply) && stristr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}else{
										if(!empty($reply) && strstr($answerdesc,$reply)){
											$point+=$entitymeta['p'][$answerid];
											$correct=1;
										}
									}
								}
								break;
							}
						}
						$pointscored+=$point;
					}
				}else{$correct=4;}
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
			$content.='</p>';
			if(($prevquestdet)&&(empty($rejectprev))){$reply=$prevquestdet->reply;}else{$reply='';}
			$content.='<textarea name="answer-'.$questionid.'">'.$reply.'</textarea></div>';
			return $content;		
		}
	}
}
/* EOF */