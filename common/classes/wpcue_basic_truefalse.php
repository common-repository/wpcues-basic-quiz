<?php 
 if(!class_exists('WpCueBasicTruefalse'))
{
    class WpCueBasicTruefalse extends WpCueBasicQuestion
    {
		protected function save_questiondata($questmeta,$output,$questionstatus){
			if(isset($output['coranswer'])){
				$questmeta['c']=$output['coranswer'];
				$questmeta['p']=$output['points'];
			}else{$questmeta['p']=0;}
			return $questmeta;			
		}
		protected function addinitial_main($questmeta){
			if(!isset($questmeta['c'])){$coranswer=-2;
			}else{$coranswer=$questmeta['c'];}
			if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
			echo '<div id="truefalsebox" class="questdepbox ';
			echo '">';
			echo '<table class="widefat fixed">';
			echo '<tr><td style="width:30%;">';_e('Correct Answer','wpcues-basic-quiz');echo ' : </td>';
			echo '<td style="width:70%;">';_e('True','wpcues-basic-quiz');echo ' <input type="radio" name="coranswer" value="1"  class="questioncorrectanswer"  ';
			if($coranswer==1){echo 'checked';}
			echo '>';
			_e('False','wpcues-basic-quiz');
			echo '<input type="radio" name="coranswer" value="0" class="questioncorrectanswer" ';
			if($coranswer==0){echo 'checked';}
			echo '></td></tr><tr><td style="width:30%;">';
			_e('Points','wpcues-basic-quiz');echo ' : </td><td style="width:70%;">';
			echo '<input type="text" name="points" value="'.$point.'" class="questionpoint"></td></tr></table></div>';
		}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){}
		public function get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber){
			$report='<div class="rowquest"><div class="questnumber';
			if(empty($showquestsymbol)){$report.=' hiddendiv';}
			$report.='">';
			if(!empty($questionsymbol)){
				$report.='<span class="questionsymbol">'.$questionsymbol.'</span>';
			}
			if(!empty($showquestnumber)){$report.=$questnum;}
			$report.='</div><div class="mainquest"><div class="questdesc">'.$questmeta['desc'].'</div><div class="answerdesc"><ul class="reportans truefalserepans">';
			foreach($answer as $key=>$answerid){
				$class='';
				if($answerid==1){$ansdesc='True';}else{$ansdesc='False';}
				$class=$this->get_class($discloseans,$reply,$answerid,$questmeta['c'],$status);
				$report.='<li ';
				$report.='class="'.$class.'"';
				$report.= '>'.$ansdesc;
				$report.= '</li>';
			}
			$report.='</ul></div></div></div>';
			return $report;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$fontcolor){
			$report.='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
			foreach($answer as $key=>$answerid){
				$class='';
				if($answerid==1){$ansdesc='True';}else{$ansdesc='False';}
				$class=$this->get_class($discloseans,$reply,$answerid,$correctanswer,$status);
				$report.='<tr><td></td><td><table><tr><td> ';
				if(!empty($class)){$report.='<font color="'.$fontcolor[$class].'">';}
				$report.= '<table><tr><td>'.($key+1).'. </td><td>'.$ansdesc.'</td>';
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
		private function get_class($discloseans,$reply,$answerid,$correctanswer,$status){
			$class='';
			switch($discloseans){
				case 0:
					if(($status != 4)&&($reply==$answerid)){$class="ansreplied";}
					break;
				case 1:
					switch($status){
						case 0:
							if($answerid==$reply){$class="wrongansreplied";}elseif($answerid==$correctanswer){$class="correctans";}
							break;
						case 1:
							if($answerid==$reply){$class="correctansreplied";}
							break;
						case 3:
							if($answerid==$reply){$class="ansreplied";}
							break;
						case 4:
							if(!empty($correctanswer) && ($answerid==$correctanswer)){$class="correctans";}
							break;
					}
								break;
				case 2:
					if($status !=4){
						if($answerid==$reply){
							switch($status){
								case 0:
									$class="wrongansreplied";
									break;
								case 1:
									$class="correctansreplied";
									break;
								case 3:
									$class="ansreplied";
									break;
							}
						}elseif(!empty($correctanswer) && ($answerid==$correctanswer)){$class="correctans";}
					}
					break;
				case 3:
					if($answerid==$reply){
						if($status==1){$class="correctansreplied";}elseif($status !=4){$class="ansreplied";}
					}
					break;
									
			}
			return $class;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			if(isset($output['answer-'.$entityid])){
				if(isset($entitymeta['c'])){
					if($output['answer-'.$entityid]==$entitymeta['c']){
						$point=$entitymeta['p'];$correct=1;$pointscored+=$point;$percent+=100;
					}else{$correct=0;$point=0;}
				}else{$point=0;$correct=3;}
				$reply=$output['answer-'.$entityid];
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
			$content.='</p><div class="answerdesc"><ul class="truefalsequestion" style="list-style-type:none">';
			if(($prevquestdet)&&(empty($rejectprev))){$answerids=maybe_unserialize($prevquestdet->answer);$reply=$prevquestdet->reply;}
			else{$answerids=array(0,1);
			if(!(empty($randomans))){shuffle($answerids);}}
			foreach($answerids as $answer){
				$content.='<li><input type="radio" name="answer-'.$questionid.'" value="'.$answer.'"';
				if((!(empty($reply))) && ($answer==$prevquestdet->reply)){
					$content.='checked';
				}
				if($answer==1){$ansdesc='True';}else{$ansdesc='False';}
				$content.='> '.$ansdesc.'</li>';
			}
			$content.='</ul></div>';
			$content.="<input type='hidden' name='answerids-".$questionid."' value='".serialize($answerids)."'/></div>";
			return $content;			
		}
	}
}
/* EOF */