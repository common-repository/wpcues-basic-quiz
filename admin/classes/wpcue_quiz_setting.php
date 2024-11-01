<?php
/**
* Quiz list class to make changes on All Quizzes page
*/
 if(!class_exists('WpCueQuizSettings'))
{
    class WpCueQuizSettings extends WpCueQuiz_Base
    {

		/**
		* hook into WP's init action hook
		*/
		protected function init()
		{
			add_action( 'admin_init',array(&$this,'settings_register' ));
		}
		public function settings_register(){
			$origoption=(array)$this->_config->setting;
			add_settings_section( 'wpcuebasicquiz_basic_setting', null,null,'wpcuebasicquiz_basic_settings' );
			add_settings_field('publicquiz','Quiz Post type',array(&$this,'wpcuebasicquizsetting_quiztype'),'wpcuebasicquiz_basic_settings','wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>''));	
			add_settings_field('quizslug','',array(&$this,'wpcuebasicquizsetting_quizslug'),'wpcuebasicquiz_basic_settings','wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>__('Slug','wpcues-basic-quiz')));	
			add_settings_field( 'Login', 'Login', array(&$this,'wpcuebasicquizsetting_login'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>__('Show login form in dialog box when login button is clicked. (for quizzes requiring login)','wpcues-basic-quiz')));	
			add_settings_field( 'adminemail', 'Aministrator Email', array(&$this,'wpcuebasicquizsetting_adminemail'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>__('Please Enter the administrator emailid required to receive various emails','wpcues-basic-quiz')));	
			add_settings_field( 'schedule_badgelevel_cron', 'Schedule Badge/Level Cron', array(&$this,'wpcuebasicquizsetting_badgelevelcron'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>''));	
			add_settings_section('wpcuebasicquiz_email_option',null,null,'wpcuebasicquiz_email_options');
			add_settings_field( 'emailleveladmin', 'Email', array(&$this,'wpcuebasicquizsetting_leveladminemail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>__('Notify admin when user attains new level','wpcues-basic-quiz')));	
			add_settings_field( 'emailleveluser',null, array(&$this,'wpcuebasicquizsetting_leveluseremail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>__('Notify user when user attains new level','wpcues-basic-quiz')));
			add_settings_field( 'emailbadgeadmin',null, array(&$this,'wpcuebasicquizsetting_badgeadminemail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>__('Notify admin when new Badge issued to user','wpcues-basic-quiz')));
			add_settings_field( 'emailbadgeuser',null, array(&$this,'wpcuebasicquizsetting_badgeuseremail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>__('Notify user when new Badge issued to him','wpcues-basic-quiz')));	
			add_settings_field('wpprocueactivetab',null, array(&$this,'wpcuebasicquizsetting_activetab'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',array('origoption'=>$origoption));
			add_settings_section( 'wpcuebasicquiz_recaptcha_setting', null,null,'wpcuebasicquiz_recaptcha_settings');
			add_settings_field('wpcuebasicquiz_recpacha_public_key','Site key',array(&$this,'wpcuebasicquiz_recapcha_publickey'),'wpcuebasicquiz_recaptcha_settings','wpcuebasicquiz_recaptcha_setting',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpuebasicquiz_recpacha_private_key','Secret key',array(&$this,'wpuebasicquiz_recpacha_private_key'),'wpcuebasicquiz_recaptcha_settings','wpcuebasicquiz_recaptcha_setting',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_text_setting', null,null,'wpcuebasicquiz_text_settings' );
			add_settings_field('wpcuebasicquiz_question_symbol','Question Symbol',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'questionsymbol','label'=>''));
			add_settings_field('wpcuebasicquiz_login_text','Login Text',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'logintext','label'=>__('You need to be registered and logged in to take this quiz.','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_login_button','Login Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'login','label'=>__('login','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_submit_button','Submit Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'submit','label'=>__('Submit','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_start_button','Start Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'start','label'=>__('Start','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_continue_button','Continue Quiz Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'continue','label'=>__('Continue','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_next_button','Next Question Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'next','label'=>__('Next','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_prev_button','Previous Question Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'prev','label'=>__('Previous','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_duration','Quiz Duration',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'quizduration','label'=>__('Duration : ','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_timeleft','Time Left (timer text)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'timeleft','label'=>__('Time Left : ','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_leftcolumnheading','Left column heading (for match question)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'leftcolumnheading','label'=>__('Column A','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_rightcolumnheading','Right column heading (for match question)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'rightcolumnheading','label'=>__('Column B','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_helpmatchmessage','Help Message (for match question)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'helpmatchmessage','label'=>__('Sort %%COLUMNHEAD%% to match to %%COLUMNHEAD%% entries','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_helpsortmessage','Help Message (for sort question)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'helpsortmessage','label'=>__('Sort the answers in correct order','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_markcorinput','Mark Correct input (for match / sort question)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'markcorinput','label'=>__('Mark as correct','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_processquiz','Processing Quiz Message',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'processingquiz','label'=>__('Processing Quiz.Wait for the result... ','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_startquiz','Fetching Questions Message',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'startingquiz','label'=>__('Fetching questions.Please Wait ... ','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_startquiz','Fetching Next Question Message',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'fetchnextquestion','label'=>__('Fetching next question.Please Wait ... ','wpcues-basic-quiz')));	
			add_settings_field('wpcuebasicquiz_startquiz','Fetching Prev Question Message',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'fetchprevquestion','label'=>__('Fetching previous question.Please Wait ... ','wpcues-basic-quiz')));	
			add_settings_section( 'wpcuebasicquiz_level_adminemail', 'Admin',null,'wpcuebasicquiz_level_adminemails' );
			add_settings_field('level_adminemailsubj','Subject',array(&$this,'wpuebasicquiz_level_adminemailsubj'),'wpcuebasicquiz_level_adminemails','wpcuebasicquiz_level_adminemail',
				array('origoption'=>$origoption,'label'=>__('User reached new level','wpcues-basic-quiz')));
			add_settings_field('level_adminemailbody','Body',array(&$this,'wpuebasicquiz_level_adminemailbody'),'wpcuebasicquiz_level_adminemails','wpcuebasicquiz_level_adminemail',
				array('origoption'=>$origoption,'label'=>__('%%USERNAME%% earned %%NEWLEVEL%% level.','wpcues-basic-quiz')));
			add_settings_section( 'wpcuebasicquiz_level_useremail', 'User',null,'wpcuebasicquiz_level_useremails' );
			add_settings_field('level_useremailsubj','Subject',array(&$this,'wpuebasicquiz_level_useremailsubj'),'wpcuebasicquiz_level_useremails','wpcuebasicquiz_level_useremail',
				array('origoption'=>$origoption,'label'=>__('Congrats! You reached new level','wpcues-basic-quiz')));
			add_settings_field('level_useremailbody','Body',array(&$this,'wpuebasicquiz_level_useremailbody'),'wpcuebasicquiz_level_useremails','wpcuebasicquiz_level_useremail',
				array('origoption'=>$origoption,'label'=>__('Dear %%USERNAME%%,Congratulations with your new level: %%NEWLEVEL%% .Greetings,Admin Team','wpcues-basic-quiz')));
			add_settings_section( 'wpcuebasicquiz_badge_adminemail', 'Admin',null,'wpcuebasicquiz_badge_adminemails' );
			add_settings_field('badge_adminemailsubj','Subject',array(&$this,'wpuebasicquiz_badge_adminemailsubj'),'wpcuebasicquiz_badge_adminemails','wpcuebasicquiz_badge_adminemail',
				array('origoption'=>$origoption,'label'=>__('User earned new badge','wpcues-basic-quiz')));
			add_settings_field('badge_adminemailbody','Body',array(&$this,'wpuebasicquiz_badge_adminemailbody'),'wpcuebasicquiz_badge_adminemails','wpcuebasicquiz_badge_adminemail',
				array('origoption'=>$origoption,'label'=>__('%%USERNAME%% earned new badge %%BADGENAME%% ','wpcues-basic-quiz')));
			add_settings_section( 'wpcuebasicquiz_badge_useremail', 'User',null,'wpcuebasicquiz_badge_useremails' );
			add_settings_field('badge_useremailsubj','Subject',array(&$this,'wpuebasicquiz_badge_useremailsubj'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>__('Congrats! You earned new badge','wpcues-basic-quiz')));
			add_settings_field('badge_useremailbody','Body',array(&$this,'wpuebasicquiz_badge_useremailbody'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>__('Dear %%USERNAME%%,Congratulations with your new Badge: %%BADGEIMAGE%% .','wpcues-basic-quiz')));
			add_settings_field('badge_mozurltext','%%BADGEOPENMOZURL%% text',array(&$this,'wpcuebasicquiz_badge_mozurltext'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>__('Click here to claim your badge.','wpcues-basic-quiz')));
			add_settings_section( 'wpcuebasicquiz_payment_method',null,null,'wpcuebasicquiz_payment_methods' );
			add_settings_field('wpcuebasicquiz_payment_option','Payment Method',array(&$this,'wpcuebasicquiz_paymentoptions'),'wpcuebasicquiz_payment_methods','wpcuebasicquiz_payment_method',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_stripe_detail','Stripe',null,'wpcuebasicquiz_stripe_details' );
			add_settings_field('wpcuebasicquiz_stripe_apiprivate','Private Key',array(&$this,'wpcuebasicquiz_stripeapiprivate'),'wpcuebasicquiz_stripe_details','wpcuebasicquiz_stripe_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_stripe_apipublic','Public Key',array(&$this,'wpcuebasicquiz_stripeapipublic'),'wpcuebasicquiz_stripe_details','wpcuebasicquiz_stripe_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_paypal_detail','Paypal',null,'wpcuebasicquiz_paypal_details' );
			add_settings_field('wpcuebasicquiz_paypal_apiusername','Api User name',array(&$this,'wpcuebasicquiz_paypalapiuser'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_paypal_apipassword','Api User name',array(&$this,'wpcuebasicquiz_paypalapipassword'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_paypal_apisignature','Api User name',array(&$this,'wpcuebasicquiz_paypalapisignature'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_issuer_detail','Issuer Details',null,'wpcuebasicquiz_issuer_details' );
			add_settings_field('wpcuebasicquiz_issuername','Issuer name',array(&$this,'wpcuebasicquiz_issuername'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issueremail','Issuer Email',array(&$this,'wpcuebasicquiz_issueremail'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerurl','Issuer Url',array(&$this,'wpcuebasicquiz_issuerurl'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerdescription','Issuer Description',array(&$this,'wpcuebasicquiz_issuerdescription'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerlogo','Issuer Logo',array(&$this,'wpcuebasicquiz_issuerlogo'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuwbasicquiz_submit_dialog','Submit Dialog',null,'wpcuwbasicquiz_submit_dialogs' );
			add_settings_field('wpcuebasicquiz_submitdialogstat','Staus',array(&$this,'wpcuebasicquiz_submitdialogstat'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_submitdialog','Dialog',array(&$this,'wpcuebasicquiz_submitdialog'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>__('Thanks for taking this quiz.','wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_submitdialheight','Height(px)',array(&$this,'wpcuebasicquiz_submitdialheight'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_field('wpcuebasicquiz_submitdialwidth','Width(px)',array(&$this,'wpcuebasicquiz_submitdialwidth'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_section( 'wpcuwbasicquiz_autosubmit_dialog','Autosubmit Dialog',null,'wpcuwbasicquiz_autosubmit_dialogs' );
			add_settings_field('wpcuebasicquiz_autosubmitdialogstat','Status',array(&$this,'wpcuebasicquiz_autosubmitdialogstat'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>""));
			add_settings_field('wpcuebasicquiz_autosubmitdialog','Dialog',array(&$this,'wpcuebasicquiz_autosubmitdialog'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>__("Time's Up !",'wpcues-basic-quiz')));
			add_settings_field('wpcuebasicquiz_autodialheight','Height(px)',array(&$this,'wpcuebasicquiz_autodialheight'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_field('wpcuebasicquiz_autodialwidth','Width(px)',array(&$this,'wpcuebasicquiz_autodialwidth'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			register_setting('wpcuebaiscquiz_basic_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_email_options','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_recaptcha_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_text_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_level_adminemails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_level_useremails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_badge_adminemails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_badge_useremails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_payment_methods','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_stripe_details','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_issuer_details','wpcuequiz_setting');
			register_setting('wpcuwbasicquiz_submit_dialogs','wpcuequiz_setting');
			register_setting('wpcuwbasicquiz_autosubmit_dialogs','wpcuequiz_setting');
		}
		public function wpcuebasicquizsetting_quiztype($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['quiztype'])){$wpcuequiztype=$origoption['basic']['quiztype'];}else{$wpcuequiztype=1;}
			echo "<select name='wpcuequiz_setting[basic][quiztype]' id='wpcuequiz-quiztypesetting'>";
			switch($wpcuequiztype){
				case 1:
					echo '<option value="1" selected>'.__('Private','wpcues-basic-quiz').'</option><option value="2">'.__('Public','wpcues-basic-quiz').'</option>';
					break;
				case 2:
					echo '<option value="1" >'.__('Private','wpcues-basic-quiz').'</option><option value="2" selected>'.__('Public','wpcues-basic-quiz').'</option>';
					break;
			}
			echo '</select>';
		}
		public function wpcuebasicquizsetting_quizslug($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['quiztype'])){$quiztype=$origoption['basic']['quiztype'];}else{$quiztype=1;}
			$label = esc_attr( $args['label'] );
			if($quiztype==1){
				echo '<div id="quizslug" class="hiddendiv">';
				echo  '<label for="show_header"> '  . $label . ' : </label>';
				echo '<input type="text" name="wpcuequiz_setting[basic][quizslug]" value="" id="quizslugvalue">';
				echo '</div>';
			}else{
				if(isset($origoption['basic']['quizslug'])){$quizslug=$origoption['basic']['quizslug'];}else{$quizslug='';}
				echo '<div id="quizslug">';
				echo  '<label for="show_header"> '  . $label . ' : </label>';
				echo '<input type="text" name="wpcuequiz_setting[basic][quizslug]" value="'.$quizslug.'" id="quizslugvalue">';
				echo '</div>';
			}
		}
		public function wpcuebasicquizsetting_login($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['login'])){$wpprocuelogin=$origoption['basic']['login'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][login]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_adminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['adminemail'])){
				$adminemail=$origoption['basic']['adminemail'];
			}else{
				 $adminemail=get_option('admin_email');
			}
			echo '<input type="text" name="wpcuequiz_setting[basic][adminemail]" value="'.$adminemail.'">';
			$label = esc_attr( $args['label'] );
		}
		public function wpcuebasicquizsetting_activetab($args){
			$origoption=$args['origoption'];
			if(empty($origoption['activetab'])){$activetab=1;}else{$activetab=$origoption['activetab'];}
			echo '<input type="hidden" name="wpcuequiz_setting[activetab]" value="'.$activetab.'">';
		}
		public function wpcuebasicquizsetting_social($args){
			$origoption=$args['origoption'];
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][social]' value='1' ";
			if(!(empty($origoption['basic']['social']))){echo 'checked';}
			echo " />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
			
		}
		public function wpuebasicquiz_recpacha_private_key($args){
			$origoption=$args['origoption'];
			if(empty($origoption['recaptcha']['privatekey'])){$value=$args['label'];}else{$value=$origoption['recaptcha']['privatekey'];}
			echo '<input type="text" name="wpcuequiz_setting[recaptcha][privatekey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_recapcha_publickey($args){
			$origoption=$args['origoption'];
			if(empty($origoption['recaptcha']['publickey'])){$value=$args['label'];}else{$value=$origoption['recaptcha']['privatekey'];}
			echo '<input type="text" name="wpcuequiz_setting[recaptcha][publickey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_textsetting($args){
			$origoption=$args['origoption'];
			$settingvariable=$args['settingvariable'];
			if(isset($origoption['text'][$settingvariable])){$value=$origoption['text'][$settingvariable];}else{$value=esc_attr($args['label']);}
			if($settingvariable=='logintext'){
				echo '<textarea name="wpcuequiz_setting[text]['.$settingvariable.']" style="width:50%;">'.$value.'</textarea>';
			}else{
				echo "<input type='text' name='wpcuequiz_setting[text][".$settingvariable."]' value='".$value."'>";
			}
			
		}
		
		public function wpcuebasicquizsetting_badgelevelcron($args){
			$origoption=$args['origoption'];
			if(!(empty($origoption['basic']['badgelevelcron']))){$value=$origoption['basic']['badgelevelcron'];}else{$value=1;}
			echo "<select name='wpcuequiz_setting[basic][badgelevelcron]'>";
			echo '<option value="1"';
			if($value==1){echo 'selected';}
			echo '>'.__('Hourly','wpcue-basic-quiz').'</option>';
			echo '<option value="2"';
			if($value==2){echo 'selected';}
			echo '>'.__('Daily','wpcue-basic-quiz').'</option>';
			echo '<option value="3"';
			if($value==3){echo 'selected';}
			echo '>'.__('twicedaily','wpcue-basic-quiz').'</option>';
			echo '</select>';
			
		}
		public function wpcuebasicquizsetting_leveladminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['leveladmin'])){$wpprocuelogin=$origoption['basic']['leveladmin'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][leveladmin]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_leveluseremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['leveluser'])){$wpprocuelogin=$origoption['basic']['leveluser'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][leveluser]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_badgeadminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['badgeadmin'])){$wpprocuelogin=$origoption['basic']['badgeadmin'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][badgeadmin]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_badgeuseremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['badgeuser'])){$wpprocuelogin=$origoption['basic']['badgeuser'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][badgeuser]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
			 
		}
		public function wpuebasicquiz_level_adminemailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['adminemailsubj'])){$value=$origoption['level']['adminemailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[level][adminemailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_level_adminemailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['adminemailbody'])){$value=$origoption['level']['adminemailbody'];}else{$value=esc_attr($args['label']);}
			echo wp_editor($value,'wpcuebasicquiz_level_adminemailbody',array('textarea_name'=>"wpcuequiz_setting[level][adminemailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">'.__('You can use the following variables','wpcues-basic-quiz').': '.__('%%USERNAME%%','wpcues-basic-quiz').' , '.__('%%EMAIL%%','wpcues-basic-quiz').' , '.__('%%NEWLEVEL%%','wpcues-basic-quiz').' , '.__('%%OLDLEVEL%%','wpcues-basic-quiz').'</div>';
		}
		public function wpuebasicquiz_level_useremailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['useremailsubj'])){$value=$origoption['level']['useremailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[level][useremailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_level_useremailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['useremailbody'])){$value=$origoption['level']['useremailbody'];}else{$value=esc_attr($args['label']);}
			wp_editor($value,'wpcuebasicquiz_level_useremail',array('textarea_name'=>"wpcuequiz_setting[level][useremailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">'.__('You can use the following variables','wpcues-basic-quiz').': '.__('%%USERNAME%%','wpcues-basic-quiz').' , '.__('%%EMAIL%%','wpcues-basic-quiz').' , '.__('%%NEWLEVEL%%','wpcues-basic-quiz').' , '.__('%%OLDLEVEL%%','wpcues-basic-quiz').'</div>';
		}
		public function wpuebasicquiz_badge_adminemailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['adminemailsubj'])){$value=$origoption['badge']['adminemailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][adminemailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_badge_adminemailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['adminemailbody'])){$value=$origoption['badge']['adminemailbody'];}else{$value=esc_attr($args['label']);}
			echo wp_editor($value,'wpcuebasicquiz_badge_adminemailbody',array('textarea_name'=>"wpcuequiz_setting[badge][adminemailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">'.__('You can use the following variables','wpcues-basic-quiz').': '.__('%%USERNAME%%','wpcues-basic-quiz').' , '.__('%%EMAIL%%','wpcues-basic-quiz').' , '.__('%%BADGENAME%%','wpcues-basic-quiz').' , '.__('%%BADGEIMAGE%%','wpcues-basic-quiz').'</div>';
		}
		public function wpuebasicquiz_badge_useremailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['useremailsubj'])){$value=$origoption['badge']['useremailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][useremailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_badge_useremailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['useremailbody'])){$value=$origoption['badge']['useremailbody'];}else{$value=esc_attr($args['label']);}
			wp_editor($value,'wpcuebasicquiz_badge_useremail',array('textarea_name'=>"wpcuequiz_setting[badge][useremailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">'.__('You can use the following variables','wpcues-basic-quiz').': '.__('%%USERNAME%%','wpcues-basic-quiz').' , '.__('%%EMAIL%%','wpcues-basic-quiz').' , '.__('%%BADGENAME%%','wpcues-basic-quiz').' , '.__('%%BADGEIMAGE%%','wpcues-basic-quiz').' , '.__('%%BADGEOPENMOZURL%%','wpcues-basic-quiz').'</div>';
		}
		public function wpcuebasicquiz_badge_mozurltext($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['mozurltext'])){$value=$origoption['badge']['mozurltext'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][mozurltext]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_paymentoptions($args){
			$origoption=$args['origoption'];
			if(isset($origoption['payment']['method'])){$value=$origoption['payment']['method'];}else{$value=1;}
			echo '<select name="wpcuequiz_setting[payment][method]">';
			echo '<option value="1"';
			if($value == 1){echo ' selected';}
			echo '>'.__('Stripe','wpcues-basic-quiz').'</option>';
			echo '</select>';
		}
		public function wpcuebasicquiz_stripeapiprivate($args){
			$origoption=$args['origoption'];
			if(isset($origoption['stripe']['privatekey'])){$value=$origoption['stripe']['privatekey'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[stripe][privatekey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_stripeapipublic($args){
			$origoption=$args['origoption'];
			if(isset($origoption['stripe']['publickey'])){$value=$origoption['stripe']['publickey'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[stripe][publickey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_paypalapisignature($args){
			$origoption=$args['origoption'];
			if(isset($origoption['paypal']['signature'])){$value=$origoption['paypal']['signature'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[paypal][signature]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuername($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['name'])){$value=$origoption['badgeissuer']['name'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][name]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerurl($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['url'])){$value=$origoption['badgeissuer']['url'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][url]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerdescription($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['description'])){$value=$origoption['badgeissuer']['description'];}else{$value='';}
			wp_editor( $value,'wpcuebasicquiz-issuerdesc',array('textarea_name'=>'wpcuequiz_setting[badgeissuer][description]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>400));
		}
		public function wpcuebasicquiz_issueremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['email'])){$value=$origoption['badgeissuer']['email'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][email]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerlogo($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['logo'])){$value=$origoption['badgeissuer']['logo'];}else{$value='';}
			?>
			<div class='badgeimage'>
				<div id="addedimage" <?php if(empty($value)){echo 'class="hiddendiv"';}?> >
					<div id="imagecontainer">
						<img src="<?php echo $value; ?>" id='badgeimage'>
					</div>
					<div id="imageremovetool"></div>
				</div>
				<div id="badgeimagebutton">
					<input id="upload_image_button" type="button" value="Upload Image" />
					<input type="hidden" name="wpcuequiz_setting[badgeissuer][logo]" id="wpcuebasicquiz-setting-issuerlogo"value="<?php echo $value; ?>">
				</div>
			</div>
			<?php
		}
		public function wpcuebasicquiz_submitdialogstat($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['status'])){
				$value=$origoption['submitdial']['status'];
			}else{$value=1;}
			echo '<div class="switch demo3"><input type="checkbox" name="wpcuequiz_setting[submitdial][status]" value="1"';
			if(!empty($value)){echo ' checked';}
			echo '><label><i></i></label></div>';
		}
		public function wpcuebasicquiz_submitdialog($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['dialog'])){
				$value=$origoption['submitdial']['dialog'];
			}else{$value=esc_attr($args['label']);}
			wp_editor( $value,'wpcuebasicquiz-submitdial',array('textarea_name'=>'wpcuequiz_setting[submitdial][dialog]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>100));
		}
		public function wpcuebasicquiz_submitdialheight($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['height'])){
				$value=$origoption['submitdial']['height'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[submitdial][height]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_submitdialwidth($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['width'])){
				$value=$origoption['submitdial']['width'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[submitdial][width]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_autosubmitdialogstat($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['status'])){
				$value=$origoption['autosubdial']['status'];
			}else{$value=1;}
			echo '<div class="switch demo3"><input type="checkbox" name="wpcuequiz_setting[autosubdial][status]" value="1"';
			if(!empty($value)){echo ' checked';}
			echo '><label><i></i></label></div>';
		}
		public function wpcuebasicquiz_autosubmitdialog($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['dialog'])){
				$value=$origoption['autosubdial']['dialog'];
			}else{$value=esc_attr($args['label']);}
			wp_editor( $value,'wpcuebasicquiz-autosubdial',array('textarea_name'=>'wpcuequiz_setting[autosubdial][dialog]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>100));
		}
		public function wpcuebasicquiz_autodialheight($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['height'])){
				$value=$origoption['autosubdial']['height'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[autosubdial][height]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_autodialwidth($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['width'])){
				$value=$origoption['autosubdial']['width'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[autosubdial][width]" value="'.$value.'">';
		}
	
	 } // END class WpCueQuizSettings
} // END if(!class_exists('WpCueQuizSettings'))

/* EOF */