<?php
/**
 * WpCueQuiz_Public class
*/
	 if(!class_exists('WpCueQuiz_Public'))
{
    class WpCueQuiz_Public extends WpCueQuiz_Plugin
    {
		/**
		* hook into WP's init action hook
		*/
		protected function init()
		{
			parent::init();
			$this->add_filter('the_content','formatcontent_quiz');
			$this->add_action('template_redirect','wpcue_templateRedirect');
			$this->add_action('init','register_quizshortcode');
			$this->add_filter('query_vars','wpcuequiz_plugin_query_vars');
		} // END protected function init()
		public  function wpcuequiz_plugin_query_vars($vars) {
			$vars[] = 'wpcuecertificateid';
			$vars[]='wpcuebadgeuid';
			$vars[]='wpcuebadgeid';
			$vars[]='wpcuequizid';
			return $vars;
		}
		public function register_quizshortcode(){
			add_shortcode('wpcuebasicquiz',array(&$this,'quiz_shortcode'));
		}
		private function enqueue_frontscript($captchastatus){
			$wpprocuesetting=$this->_config->setting;
			wp_register_style( 'tabs_css', WPCUES_BASICQUIZ_URL.'/common/css/jquery-ui-smooth.css');
			wp_enqueue_style('tabs_css');
			wp_register_script('mathjax','//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
			wp_enqueue_script('mathjax');
			if(empty($captchastatus)){
				wp_register_script('google-recaptcha','https://www.google.com/recaptcha/api.js');
				wp_enqueue_script('google-recaptcha');
				wp_register_script('wpcuequiz-frontal',WPCUES_BASICQUIZ_URL.'/public/js/wpcuebasicquiz-front.js',array('jquery','jquery-ui-core','jquery-ui-sortable','jquery-ui-dialog','jquery-form','jquery-ui-draggable','google-recaptcha'));
				wp_enqueue_script('wpcuequiz-frontal');
			}else{
				wp_register_script('wpcuequiz-front',WPCUES_BASICQUIZ_URL.'/public/js/wpcuebasicquiz-front.js',array('jquery','jquery-ui-core','jquery-ui-sortable','jquery-ui-dialog','jquery-form','jquery-ui-draggable'));
				wp_enqueue_script('wpcuequiz-front');
			}
			
			$frontL10n=array('submit'=>__('submit','wpcues-basic-quiz'),
								'errordet'=>__('please enter error detail','wpcues-basic-quiz'),
								'errortitle'=>__('please enter error title','wpcues-basic-quiz'),
								'coranswer'=>__('Are you sure you want to view the correct answer ? If yes, you will not be able to change your marked answer then.','wpcues-basic-quiz'),
								'errormsg'=>__('Some error occured.Please try again.','wpcues-basic-quiz'),
								'anscaptcha'=>__('please answer captcha','wpcues-basic-quiz'),
								'questfirst'=>__('Please reply the question first','wpcues-basic-quiz'),
								);
			if(empty($captchastatus)){					
				wp_localize_script('wpcuequiz-frontal','frontL10n',$frontL10n);
				wp_localize_script('wpcuequiz-frontal','wpcuebasicquizajax',array('ajaxurl' => admin_url('admin-ajax.php')));
				wp_localize_script('wpcuequiz-frontal','googlerecaptcha',array('key' =>$wpprocuesetting['recaptcha']['publickey']));
			}else{
				wp_localize_script('wpcuequiz-front','frontL10n',$frontL10n);
				wp_localize_script('wpcuequiz-front','wpcuebasicquizajax',array('ajaxurl' => admin_url('admin-ajax.php')));
			}
		}
		private function captchastatus($captchasetting=false){
			$captchastatus=0;
			$wpprocuesetting=$this->_config->setting;
			if(empty($wpprocuesetting['recaptcha']['privatekey'])){$captchastatus+=1;}
			if(empty($wpprocuesetting['recaptcha']['publickey'])){$captchastatus+=2;}
			return $captchastatus;
		}
		public function quiz_shortcode($atts){
			$quizid=$atts[0];
			$quiz=get_post($quizid);
			$quizmeta=get_post_custom($quizid);
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!empty($basicsetting['addcaptcha'])){
				$captchastatus=$this->captchastatus($basicsetting['addcaptcha']);
			}else{$captchastatus=4;}
			$this->enqueue_frontscript($captchastatus);
			$content='';
			if(empty($quizmeta['customcss'])){
				wp_register_style( 'wpcuebasicquiz-frontmaincss', WPCUES_BASICQUIZ_URL.'/common/css/wpcuebasicquiz-frontmain.css');
				wp_enqueue_style('wpcuebasicquiz-frontmaincss');
			}else{
				$customcss=$quizmeta['customcss'][0];
				$content.='<style type="text/css">'.$customcss.'</style>';
			}
			$content.='<p class="title">'.$quiz->post_title.'</p>';
			$content.=$this->get_content($quizmeta,$quiz->ID,$quiz->post_content,$quiz->post_title,$captchastatus);
			return $content;
		}
		public function formatcontent_quiz($content){
			global $post;
			$wpprocuesetting=$this->_config->setting;
			if(($post->post_type == 'wpcuebasicquiz')){
				$content='';
				wp_register_script('mathjax','//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
				wp_enqueue_script('mathjax');
				$quizmeta=get_post_custom($post->ID);$quizid=$post->ID;
				$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
				if(!empty($basicsetting['addcaptcha'])){
					$captchastatus=$this->captchastatus($basicsetting['addcaptcha']);
				}else{$captchastatus=4;}
				$this->enqueue_frontscript($captchastatus);
				if(empty($quizmeta['customcss'])){
					wp_register_style( 'wpcuebasicquiz-frontmaincss', WPCUES_BASICQUIZ_URL.'/common/css/wpcuebasicquiz-frontmain.css');
					wp_enqueue_style('wpcuebasicquiz-frontmaincss');
				}else{
					$content.='<style type="text/css">'.$quizmeta['customcss'][0].'</style>';
				}
				$content.=$this->get_content($quizmeta,$post->ID,$post->post_content,$post->post_title,$captchastatus);
			}
			return $content;
		}
		private function get_content($quizmeta,$postid,$postcontent,$post_title,$captchastatus){
			global $wpdb;
			$wpprocuesetting=$this->_config->setting;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
			$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			if(!(empty($quizmeta['displaysetting']))){
				$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]);
				if(!(empty($displaysetting['displaysubdial']))){
					wp_enqueue_script('jquery-ui-dialog');
				}
			}else{$displaysetting=array();}
			
			$userid=get_current_user_id();
			$pagedetail=$this->get_page($basicsetting,$userid,$postid);
			$quizlast=$pagedetail['quizlast'];$page=$pagedetail['page'];
			$content='<input type="hidden" name="quizid" value="'.$postid.'">';
			if(!(empty($wpprocuesetting['submitdial']['status']))){
				$submitdialogcontent='<div class="wp-dialog" id="quizsubmitdialog" style="width:'.$wpprocuesetting['submitdial']['width'].';height:'.$wpprocuesetting['submitdial']['height'].';">'.$wpprocuesetting['submitdial']['dialog'].'</div>';
			}else{$submitdialogcontent='';}
			if(!(empty($wpprocuesetting['autosubdial']['status']))){
				$autosubmitdialogcontent='<div class="wp-dialog" id="autosubmitdialog" style="width:'.$wpprocuesetting['autosubdial']['width'].';height:'.$wpprocuesetting['autosubdial']['height'].';">'.$wpprocuesetting['autosubdial']['dialog'].'</div>';
			}else{$autosubmitdialogcontent='';}
			$content.='<div id="processdiv"><div id="spinnerdiv"><span class="spinner"></span></div><div id="processtextdiv">'.$wpprocuesetting['text']['processingquiz'].'</div></div>';
			switch($page){
				case 0:
					$content.=$this->start_page($displaysetting,$basicsetting,$postid,$quizmeta,$postcontent,$userid,$captchastatus);
					break;
				case 1:
					$content.=$this->start_loginpage($displaysetting,$basicsetting,$quizmeta,$postcontent,$captchastatus);
					break;
				case 2:
					$content.=$this->get_intermediatepage($postid,$quizmeta,$quizlast,$post_title,$basicsetting,$displaysetting,$captchastatus);
					break;
				case 3:
					$content.=$this->get_completionpage($postid,$quizlast,$quizmeta,$post_title);
					break;
			}
			$content.=$submitdialogcontent;
			$content.=$autosubmitdialogcontent;
			return $content;
		}
		private function get_page($basicsetting,$userid,$postid){
			$quizlast=array();global $wpdb;$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
			if(empty($basicsetting['login'])){
				$page=0;
			}else{
				if(empty($userid)){
					$page=1;
				}else{
					if(empty($basicsetting['changedquiztimestamp'])){$changedquiztimestamp=0;}else{$changedquiztimestamp=$basicsetting['changedquiztimestamp'];}
					if(empty($basicsetting['lognum'])){
						$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=0 and UNIX_TIMESTAMP(endtime) > $changedquiztimestamp order by endtime LIMIT 0,1",$userid,$postid),ARRAY_A );
						if(is_null($quizlast)){$page=0;}else{$page=2;}
					}else{
						$trialnum=$wpdb->get_var($wpdb->prepare("select count(*) as count from $table_name where userid=%d and quizid=%d and status=1 and UNIX_TIMESTAMP(endtime) > $changedquiztimestamp  order by endtime",$userid,$postid));
						if($trialnum>=$basicsetting['lognum']){
							$page=3;
							$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=1 and UNIX_TIMESTAMP(endtime) > $changedquiztimestamp  order by endtime desc LIMIT 0,1",$userid,$postid),ARRAY_A );
						}else{
							$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where userid=%d and quizid=%d and status=0 and UNIX_TIMESTAMP(endtime) > $changedquiztimestamp  order by endtime desc LIMIT 0,1",$userid,$postid),ARRAY_A );
							if(is_null($quizlast)){$page=0;}else{$page=2;}
						}
					}
				}
			}
			$pagedetail=array('quizlast'=>$quizlast,'page'=>$page);
			return $pagedetail;
		}
		private function start_page($displaysetting,$basicsetting,$postid,$quizmeta,$postcontent,$userid,$captchastatus){
			$wpprocuesetting=$this->_config->setting;global $wpdb;$table_name = $wpdb->prefix.'wpcuequiz_quizstat';
			$content="<div id='quizstartpage' class='";
			if(!empty($displaysetting['disablequizdesc']) && !empty($displaysetting['disablestartbutton'])){$content.='"hiddendiv"';}
			$content.="'>";
			if(empty($displaysetting['disablequizdesc'])){
				if(isset($postcontent)){$content.='<div id="quizdesc">'.$postcontent.'</div>';}
			}
			$content.="<div class='startbutton'>";
			if(empty($displaysetting['disablestartbutton'])){
				$content.="<input type='button' name='startquizbutton' id='startquizbutton' value='".$wpprocuesetting['text']['start']."'>";
				$instanceid=0;
			}else{
				$post=$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,userid,timeremaining,mode,starttime,endtime,status) VALUES (%d,%d,%d,%d,now(),now(),0)",$postid,$userid,intval($basicsetting['duration']),intval($basicsetting['mode'])));
				$instanceid=$wpdb->insert_id;
			}
			$content.="<input type='hidden' name='instanceid' value='".$instanceid."'/>";
			$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
			if(!empty($wpprocuesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
			$content.="</div></div>";
			$content.='<div id="quizmainpage">';
			if(!(empty($displaysetting['disablestartbutton']))){
				$content.='<input type="hidden" name="disablestartbutton" value="'.$displaysetting['disablestartbutton'].'">';
				$content.=$this->get_mainpage($quizmeta,$postid,0,$captchastatus);
			}
			$content.='</div>';
			if(!(empty($quizmeta['quizfinal'][0]))){$content.='<div id="quizfinalpage"></div>';}
			return $content;
		}
		private function start_loginpage($displaysetting,$basicsetting,$quizmeta,$postcontent,$captchastatus){
			$wpprocuesetting=$this->_wpprocuesetting;
			$content="<div id='quizstartpage'>";
			if(empty($displaysetting['disablequizdesc'])){
				if(isset($postcontent)){$content.='<div id="quizdesc">'.$postcontent.'</div>';}
			}
			$content.="<input type='hidden' name='quizlogin' value='0'>";
			$content.="<input type='hidden' name='instanceid' value='0'/>";
			$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
			if(!empty($wpprouesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
			$content.="<div class='quizlogintext'>".$wpprocuesetting['text']['logintext']."</div><div class='logincontrol'><a href='";
			if(empty($wpprocuesetting['basic']['login'])){$content.=wp_login_url(get_permalink())."'";}else{$content.="#'";}
			$content.=" title='Login' class='button";if(!(empty($wpprocuesetting['basic']['login']))){$content.=" dialoglogin";}
			$content.="' id='quizloginbutton'>".$wpprocuesetting['text']['login']."</a>";
			$content.='</div></div>';
			if(!(empty($wpprocuesetting['basic']['login']))){$content.='<div class="wp-dialog" id="quizllogindialog">'.wp_login_form(array('echo'=>false)).'</div>';}
			return $content;
		}
		private function get_intermediatepage($postid,$quizmeta,$quizlast,$post_title,$basicsetting,$displaysetting,$captchastatus){
			$quizinfo=$this->quizinfo($postid);global $wpdb;$table_name = $wpdb->prefix.'wpcuequiz_quizstat';
			$intermediatecontent=$this->getfinal_content($postid,$quizlast['instanceid'],$quizmeta['quizintermediate'][0],$quizlast,$quizmeta,$post_title,$quizinfo['totalquestions'],$quizinfo['totalpoint']);
			$content="<div id='quizintermediatepage'><div class='quizintermediatecontent'>".$intermediatecontent[0]."</div>";
			$content.="<div class='startbutton'>";
			$content.="<input type='hidden' name='instanceid' value='".$quizlast['instanceid']."'/>";
			$content.="<input type='hidden' name='quizmode' value='".$basicsetting['mode']."'>";
			if(!empty($wpprouesetting['autosubdial']['status'])){$content.="<input type='hidden' name='autosubmission' value='".$wpprocuesetting['autosubdial']['status']."'>";}
			if(empty($displaysetting['intermediatecontrol'])){$content.="<input type='button' name='continuequizbutton' id='continuequizbutton' value='continue'>";}
			$content.='</div></div>';
			$content.='<div id="quizmainpage">';
			if(!(empty($displaysetting['intermediatecontrol']))){
				$content.='<input type="hidden" name="disableintermediatecontrol" value="'.$displaysetting['intermediatecontrol'].'">';
				$content.=$this->get_mainpage($quizmeta,$postid,$quizlast['instanceid'],$captchastatus);
				$post=$wpdb->query($wpdb->prepare("UPDATE $table_name set starttime=now(),endtime=now() where instanceid=%d",$quizlast['instanceid']));
			}
			$content.='</div>';
			if(!(empty($quizmeta['quizfinal'][0]))){$content.='<div id="quizfinalpage"></div>';}
			return $content;
		}
		private function get_completionpage($postid,$quizlast,$quizmeta,$post_title){
			$quizinfo=$this->quizinfo($postid);
			$completedcontent=$this->getfinal_content($postid,$quizlast['instanceid'],$quizmeta['quizcomplete'][0],$quizlast,$quizmeta,$post_title,$quizinfo['totalquestions'],$quizinfo['totalpoint']);
			$content="<div id='quizcompletedpage'><div class='quizcompletedcontent'>".$completedcontent[0]."</div>";
			return $content;
		}
		public function wpcue_templateRedirect(){
			$page = get_query_var('pagename');global $wpdb;
			switch($page){
				case 'wpcuecertificate':
				$certificateid = get_query_var('wpcuecertificateid');
				if('' != $certificateid){
					$table_name=$wpdb->prefix.'wpcuequiz_quizstat';$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
					$result=$wpdb->get_row($wpdb->prepare("select quizid,grade,userid,endtime from $table_name where instanceid=%d",$certificateid),ARRAY_A);
					if(empty($result)){
						header("HTTP/1.0 404 Not Found");
					}else{
						header("HTTP/1.1 200 OK");
						if(!(empty($result['grade']))){
							$gradegroupid=get_post_meta($result['quizid'],'quizgrade',true);
							$gradegroup=get_post($gradegroupid);$grademeta=unserialize($gradegroup->post_content);
							$certi=(int)$grademeta[$result['grade']]['certi'];
							if(empty($certi)){
								_e('Sorry, no certificate have not been assigned to grade obtained by you','wpcues-basic-quiz');
							}else{
							$grade=$grademeta[$result['grade']]['title'];
							$certificate=get_post($certi);
							$certificatemet =get_post_meta($certi,'wpcuecertificate_det');$certificatemeta=maybe_unserialize($certificatemet);
							$certificatemetavalues=$certificatemeta[0]; 
							if(empty($certificatemetavalues['approval'])){
								$point=$wpdb->get_var($wpdb->prepare("select sum(point) as point from $table_name1 where instanceid=%d",$certificateid));
								$certificatecontent=$certificate->post_content;
								$user=get_user_by('id',$result['userid']);$quizname=get_the_title($result['quizid']);
								$pattern=array('/%%GRADE%%/','/%%QUIZNAME%%/','/%%DATE%%/','/%%POINTS%%/','/%%USERNAME%%/');
								$replace=array($grade,$quizname,$result['endtime'],$point,$user->user_login);
								$certificatecontent=preg_replace($pattern,$replace,$certificatecontent);
								if($certificatemetavalues['certype']==1){
									include(WPCUES_BASICQUIZ_PATH."/public/lib/mpdf/mpdf.php");
									$mpdf=new mPDF('utf-8',array(100,50));
									$mpdf->WriteHTML($certificatecontent);
									$mpdf->Output();
									exit;
								}else{
									echo '<!DOCTYPE html><html><head></head><body>'.$certificatecontent.'</body></html>';
								}
							}else{
							
								_e('This certificate need admin approval to be issued. You will be notified when approved.','wpcues-quiz-pro');
							}
							}
						}else{
							$post=get_post($result['quizid']);
							$current_user=wp_get_current_user();
							if (is_user_logged_in())  {
								_e('Sorry, you have not been assigned any grade for point obtained','wpcues-basic-quiz');
							}
						}
					}
					exit;
				}else{
					header("HTTP/1.0 404 Not Found");
					exit;
				}
				break;
				case 'wpcuenewbadge':
					$badgeguid=get_query_var('wpcuebadgeuid');
					if(!(empty($badgeguid))){
						global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
						$processed=$wpdb->get_var($wpdb->prepare("SELECT status from $table_name where id=%d",$badgeguid));
						if(!empty($processed)){
						header("HTTP/1.1 200 OK");
						$badgeurl=get_site_url().'/wpcuebadgejson/'.$badgeguid.'/';
						?>
						<!DOCTYPE html><html><head><script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" ></script><script src="https://backpack.openbadges.org/issuer.js"></script><script type="text/javascript">var ajaxurl="<?php echo admin_url('admin-ajax.php');?>";</script></head><body>Click here
						<a href="javascript:OpenBadges.issue(['<?php echo urlencode($badgeurl);?>'],function(errors, successes){if(successes != ''){$.ajax({
							type: 'POST',
							dataType:'html',
							url:ajaxurl,
							data: {'action':'wpcuequizbadgesuccess_action','badgeguid':<?php echo $bageguid;?>
							},success: function(response){}});}
						});">Mozilla Badge Backpack</a></body></html>
						<?php	exit;
						}else{
							header("HTTP/1.0 404 Not Found");
							exit;
						}
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					break;
				case 'wpcuebadgejson':
					$badgeguid=get_query_var('wpcuebadgeuid');
					global $wp;
					$verifyurl=home_url(add_query_arg(array(),$wp->request)).'/';
					if(!empty($badgeguid)){
						global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
						$badgedet=$wpdb->get_row($wpdb->prepare("SELECT id,badgeid,userid,UNIX_TIMESTAMP(issueddate) as issueddate from $table_name where id=%d",$badgeguid),ARRAY_A);
						if(!empty($badgedet)){
							header('Content-Type: application/json');
							header("HTTP/1.1 200 OK");
							$badge=get_site_url().'/wpcuebadgeclassjson/'.$badgedet['badgeid'].'/';
							$userid=$badgedet['userid'];$user=get_user_by('id',$userid);$hashedemail=$user->user_email;
							$string='{"uid":"'.$badgeguid.'","badge":"'.$badge.'","verify":{"type":"hosted","url":"'.$verifyurl.'"},"recipient":{"type":"email","hashed":false,"identity":"'.$hashedemail.'"},"issuedOn":'.$badgedet['issueddate'].'}';
							echo $string;
							exit;
						}else{
							header("HTTP/1.0 404 Not Found");
							exit;
						}
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					break;
				case 'wpcuebadgeclassjson':
					$badgeid=get_query_var('wpcuebadgeid');
					$badge=get_post($badgeid);
					if($badge){
						header('Content-Type: application/json');
						header("HTTP/1.1 200 OK");
						$criteriaurl=get_permalink($badgeid);
						$baseurl=get_site_url();
						$issuerurl=$baseurl.'/wpcueissuerjson/';
						$imageurl=get_post_meta($badgeid,'wpcuebadgeimage',true);
						$string='{"name":"'.$badge->post_title.'","description":"'.$badge->post_content.'","image":"'.$imageurl.'","criteria":"'.$criteriaurl.'","issuer":"'.$issuerurl.'"}';
						echo $string;
						exit;
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					
					break;
				case 'wpcueissuerjson':
					$wpprocuesetting=$this->_config->setting;
					if(!empty($wpprocuesetting['badgeissuer']['name']) && !empty($wpprocuesetting['badgeissuer']['url'])){
						header('Content-Type: application/json');
						header("HTTP/1.1 200 OK");
						$string='{"name":"'.$wpprocuesetting['badgeissuer']['name'].'","url":"'.$wpprocuesetting['badgeissuer']['url'].'"';
						if(!empty($wpprocuesetting['badgeissuer']['description'])){$string.=',"description":"'.$wpprocuesetting['badgeissuer']['description'].'"';}
						if(!empty($wpprocuesetting['badgeissuer']['email'])){$string.=',"email":"'.$wpprocuesetting['badgeissuer']['email'].'"';}
						if(!empty($wpprocuesetting['badgeissuer']['logo'])){$string.=',"image":"'.$wpprocuesetting['badgeissuer']['logo'].'"';}
						$string.='}';
						echo $string;
					}else{
						header("HTTP/1.0 404 Not Found");
					}
					exit;
					break;
				case 'wpcuedynamiccss':
					$quizid=get_query_var('wpcuequizid');
					$customcss=get_post_meta($quizid,'customcss',true);
					header('Content-Type: text/css');
					header("HTTP/1.1 200 OK");
					echo $customcss;
					exit;
					break;
			}	
		}
    } // END class WpCueBasicQuiz
} // END if(!class_exists('WpCueBasicQuiz'))
/* EOF */
