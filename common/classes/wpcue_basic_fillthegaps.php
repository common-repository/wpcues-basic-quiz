<?php 
 if(!class_exists('WpCueBasicFillgaps'))
{
    class WpCueBasicFillgaps extends WpCueBasicQuestion
    {
		private $phpversion;
		protected function init(){
			$this->phpversion=version_compare(phpversion(),'5.3.0');
		}
		protected function save_questiondata($questmeta,$output,$questionstatus){
			preg_match_all('/\{\{\{(.*)\}\}\}/U',$questmeta['desc'],$matches);
			$questmeta['c']=$matches[1];
			if(!(empty($questmeta['c']))){
				$questmeta['p']=$output['points'];
				if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
			}else{$questmeta['p']=0;$questmeta['partialpoint']=0;}
			return $questmeta;
		}
		protected function addinitial_main($questmeta){
			if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
			if(!empty($questmeta['partialpoint'])){$partialpoint=$questmeta['partialpoint'];}else{$partialpoint=0;}
			echo '<div id="fillgapsbox" class="questdepbox"><table class="widefat fixed"><tr><td style="width:12%">';
			_e('Points','wpcues-basic-quiz');echo ' : </td>';
			echo '<td style="width:88%"><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr>';
			echo '<td colspan="2"><input type="checkbox" name="partialpoint" class="partialpoint" value="1"';
			if($partialpoint == 1){echo 'checked';}
			echo '>';
			_e('Award weighted point to partial correct answers','wpcues-basic-quiz');
			echo '</td></tr>';
			echo '</table></div>';
		}
		public function answer_form($i,$answerid,$ansdesc,$boxstatus,$point,$corstatus){}
		public function get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber){
			$disstatus=$this->get_disstatus($discloseans,$questmeta['c']);
			if($status != 4){
				$i=0;$replacements=array();
				foreach($reply as $answer){
					$replacements[$i]='<span class="fillgapsrep';
					if(!(empty($disstatus))){
							if(in_array($answer,$questmeta['c'])){$replacements[$i].= ' correctansreplied';}else{$replacements[$i].= ' wrongansreplied';}
					}
					$replacements[$i].='">'.$answer.'</span>';
					$i++;
				}
				$repcontent=$this->get_gapcontent($replacements,$questmeta['desc']);			
					
			}
			unset($replacements);
			if(!(empty($disstatus))){
				foreach($questmeta['c'] as $answer){
					$replacements[$i]='<span class="fillgapscorrect">'.$answer.'</span>';
					$i++;
				}
				$correctcontent=$this->get_gapcontent($replacements,$questmeta['desc']);			
			}
			$questdesc=preg_replace('/\{\{\{(.*)\}\}\}/U','.......', $questmeta['desc']);
			$report='<div class="rowquest"><div class="questnumber';
			if(empty($showquestsymbol)){$report.=' hiddendiv';}
				$report.='">';
				if(!empty($questionsymbol)){
					$report.='<span class="questionsymbol">'.$questionsymbol.'</span>';
				}
				if(!empty($showquestnumber)){$report.=$questnum;}
			$report.='</div><div class="mainquest"><div class="questdesc">';
			$report.=$questdesc.'</div><div class="answerdesc"><ul class="reportans gapsrepans">';
			if(!(empty($repcontent))){$report.='<li class="repgaplist">Replied Answer : '.$repcontent.'</li>';}
			if(!(empty($correctcontent))){$report.='<li class="correctgaplist">Correct Answer : '.$correctcontent.'</li>';}
			$report.='</ul></div></div></div>';
			return $report;			
		}
		private function get_disstatus($discloseans,$correctanswer=false){
			if(!(empty($discloseans)) && (!(empty($correctanswer)))){
				$i=0;$disstatus=1;
				if(($discloseans==3)&&($status !=1)){$disstatus=0;}
			}else{$disstatus=0;}
			return $disstatus;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$fontcolor){
			if($status != 4){
				$i=0;$replacements=array();
				foreach($reply as $answer){
					$replacements[$i]='<span style="border-bottom:1px dashed #000000;"';
					$replacements[$i].='">'.$answer;
					if((!(empty($show))) && (!empty($emailprocess))){
						if(in_array($answer,$questmeta['c'])){$replacements[$i].= '<img src="'.WPCUES_BASICQUIZ_URL.'/public/img/correctans.png">';
						}else{$replacements[$i].= '<img src="'.WPCUES_BASICQUIZ_URL.'/public/img/wrongtans.png">';}
					}
					$replacements[$i].='</span>';
					$i++;
				}
				$repcontent=$this->get_gapcontent($replacements,$questmeta['desc']);			
			}
			if(!(empty($discloseans)) && (!(empty($questmeta['c'])))){
				$i=0;$disstatus=1;
				if(($discloseans==3)&&($status !=1)){$disstatus=0;}
				if(!empty($disstatus)){
					foreach($questmeta['c'] as $answer){
						$replacements[$i]='<span style="border-bottom:1px dashed #000000;">'.$answer.'</span>';
						$i++;
					}
					$correctcontent=$this->get_gapcontent($replacements,$questmeta['desc']);			
				}
			}
			$questdesc=preg_replace('/\{\{\{(.*)\}\}\}/U','.......', $questmeta['desc']);
			$report='<tr><td>Q. '.$questnum.'</td><td>'.$questdesc.'</td></tr>';
			if(!(empty($repcontent))){$report.='<tr><td></td><td><table><tr><td>Replied Answer : </td><td>'.$repcontent.'</td></tr></table></td></tr>';}
			if(!(empty($correctcontent))){$report.='<tr><td></td><td><table><tr><td>Correct Answer : </td><td>'.$correctcontent.'</td></tr></table></td></tr>';}
			return $report;
		}
		private function get_gapcontent($replacements,$questiondesc){
			if($this->phpversion < 0){
				$this->replacements=$replacements;
				$content=preg_replace_callback('/\{\{\{(.*)\}\}\}/U',array(&$this,'replace_matches'), $questiondesc);	
			}else{
				$kawasaki=$questiondesc;
				include(sprintf("%s/trial.php", dirname(__FILE__)));
				$content=$cont;
			}
			return $content;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			ob_start();
			$answerid=$output['answer-'.$entityid];
			$trimmedanswerid=array_map('trim',$answerid);
			$answerid = array_filter($answerid,'strlen');
			if(!(empty($output['answer-'.$entityid])) && (!empty($answerid))){
				if(!(empty($entitymeta['c']))){
					$result = array_udiff_assoc($entitymeta['c'],$output['answer-'.$entityid], 'strcasecmp');
					if($entitymeta['partialpoint']==0){
						if(empty($result)){$point=$entitymeta['p'];$correct=1;$pointscored+=$point;$percent+=100;}else{$point=0;$correct=0;}
					}else{
						$cor=(count($entitymeta['c'])-count($result))/count($entitymeta['c']);
						$point=$cor*$entitymeta['p'];$pointscored+=$point;$percent+=$cor*100;
						if($cor == count($entitymeta['c'])){$correct=1;}elseif($cor != 0){$correct=2;}else{$correct=0;}
					}
				}else{$point=0;$correct=3;}
				$reply=serialize($trimmedanswerid);
			}else{$reply=NULL;$point=0;$correct=4;}
			if(!(empty($entitymeta['c']))){$answer=serialize($entitymeta['c']);}else{$answer=NULL;}
			$resultmeta=array('answer'=>$answer,'reply'=>$reply,'correct'=>$correct,'point'=>$point,'pointscored'=>$pointscored,'percent'=>$percent);
			echo ob_get_clean();
			return $resultmeta;
		}
		protected function getquest_det($questmeta,$questionid,$randomans,$prevquestdet = false,$rejectprev=false){
			$content='';$disabled=0;
			if($prevquestdet && (!empty($prevquestdet->disabled))){$disabled=1;}
			$content.='<div class="questdesc"><p>';
			if($questmeta['t'] !=5){$content.=$questmeta['desc'];}
			$content.="<input type='hidden' name='disablestatus-".$questionid."' class='disablestatus' value='".$disabled."'>";
			$content.="<input type='hidden' name='questiontype-".$questionid."' value='".$questmeta['t']."'>";
			if(($prevquestdet)&&(empty($rejectprev)) && (!(empty($prevquestdet->reply)))){
				$i=0;$replacements=array();$answerids=maybe_unserialize($prevquestdet->reply);
				foreach($answerids as $answer){
					$replacements[$i]='<input type="text" name="answer-'.$questionid.'[]" class="answrfillthegaps" value="'.$answer.'"';
					if($disabled){$replacements[$i].=' disabled';}$replacements[$i].='>';
					$i++;
				}
				
				$content.=$this->get_gapcontent($replacements,$questmeta['desc']).'</p></div>';
			}else{$replacement='<input type="text" name="answer-'.$questionid.'[]" class="answrfillthegaps">';
			$content.=preg_replace('/\{\{\{(.*)\}\}\}/U',$replacement, $questmeta['desc']).'</p></div>';}
			return $content;		
		}
		public function replace_matches($matches){
			return array_shift($this->replacements);
		}
	}
}
/* EOF */