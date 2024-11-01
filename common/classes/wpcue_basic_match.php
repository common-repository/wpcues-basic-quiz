<?php 
 if(!class_exists('WpCueBasicMatch'))
{
    class WpCueBasicMatch extends WpCueBasicQuestion
    {
		protected function save_questiondata($questmeta,$output,$questionstatus){
			$questmeta=$this->save_answertabdata(1,'la',$output,$questmeta,$questionstatus);
			$questmeta=$this->save_answertabdata(2,'ra',$output,$questmeta,$questionstatus);
			if(!(empty($output['coranswer']))){
				if(!function_exists('str_getcsv')) {
					$questmeta['c']=stripslashes($output['coranswer']);
					$coranswer=explode('"',$questmeta['c']);
					$coranswer=array_diff(array_filter($coranswer),array(','));
				}else{
					$questmeta['c']=$output['coranswer'];
					$coranswer=str_getcsv($questmeta['c']);
				}
				foreach($coranswer as $answergrp){
					$ans=explode(',',$answergrp);
					$questmeta['coranswer'][$questmeta['la']['id'][intval($ans[0])-1]]=$questmeta['ra']['id'][intval($ans[1])-1];
				}
				$questmeta['p']=$output['points'];
				if(!(empty($output['partialpoint']))){$questmeta['partialpoint']=$output['partialpoint'];}else{$questmeta['partialpoint']=0;}
			}else{$questmeta['p']=0;$questmeta['partialpoint']=0;}
			if(!empty($output['markinputbox'])){$questmeta['markinputbox']=1;}
			if(!empty($output['matchhelpmsg'])){$questmeta['matchhelpmsg']=1;}
			return $questmeta;
			
		}
		protected function addinitial_main($questmeta){
			if(!empty($questmeta['partialpoint'])){$partialpoint=$questmeta['partialpoint'];}else{$partialpoint=0;}
			for($l=1;$l<=2;$l++){$j=2;
			if($l==1){
				echo "<ul id='matchquestion' class='innertabs'>";
				echo "<li><a href='#answersbox-1'>".__('Left Column','wpcues-basic-quiz')."</a></li><li><a href='#answersbox-2'>".__('Right Column','wpcues-basic-quiz')."</a></li></ul>";
				if(!(empty($questmeta))){$j=count($questmeta['la']['id']);}
			}elseif(!(empty($questmeta))){$j=count($questmeta['ra']['id']);}
			echo '<div id="answersbox-'.$l.'" class="innertabcontent"><div class="answeraddtools">';
			echo '<div class="button add_answer_button">'.__('Add Answer','wpcues-basic-quiz').'</div></div><div id="answersboxtab-'.$l.'">';
			echo '<ul class="innernumtabs">';
			for($i=1;$i<=$j;$i++){
				echo '<li class="activetab"><a href="#'.$l.'answereditortab-'.$i.'">'.$i.'</a></li>';
			} 
			echo '</ul>';
			for($i=1;$i<=$j;$i++){
				$point=0;$corstatus=0;$answerid=$i;$ansdesc='';
				if(!(empty($questmeta))){
					$k=$i-1;
					if($l==1){
						$answerid=$questmeta['la']['id'][$k];$ansdesc=$questmeta['la'][$answerid]['desc'];
					}else{$answerid=$questmeta['ra']['id'][$k];$ansdesc=$questmeta['ra'][$answerid]['desc'];}
				}
				$this->answer_form($i,$answerid,$ansdesc,$l,$point,$corstatus);
			}
			echo '</div></div>';
			}
			if(!(empty($questmeta['p']))){$point=$questmeta['p'];}else{$point=0;}
			if(!(empty($questmeta['c']))){$coranswer=$questmeta['c'];}else{$coranswer=array();}
			if(empty($coranswer)){$coranswer='';$disabledstatus=1;}else{$disabledstatus=0;}
			echo '<div id="matchquestionbox" class="questdepbox"><table class="widefat fixed"><tr><td>'.__('Points','wpcues-basic-quiz').'</td>';
			echo '<td><input type="text" name="points" class="questionpoint" value="'.$point.'"></td></tr><tr><td>'.__('Correct Answer','wpcues-basic-quiz').' :</td>';
			echo "<td><input type='text' name='coranswer' value='".$coranswer."'  class='questioncorrectanswer' /></td></tr>";
			echo '<tr><td colspan="2"><div class="entitymsg">'.__('Please enter whatever your correct matching order is, as "1,1","2,3","3,4","4,2".','wpcues-basic-quiz').'</div></td></tr>';
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
			$report.='</div><div class="mainquest"><div class="questdesc"><p>'.$questmeta['desc'].'</p>';
			if($answer['column']=='leftcolumn'){$report.='<p class="entitymsg">'.__('Sort column A to match to column B entries','wpcues-basic-quiz').'</p>';}else{
				$report.='<p class="entitymsg">'.__('Sort column B to match to column A entries','wpcues-basic-quiz').'</p>';
			}
			$report.='</div><div class="answerdesc">';
			$report.='<div class="leftcolumnquest"><p>Column A</p><ul class="matchleftans">';
			foreach($answer['la'] as $key=>$answerid){
				$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
			}
			$report.='</ul></div><div class="rightcolumnquest"><p>Column B</p><ul class="matchrightans">';
			foreach($answer['ra'] as $key=>$answerid){
				$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
			}
			$report.='</ul></div>';
			if($status != 4){
				$report.='<div class="matchrepans"><p class="repliedanstext">Replied Answer</p]><ul class="matchrepans">';
				foreach($reply as $key=>$answerid){
					if($answer['column']=='rightcolumn'){
						$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
					}else{
						$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
					}
							
				}
				$report.='</ul></div>';
			}
			if(!empty($discloseans) && (!(empty($questmeta['coranswer'])))){
				$disstatus=1;
				if(($discloseans==3)&&($status !=1)){$disstatus=0;}
				if(!empty($disstatus)){
					$report.='<div class="matchcorans"><p class="correctanstext">Correct Answer</p><ul class="matchcorans">';
					if($answer['column']=='rightcolumn'){$answerids=array_keys(array_flip($questmeta['coranswer']));
					}else{$answerids=array_keys($questmeta['coranswer']);}
					foreach($answerids as $key=>$answerid){
						if($answer['column']=='rightcolumn'){
							$report.='<li>'.$questmeta['ra'][$answerid]['desc'].'</li>';
						}else{
							$report.='<li>'.$questmeta['la'][$answerid]['desc'].'</li>';
						}
								
					}
				}
				$report.='</ul></div>';
							
						
			}
			$report.='</div></div>';
			return $report;
		}
		public function get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$fontcolor){
			$report='<tr><td>Q. '.$questnum.'</td><td>'.$questmeta['desc'].'</td></tr>';
			foreach($answer['la'] as $key=>$answerid){
				$report.='<tr><td></td><td><table><tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr></table></d></tr>';
			}	
			foreach($answer['ra'] as $key=>$answerid){
				$report.='<tr><td></td><td><table><tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr></table></d></tr>';
			}	
			if($status != 4){
				$report.='<tr><td></td><td><table><tr><td>Replied Answer</td><td></td></tr>';
				foreach($reply as $key=>$answerid){
					if($answer['column']=='rightcolumn'){
						$report.='<tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr>';
					}else{
						$report.='<tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr>';
					}
				}
				$report.='</table></td></tr>';
			}
			if(!empty($discloseans) && (!(empty($questmeta['coranswer'])))){
				$disstatus=1;
				if(($discloseans==3)&&($status !=1)){$disstatus=0;}
				if(!empty($disstatus)){
					$report.='<tr><td></td><td><table><tr><td>Correct Answer</td><td></td></tr>';
					if($answer['column']=='rightcolumn'){$answerids=array_keys(array_flip($questmeta['coranswer']));
					}else{$answerids=array_keys($questmeta['coranswer']);}
					foreach($answerids as $key=>$answerid){
						if($answer['column']=='rightcolumn'){
							$report.='<tr><td>'.$key.'</td><td>'.$questmeta['ra'][$answerid]['desc'].'</td></tr>';
						}else{
							$report.='<tr><td>'.$key.'</td><td>'.$questmeta['la'][$answerid]['desc'].'</td></tr>';
						}
					}
				}
				$report.='</table></td></tr>';
			}		
			return $report;
		}
		public function get_questionresult($output,$instanceid,$entityid,$entitymeta,$percent,$pointscored,$disabled){
			$column=$output['colmatchquestion-'.$entityid];
			$matchcount=$output['matchcount-'.$entityid];
			$answerar['la']=unserialize(stripslashes($output['lanswerids-'.$entityid]));
			$answerar['ra']=unserialize(stripslashes($output['ranswerids-'.$entityid]));
			if(!(empty($output['markmatchquestion']))){
				if($column=='rightcolumn'){
					$reply=$rightarray=$output['ranswer-'.$entityid];
					$leftarray=array_slice($answerar['la'],0,$matchcount );
				}else{
					$reply=$leftarray=$output['lanswer-'.$entityid];
					$rightarray=array_slice($answerar['ra'],0,$matchcount );
				}
				$combreply=array_combine($leftarray,$rightarray);
				if(!(empty($entitymeta['coranswer']))){
					$misans=array_diff_assoc($entitymeta['coranswer'],$combreply);
					if($entitymeta['partialpoint']==0){
						if(empty($misans)){
							$point=$entitymeta['p'];$percent+=100;$pointscored+=$point;
							$correct=1;
						}else{$correct=0;$point=0;}
					}else{
						$cor=(count($entitymeta['coranswer'])-count($misans))/count($entitymeta['coranswer']);
						$point=$cor*$entitymeta['p'];$pointscored+=$point;$percent+=$cor*100;
						if($cor==count($entitymeta['coranswer'])){$correct=1;}elseif($cor !=0 ){$correct=2;}else{$correct=0;}
					}	
				}else{$point=0;$correct=3;}
				$reply=serialize($reply);
			}else{$reply=NULL;$point=0;$correct=4;}
			$answerar['column']=$column;
			$answerar['count']=$matchcount;
			$answer=serialize($answerar);
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
			$stat=0;
			if(($prevquestdet)&&(empty($rejectprev))){
				$answer=maybe_unserialize($prevquestdet->answer);$reply=maybe_unserialize($prevquestdet->reply);
				$lanswerids=$origlanswerids=$answer['la'];$ranswerids=$origranswerids=$answer['ra'];
				$column=$answer['column'];$count=$answer['count'];
				if(!empty($reply)){
					if($column=='rightcolumn'){
						$stat=1;$lanswerids=$origlanswerids;$ranswerids=$reply;
					}else{
						$lanswerids=$reply;$ranswerids=$origranswerids;
					}
				}
			}else{
				$column='';$count=0;
				$leftcount=count($questmeta['la']['id']);$rightcount=count($questmeta['ra']['id']);
				$origlanswerids=$lanswerids=$questmeta['la']['id'];$origranswerids=$ranswerids=$questmeta['ra']['id'];
				if(!(empty($randomans))){shuffle($lanswerids);shuffle($ranswerids);}
				if($leftcount <= $rightcount){$stat=1;$count=$leftcount;$column='rightcolumn';}else{$count=$rightcount;$column='leftcolumn';}
			}
			$content.='</p>';
			if($stat==0){$content.='<p class="entitymsg">'.__('Sort column A to match to column B entries','wpcues-basic-quiz').'</p></div>';}else{
				$content.='<p class="entitymsg">'.__('Sort column B to match to column A entries','wpcues-basic-quiz').'</p></div>';
			}
			$content.='<div class="answerdesc"><div class="leftcolumnquest">';
			$content.='<p>Column A </p><ul class="leftmatchquestion ';
			if($stat==0){$content.='matchquestion" id="matchquestion-'.$questionid.'"';}
			$content.=' style="list-style-type:none;">';
			foreach($lanswerids as $answer){
				$content.='<li id="answer-'.$questionid.'-'.$answer.'" ';
				if($stat==0){$content.=' class="ui-state-default"';}
				$content.='><input type="hidden" name="lanswer-'.$questionid.'[]" value="'.$answer.'">';
				$content.=$questmeta['la'][$answer]['desc'].'</li>';
			}
			$content.='</ul></div><div class="rightcolumnquest">';
			$content.='<p>Column B </p><ul class="rightmatchquestion ';
			if($stat==1){$content.='matchquestion" id="matchquestion-'.$questionid.'"';}
			$content.=' style="list-style-type:none;">';
			foreach($ranswerids as $answer){
				$content.='<li id="answer-'.$questionid.'-'.$answer.'" ';
				if($stat==1){$content.=' class="ui-state-default"';}
				$content.='><input type="hidden" name="ranswer-'.$questionid.'[]" value="'.$answer.'">';
				$content.=$questmeta['ra'][$answer]['desc'].'</li>';
			}
			$content.='</ul></div>';
			$content.='<input type="checkbox" name="markmatchquestion" value="1"';
			if(!(empty($reply))){$content.=' checked';}
			if($disabled){$content=' disabled';}
			$content.='> Mark as Correct';
			$content.='<input type="hidden" name= "colmatchquestion-'.$questionid.'" value="'.$column.'">';
			$content.='<input type="hidden" name="matchcount-'.$questionid.'" value="'.$count.'">';
			$content.="<input type='hidden' name='lanswerids-".$questionid."' value='".serialize($origlanswerids)."'>";
			$content.="<input type='hidden' name='ranswerids-".$questionid."' value='".serialize($origranswerids)."'>";
			$content.='</div>';
			return $content;	
		}
	}
}
/* EOF */