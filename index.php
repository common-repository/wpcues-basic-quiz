<?php
/*
Plugin Name: WpCues Basic Quiz
Description: This is a plugin to generate Quizzes having multimedia and math in questions and answers both.
Text Domain: wpcues-basic-quiz
Domain Path: /languages/
Author: wpcues
Version: 1.6.5
*/
define( 'WPCUES_BASICQUIZ_PATH', dirname( __FILE__ ) );
define( 'WPCUES_BASICQUIZ_URL', plugins_url( '', __FILE__ ) );
define( 'WPCUES_BASICQUIZ_FILE', plugin_basename( __FILE__ ) );
define( 'WPCUES_BASICQUIZ_COMMON', WPCUES_BASICQUIZ_PATH . '/common' );
define( 'WPCUES_BASICQUIZ_ADMIN', WPCUES_BASICQUIZ_PATH . '/admin' );
define( 'WPCUES_BASICQUIZ_PUBLIC', WPCUES_BASICQUIZ_PATH . '/public' );
require_once( WPCUES_BASICQUIZ_COMMON . '/classes/wpcue_quiz_base.php' );
require_once( WPCUES_BASICQUIZ_COMMON . '/classes/wpcue_quiz_plugin.php' );
require_once( WPCUES_BASICQUIZ_COMMON . '/classes/wpcue_quiz_config.php' );
$my_class = 'WpCueQuiz_';
if ( is_admin() ) {
$my_class .= 'Admin';
require_once( WPCUES_BASICQUIZ_ADMIN . '/wpcue_quiz_admin.php' );
} else {
$my_class .= 'Public';
require_once( WPCUES_BASICQUIZ_PUBLIC . '/wpcue_quiz_public.php' );
}
$plugin_setting_data=get_option('wpcuequiz_setting');
if(empty($plugin_setting_data)){
	$settings=array();
	$settings['basic']['quiztype']=1;
	$settings['basic']['login']=1;
	$settings['basic']['adminemail']='';
	$settings['activetab']=1;
	$settings['basic']['social']=1;
	$settings['text']['logintext']='You need to be registered and logged in to take this quiz.';
	$settings['text']['login']='login';
	$settings['text']['submit']='Submit';
	$settings['text']['start']='Start';
	$settings['text']['continue']='Continue';
	$settings['text']['next']='Next';
	$settings['text']['prev']='Previous';
	$settings['text']['quizduration']='Duration : ';
	$settings['text']['timeleft']='Time Left : ';
	$settings['text']['processingquiz']='Processing Quiz.Wait for the result... ';
	$settings['basic']['badgelevelcron']=1;
	$settings['level']['adminemailsubj']='User reached new level';
	$settings['level']['adminemailbody']='%%USERNAME%% earned %%NEWLEVEL%% level.';
	$settings['level']['useremailsubj']='Congrats! You reached new level';
	$settings['level']['useremailbody']='Dear %%USERNAME%%,Congratulations with your new level: %%NEWLEVEL%% .Greetings,Admin Team';
	$settings['badge']['adminemailsubj']='User earned new badge';
	$settings['badge']['adminemailbody']='%%USERNAME%% earned new badge %%BADGENAME%% ';
	$settings['badge']['useremailsubj']='Congrats! You earned new badge';
	$settings['badge']['useremailbody']='Dear %%USERNAME%%,Congratulations with your new Badge: %%BADGEIMAGE%% .';
	$settings['badge']['mozurltext']='Click here to claim your badge.';
	$plugin_config_data['setting']=$settings;
}else{$plugin_config_data['setting']=$plugin_setting_data;}
$my_plugin = new $my_class( new WpCueQuiz_Config($plugin_config_data) );
unset($my_class, $plugin_config_data,$plugin_setting_data);