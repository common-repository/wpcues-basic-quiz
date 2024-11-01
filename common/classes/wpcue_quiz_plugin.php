<?php
abstract class WpCueQuiz_Plugin extends  WpCueQuiz_Base{
	protected function init(){
		$this->required_classes();
	}
	private function required_classes(){
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_quiz.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_question.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_singlecor.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_multiplecor.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_match.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_sort.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_fillthegaps.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_truefalse.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_openend.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_questionpost.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_badge.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_section.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_gradegroup.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_leaderboard.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_chart.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_certificate.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_level.php");
		require_once(WPCUES_BASICQUIZ_PATH."/common/classes/wpcue_basic_product.php");
		$WpCueBasicQuiz=new WpCueBasicQuiz($this->_config);
		$WpCueBasicBadge=new WpCueBasicBadge($this->_config);
		$WpCueBasicQuestionPost = new WpCueBasicQuestionPost($this->_config);
		$WpCueBasicCertificate = new WpCueBasicCertificate($this->_config);
		$WpCueBasicLeaderboard= new WpCueBasicLeaderboard($this->_config);
		$WpCueBasicChart= new WpCueBasicChart($this->_config);
		$WpCueBasicSection=new WpCueBasicSection($this->_config);
		$WpCueBasicGradeGroup = new WpCueBasicGradeGroup($this->_config);
		$WpCueBasicLevel= new WpCueBasicLevel($this->_config);
		$WpCueBasicProduct= new WpCueBasicProduct($this->_config);
	}
	protected function wpcue_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$emailprocess,$showquestsymbol,$questionsymbol,$showquestnumber){
		$answer=maybe_unserialize(stripslashes($answer));$reply=maybe_unserialize(stripslashes($reply));
		$report='';
		if(!isset($discloseans)){$discloseans=0;}
		$wpcuequestion=$this->get_questionobject($questmeta['t']);
		if(empty($emailprocess)){
			$report.=$wpcuequestion->get_report($answer,$reply,$status,$questmeta,$discloseans,$questnum,$showquestsymbol,$questionsymbol,$showquestnumber);
			
		}else{
			$fontcolor=array('correctansreplied'=>'#008800','wrongansreplied'=>'#FF0000','ansreplied'=>'#008800','correctans'=>'#008800');
			$report.=$wpcuequestion->get_emailreport($answer,$reply,$status,$questmeta,$discloseans,$questnum,$bgcolor);
			
		}
		return $report;
	}
	protected function quizinfo($quizid){
		global $wpdb;
		$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
		$quizinfo=$wpdb->get_row($wpdb->prepare("select count(id) as totalquestions, sum(point) as totalpoint from $quizinfotable where quizid=%d and parentid != -1",$quizid),ARRAY_A);
		if(empty($quizinfo['totalquestions'])){$quizinfo['totalquestions']=0;}
		if(empty($quizinfo['totalpoint'])){$quizinfo['totalpoint']=0;}
		return $quizinfo;
	}
	protected function get_questionobject($questiontype){
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
		protected function get_mainpage($quizmeta,$postid,$instance,$captchastatus){
			global $wpdb;
			$table_name3=$wpdb->prefix.'wpcuequiz_quizinfo';
			$wpprocuesetting=$this->_config->setting;
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!(empty($quizmeta['randomizsetting']))){$randomizsetting=maybe_unserialize($quizmeta['randomizsetting'][0]);}else{$randomizsetting=array();}
			if(!(empty($quizmeta['displaysetting']))){$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]);}else{$displaysetting=array();}
			if(!(empty($quizmeta['questtools']))){$questtools=maybe_unserialize($quizmeta['questtools'][0]);}else{$questtools=array();}
			$totalquestcount=$wpdb->get_col($wpdb->prepare("select count(*) as counter from $table_name3 where quizid=%d and parentid != -1",$postid));
			$totalquestcount=(int)$totalquestcount[0];
			$entities=$this->get_questions($randomizsetting,$instance,$postid);
			if(!empty($basicsetting['questloaded'])){
				if($totalquestcount > $basicsetting['questloaded']){$totalquestcount=$basicsetting['questloaded'];}
				$entities=array_slice($entities,0,$basicsetting['questloaded']);
			}
			$content='<script>jQuery(document).ready(function($){$(".hiddendiv").hide();});</script>';
			if($instance){
				$table_name2=$wpdb->prefix.'wpcuequiz_quizstat';
				$quizstat=$wpdb->get_row($wpdb->prepare("SELECT timeremaining,UNIX_TIMESTAMP(endtime) as endtime from $table_name2 where instanceid=%d",$instance),ARRAY_A);
			}
			$secent=$wpdb->get_results($wpdb->prepare("select parentid,count(*) as counter from $table_name3 where parentid != -1 and quizid=%d group by parentid",$postid),OBJECT_K);
			if(!(empty($basicsetting['duration']))){
				if($instance){$totalremainingtime=$quizstat['timeremaining'];}else{$totalremainingtime=$basicsetting['duration'];}
				$totaltime=$basicsetting['duration'];
				$content.=$this->get_timerblock($totaltime,$totalremainingtime,$wpprocuesetting['text'],$displaysetting);
			}
			if(!empty($entities)){
				if(!(empty($basicsetting['login'])) && (!(empty($displaysetting['savebuttonstat'])))){$savebuttonstat=1;}else{$savebuttonstat=0;}
				$settings=array('displaysetting'=>$displaysetting,'basicsetting'=>$basicsetting,'captchastatus'=>$captchastatus,
				'text'=>$wpprocuesetting['text'],'randomizsetting'=>$randomizsetting,
				'totalquestcount'=>$totalquestcount,'recaptchakey'=>$wpprocuesetting['recaptcha']['publickey'],'savebuttonstat'=>$savebuttonstat);
				$content.=$this->get_maincontent($entities,$settings,$postid,$totalquestcount,$captchastatus,$savebuttonstat,$instance,$questtools);
			}
			return $content;
		}
		protected function get_timerblock($totaltime,$totalremainingtime,$textsetting,$displaysetting){
			$content='<div id="quiztimetools">';
				if($totaltime >= 3600){
					$hours=floor($totaltime/3600);
					$totaltime=$totaltime-$hours*3600;}
				if($totaltime>=60){	
					$mins=floor($totaltime/60);
					$seconds=$totaltime-$mins*60;
				}else{$mins=0;$seconds=$totaltime;}
				if($totalremainingtime >= 3600){
					$hoursremaining=floor($totalremainingtime/3600);
					$totalremainingtime=$totalremainingtime-$hoursremaining*3600;}
				if($totalremainingtime>=60){	
					$minsremaining=floor($totalremainingtime/60);
					$secondsremaining=$totalremainingtime-$minsremaining*60;
				}else{$minsremaining=0;$secondsremaining=$totalremainingtime;}
				$content.='<div id="quizduration">'.$textsetting['quizduration'];
				if(!(empty($hours))){$content.=$hours.' Hour';}
				if(!(empty($mins))){$content.=$mins.' Minutes';}
				if(!(empty($seconds))){$content.=$seconds.' Seconds';}
				$content.='</div>';
				$content.="<div id='wpcuebasicquiztimercontent'";
				if(empty($displaysetting['timer'])){$content.=" class='hiddendiv'";}
				$content.="><ul><li class='wpcuebasicquiztimerdesc'>".$textsetting['timeleft']."</li><li class='wpcuebasicquiztimerpoint'>:</li>";
				if(isset($hoursremaining)){
				$content.="<li id='wpcuebasicquiztimerhours' class='wpcuebasicquiztimertimeunit'>";
				if($hoursremaining<10){$content.='0'.$hoursremaining;}else{$content.=$hoursremaining;}
				$content.="</li><li class='wpcuebasicquiztimerpoint'>:</li> ";}
				if(isset($minsremaining)){$content.="<li id='wpcuebasicquiztimermins' class='wpcuebasicquiztimertimeunit'>";
				if($minsremaining<10){$content.='0'.$minsremaining;}else{$content.=$minsremaining;}
				$content.="</li><li class='wpcuebasicquiztimerpoint'>:</li>";}
				$content.="<li id='wpcuebasicquiztimersecs' class='wpcuebasicquiztimertimeunit'>";
				if($secondsremaining<10){$content.='0'.$secondsremaining;}else{$content.=$secondsremaining;}
				$content.="</div>";
				$content.="</div>";
				return $content;
		}
		protected function get_maincontent($entities,$settings,$postid,$totalquestcount,$captchastatus,$savebuttonstat,$instance=false,$questtools=false){
			global $wpdb;$entitystat=array();$quizlast=array();$errorstat=array();$quizstat=array();
			if($instance){
				$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
				$tablename3=$wpdb->prefix.'wpcuequiz_quizerrorinfo';$table_name4=$wpdb->prefix.'wpcuequiz_quizinfo';
				$quizlast=$wpdb->get_results($wpdb->prepare("select entityid,answer,reply,point,status,disabled from $table_name1 where instanceid=%d",$instance),OBJECT_K);
				$entitystat=$wpdb->get_results($wpdb->prepare("SELECT entityid,questionchange,UNIX_TIMESTAMP(questionchangedate) as questionchangedate from $table_name4 where quizid=%d",$postid),OBJECT_K);
				$errorstat=$wpdb->get_results($wpdb->prepare("select entityid,errorid from $tablename3 where instanceid=%d",$instance),ARRAY_A);
				$errorids=array();$mappederror=array();
				if(!(empty($errorstat))){
					foreach($errorstat as $errorent){
						$entityid=$errorent['entityid'];$errorid=$errorent['errorid'];
						array_push($errorids,$errorent['errorid']);
						if(empty($mappederror[$entityid])){
							$mappederror[$entityid]=array($errorid);
						}else{array_push($mappederror[$entityid],$errorid);}
					}
				}
				if(!(empty($errorids))){
					$tablename4=$wpdb->prefix.'posts';$errorarr='('.implode(',',$errorids).')';
					$errors=$wpdb->get_results("select ID,post_content,post_title from $tablename4 where ID in $errorarr",OBJECT_K);
				}
			}
			$content='<div id="quizmaincontent"><form name="myfromdata" id="quizpost" autocomplete="off">';
			$content.='<input type="hidden" name="quizid" id="origquizid" value="'.$postid.'" />';
			if(!empty($entities)){
				$args = array( 'post__in'=>$entities,'post_type'=>array('wpcuebasicquestion','wpcuebasicsection'),'orderby'=>'post__in','posts_per_page' => -1);
				$entityquery = new WP_Query($args);
				$curquiz=array();
				$i=0;$pagenum=1;$j=1;$rownum=1;$k=0;$totent=count($entities);
				if($settings['basicsetting']['questperpage']==0){$totpagenum=1;
				}else{$totpagenum=ceil($totalquestcount/$settings['basicsetting']['questperpage']);}
				$section=0;
				while ($entityquery->have_posts()){
					$entityquery->the_post();
					$entitypost=$entityquery->post;
					if($i==0 && $section==0){$content.='<div id="questpage-'.$pagenum.'" class="questpage';
					if($j>1){$content.=' hiddendiv';}$content.='">';}
					if(empty($captchastatus)&&empty($settings['basicsetting']['captchalocation'])&&($j=1)){
							$recaptchasitekey=$recaptchapublic;
							$content.='<div class="rowquest"><div id="recaptchadiv"></div></div>';
						}
					if($entitypost->post_type == 'wpcuebasicsection'){
						$section=1;
						if(!(empty($secent[$entitypost->ID]))){
							$content.='<div id="rowsec-'.$entitypost->ID.'" class="rowsec">'.$entitypost->post_content.'</div>';
							$content.='<input type="hidden" name="questionid[]" value="'.$entitypost->ID.'">';
						}
					
					}else{
						$iterators=array('i'=>$i,'j'=>$j,'rownum'=>$rownum,'pagenum'=>$pagenum);
						$entitymeta=unserialize($entitypost->post_content);
						$wpcuequestion=$this->get_questionobject($entitymeta['t']);
						if(!empty($instance)&&!(empty($quizlast))){
							$content.=$wpcuequestion->get_question($entitypost,$settings,$iterators,$instance,$quizlast[$entitypost->ID],$entitystat,$quizstat);
						}else{
							$content.=$wpcuequestion->get_question($entitypost,$settings,$iterators);
						}
						if(!empty($questtools)){
							if(!empty($instance)){
								$errorids=$mappederror[$entitypost->ID];$erroridsarray=array_fill(0,count($errorids),0);
								$questerrors=array_intersect_key($errors,array_combine($errorids, $erroridsarray));
							}else{$questerrors=array();}
							$content.=$this->question_tools($questtools,$questerrors,$entitymeta,$entitypost->ID);
						}
						if(empty($captchastatus)&&($settings['basicsetting']['captchalocation']==$j)){
							$recaptchasitekey=$recaptchapublic;
							$content.='<div class="rowquest"><div id="recaptchadiv"></div></div>';
						}
						$i++;$rownum++;
						if($settings['basicsetting']['questperpage'] != 0){
							if(($i==$settings['basicsetting']['questperpage']) ||($j==$totalquestcount)) {
								if(empty($settings['displaysetting']['submitbuttonstat'])){$submitbuttonstat=0;}else{$submitbuttonstat=1;}
								$content.=$this->submit_block($i,$j,$totpagenum,$totalquestcount,$pagenum,$settings['text'],$savebuttonstat,1,$submitbuttonstat);
								$i=0;$pagenum++;
								}
						}else{
							if($j == $totalquestcount){
								$content.=$this->submit_block($i,$j,$totpagenum,$totalquestcount,$pagenum,$settings['text'],$savebuttonstat);
							}
						}
						$j++;
					}
					$k++;
				
				}
				wp_reset_postdata();
			}
			$content.='</form></div>';
			return $content;
		}
		public function get_questions($randomizsetting,$instanceid,$quizid){
			global $wpdb;
			ob_start();
			$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			if(!(empty($instanceid))){$status=$wpdb->get_var($wpdb->prepare("select distinct instanceid from $table_name1 where instanceid=%d",$instanceid));if(is_null($status)){$status=0;}}else{$status=0;}
			if(empty($status)){
				$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
				if(!(empty($randomizsetting['randomquest']))){
					if(!(empty($randomizsetting['randomquestcat']))){
						$questcat=$wpdb->get_results($wpdb->prepare("SELECT entityid,category from $table_name where quizid=%d and parentid=0 group by category,entityid",$quizid),ARRAY_N);
						foreach($questcat as $key=>$value){
							$questcatrow=$value;
							if(!(empty($questcats[$questcatrow[1]]))){array_push($questcats[$questcatrow[1]],$questcatrow[0]);}else{$questcats[$questcatrow[1]]=array($questcatrow[0]);}
						}
						$questcateg=array_keys($questcats);$flipcat=array_flip($questcateg);
						$section=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where quizid=%d and parentid=-1",$quizid));
						$entities=array_merge($questcateg,$section);shuffle($entities);
						foreach($questcateg as $category){
							$flipentities=array_flip($entities);
							if(is_array($questcats[$category])){
								$value=$questcats[$category];shuffle($value);
							}else{$value=$questcats[$value];}
							array_splice($entities,$flipentities[$category],1,$value);
						}
					}else{
						$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where quizid=%d and parentid IN (0,-1)",$quizid));
						shuffle($entities);
						$section=$wpdb->get_col($wpdb->prepare("select entityid from $table_name where quizid=%d and parentid=-1",$quizid));
					}
					$secquest=$wpdb->get_results($wpdb->prepare("SELECT parentid,entityid from $table_name where quizid=%d and parentid NOT IN (-1,0) order by parentid asc",$quizid),ARRAY_N);
					foreach($secquest as $key=>$value){
						$secquestrow=$value;
						if(!(empty($secquestion[$secquestrow[0]]))){array_push($secquestion[$secquestrow[0]],$secquestrow[1]);}else{$secquestion[$secquestrow[0]]=array($secquestrow[1]);}
					}
					$flipentities=array_flip($entities);
					foreach($section as $sectionid){
						$flipentities=array_flip($entities);
						if(isset($secquestion[$sectionid])){$value=$secquestion[$sectionid];shuffle($value);
							array_splice($entities,$flipentities[$sectionid]+1,0,$value);
						}
					}
				}else{
					$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where quizid=%d order by entityorder asc",$quizid));
				}
				
			}else{
				$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';
				$entities=$wpdb->get_col($wpdb->prepare("SELECT entityid from $table_name where instanceid=%d order by id asc",$instanceid));
			}
			echo ob_get_clean();
			return $entities;
		
		}
		public static function getfinal_content($quizid,$instanceid,$content,$quizlast,$quizmeta,$post_title,$totalquestions,$totalpoint,$emailprocess=false,$adminemailsubj=false,$adminemail=false,$useremailsubject=false,$useremail=false,$report=false,$emailreport=false,$grade=false,$gradedesc=false,$certi=false){
			$currentuser=wp_get_current_user();
			$userid=$currentuser->ID;
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!empty($grade)){
				$gradegroupid=$quizmeta['quizgrade'][0];$gradegroup=get_post($gradegroupid);$gradegroupcontent=unserialize($gradegroup->post_content);$gradeid=$quizlast['grade'];
				$grade=$gradegroupcontent[$gradeid]['title'];$gradedesc=$gradegroupcontent[$gradeid]['content'];
			}
			if(isset($basicsetting['duration'])){$timeused=$basicsetting['duration']-($quizlast['timeremaining']);}else{$timeused='';}
			global $wpdb;
			$table_name=$wpdb->prefix.'wpcuequiz_quizstatinfo';$table_name1=$wpdb->prefix.'wpcuequiz_quizstat';$table_name3=$wpdb->prefix.'wpcuequiz_quizinfo';
			$userstat=$wpdb->get_results($wpdb->prepare("SELECT a.status,sum(a.point) as point,count(a.id) as counter from $table_name a,$table_name1 b where b.quizid=%d and a.instanceid=b.instanceid and b.userid != %d and a.status != -1 group by a.status",$quizid,$userid),ARRAY_A);
			$instances=$wpdb->get_var($wpdb->prepare("SELECT count(instanceid) as instances from $table_name1 where instanceid != %d and quizid=%d and userid != %d",$instanceid,$quizid,$userid));
			if(!(empty($instances))){$correctpoint=0;if(!empty($userstat[1])){$correctpoint+=$userstat[1]['point'];}if(!(empty($userstat[2]))){$correctpoint+=$userstat[2]['point'];}$avgpoint=($correctpoint)/$instances;
			if(!empty($userstat[1])){$avgcor=($userstat[1]['counter']*100)/($instances*$totalquestions);}else{$avgcor=0;}}else{$avgpoint=0;$avgcor=0;}
			$quizstat=$wpdb->get_results($wpdb->prepare("SELECT status,sum(point) as point,count(*) as counter from $table_name where instanceid=%d group by status",$instanceid),OBJECT_K);
			$pattern=array('/%%CORRECT%%/','/%%PARTCORRECT%%/','/%%TOTAL%%/','/%%POINTS%%/','/%%MAXPOINTS%%/','/%%GRADE%%/','/%%GDESC%%/','/%%QUIZNAME%%/','/%%UNTRIED%%/','/%%WRONG%%/','/%%DATE%%/','/%%EMAIL%%/','/%%USERNAME%%/','/%%AVGPOINTS%%/','/%%AVGCORRECT%%/','/%%TIMEALLOWED%%/','/%%TIMEUSED%%/','/%%PERCENTPOINT%%/','/%%PERCENTQUEST%%/','/%%REPORT%%/');
			$point=0;
			if(!(empty($quizstat[1]))){$point+=$quizstat[1]->point;}
			if(!(empty($quizstat[2]))){$point+=$quizstat[2]->point;}
			if(empty($point)){$point=0;}
			if(!empty($quizstat[4])){$progquest=(($totalquestions-$quizstat[4]->counter)*100)/$totalquestions;}else{$progquest=0;}
			$progpoint=$wpdb->get_var($wpdb->prepare("select sum(a.point) as point from $table_name3 a,$table_name b where a.entityid=b.entityid and b.status IN (0,1,2) and a.quizid=%d and b.instanceid=%d",$quizid,$instanceid));
			if(!(empty($quizstat[1]))){$correct=$quizstat[1]->counter;}else{$correct=0;}
			if(!(empty($quizstat[2]))){$partcorrect=$quizstat[2]->counter;}else{$partcorrect=0;}
			if(!(empty($quizstat[4]))){$untried=$quizstat[4]->counter;}else{$untried=0;}
			if(!(empty($quizstat[0]))){$wrong=$quizstat[0]->counter;}else{$wrong=0;}
			$replace=array($correct,$partcorrect,$totalquestions,$point,$totalpoint,$grade,$gradedesc,$post_title,$untried,$wrong,$quizlast['endtime'],$currentuser->user_email,$currentuser->user_login,$avgpoint,$avgcor,$basicsetting['duration'],$timeused,$progpoint.' %',$progquest.' %',$report);
			$contentarray=array();
			$certificatelink=strpos($content,'%%CERTIFICATELINK%%');
			$certificate=strpos($content,'%%CERTIFICATE%%');
			if(($certificatelink != false)||($certificate != false)){
				$permalink=get_site_url().'/wpcuecertificate/'.$instanceid.'/';
				if($certificatelink != false){
					$content=str_replace('%%CERTIFICATELINK%%','<a href="'.$permalink.'">here</a>',$content);
				}
				if($certificate != false){
					$content=str_replace('%%CERTIFICATE%%','<object width="100%" height="200px" data="'.$permalink.'"></object>',$content);
				}
			}
			$contentarray[0]=do_shortcode(preg_replace($pattern,$replace,$content));
			if(!(empty($emailprocess))){
				$emailreplace=array($correct,$partcorrect,$totalquestions,$point,$totalpoint,$grade,$gradedesc,$post_title,$untried,$wrong,$quizlast['endtime'],$currentuser->user_email,$currentuser->user_login,$avgpoint,$avgcor,$basicsetting['duration'],$timeused,$progpoint.' %',$progquest.' %',$emailreport);
				$contentarray[1]=preg_replace($pattern,$emailreplace,$adminemailsubj);
				$contentarray[2]=preg_replace($pattern,$emailreplace,$adminemail);
				$contentarray[3]=preg_replace($pattern,$emailreplace,$useremailsubject);
				$contentarray[4]=preg_replace($pattern,$emailreplace,$useremail);
			}
			return $contentarray;
		}
		protected function insert_errorid($errorids,$output,$instanceid){
			foreach($errorids as $errorid){
				$addedstatus=$output['erroraddedstatus-'.$errorid];
				$editstatus=$output['erroreditedstatus-'.$errorid];
				$errortitle=$output['errortitle-'.$errorid];
				$errordesc=$output['errordesc-'.$errorid];
				if(empty($addedstatus)){
					$errorid=wp_insert_post(array('post_title'=>$errortitle,'post_content'=>$errordesc,'post_type'=>'wpcuebasicerror','post_status'=>'publish','post_author'=>$user_ID));
					if(!(empty($errorid))){
						$wpdb->insert($errortable,array('instanceid'=>$instanceid,'quizid'=>$quizid,'errorid'=>$errorid,'entityid'=>$entitypost->ID,'status'=>0),array('%d','%d','%d','%d'));
					}
				}else{
					if(!(empty($editstatus))){wp_update_post(array('ID'=>$errorid,'post_content'=>$errordesc,'post_title'=>$errortile));}
				}
			}
		}
		public function wpcuemail_set_content_type(){
			return "text/html";
		}
		protected function question_tools($questtools,$errors,$entitymeta,$entityid){
			$content='<div class="questtools">';
			$content.='<p class="questtoolsmsg"></p>';
			$content.='<div class="questtoolsicons">';
			$content.='<ul class=questtootlslist>';	
			if(!(empty($questtools['showanswer']))){
				$content.='<li><a href="#" class="showanswer">Show Answer</a></li>';
			}
			if(!(empty($questtools['reportquest']))){
				$content.='<li><a href="#" class="reportquestion">Report Error</a></li>';
			}
			if(!(empty($questtools['showhint']))){
				$content.='<li><a href="#" class="showhintquestion">Show Hint</a></li>';
			}
			$content.='</ul></div>';
			$content.='<div class="questtoolsblock">';
			if(!(empty($questtools['showanswer']))){
				$content.=$this->showanswer_block($entitymeta['correctansdesc']);
			}
			if(!(empty($questtools['reportquest']))){
				$content.=$this->reportquest_block($errors,$entityid);
			}
			if(!(empty($questtools['showhint']))){
				$content.=$this->showhint_block($entitymeta['anshint']);
			}
			$content.='</div></div>';
			return $content;
		}
		protected function showanswer_block($correctansdesc=false){
			$content='<div class="answercontainer hiddendiv">';
				if(empty($correctansdesc)){
					$content.='No description for correct answer for this question';
				}else{
					$content.=$correctansdesc;
				}
				$content.='</div>';
				return $content;
		}
		protected function reportquest_block($errors,$entityid){
			$content='<div class="reportquestcontainer hiddendiv">';
			$content.='<div class="reportquestform">';
			$content.='<input type="hidden" name="errorid" value="0" class="reportquestid">';
			$content.='Title : <input type="text" name="errortitle" class="reportquesttitle">';
			$content.='Description : <textarea name="errordesc" class="reportquestdesc"></textarea>';
			$content.='<input type="button" name="saveerror" value="'.__('save','wpcues-basic-quiz').'" class="saveerror">';
			$content.='</div><div class="reportquestadded reportquesttable">';
			if(!(empty($errors))){
				foreach($errors as $errorid=>$error){
					$content.='<div id="error-'.$errorid.'" class="errorentity"><div class="errorinfo">';
					$content.='<div class="errortitle">'.__('Title','wpcues-basic-quiz').' :'.$error->post_title.'</div>';
					$content.='<div class="errordesc">'.__('Description','wpcues-basic-quiz').' :'.$error->post_content.'</div></div>';
					$content.='<div class="erroredit"></div><div class="errordelete"></div>';
					$content.='<input type="hidden" name="errorid-'.$entityid.'[]" value="'.$errorid.'">';
					$content.='<input type="hidden" name="erroraddedstatus-'.$errorid.'" value="1">';
					$content.='<input type="hidden" name="erroreditedstatus-'.$errorid.'" value="0">';
					$content.='<input type="hidden" name="errordesc-'.$errorid.'" value="'.$error->post_content.'">';
					$content.='<input type="hidden" name="errortitle-'.$errorid.'" value="'.$error->post_title.'">';
					$content.='</div>';
				}
			}
			$content.='</div>';
			$content.='</div>';
			return $content;
		}
		protected function showhint_block($anshint=false){
			$content='<div class="showhintcontainer hiddendiv">';
			if(empty($anshint)){
				$content.='No hint for this question';
			}else{
				$content.=$anshint;
			}
			$content.='</div>';
			return $content;
		}
		protected function submit_block($i,$j,$totpagenum,$totalquestcount,$pagenum,$textsettings,$savebuttonstat,$type=false,$submitbuttonstat=false){
			$content='';
			if(!empty($type)){
				if($totpagenum > 1){
					$content.='<div class="paginationbutton">';
					if($pagenum != $totpagenum){
						if($pagenum==1){
							$content.="<input type='button' name='nextpagebutton' class='nextpagebutton' value='".$textsettings['next']."'></div>";}
						else{
							$content.="<input type='button' name='prevpagebutton' class='prevpagebutton' value='".$textsettings['prev']."'>";
							$content.="<input type='button' name='nextpagebutton' class='nextpagebutton' value='".$textsettings['next']."'></div>";}
					}else{
						if($totpagenum >1){$content.="<input type='button' name='prevpagebutton' class='prevpagebutton' value='".$textsettings['prev']."'></div>";}
					}
				}
			}
			$content.='<div class="quizsubmittools">';
			if(!(empty($savebuttonstat))){$content.="<div class='submitquizbutton'><input type='button' name='savequizbut' class='savequizbut' value='Save'></div>";}
			if(!empty($type) && !empty($submitbuttonstat)){
				$content.="<div class='submitquizbutton'><input type='button' name='submitquizbut' class='submitquizbut' value='".$textsettings['submit']."'></div>";
			}elseif($j==$totalquestcount){
				$content.="<div class='submitquizbutton'><input type='button' name='submitquizbut' class='submitquizbut' value='".$textsettings['submit']."'></div>";
			}
			$content.='</div>';
			$content.='</div>';
			return $content;
		}
		public function entityids($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d order by entityorder asc",$quizid));
			return $entityids;
		}
		public function getquestions($quizid){
			$entityids=array();
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_quizinfo';
			$entityids=$wpdb->get_col($wpdb->prepare("Select entityid from $table_name where quizid=%d and parentid != -1 order by entityorder asc",$quizid));
			return $entityids;
		}
}
?>