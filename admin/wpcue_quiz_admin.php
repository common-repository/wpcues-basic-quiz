<?php
if(!class_exists('WpCueQuiz_Admin')){
class WpCueQuiz_Admin extends WpCueQuiz_Plugin{
		/**
         * Construct the plugin object
         */ 
		protected function init()
		{	
			parent::init();
			$plugin=WPCUES_BASICQUIZ_FILE;
			register_activation_hook($plugin,array(&$this,'activate'));
			register_deactivation_hook($plugin, array(&$this,'deactivate'));
			register_uninstall_hook($plugin, array('WpCueQuiz_Admin','uninstall_wpprocue' ) );
			$this->add_action('init','wpcuequiz_rewrite_rules');
			//admin-menu pages
			$this->add_action('admin_menu','wpcue_proquiz_add_page');
			$this->add_action('pre_update_option_wpcuequiz_setting','wpcue_changequiztype',10,2);
			$this->add_action('wp_kses_allowed_html','wpcue_allowed_html',10,1);
			$this->add_filter("plugin_action_links_$plugin", 'proquiz_settings_link');
			load_plugin_textdomain('wpcues-basic-quiz', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			//Include Classes
			require_once(WPCUES_BASICQUIZ_PATH."/admin/classes/wpcue_quiz_action.php");
			require_once(WPCUES_BASICQUIZ_PATH."/admin/classes/wpcue_quiz_setting.php");
			$WpCueQuizAction=new WpCueQuizAction($this->_config);
			$WpCueQuizSettings= new WpCueQuizSettings($this->_config);
			//Show author specific posts and comments
			$this->add_action('wp_ajax_dynamic_css','dynaminc_css');
			$this->add_action('admin_init','wpcue_versioncheck');
			$this->add_action('wp_ajax_wpcuequizgetquizresult_action','wpcue_proquiz_final_result');
			$this->add_action('wp_ajax_nopriv_wpcuequizgetquizresult_action','wpcue_proquiz_final_result');
			$this->add_action('wp_ajax_wpcuequizstartquiz_action','wpcue_proquiz_startquiz');
			$this->add_action('wp_ajax_nopriv_wpcuequizstartquiz_action','wpcue_proquiz_startquiz');
			if(version_compare(get_bloginfo('version'),'4.2')<0){
				wp_register_style('spinner-olderwp',WPCUES_BASICQUIZ_URL.'/admin/css/olderwp-spinner.css');
				wp_enqueue_style('spinner-olderwp');
				
			}
        } 
        /**
         * Activate the plugin
         */
        public function activate($network_wide){	
			$this->check_wpversion();
			global $wpdb;
			$WpCueBasicQuiz=new WpCueBasicQuiz($this->_config);
			$WpCueBasicBadge=new WpCueBasicBadge($this->_config);
			 if ( is_multisite() && $network_wide ) {
				// store the current blog id
			$current_blog = $wpdb->blogid;
			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->wpcue_versioncheck();
					$this->wpcuebasicquiz_quizstatdb();
					$this->add_default_settings();
					$this->wpcuequiz_rewrite_rules();
					$WpCueBasicQuiz->create_post_type();
					$WpCueBasicBadge->create_post_type();
					flush_rewrite_rules();
					restore_current_blog();
				}
			}else {
					$this->wpcue_versioncheck();
					$this->wpcuebasicquiz_quizstatdb();
					$this->add_default_settings();
					$this->wpcuequiz_rewrite_rules();
					$WpCueBasicQuiz->create_post_type();
					$WpCueBasicBadge->create_post_type();
					flush_rewrite_rules();
			}
        } // END public static function activate

        /**
         * Deactivate the plugin
         */     
        public function deactivate()
        {
            flush_rewrite_rules();
        } // END public static function deactivate
		/**
		* Remove options and tables when uninstalled
		*/
		public static function uninstall_wpprocue($network_wide){
			if ( ! current_user_can( 'activate_plugins' ) )
				return;
			check_admin_referer( 'bulk-plugins' );
			global $wpdb;
			if ( is_multisite() && $network_wide ){
				$current_blog = $wpdb->blogid;

			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$table_names = array($wpdb->prefix.'wpcuequiz_quizstat',$wpdb->prefix.'wpcuequiz_quizstatinfo');	
					$table_name=implode(",",$table_names);
					$wpdb->query( "DROP TABLE IF EXISTS  $table_name");
					flush_rewrite_rules();
					restore_current_blog();
					
				}
				
			} else {
					$table_names = array($wpdb->prefix.'wpcuequiz_quizstat',$wpdb->prefix.'wpcuequiz_quizstatinfo');	
					$table_name=implode(",",$table_names);
					$wpdb->query( "DROP TABLE IF EXISTS  $table_name");
					//flush_rewrite_rules();
			}
		
		}
		private function check_wpversion(){
			global $wp_version;
			if(version_compare( $wp_version,'3.5', '<' ) ){
				$this->deactivate();
				wp_die('<p>The <strong>WpCue Basic Quiz</strong> plugin requires wordpress  version 5.3 or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
			}
		}
		/**
		* Add mathslate plugin to tinymce editors
		*/
		public function  wpcue_custom_plugins($plugins_array){
			$plugins = array('mathslate'); //Add any more plugins you want to load here
			//Build the response - the key is the plugin name, value is the URL to the plugin JS
			foreach ($plugins as $plugin ) {
				$plugins_array[ $plugin ] = WPCUES_BASICQUIZ_URL.'/admin/tinymce/'. $plugin . '/plugin.js';
			}
			return $plugins_array;
		}
		public function wpcue_register_mathslate_button($buttons){
			array_push($buttons, "mathslate");
			return $buttons;
		}
		/**
		* Create Menu page
		*/
		public function wpcue_proquiz_add_page() {
			// Main Menu Page
			global $wp_version;
			add_menu_page( 'Quiz', 'Quiz', 'edit_posts','edit.php?post_type=wpcuebasicquiz','','dashicons-admin-page','5.9025');
			//Create Submenu
			add_submenu_page('edit.php?post_type=wpcuebasicquiz','All Quizzes', 'All Quizzes', 'edit_posts','edit.php?post_type=wpcuebasicquiz');
			$createquiz_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Add New quiz', 'Add New quiz', 'edit_posts','wpcuequizaddnew',array(&$this,'wpcue_proquiz_createquiz_page'));
			remove_submenu_page('edit.php','edit-tags.php?taxonomy=wpcuebasicquizcat&post_type=wpcuebasicquiz');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Quiz Categories', 'Quiz Category', 'manage_categories','edit-tags.php?taxonomy=wpcuebasicquizcat&post_type=wpcuebasicquiz');
			$questcat_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Question Categories', 'Question Category', 'manage_categories','edit-tags.php?taxonomy=wpcuebasicquestcat&post_type=wpcuebasicquestion');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Certificates', 'Certificates', 'edit_posts','edit.php?post_type=wpcuecertificate');
			$createlevel_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Levels', 'Levels', 'edit_posts','edit.php?post_type=wpcuebasiclevel');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Badges', 'Badges', 'edit_posts','edit.php?post_type=wpcuebasicbadge');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Monetization', 'Products', 'edit_posts','edit.php?post_type=wpcuebasicproduct');
			$report_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Report Generation', 'Report Generation', 'edit_posts','wpcuequizreport',array(&$this,'wpcue_proquiz_report_page'));
			$quizstat_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Statistics', 'Statistics', 'edit_posts','wpcuequizstatistics',array(&$this,'wpcue_proquiz_quizstat_page'));
			$quizsetting_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Settings', 'Settings','edit_posts','wpcuequizsetting',array(&$this,'wpcue_proquiz_setting_page'));
			add_submenu_page(null, 'Add New Certificate', 'Add New Certificate', 'edit_posts','wpcuequizcertificate',array(&$this,'wpcue_proquiz_createcertificate_page'));
			add_submenu_page(null, 'Add New Badge', 'Add New Badge', 'edit_posts','wpcuequizbadge',array(&$this,'wpcue_proquiz_createbadge_page'));
			add_submenu_page(null, 'Add New Level', 'Add New Level', 'edit_posts','wpcuequizlevel',array(&$this,'wpcue_proquiz_createlevel_page'));
			$quizmonetize_hook_suffix=add_submenu_page(null, 'Add New Product', 'Add New Product', 'edit_posts','wpcuequizproduct',array(&$this,'wpcue_proquiz_createproduct_page'));
			//add admin scripts to menu and submenu pages
			add_action('load-' . $createquiz_hook_suffix, array(&$this,'wpcue_createquizpage_add'));
			add_action('load-'.$quizstat_hook_suffix,array(&$this,'load_quizstat_script'));
			add_action('load-edit-tags.php',array(&$this,'load_questcat_script'));
			add_action('load-'.$report_hook_suffix,array(&$this,'load_report_script'));
			add_action('load-'.$quizsetting_hook_suffix,array(&$this,'load_quizsetting_script'));
			add_action('load-'.$quizmonetize_hook_suffix,array(&$this,'load_monetize_script'));
		}
		public function load_questcat_script(){
			$screen = get_current_screen();
			if (!isset($screen->taxonomy)){return;}
			$taxonomy=$screen->taxonomy;
			switch($taxonomy){
				case 'wpcuebasicquestcat':
					add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_questcat_scripts'));
					break;
				case 'wpcuebasicquizcat':
					add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_quizcat_scripts'));
					break;
			}
		}
		public function load_report_script(){
			add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_report_scripts'));
		}
		public function wpcue_proquiz_questcat_scripts(){
			wp_register_script( 'wpcuebasicquiz-questcat', WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-questcat.js',array('jquery') );
			wp_enqueue_script('wpcuebasicquiz-questcat');
		}
		public function wpcue_proquiz_quizcat_scripts(){
			wp_register_script( 'wpcuebasicquiz-quizcat', WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-quizcat.js',array('jquery') );
			wp_enqueue_script('wpcuebasicquiz-quizcat');
		}
		public function load_quizsetting_script(){
			wp_register_style( 'wpcuebasicquiz-createquiz', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz.css');
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_register_script('wpcuebasicquiz-quizsetting',WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-quizsetting.js',array('jquery','jquery-ui-core','jquery-ui-tabs'));
			wp_enqueue_script('wpcuebasicquiz-quizsetting');
		}
		public function load_monetize_script(){
			wp_register_style( 'wpcuebasicquiz-createquiz', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz.css');
			wp_register_script('wpcuebasicquiz-product',WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-product.js',array('jquery-ui-datepicker','jquery','jquery-ui-core'));
			wp_enqueue_script('wpcuebasicquiz-product');
			$productL10n=array('producttrash'=>__('could not be trashed','wpcues-basic-quiz'),
								'itemeditor'=>__('Item Editor already open','wpcues-basic-quiz'),
								'publish'=>__('publish','wpcues-basic-quiz'),
								'errormsg'=>__('Your request to fetch item failed. Please try again','wpcues-basic-quiz'),
								'additemmsg'=>__('Please add any item first.','wpcues-basic-quiz'),
								'addproducttitle'=>__('Please add product name','wpcues-basic-quiz')
								);
			wp_localize_script('wpcuebasicquiz-product','productL10n',$productL10n);
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_enqueue_script('jquery-ui-tabs');
		}
		public function dynaminc_css(){
			require(WPCUES_BASICQUIZ_PATH."/admin/css/trial.php");
			exit;
		}
		/**
		* Enqueue scripts for report page
		*/
		public function wpcue_proquiz_report_scripts(){
			wp_register_style( 'wpcuebasicquiz-createquiz', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz.css');
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_register_script('wpcuebasicquiz-report', WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-report.js',array('jquery-ui-dialog','jquery-form','jquery','jquery-ui-tabs'));
			wp_enqueue_script('wpcuebasicquiz-report');
			$reportL10n=array('chartdelete'=>__('could not delete the entry','wpcues-basic-quiz'),
								'chartadded'=>__('Sorry, Chart could not be added','wpcues-basic-quiz'),
								'chartsaved'=>__('Sorry,Chart data could not be saved','wpcues-basic-quiz'),
								'edit'=>__('Edit','wpcues-basic-quiz'),
								'delete'=>__('Delete','wpcues-basic-quiz')
								);
			wp_localize_script('wpcuebasicquiz-report','reportL10n',$reportL10n);
		}
		
		
		/**
		* Create submenu Pages
		*/
		public function wpcue_proquiz_createquiz_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/createquiz.php");}
		public function wpcue_proquiz_quizstat_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/quizstat.php");}
		public function wpcue_proquiz_report_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/report.php");}
		public function wpcue_proquiz_setting_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/wpprocue_setting.php");}
		public function wpcue_proquiz_createbadge_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/edit-badge-form.php");}
		public function wpcue_proquiz_createlevel_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/edit-level-form.php");}
		public function wpcue_proquiz_createcertificate_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/edit-certificate-form.php");}
		public function wpcue_proquiz_createproduct_page(){require_once(WPCUES_BASICQUIZ_PATH."/admin/templates/edit-product-form.php");}
		/**
		* Add action to enqueue scripts and style
		*/
		public function wpcue_createquizpage_add($pagehook){
			$screen=get_current_screen();
			$screen->show_screen_options();
			add_filter('tiny_mce_before_init', array(&$this,'wpcue_change_mce_options'));
			add_filter('mce_external_plugins', array(&$this,'wpcue_custom_plugins'));
			add_filter('mce_buttons', array(&$this,'wpcue_register_mathslate_button'));
			add_action('admin_enqueue_scripts',array(&$this,'wpcue_createquiz_scripts'));
		}
		/**
		* Enqueue scripts for Quiz menu pages
		*/
		public function wpcue_createquiz_scripts(){
			global $wp_version;
			wp_register_script( 'wpcuebasicquiz-upload', WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-main.js',array('jquery','jquery-ui-core','jquery-ui-dialog','jquery-ui-tabs','jquery-ui-tooltip','postbox') );
			wp_register_script('wpcuebasicquiz-questioneditor',WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-questioneditor.js',array('jquery','jquery-ui-core','jquery-ui-tabs'));
			wp_register_script('wpcuebasicquiz-quizeditor',WPCUES_BASICQUIZ_URL.'/admin/js/wpcuebasicquiz-quizeditor.js',array('jquery','jquery-ui-core','jquery-ui-tabs'));
			if( version_compare($wp_version, '3.5', '<')){
				wp_register_style( 'wpcuebasicquiz-createquizold', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz-old.css');
				wp_enqueue_style('wpcuebasicquiz-createquizold');
				wp_register_style('jquery-smooth-old',WPCUES_BASICQUIZ_URL.'/admin/css/jquery-ui-smooth-old.css');
				wp_enqueue_style('jquery-smooth-old');
			}else{
				wp_register_style( 'wpcuebasicquiz-createquiz', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz.css');
				wp_enqueue_style('wpcuebasicquiz-createquiz');
			}
			wp_enqueue_script('wpcuebasicquiz-upload');
			$createquizL10n=array('update'=>__('Update','wpcues-basic-quiz'),
								'newcat'=>__('New Category Name','wpcues-basic-quiz'),
								'quiztit'=>__('Please Enter Quiz Title','wpcues-basic-quiz'),
								'savequest'=>__('Please first save the question','wpcues-basic-quiz'),
								'savegrade'=>__('Please first save the grade group','wpcues-basic-quiz'),
								'savepost'=>__('could not save the post','wpcues-basic-quiz'),
								'errormsg'=>__('Please try again.Some error occured','wpcues-basic-quiz'),
								'draftmsg'=>__('Please enter Quiz title first and click on Save Draft button','wpcues-basic-quiz'),
								'gradeadded'=>__('Grade group have already been added for this quiz','wpcues-basic-quiz'),
								'gradeeditor'=>__('Editor to add grade group is already open','wpcues-basic-quiz'),
								'quiztitle'=>__('Please enter Quiz title first','wpcues-basic-quiz'),
								'gradename'=>__('please enter Grade Group Name','wpcues-basic-quiz'),
								'gradetitle'=>__('please enter Grade Title for grade no.','wpcues-basic-quiz'),
								'gradedesc'=>__('please enter Grade Description for grade no.','wpcues-basic-quiz'),
								'gradebasis'=>__('please enter correct grade basis for grade no','wpcues-basic-quiz'),
								'errorshort'=>__('some error occured','wpcues-basic-quiz'),
								'catadd'=>__('Could not add the new category.Please try again','wpcues-basic-quiz'),
								'autodraft'=>__('auto-draft','wpcues-basic-quiz'),
								'edit'=>__('Edit','wpcues-basic-quiz'),
								'remove'=>__('Remove','wpcues-basic-quiz'),
								'publish'=>__('publish','wpcues-basic-quiz'),
								'quizupdated'=>__('Quiz Updated','wpcues-basic-quiz'),
								'quizsaved'=>__('Quiz Saved','wpcues-basic-quiz'),
								'quizpublished'=>__('Quiz Published','wpcues-basic-quiz'),
								'points'=>__('Points','wpcues-basic-quiz'),
								'corans'=>__('Correct Answer','wpcues-basic-quiz')
								);
			wp_localize_script('wpcuebasicquiz-upload','createquizL10n',$createquizL10n);
			wp_enqueue_script('wpcuebasicquiz-quizeditor');
			wp_enqueue_script('wpcuebasicquiz-questioneditor');
			wp_register_script('mathjax','//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
			wp_enqueue_script('mathjax');
			$questioneditorL10n=array('publish'=>__('publish','wpcues-basic-quiz'));
			wp_localize_script('wpcuebasicquiz-questioneditor','questioneditorL10n',$questioneditorL10n);
			$quizeditorL10n=array('editorexist'=>__('editor existing','wpcues-basic-quiz'),
								'errormsg'=>__('Somer error occured.Please try again','wpcues-basic-quiz'),
								'questeditor'=>__('Question editor is already Open.','wpcues-basic-quiz'),
								'draftmsg'=>__('Please enter Quiz title first and Save Quiz as draft','wpcues-basic-quiz'),
								'questtype'=>__('please select question type','wpcues-basic-quiz'),
								'questdet'=>__('please enter question details','wpcues-basic-quiz'),
								'answerdet'=>__('please enter answer for answer number','wpcues-basic-quiz'),
								'leftcolumn'=>__('in left column','wpcues-basic-quiz'),
								'rightcolumn'=>__('in right column','wpcues-basic-quiz'),
								'publish'=>__('publish','wpcues-basic-quiz'),
								'autodraft'=>__('auto-draft','wpcues-basic-quiz'),
								'savepost'=>__('could not save the post','wpcues-basic-quiz')
								);
			wp_localize_script('wpcuebasicquiz-quizeditor','quizeditorL10n',$quizeditorL10n);
		}
		public function wpcue_loginform(){
			$content='<a href="'.wp_registration_url().'">Register</a> | ';
			$content.='<a href="'.wp_lostpassword_url().'">Lost Password?</a>';
			return $content;
		}
		public function load_quizstat_script(){
			global $wpdb,$wp;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';		
			if(isset($_GET['tab'])){$activetab=$_GET['tab']-1;}else{$activetab=0;}
			if(isset($_GET['action'])){
				$action=$_GET['action'];
			}
			if(empty($activetab) && !(empty($action)) && ($action=='delete')){
				$arr_params = array( 'action');
				remove_query_arg( $arr_params );
				$current_uri=add_query_arg(array('action'=>'trashed'));
				$count=$_GET['trashed'];
				if($count>1){
					$instanceid=explode(',',$_GET['instance']);
				}else{
					$instanceid=(array)$_GET['instance'];
				}
				foreach($instanceid as $instance){
					$wpdb->delete($table_name,array('instanceid'=>$instance), array( '%d' ) );
				}
				wp_redirect($current_uri);
			}else{
				add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_quizstat_scripts'));
			}
		}
		public function wpcue_proquiz_quizstat_scripts(){
			wp_register_style( 'wpcuebasicquiz-createquiz', WPCUES_BASICQUIZ_URL.'/admin/css/wpcuebasicquiz-createquiz.css');
			wp_register_style('wpcuebasicquiz-report',WPCUES_BASICQUIZ_URL.'/common/css/wpcuebasicquiz-frontmain.css');
			wp_enqueue_script('jquery-ui');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_enqueue_style('wpcuebasicquiz-report');
		
		}
		/**
		* Generic Function to get summary
		*/
		public static function summary($str, $limit=100, $strip = false) {
				$str = ($strip == true)?strip_tags($str):$str;
				if (strlen ($str) > $limit) {
					$str = substr ($str, 0, $limit - 3);
					return (substr ($str, 0, strrpos ($str, ' ')).'...');
			}
			return trim($str);
		}
		private function wpcuebasicquiz_quizstatdb() {
			global $wpdb;
			$table_name1 = $wpdb->prefix.'wpcuequiz_quizstat';	
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}
			$sql1= "CREATE TABLE $table_name1 (
					instanceid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					starttime datetime DEFAULT '0000-00-00 00:00:00' NULL,
					endtime datetime,
					quizid bigint(20) unsigned NOT NULL,
					userid bigint(20) unsigned NOT NULL,
					grade varchar(20),
					certificate bigint(20) unsigned,
					mode tinyint(2) unsigned,
					status tinyint(1) unsigned,	
					timeremaining int(10) unsigned,
					processed tinyint(1) DEFAULT 0 NOT NULL,
					UNIQUE KEY id (instanceid)
				) $charset_collate;";
			$table_name2=$wpdb->prefix.'wpcuequiz_quizinfo';
			$sql2="CREATE TABLE $table_name2 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					quizid bigint(20) unsigned NOT NULL,
					entityid bigint(20) unsigned NOT NULL,
					parentid bigint(20),
					entityorder DECIMAL(15,8) unsigned,
					category int(10),
					point int(10),
					questionchange tinyint(1) DEFAULT 0 NOT NULL,
					questionchangedate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name3=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$sql3="CREATE TABLE $table_name3 (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				instanceid bigint(20) unsigned NOT NULL,
				entityid bigint(20) unsigned NOT NULL,
				answer text(20),
				reply text(20),
				point int(4),
				status tinyint(3) unsigned,
				disabled tinyint(1) DEFAULT 0 NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name4=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
			$sql4="CREATE TABLE $table_name4 (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				instanceid bigint(20) unsigned NOT NULL,
				quizid bigint(20) unsigned NOT NULL,
				entityid bigint(20) unsigned NOT NULL,
				errorid bigint(20) unsigned NOT NULL,
				status tinyint(2) unsigned NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name5=$wpdb->prefix.'wpcuequiz_productinfo';
			$sql5="CREATE TABLE $table_name5 (
					productid bigint(20) unsigned NOT NULL,
					itemid bigint(2) unsigned NOT NULL,
					itemtype tinyint(2) unsigned NOT NULL
				) $charset_collate;";
			$table_name6=$wpdb->prefix.'wpcuequiz_productsale';
			$sql6="CREATE TABLE $table_name6 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					productid bigint(20) unsigned NOT NULL,
					userid bigint(2) unsigned NOT NULL,
					purchasedate datetime,
					UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name7=$wpdb->prefix.'wpcuequiz_badgestat';
			$sql7="CREATE TABLE $table_name7 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					userid bigint(20) unsigned NOT NULL,
					badgeid bigint(2) unsigned NOT NULL,
					issueddate  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					status tinyint(1) DEFAULT 0 NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql1 );dbDelta( $sql2 );dbDelta( $sql3 );
			dbDelta($sql4);dbDelta($sql5);dbDelta($sql6);dbDelta($sql7);
		}
		private function add_default_settings(){
			$settings=$this->_config->setting;
			update_option('wpcuequiz_setting',$settings);
		}
		public function wpcue_changequiztype($new_value,$old_value){
			if($new_value['basic']['quiztype'] != 1){
				if($new_value['basic']['quiztype'] != $old_value['basic']['quiztype']){
					$new_value['basic']['quizchanged']=1;
				}elseif($new_value['basic']['quizslug'] != $old_value['basic']['quizslug']){
					$new_value['basic']['quizchanged']=1;
				}
			}
			return $new_value;
		}
		public function wpcue_change_mce_options($initArray) {
				//$initArray['verify_html'] = false;
				//$initArray['remove_redundant_brs'] = false;
				//$initArray['remove_linebreaks'] = false;
				//$initArray['force_br_newlines'] = false;
			return $initArray;
		}
		public function wpcue_versioncheck(){
			$wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');
			if(empty($wpcuebasicquiz_version)){update_option('wpcuebasicquiz_version',1);}
		}
		public function wpcuequiz_rewrite_rules(){
			add_rewrite_rule('wpcuecertificate/?([^/]*)', 'index.php?pagename=wpcuecertificate&wpcuecertificateid=$matches[1]', 'top');
			add_rewrite_rule('wpcuenewbadge/?([^/]*)','index.php?pagename=wpcuenewbadge&wpcuebadgeuid=$matches[1]', 'top');
			add_rewrite_rule('wpcuebadgejson/?([^/]*)','index.php?pagename=wpcuebadgejson&wpcuebadgeuid=$matches[1]', 'top');
			add_rewrite_rule('wpcuebadgeclassjson/?([^/]*)','index.php?pagename=wpcuebadgeclassjson&wpcuebadgeid=$matches[1]', 'top');
			add_rewrite_rule('wpcueissuerjson/?([^/]*)','index.php?pagename=wpcueissuerjson', 'top');
			add_rewrite_rule('wpcuedynamiccss/?([^/]*)','index.php?pagename=wpcuedynamiccss&wpcuequizid=$matches[1]', 'top');
		}
		public function wpcue_proquiz_startquiz(){
			ob_start();
			global $wpdb;
			$wpprocuesetting=$this->_config->setting;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';	
			$quizid=intval($_REQUEST['quizid']);
			$quizmeta=get_post_custom($quizid);$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);
			$instanceid=$_REQUEST['instanceid'];$previnstance=$instanceid;
			$user_ID = get_current_user_id();
			if(empty($instanceid)){
				$previnstance=0;
				$post=$wpdb->query($wpdb->prepare("INSERT INTO $table_name (quizid,userid,timeremaining,mode,starttime,endtime,status) VALUES (%d,%d,%d,%d,now(),now(),0)",$quizid,$user_ID,intval($basicsetting['duration']),intval($basicsetting['mode'])));
				$instanceid=$wpdb->insert_id;
			}else{
				$post=$wpdb->query($wpdb->prepare("UPDATE $table_name SET starttime=now(),endtime=now() where instanceid=%d",$instanceid));
			}
			if(!empty($basicsetting['addcaptcha'])){
				$captchastatus=0;
				if(empty($wpprocuesetting['recaptcha']['privatekey'])){$captchastatus+=1;}
				if(empty($wpprocuesetting['recaptcha']['publickey'])){$captchastatus+=2;}
				if(empty($captchastatus)){
					wp_register_script('google-recaptcha','https://www.google.com/recaptcha/api.js');
					wp_enqueue_script('google-recaptcha');
				}	
			}else{$captchastatus=4;}
			$content=$this->get_mainpage($quizmeta,$quizid,$previnstance,$captchastatus);
			if($post){
				echo json_encode(array('msg'=>'success','instance'=>$instanceid,'content'=>$content));
			}else{
				echo json_encode(array('msg'=>'failed'));
			}
			echo ob_get_clean();
			die();
		}
		public function wpcue_proquiz_final_result(){
			ob_start();
			global $wpdb;
			$wpprocuesetting=$this->_config->setting;
			$table_name = $wpdb->prefix.'wpcuequiz_quizstat';$quizinfotable=$wpdb->prefix.'wpcuequiz_quizinfo';
			if(get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
			$myformdata=stripslashes($_REQUEST['myformdata']);
			}else{$myformdata=$_REQUEST['myformdata'];}
			parse_str($myformdata,$output);
			$action=$_REQUEST['quizaction'];
			if(!(empty($_REQUEST['matchquestres']))){$matchquestres=$_REQUEST['matchquestres'];}
			$questids=$output['questionid'];
			$current_user = wp_get_current_user();
			$user_ID = $current_user->ID;
			$quizid=intval($output['quizid']);
			$args = array('post__in'=>$questids,'post_type'=>array('wpcuebasicsection','wpcuebasicquestion'),'orderby'=>'post__in','posts_per_page' => -1);
			$entityquery = new WP_Query($args);
			$flippedquestid=array_flip($questids);
			$quizmeta=get_post_custom($quizid);
			if(!empty($quizmeta['basicsetting'][0])){$basicsetting=maybe_unserialize($quizmeta['basicsetting'][0]);}else{$basicsetting=array();}
			if(!empty($quizmeta['displaysetting'][0])){$displaysetting=maybe_unserialize($quizmeta['displaysetting'][0]);}else{$displaysetting=array();}
			$report='';$emailreport='';$i=1;$j=1;
			if($action=='submit'){
				$report.='<div class="quizreport">';	
			}
			$quiz=get_post($quizid);$quizstatinfo='';
			$quiztitle=$quiz->post_title;
			$postauthor=intval($quiz->post_author);
			$instanceid=$_REQUEST['instanceid'];$percent=0;$pointscored=0;$errors=array();
			$errortable=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
			if(!empty($displaysetting['showquestsymbol'])){$showquestsymbol=$displaysetting['showquestsymbol'];}else{$showquestsymbol=0;}
			if(empty($wpprocuesetting['text']['questionsymbol'])){$questionsymbol='';}else{$questionsymbol=$wpprocuesetting['text']['questionsymbol'];}
			if(!empty($displaysetting['showquestnumber'])){$showquestnumber=$displaysetting['showquestnumber'];}else{$showquestnumber=0;}
			if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailreport.='<table>';}
			$totalent=$entityquery->found_posts;
			$table_name1=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$mappedentids=$wpdb->get_results($wpdb->prepare("select entityid,id from $table_name1 where instanceid=%d",$instanceid),OBJECT_K);
			while ($entityquery->have_posts()){
				$entityquery->the_post();
				$entitypost=$entityquery->post;
				$point=0;
				if($entitypost->post_type=='wpcuebasicsection'){
					if(empty($mappedentids)){
						$quizstatar=array($instanceid,$entitypost->ID,'NULL','NULL',0,-1,0);
					}else{
						$quizstatar=array($mappedentids[$entitypost->ID]->id,$instanceid,$entitypost->ID,'NULL','NULL',0,-1,0);
					}
					$quizstatinfo.='('.implode(',',$quizstatar).')';
					if($action=='submit'){
						$report.='<div class"rowsec">'.$entitypost->post_content.'</div>';	
					}
					$i--;
				}elseif($entitypost->post_type =='wpcuebasicquestion'){
					$entitymeta=maybe_unserialize($entitypost->post_content);
					if(!empty($output['errorid-'.$entitypost->ID])){
						$this->insert_errorids($output['errorid-'.$entitypost->iD],$output,$instanceid);
					}
					$disabled=$output['disablestatus-'.$entitypost->ID];
					$wpcuequestion=$this->get_questionobject($entitymeta['t']);
					$resultmeta=$wpcuequestion->get_questionresult($output,$instanceid,$entitypost->ID,$entitymeta,$percent,$pointscored,$disabled);
					$pointscored=$resultmeta['pointscored'];$percent=$resultmeta['percent'];
					if(empty($mappedentids)){
						$quizstatinfo.="(".$instanceid.",".$entitypost->ID.",'".$resultmeta['answer']."','".$resultmeta['reply']."',".$resultmeta['point'].",".$resultmeta['correct'].",".$disabled.")";
					}else{
						$quizstatinfo.="(".$mappedentids[$entitypost->ID]->id.",".$instanceid.",".$entitypost->ID.",'".$resultmeta['answer']."','".$resultmeta['reply']."',".$resultmeta['point'].",".$resultmeta['correct'].",".$disabled.")";
					}
					if($action=='submit'){
						$report.=$this->wpcue_report($resultmeta['answer'],$resultmeta['reply'],$resultmeta['correct'],$entitymeta,$basicsetting['discloseans'],$i,0,$showquestsymbol,$questionsymbol,$showquestnumber);
						if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){
							$emailreport.=$this->wpcue_report($resultmeta['answer'],$resultmeta['reply'],$resultmeta['correct'],$entitymeta,$basicsetting['discloseans'],$i,1,$showquestsymbol,$questionsymbol,$showquestnumber);
						}
					}
				}
				if(($totalent>1) && ($j<$totalent)){$quizstatinfo.=',';}
				$i++;$j++;	
			}
			if(empty($mappedentids)){
				$status=$wpdb->query("INSERT INTO $table_name1 (instanceid,entityid,answer,reply,point,status,disabled) VALUES $quizstatinfo");
			}else{
				$status=$wpdb->query("INSERT INTO $table_name1 (id,instanceid,entityid,answer,reply,point,status,disabled) VALUES $quizstatinfo ON DUPLICATE KEY UPDATE id=VALUES(id),instanceid=VALUES(instanceid),answer=VALUES(answer),reply=VALUES(reply),point=VALUES(point),status=VALUES(status),disabled=VALUES(disabled)");
			}
			
			if($action=='submit'){
				$report.='</div>';
				if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailreport.='</table>';}	
			}
			$currtime=$wpdb->get_row("select NOW() as curtime from $table_name");
			$now = new DateTime($currtime->curtime);
			$datesent=$now->format('Y-m-d H:i:s');
			$timeremaining=(int)$_REQUEST['timeremaining'];
			$error=0;
			$quizinfo=$this->quizinfo($quizid);
			$percent=($percent/$quizinfo['totalquestions']);
			if(!(empty($quizmeta['quizgrade'][0]))){$gradedef=$quizmeta['quizgrade'][0];}
			if($action=='submit'){
				$grade='';
				$gradedesc='';
				$certi=0;$assignedgradeid='';
				if(!(empty($gradedef))){
					$gradepost=get_post($gradedef);
					$grademeta=unserialize($gradepost->post_content);
					$gradeobtained=$this->get_gradeobtained($grademeta,$pointscored,$percent);
					if(!empty($gradeobtained)){
						$grade=$gradeobtained['grade'];
						$gradedesc=$gradeobtained['gradedesc'];
						$certi=$gradeobtained['certi'];$assignedgradeid=$gradeobtained['assignedgradeid'];
					}
				}
			}
			if($action == 'submit'){
				if(empty($grade)){$grade='';}if(empty($certi)){$certi=0;} 
					$status=$wpdb->update($table_name,array('endtime'=>$datesent,'status'=>1,'timeremaining'=>$timeremaining,'certificate'=>$certi,'grade'=>$assignedgradeid)
					,array('instanceid'=>$instanceid),array('%s','%d','%d','%d','%s'),array('%d'));
					if(!($status)){$error=1;}
			}else{
				$status=$wpdb->update($table_name,array('endtime'=>$datesent,'status'=>0,'timeremaining'=>$timeremaining),array('instanceid'=>$instanceid),array('%s','%d','%d'),array('%d'));
				if($status === false){$error=1;}
			}
			if(($error == 0) &&($action=='submit')){
				
				if((!(empty($basicsetting['notifyadmin']))) || (!(empty($basicsetting['notifyuser'])))){$emailprocess=1;}else{$emailprocess=0;}
				if(!(empty($emailprocess))){if(!(empty($basicsetting['notifyadmin']))){
					$adminemail=maybe_unserialize($quizmeta['quizadminemail'][0]);
					if(!(empty($adminemail['subject']))){
						$adminemailsubj=$adminemail['subject'];
					}else{
						$adminemailsubj='New Quiz Result';
					}
					if(!(empty($adminemail['mail']))){
						$adminemail=$adminemail['mail'];
					}else{$adminemail='User '.$user_ID.' has just taken quiz '.$quizid;}
				}
				if(!(empty($basicsetting['notifyuser']))){
					$useremail=maybe_unserialize($quizmeta['quizuseremail'][0]);
					if(!(empty($useremail['subject']))){
						$useremailsubject=$useremail['subject'];
					}else{
						$useremailsubject='New Quiz Result';
					}
					if(!(empty($useremail['mail']))){
						$useremail=$useremail['mail'];
					}
				}}else{$adminemailsubj='';$adminemail='';$useremailsubject='';$useremail='';}
				$quizlast=$wpdb->get_row($wpdb->prepare("select * from $table_name where instanceid=%d",$instanceid),ARRAY_A );
				$contentarray=$this->getfinal_content($quizid,$instanceid,$quizmeta['quizfinal'][0],$quizlast,$quizmeta,$quiztitle,$quizinfo['totalquestions'],$quizinfo['totalpoint'],$emailprocess,
				$adminemailsubj,$adminemail,$useremailsubject,$useremail,$report,$emailreport,$grade,$gradedesc);
				if(!(empty($emailprocess))){
					if(!(empty($basicsetting['notifyadmin']))){
						$adminemailsubj=$contentarray[1];
						$adminemail=$contentarray[2];
						add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
						wp_mail(get_the_author_meta('user_email',$postauthor),$adminemailsubj,$adminemail);
						remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					}
					if(($user_ID != 0) && (!(empty($basicsetting['notifyuser'])))){
						$useremailsubject=$contentarray[3];
						$useremail=$contentarray[4];
						add_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
						wp_mail($current_user->user_email,$useremailsubject,$useremail);
						remove_filter('wp_mail_content_type',array(&$this,'wpcuemail_set_content_type'));
					}
				}	
				$finaldesc='<div id="quizfinalcontent">'.$contentarray[0].'</div>';
				$socialdescstat=strpos($contentarray[0],'%%SOCIALSHARE%%');
				if($socialdescstat){
					$socialshare='<iframe src="http://www.facebook.com/plugins/like.php?href='.get_permalink($quizid).'&width&layout=button_count&action=like&show_faces=false&share=false&height=35&appId=" frameBorder="0" width="150" height="25">
</iframe>';
					$socialshare.="<div class='wpcue-twitshare'><a href='".get_permalink($quizid)."' class='twitter-share-button' data-text='anything' data-count='none'>t</a>";
					$socialshare.="<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script></div>";
					$finaldesc=str_replace('%%SOCIALSHARE%%',$socialshare,$finaldesc);
				}	
				echo $finaldesc;
				
			}
			echo ob_get_clean();
			die();
		}
		public function get_gradeobtained($grademeta,$pointscored,$percent){
			$gradeobtained=array();
			if($grademeta['gradebase']==1){
						foreach($grademeta['gradeid'] as $gradeid){
							if($pointscored >= intval($grademeta[$gradeid]['gradebasefrom']) && $pointscored < intval($grademeta[$gradeid]['gradebaseto'])){
								$gradeobtained['assignedgradeid']=$gradeid;
								break;
							}
						}
					}else{
						foreach($grademeta['gradeid'] as $gradeid){
							if($percent >= intval($grademeta[$gradeid]['gradebasefrom']) && $percent < intval($grademeta[$gradeid]['gradebaseto'])){
								$gradeobtained['assignedgradeid']=$gradeid;
								break;
							}
						}
					}
				if(!empty($gradeobtained)){
					$gradeobtained['grade']=$grademeta[$gradeobtained['assignedgradeid']]['title'];
					$gradeobtained['gradedesc']=$grademeta[$gradeobtained['assignedgradeid']]['content'];
					$gradeobtained['certi']=$grademeta[$gradeobtained['assignedgradeid']]['certi'];	
				}
				return $gradeobtained;	
		}
		public function wpcue_allowed_html($tags){
			$tags['iframe']=array('src'=>1);
			//array_push($tags['div'],'data-send'=>1);
			$tags['div']['data-send']=1;$tags['div']['data-width']=1;$tags['div']['data-show-faces']=1;
			$tags['div']['data-href']=1;$tags['div']['data-action']=1;$tags['div']['data-layout']=1;$tags['div']['data-share']=1;
			//$tags['div']=array('data-send'=>1,'data-width'=>1,'data-show-faces'=>1);
			$tags['math']=array('display'=>1,'class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'display'=>1,'mode'=>1,'overflow'=>1);
			$tags['mi']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['mo']=array('accent'=>1,'class'=>1,'id'=>1,'style'=>1,'dir'=>1,'fence'=>1,'form'=>1,'href'=>1,'largeop'=>1,'lspace'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1,'maxsize'=>1,'minsize'=>1,'movablelimits'=>1,'rspace'=>1,'separator'=>1,'stretchy'=>1,'symmetric'=>1);
			$tags['mn']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['mtext']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['ms']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1,'lquote'=>1,'rquote'=>1);
			$tags['msub']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1);
			$tags['msup']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'superscriptshift'=>1);
			$tags['msubsup']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1,'superscriptshift'=>1);
			$tags['munder']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accentunder'=>1,'align'=>1);
			$tags['mover']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accent'=>1,'align'=>1);
			$tags['munderover']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accent'=>1,'accentunder'=>1,'align'=>1);
			$tags['mmultiscripts']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1,'superscriptshift'=>1);
			$tags['mrow']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mfrac']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'linethickness'=>1,'mathbackground'=>1,'mathcolor'=>1,'numalign'=>1);
			$tags['msqrt']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mroot']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mpadded']=array('class'=>1,'id'=>1,'style'=>1,'depth'=>1,'height'=>1,'href'=>1,'lspace'=>1,'mathbackground'=>1,'mathcolor'=>1,'voffset'=>1,'width'=>1);
			$tags['mphatnom']=array('class'=>1,'id'=>1,'style'=>1,'mathbackground'=>1);
			$tags['mfenced']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'close'=>1,'open'=>1,'separators'=>1);
			$tags['menclose']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'notation'=>1);
			$tags['mtable']=array('align'=>1,'alignmentscope'=>1,'class'=>1,'id'=>1,'style'=>1,'columnalign'=>1,'columnlines'=>1,'columnspacing'=>1,'columnwidth'=>1,'displaystyle'=>1,'equalcolumns'=>1,'equalrows'=>1,'frame'=>1,'framespacing'=>1,'groupalign'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'minlabelspacing'=>1,'rowalign'=>1,'rowlines'=>1,'rowspacing'=>1,'side'=>1,'width'=>1);
			$tags['mtd']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'columnalign'=>1,'columnspan'=>1,'groupalign'=>1,'rowalign'=>1,'rowspan'=>1);
			$tags['mtr']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'columnalign'=>1,'groupalign'=>1,'rowalign'=>1);
			$tags['maction']=array('actiontype'=>1,'class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'selection'=>1);
			$tags['mstyle']=array('dir'=>1,'decimalpoint'=>1,'displaystyle'=>1,'infixlinebreakstyle'=>1,'scriptlevel'=>1,'scriptminsize'=>1,'scriptsizemultiplier'=>1);
			$tags['mglyph']=array('alt'=>1,'class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'height'=>1,'src'=>1,'valign'=>1,'width'=>1);
			$tags['mspace']=array('class'=>1,'id'=>1,'style'=>1,'depth'=>1,'mathbackground'=>1,'height'=>1,'linebreak'=>1,'width'=>1);
			$tags['mgroupalign']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'groupalign'=>1);
			$tags['mstack']=array('mathcolor'=>1,'mathbackground'=>1,'align'=>1,'stackalign'=>1,'charalign'=>1,'charspacing'=>1);
			$tags['mlongdiv']=array('mathcolor'=>1,'mathbackground'=>1,'align'=>1,'stackalign'=>1,'charalign'=>1,'charspacing'=>1,'longdivstyle'=>1);
			return $tags;
		}
		public function proquiz_settings_link($links)
    { 
        $settings_link = '<a href="edit.php?post_type=wpcuebasicquiz&page=wpcuequizsetting">Settings</a>'; 
        array_unshift($links, $settings_link); 
        return $links; 
    }
		
	
	}
	
}

/* EOF */