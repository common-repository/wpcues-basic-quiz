<?php 
 if(!class_exists('WpCueBasicMultiplecor'))
{
    class WpCueBasicMultiplecor extends WpCueBasicQuestion
    {
		protected function save_questiondata($questmeta,$output,$questionstatus){
			$questmeta=$this->save_answertabdata(1,'a',$output,$questmeta,$questionstatus);
			$coransarr=array();
			$totalpoint=0;$questmeta['p']=array();
			foreach($output['1answerid'] as $key=>$value){
				$answerid=$questmeta['a']['id'][$key];
				if((!(empty($output['coranswer'])))&& (in_array($output['1tabids'][$key],$output['coranswer']))){
					array_push($coransarr,$answerid);
					$questmeta['p'][$answerid]=$output['points-'.$output['1tabids'][$key]];
					$totalpoint+=$questmeta['p'][$answerid];
				}
			}
			if(empty($questmeta['p'])){$questmeta['p']=0;}
			$questmeta['c']=$coransarr;
			$questmeta['totalpoint']=$totalpoint;
			if(!empty($questmeta['c'])){
				if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
			}else{$questmeta['partialpoint']=0;}
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
					if((!(empty($questmeta['c'])))&&(in_array($answerid,$questmeta['c']))){$point=$questmeta['p'][$answerid];$corstatus=1;}
				}
				$this->answer_form($i,$answerid,$ansdesc,$status,$point,$corstatus);
			}
			echo '</div></div>';
		}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){
			echo "<div id='".$boxstatus."answereditortab-".$i."'><div class='answerclosetools'><div class='button answer_close_button'>X</div></div>";
				wp_editor($ansdesc,$boxstatus.'answeredittab-'.$answerid,array('wpautop'=>false,'default_editor'=>'tinymce','textarea_rows'=>40,'editor_height'=>40,'quicktags'=>true,'dfw'=>true,'media_buttons'=>true));
				echo '<input type="hidden" name="'.$boxstatus.'answerid[]" value="'.$answerid.'">';
				echo '<input type="hidden" name="'.$boxstatus.'tabids[]" value="'.$i.'">';
				echo '<div id="multiplechoicebox-<?php echo $i;?>" class="questdepbox multiplechoicebox"><table class="widefat fixed">';
				echo '<tr><td>'.__('Points','wpcues-basic-quiz').'</td><td><input type="text" name="points-'.$i.'" class="questionpoint" value="'.$point.'"></td></tr>';
				echo '<tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td><td><input type="checkbox" name="coranswer[]"  class="questioncorrectanswer" value="'.$i.'" ';
				if(!(empty($corstatus))){echo 'checked ';}
				echo '/></td></tr></table></div>';
				echo '</div>';
		
		}
		public function get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber){
				$report='';
				$report='<div class="rowquest"><div class="questnumber';
				if(empty($showquestsymbol)){$report.=' hiddendiv';}
				$report.='">';
				if(!empty($questionsymbol)){
					$report.='<span class="questionsymbol">'.$questionsymbol.'</span>';
				}
				if(!empty($showquestnumber)){$report.=$questnum;}
				$report.='</div><div class="mainquest"><div class="questdesc"> '.$questmeta['desc'].'</div><div class="answerdesc"><ul class="reportans multiplerepans">';
				foreach($answer as $key=>$answerid){
					$class=$this->get_class($discloseans,$answerid,$reply,$status,$questmeta['c']);
					$report.='<li ';
					$report.='class="'.$class.'"';
					$report.= '>'.$questmeta['a'][$answerid]['desc'].'</li>';
				}
				$report.='</ul></div></div></div>';
				return $report;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$fontcolor){
			$report='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
			foreach($answer as $key=>$answerid){
				$class=$this->get_class($discloseans,$answerid,$reply,$status,$questmeta['c']);
				$report.='<tr><td></td><td><table><tr><td>';
				if(!empty($class)){$report.='<font color="'.$fontcolor[$class].'">';}
				$report.='<table><tr><td>'.($key+1).'. </td><td>'.$questmeta['a'][$answerid]['desc'];
				$report.='</td>';
				if($class=='correctansreplied'){
					$report.= '<td><img src="'.WPCUES_BASICQUIZ_URL.'/public/img/correctans.png"></td>';
				}elseif($class=='wrongansreplied'){
					$report.= '<td><img src="'.WPCUES_BASICQUIZ_URL.'/public/img/wrongans.png"></td>';
				}else{$report.='<td></td>';}
				$report.= '</tr></table>';
				if(!empty($class)){$report.='</font>';}
				$report.='</td></tr></table></td></tr>';
			}	
			return $report;
		}
		private function get_class($discloseans,$answerid,$reply,$status,$correctanswer){
			$class='';
			switch($discloseans){
				case 0:
					if(($status != 4)&&(in_array($answerid,$reply))){$class="ansreplied";}
					break;
				case 1:
					if($status==4){
						if(!empty($correctanswer) && (in_array($answerid,$correctanswer))){$class="correctans";}
					}elseif($status==3){
						if(in_array($answerid,$reply)){$class="ansreplied";}
					}else{
						if(in_array($answerid,$reply)){
							if(in_array($answerid,$correctanswer)){$class="correctansreplied";}else{$class="wrongansreplied";}
						}elseif(in_array($answerid,$correctanswer)){$class="correctans";}
					}	
					break;
				case 2:
					if($status !=4){
						if(in_array($answerid,$reply)){
							if($status==3){$class="ansreplied";}else{
								if(in_array($answerid,$correctanswer)){
									$class="correctansreplied";
								}else{$class="wrongansreplied";}
							}
						}elseif(!empty($correctanswer) && (in_array($answerid,$correctanswer))){$class="correctans";}
					}
					break;
				case 3:
					if(($status !=4) && in_array($answerid,$reply)){if($status==1){$class="correctansreplied";}elseif($status !=4){$class="ansreplied";}}
					break;
			}
			return $class;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			if(!(empty($output['answer-'.$entityid]))){
				if(!(empty($entitymeta['c']))){
					if(empty($entitymeta['partialpoint'])){
						$misans=array_diff($entitymeta['c'],$output['answer-'.$entityid]);
						if(empty($misans)){
							$point=$entitymeta['totalpoint'];$correct=1;
							$pointscored+=$point;$percent+=100;
						}else{$correct=0;$point=0;}
					}else{
						$answers=array_intersect($output['answer-'.$entityid],$entitymeta['c']);
						if(!(empty($answers))){
							$entperc=(count($answers)/count($entitymeta['c']))*100;
							$percent+=$entperc;
							foreach($answers as $answer){$point+=$entitymeta['p'][$answer];}
									$pointscored+=$point;
									if(count($answers) == count($entitymeta['c'])){$correct=1;}else{$correct=2;}
						}else{$correct=0;}
					}
				}else{$point=0;$correct=3;}
				$reply=serialize($output['answer-'.$entityid]);
			}else{$reply=NULL;$point=0;$correct=4;}
			$answer=$output['allanswer-'.$entityid];
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
			$content.='</p></div><div class="answerdesc"><ul class="standardquestion" style="list-style-type:none;">';
			if(($prevquestdet)&&(empty($rejectprev))){$answerids=maybe_unserialize($prevquestdet->answer);$reply=maybe_unserialize($prevquestdet->reply);}
			else{$answerids=$questmeta['a']['id'];
				if(!(empty($randomans))){shuffle($answerids);}
			}
			foreach($answerids as $answer){
				$content.='<li>';
				$content.='<input type="checkbox" name="answer-'.$questionid.'[]" value="'.$answer.'" ';
				if(($prevquestdet)&&(empty($rejectprev)) && (!(empty($reply))) && (in_array($answer,$reply))){
					$content.='checked';}
				$content.='>';
				$content.=$questmeta['a'][$answer]['desc'].'</li>';
			}
			$content.='</ul></div>';
			$content.="<input type='hidden' name='allanswer-".$questionid."' value='".serialize($answerids)."'/>";
			return $content;
		}
	}
}
/* EOF */