<?php
    /******************************************************************************************************************

    Plugin Name: Innografie Tool

    Plugin URI:

    Description:

    Version: 1.0

    Author:
    	
    *******************************************************************************************************************/

    load_plugin_textdomain('twentytwelve', false, basename(dirname(__FILE__)));

    add_action("preprocess_signup_form", "fbpsych_preprocess_signup_form");

    function fbpsych_preprocess_signup_form()
    {
    	// ensure "create a blog" is selected
    	$_POST['signup_for'] = "blog";
    }

    add_action("signup_hidden_fields", "fbpsych_signup_hidden_fields");

    function fbpsych_signup_hidden_fields()
    {
    	?>
    		<input type="hidden" value="validate-blog-signup" name="stage">
    		<input type="hidden" value="<?php echo 'a' . str_replace(".", "", uniqid("", true)); ?>" name="blogname">
    		<input type="hidden" value="Innografie-Tool" name="blog_title">
    		<input type="hidden" value="0" name="blog_public_off">
    	<?php
    }

    add_action("signup_finished", "fbpsych_signup_finished");

    function fbpsych_signup_finished()
    {
    	global $wpdb;
    	$query = "SELECT * FROM {$wpdb->signups} WHERE active = '0'";
    	$results = $wpdb->get_results($query, ARRAY_A);
    	
    	foreach ( $results as $row )
    	{
    		$key = $row["activation_key"];
    		wpmu_activate_signup($key);
    	}
    	
    	?>
    		<p><h2><a href="<?php echo get_site_url(1); ?>">Zurück zur Startseite</a></h2></p>
    	<?php
    }

    add_action("login_head", "fbpsych_login_head");

    function fbpsych_login_head()
    {
    	// redirect to main blog, if this is a multisite login
    	if ( !is_main_site() )
    	{
    		wp_redirect(get_site_url(1));
    		exit;
    	}
    }

    add_filter("wpmu_signup_blog_notification", "fbpsych_wpmu_signup_blog_notification", 1, 4);

    function fbpsych_wpmu_signup_blog_notification($user, $mail, $key, $meta = "")
    {
    	return false;
    }

    add_action("wpmu_new_blog", "fbpsych_wpmu_new_blog", 11, 2);

    function fbpsych_wpmu_new_blog($blogId, $userId)
    {
    	switch_to_blog($blogId);

        // switch theme
        switch_theme('innografie');

        // update user role
        wp_update_user(array(
            'ID'    => $userId,
            'role'  => 'Subscriber',
        ));

    	// set titles for inno widgets
    	update_option('widget_fbpsych_survey', array(2 => array('title' => 'Befragungen'), '_multiwidget' => 1));
    	update_option('widget_fbpsych_login', array(2 => array('title' => 'Account'), '_multiwidget' => 1));

        // configure sidebar
        update_option('sidebars_widgets', array(
            'wp_inactive_widgets' => array (),
            'sidebar-1' => array(
                0 => 'fbpsych_login-2',
                1 => 'fbpsych_survey-2',
            ),
            'array_version' => 3
        ));

        // cleanup initially created content
    	wp_delete_post(1, true);
    	wp_delete_post(2, true);
    	wp_delete_post(3, true);
    	
    	// empty blogname and description
    	update_option("blogname", "");
        update_option('blogdescription', '');
    	
    	restore_current_blog();
    }

    // extend network admin menu
    function fbpsych_network_admin_menu()
    {
        // add a top level menu page
        add_menu_page("Fragebögen", "Fragebögen", "manage_network", "admin_menu_surveys", "fbpsych_network_admin_surveys");

        add_submenu_page("admin_menu_surveys", "PDF-Versionen", "PDF-Versionen", "manage_network", "admin_menu_surveys_pdf", "fbpsych_network_admin_surveys_pdf");
        add_submenu_page("admin_menu_surveys", "Mailvorlagen", "Mailvorlagen", "manage_network", "admin_menu_mail_templates", "fbpsych_network_admin_mail_templates");
        add_submenu_page("admin_menu_surveys", "FB <=> Account", "FB <=> Account", "manage_network", "admin_menu_projects", "fbpsych_network_admin_projects");
        add_submenu_page("admin_menu_surveys", "Übersicht Umfragen", "Übersicht Umfragen", "manage_network", "admin_menu_overview", "fbpsych_network_admin_overview");
    }
    add_action("network_admin_menu", "fbpsych_network_admin_menu");

    function fbpsych_network_admin_surveys()
    {
        require_once(plugin_dir_path(__FILE__) . "fbpsych_network_admin_surveys.php");
    }

    function fbpsych_network_admin_surveys_pdf()
    {
        require_once(plugin_dir_path(__FILE__) . "fbpsych_network_admin_surveys_pdf.php");
    }

    function fbpsych_network_admin_mail_templates()
    {
        require_once(plugin_dir_path(__FILE__) . "fbpsych_network_admin_mail_templates.php");
    }

    function fbpsych_network_admin_projects()
    {
        require_once(plugin_dir_path(__FILE__) . "fbpsych_network_admin_projects.php");
    }

    function fbpsych_network_admin_overview()
    {
        require_once(plugin_dir_path(__FILE__) . "fbpsych_network_admin_overview.php");
    }

    // Redirect admins to the dashboard and other users elsewhere
    add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );
    function my_login_redirect( $redirect_to, $request, $user ) {
    	// Is there a user?
    	if ( isset($user->roles) && is_array( $user->roles ) ) {
    		// Is it an administrator?
    		if ( in_array( 'administrator', $user->roles ) )
    			return home_url( '/wp-admin/' );
    		else
    			return home_url();
    	}
    }

    // add widgets
    require_once("fbpsych_login_widget.php");
    add_action("widgets_init", function()
    {
    	return register_widget("fbpsych\loginWidget");
    });

    require_once("fbpsych_survey_widget.php");
    add_action("widgets_init", function()
    {
    	return register_widget("fbpsych\surveyWidget");
    });

    function shortcode_collapsable($attributes, $content)
    {
    	extract(shortcode_atts(array(
    		"default" => "visible"
    	), $attributes));
    	
    	if ($default === "visible") {
    		$display = "block";
    	} else {
    		$display = "none";
    	}
    	
    	$return = '
    		<div class="wipeWrapper">
    			<div class="wipeHeader">
    				<a class="wipeSuccessor" href="#">[ein-/ausklappen]</a>
    			</div>
    			<div class="wipeActor" style="display: ' . $display . ';">
    				<div class="wipeContent">' . $content . '</div>
    			</div>
    		</div>
    	';
    	
    	return $return;
    }
    add_shortcode('collapsable', 'shortcode_collapsable');

    // activation/deactivation function
    register_activation_hook(__FILE__, 'plugin_activate');
    register_deactivation_hook(__FILE__, 'plugin_deactivate');

    function plugin_activate() {
    	//fbpsych_add_rewrite();
    	//flush_rewrite_rules();
    }

    function plugin_deactivate() {
    	//flush_rewrite_rules();
    }

    // custom rewrite rules
    // function fbpsych_add_rewrite() {
    // 	add_rewrite_rule('quick/([0-9]+)/(.+)', 'index.php?pagename=quick&survey_id=$matches[1]', 'top');
    // }
    // add_action('init', 'fbpsych_add_rewrite');

    // query vars
    function fbpsych_query_vars($vars) {
    	$vars[] = 'survey_id';
    	return $vars;
    }
    add_filter('query_vars', 'fbpsych_query_vars');

    // quick links
    function fbpsych_quick_link() {
    	$pageName = get_query_var('pagename');
    	$surveyId = get_query_var('survey_id');
    	
    	if ($pageName == 'quick' && $surveyId != '') {
    		// get all wordpress multisite blog ids
    		global $wpdb;
    		$dbBlogIds = $wpdb->get_results($wpdb->prepare("SELECT blog_id FROM wp_blogs WHERE blog_id > 1"));
    		
    		$blogIds = array();
    		foreach ($dbBlogIds as $dbBlogId) {
    			$currentBlogId = $dbBlogId->blog_id;
    			
    			$surveys = get_blog_option($currentBlogId, "fbpsych_project_surveys");
    			
    			foreach ($surveys as $survey) {
    				if ( $survey["surveyId"] == $surveyId ) {
    					$surveyToken = $survey['surveyToken'];
    					
    					require_once(WP_CONTENT_DIR . "/plugins/fbpsych/config.php");
    						
    					$surveyUrl = LS_RUN_SURVEY_URL;
    					$surveyUrl = str_replace("%surveyId%", $surveyId, $surveyUrl);
    					$surveyUrl = str_replace("%token%", $surveyToken, $surveyUrl);
    					
    					wp_redirect($surveyUrl);
    					exit;
    				}
    			}
    		}
    		
    		// redirect to main page?
    		die("not found");
    		
    		exit;
    	}
    }
    add_filter('template_redirect', 'fbpsych_quick_link');

    // create survey
    function fbpsych_create_survey($data)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include("lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once("config.php");
    	
    	// get all available surveys
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);
    	
    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		$surveyExportBase64 = $rpcClient->export_survey($sessionKey, $data["surveyId"]);
    		if ( isset($surveyExportBase64) && is_array($surveyExportBase64) )
    		{
    			throw new ErrorException("export failed", 1001);
    		}
    		
    		// determe the new survey title
    		foreach ( fbpsych_get_surveys() as $survey )
    		{
    			if ( $survey["surveyId"] == $data["surveyId"] )
    			{
    				$newSurveyTitle= $survey["title"];
    					
    				$company = get_blog_option($userBlog->userblog_id, "cimy_uef_COMPANY");
    				if ( isset($company) && !empty($company) )
    				{
    					$newSurveyTitle .= " - " . $company;
    				}
    			}
    		}
    		
    		// reimport the survey structure via json-rpc
    		$importResponse = $rpcClient->import_survey($sessionKey, $surveyExportBase64, "lss", $newSurveyTitle);
    		
    		if ( !is_numeric($importResponse) )
    		{
    			throw new ErrorException("import failed", 1002);
    		}
    		
    		$newSurveyId =& $importResponse;
    		
    		// store the original id in the survey description
    		$sureyDescription = '<!-- ' . $data["surveyId"] . ' -->';
    		$setSurveyDescriptionResponse = $rpcClient->set_language_properties($sessionKey, $newSurveyId, array("surveyls_description" => $sureyDescription));
    		if ( $setSurveyDescriptionResponse["status"] !== "OK" )
    		{
    			throw new ErrorException("set survey description failed", 1006);
    		}
    		
    		// get id of question with code "teamwahl"
    		$rpcTeamQuestionId = $rpcClient->get_team_question_id($sessionKey, $newSurveyId, "teamWahl");
    		
    		if (is_numeric($rpcTeamQuestionId)) {
                // append team data
                $teamAnswers = array();

                $teamAnswers["de"] = $data["teams"];
                $teamAnswers["en"] = $data["teams"];
                
                // setup team questions
                $addTeamAnswersResponse = $rpcClient->add_team_answers($sessionKey, $newSurveyId, $rpcTeamQuestionId, $teamAnswers);
                
                if ($addTeamAnswersResponse["status"] !== "OK") {
                    throw new ErrorException("add team answers failed", 1004);
                }
    		}
    		
    		$activateSurveyResponse = $rpcClient->activate_survey($sessionKey, $newSurveyId);
    		
    		// initiate token table
    		$activateTokensResponse = $rpcClient->activate_tokens($sessionKey, $newSurveyId);
    		if ( $activateTokensResponse["status"] !== "OK")
    		{
    			throw new ErrorException("activate tokens failed", 1005);
    		}
    		
    		// create tokens
    		$tokenCount = $data["tokenCount"] + ceil($data["tokenCount"] * 0.1);
    		$addParticipantsResponse = $rpcClient->add_participants($sessionKey, $newSurveyId, array(
    			array(
    				"language"		=> "de",
    				"usesleft"		=> $tokenCount,
    			)
    		), true);
    		
    		if (!isset($addParticipantsResponse[0]) || !is_array($addParticipantsResponse[0])) {
    			throw new ErrorException("create tokens failed", 1006);
    		}
    		
    		$token = $addParticipantsResponse[0]['token'];
    		
    		// get the url to access the newly created survey containing the token
    		$surveyUrl = LS_RUN_SURVEY_URL;
    		$surveyUrl = str_replace("%surveyId%", $newSurveyId, $surveyUrl);
    		$surveyUrl = str_replace("%token%", $token, $surveyUrl);
    		
    		$rpcClient->release_session_key($sessionKey);
    		
    		// add new survey in wordpress
    		$wordpressSurveys = get_option("fbpsych_project_surveys");
    		
    		if ( $wordpressSurveys === false )
    		{
    			$wordpressSurveys = array();
    		}
    		
    		$wordpressSurveys[] = array(
    			"surveyName"	=> $data["surveyName"],
    			"surveyId"		=> $newSurveyId,
    			"title"			=> $newSurveyTitle,
    			"creationDate"	=> date("c", current_time("timestamp", 0)),
    			"templateId"	=> $data["surveyId"],
    			"surveyToken"	=> $token,
    			"tokenCount"	=> $data["tokenCount"],
    			"teamThreshold"	=> $data["teamThreshold"],
    			"teams"        	=> $data["teams"]
    		);
    		
    		update_option("fbpsych_project_surveys", $wordpressSurveys);
    		
    		$return = array(
    			"surveyUrl"		=> $surveyUrl,
    			"surveyId"		=> $newSurveyId,
    			"templateId"	=> $data["surveyId"]
    		);
    		
    		return $return;
    	}
    	catch ( ErrorException $e )
    	{
    		$rpcClient->release_session_key($sessionKey);
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    }

    function fbpsych_curl($url, $header = false, $post = null)
    {
    	// create a new curl instance
    	$curl = curl_init();
    		
    	// set headers
    	$headers = array(
    		"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    		"Cache-Control: max-age=0",
    		"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3",
    		//"Accept-Encoding: gzip,deflate,sdch",
    		"Accept-Language: de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4",
    		"Connection: keep-alive",
    		"Content-Type: application/x-www-form-urlencoded"
    	);
    		
    	// get url scheme
    	$scheme = parse_url($url, PHP_URL_SCHEME);
    		
    	// set options
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($curl, CURLOPT_HEADER, $header);
    		
    	if ( isset($scheme) && $scheme === "https" )
    	{
    		curl_setopt($curl, CURLOPT_SSLVERSION, 3);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    	}
    		
    	if ( $post !== null )
    	{
    		if ( is_array($post) )
    		{
    			$post = http_build_query($post);
    		}

    		curl_setopt($curl, CURLOPT_POST, true);
    		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    	}
    		
    	return $curl;
    }

    function fbpsych_parse_csv_rows($csvContent)
    {
    	preg_match_all('/.*?\"\r{0,1}(\n|$)/s', trim($csvContent), $matches);
    	if ($matches[0]) {
    		$rows = $matches[0];

    		foreach ($rows as $index => &$row) {
    			if (!empty($row)) {
    				$row = str_getcsv($row, ",");
    			} else {
    				unset($row[$index]);
    			}
    		}
    		return $rows;
    	}
    	
    	return array();
    }

    function fbpsych_get_percentage($surveyId)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include("lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once("config.php");

    	// get all available surveys
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);

    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		$csvResponsesEncoded = $rpcClient->export_responses($sessionKey, $surveyId, "csv", null, "complete", "code", "long");
    				
    		if ( isset($csvResponsesEncoded["status"]) && $csvResponsesEncoded["status"] === "No Data, could not get max id." ) {
    			$numResponses = 0;
    		} else if(is_string($csvResponsesEncoded)) {
    			$csvResponses = base64_decode($csvResponsesEncoded);
    			
    			// get number of lines
    			$numResponses = 0;
    			
    			$csvRowArray = fbpsych_parse_csv_rows($csvResponses);
    			foreach ($csvRowArray as $csvRow) {
    				if (!empty($csvRow)) {
    					$numResponses++;
    				}
    			}
    			
    			// in case of only incomplete answers we get an empty string
    			// so do not count the headline, if the current number of responses is greater than 0
    			if ($numResponses > 0) {
    				$numResponses--;
    			}
    		}
    		
    		$wordpressSurveys = get_option("fbpsych_project_surveys");
    			
    		if ( $wordpressSurveys === false )
    		{
    			$wordpressSurveys = array();
    		}
    		
    		foreach ( $wordpressSurveys as $survey )
    		{
    			if ( $survey["surveyId"] == $surveyId )
    			{
    				if ( isset($survey["tokenCount"]) )
    				{
    				   return min(array($numResponses * 100 / ((int) $survey["tokenCount"]), 100));
    				}
    			}
    		}
    		
    		return 0;
    	}
    	catch ( ErrorException $e )
    	{
    		$rpcClient->release_session_key($sessionKey);
    		
    		return $e->getCode();
    		//echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	
    	return false;
    }

    function fbpsych_get_ls_survey_properties($surveyId)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include("lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once("config.php");

    	// create RPC-Client
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);

    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);

    		$survey = $rpcClient->get_survey_properties($sessionKey, $surveyId, array('sid', 'starttdate', 'expires'));

    		return $survey;
    	}
    	catch ( ErrorException $e )
    	{
    		$rpcClient->release_session_key($sessionKey);
    		
    		return $e->getCode();
    		//echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	
    	return null;
    }

    function fbpsych_get_completed_surveys($surveyId)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include("lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once("config.php");

    	// create RPC-Client
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);

    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		$csvResponsesEncoded = $rpcClient->export_responses($sessionKey, $surveyId, "csv", null, "complete", "code", "long");
    		
    		if ( isset($csvResponsesEncoded["status"]) && $csvResponsesEncoded["status"] === "No Data, could not get max id." ) {
    			$numResponses = 0;
    		} else if(is_string($csvResponsesEncoded)) {
    			$csvResponses = base64_decode($csvResponsesEncoded);
    			
    			// get number of lines
    			$numResponses = 0;
    			
    			$csvRowArray = fbpsych_parse_csv_rows($csvResponses);
    			foreach ($csvRowArray as $csvRow) {
    				if (!empty($csvRow)) {
    					$numResponses++;
    				}
    			}
    			
    			// in case of only incomplete answers we get an empty string
    			// so do not count the headline, if the current number of responses is greater than 0
    			if ($numResponses > 0) {
    				$numResponses--;
    			}
    		}
    		
    		$wordpressSurveys = get_option("fbpsych_project_surveys");
    			
    		if ( $wordpressSurveys === false )
    		{
    			$wordpressSurveys = array();
    		}
    		
    		return $numResponses;
    	}
    	catch ( ErrorException $e )
    	{
    		$rpcClient->release_session_key($sessionKey);
    		
    		return $e->getCode();
    		//echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    	
    	return false;
    }

    function fbpsych_get_main_survey_information()
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
    	}

    	require_once(plugin_dir_path(__FILE__) . "config.php");

    	// get all available surveys
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);

    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);

    		$surveysFromLS = $rpcClient->list_surveys($sessionKey, null);

    		$rpcClient->release_session_key($sessionKey);

    		// get surveys from main blog
    		switch_to_blog(1);

    		$surveys = get_option("fbpsych_main_surveys");
    		if ( $surveys === false )
    		{
    			$surveys = array();
    		}

    		$return = array("wp" => array(), "ls" => array());

    		// determe survey name
    		foreach ( $surveys as $survey )
    		{
    			$return["wp"][$survey["surveyId"]] = $survey;
    		}

    		restore_current_blog();
    		
    		foreach ( $surveysFromLS as $surveyFromLS )
    		{
    			$return["ls"][$surveyFromLS["sid"]] = $surveyFromLS;
    		}

    		return $return;
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    }

    class RScriptException extends Exception
    {
    	public function __construct($message, $code = 0) {
    		parent::__construct($message, $code);
    	}
    }

    function fbpsych_evaluate_survey($surveyId, $templateId, $evaOption = 1)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include_once("lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once("config.php");

    	// get all available surveys
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);
    	
    	// create evaluation folder structure
        $folder = WP_CONTENT_DIR . "/../../evaluations/" . $surveyId . "/";
    	$staticFolder = WP_CONTENT_DIR . "/../../evaluations/static/";
    		
    	// delete evaluation folder if existing
    	if ( is_dir($folder) )
    	{
    		delTree($folder);
    	}
    	
    	mkdir($folder);
    	
    	// get the wp project survey
    	$wpProjectSurveys = get_option("fbpsych_project_surveys");
    	$projectSurvey = null;
    	
    	foreach ($wpProjectSurveys as &$wpProjectSurvey) {
    		if ($wpProjectSurvey["surveyId"] == $surveyId) {
    			$projectSurvey = &$wpProjectSurvey;
    			break;
    		}
    	}

    	try {
    		if ($projectSurvey == null) {
    			throw new Exception("project survey not found");
    		}
    		
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		if ( defined("FBP_DEBUG") && FBP_DEBUG === TRUE ) {
    			
    			// copy file
    			copy(LS_ANSWERS, $folder . 'survey.csv');
    		} else {
    			// put short answers in file
    			$csvShortResponsesEncoded = $rpcClient->export_responses($sessionKey, $surveyId, "csv", null, "all", "code", "short");
    			if (isset($csvShortResponsesEncoded["status"]) && $csvShortResponsesEncoded["status"] === "No Data, could not get max id.") {
    				throw new ErrorException("no responses", 1011);
    			}

    			$csvShortResponses = base64_decode($csvShortResponsesEncoded);
    			$shortResponseRows = fbpsych_parse_csv_rows($csvShortResponses);

    			$fileHandle = fopen($folder . 'answers-all-code-short.csv', 'w');
    			foreach ($shortResponseRows as $index => $responseRow) {
    				fputcsv($fileHandle, $responseRow);
    			}

    			// put long answers in file
    			$csvLongResponsesEncoded = $rpcClient->export_responses($sessionKey, $surveyId, "csv", null, "all", "code", "long");
    			if (isset($csvLongResponsesEncoded["status"]) && $csvLongResponsesEncoded["status"] === "No Data, could not get max id.") {
    				throw new ErrorException("no responses", 1011);
    			}

    			$csvLongResponses = base64_decode($csvLongResponsesEncoded);
    			$longResponseRows = fbpsych_parse_csv_rows($csvLongResponses);
    			$fileHandle = fopen($folder . 'answers-all-code-long.csv', 'w');
    			foreach ($longResponseRows as $index => $responseRow) {
    				fputcsv($fileHandle, $responseRow);
    			}
    		}
    		
    		// get evaluation r script
    		$surveys = fbpsych_get_surveys();
    		$rScript = "";
    		foreach ( $surveys as $survey ) {
    			if ( $survey["surveyId"] == $templateId) {
    				$rScriptVarName = "rScript" . $evaOption;
    				$rScript = $survey[$rScriptVarName];
    				
    				break;
    			}
    		}
    		
    		// check for zip archive
    		$pathInfo = pathinfo($rScript);
    		if ($pathInfo['extension'] == 'zip') {
    			$zip = new ZipArchive();
    			
    			if ($zip->open($rScript) === true) {
    				$zip->extractTo($folder);
    				$zip->close();
    			}
    		} else {
    			// copy r script
    			copy($rScript, $folder . "main.R");
    		}

            $workingDirectory = getcwd();
            chdir($folder);

            // copy static files
            if (!is_dir($staticFolder)) {
                mkdir($staticFolder);
            }
            recurse_copy($staticFolder, ".");
    		
    		// determ exec parameters
    		$params = array('--vanilla');
    		
    		$teamThreshold = 6;
    		if (isset($projectSurvey['teamThreshold'])) {
    			$teamThreshold = $projectSurvey['teamThreshold'];
    		}
    		$params[] = escapeshellarg($teamThreshold);
    		
    		// create shell command
    		$shellCommand = "RScript --vanilla main.R " . implode(" ", $params);
    		
    		// exec evaluation command
    		passthru("R CMD BATCH main.R", $return);
    		
    		if ( $return !== 0) {
    			throw new RScriptException("r script failed", 1010);
    		}
    		
    		$rpcClient->release_session_key($sessionKey);
    		
    		// generate a token for access protection
    		if (!isset($projectSurvey["accessToken"])) {
    			$projectSurvey["accessToken"] = uniqid("", true);
    		}

            // create .htaccess
            $htaccess = '
Options -Indexes
Order Allow,Deny
<FilesMatch "^index\.php$">
Allow from all
</FilesMatch>';
            file_put_contents($folder . ".htaccess", $htaccess);

            // token
            file_put_contents($folder . ".access", $projectSurvey["accessToken"]);
    		
    		chdir($workingDirectory);
    		
    		return array("status" => "success", "content" => $projectSurvey["accessToken"]);
    	} catch ( RScriptException $e ) {
    		$rpcClient->release_session_key($sessionKey);
    		
    		// check if there is a main.Rout file and get the content of it
    		if ( file_exists($folder . "main.Rout") ) {
    			if ( ($errorContent = file_get_contents($folder . "main.Rout")) !== false )
    			{
    				return array("status" => "script_error", "message" => $errorContent);
    			}
    		}
    		
    		return array("status" => "error", "code" => $e->getCode(), "message" => $e->getMessage());
    	} catch ( ErrorException $e ) {
    		$rpcClient->release_session_key($sessionKey);
    		
    		return array("status" => "error", "code" => $e->getCode(), "message" => $e->getMessage());
    	} catch ( Exception $e ) {
    	    return array("status" => "error", "code" => $e->getCode(), "message" => $e->getMessage());
    	}
    	
    	// store the wp project survey
    	update_option("fbpsych_project_surveys", $wpProjectSurveys);
    	
    	return array("status" => "error", "code" => "", "message" => "");
    }

    function delTree($dir) {
    	$files = array_diff(scandir($dir), array('.','..'));
    	foreach ($files as $file) {
    		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    	}
    	return rmdir($dir);
    }

    function fbpsych_delete_project_survey($surveyId)
    {
        // get current survey settings and set deletion flag
    	$wordpressSurveys = get_option("fbpsych_project_surveys");
        if ($wordpressSurveys === false) {
            $wordpressSurveys = array();
        }

        foreach ($wordpressSurveys as &$wordpressSurvey) {
            if ($wordpressSurvey["surveyId"] == $surveyId) {

                $wordpressSurvey['deleted'] = true;
                break;
            }
        }
    	
    	update_option("fbpsych_project_surveys", $wordpressSurveys);
    }

    function fbpsych_delete_blog( $blog_id, $drop = false ) {
    	global $wpdb, $current_site;

    	$switch = false;
    	if ( get_current_blog_id() != $blog_id ) {
    		$switch = true;
    		switch_to_blog( $blog_id );
    	}

    	$blog = get_blog_details( $blog_id );

    	do_action( 'delete_blog', $blog_id, $drop );

    	$users = get_users( array( 'blog_id' => $blog_id, 'fields' => 'ids' ) );

    	// Remove users from this blog.
    	if ( ! empty( $users ) ) {
    		foreach ( $users as $user_id ) {
    			remove_user_from_blog( $user_id, $blog_id );
    		}
    	}

    	update_blog_status( $blog_id, 'deleted', 1 );

    	// Don't destroy the initial, main, or root blog.
    	if ( $drop && ( 1 == $blog_id || is_main_site( $blog_id ) || ( $blog->path == $current_site->path && $blog->domain == $current_site->domain ) ) )
    		$drop = false;

    	if ( $drop ) {
    		$drop_tables = apply_filters( 'wpmu_drop_tables', $wpdb->tables( 'blog' ) );

    		foreach ( (array) $drop_tables as $table ) {
    			$wpdb->query( "DROP TABLE IF EXISTS `$table`" );
    		}

    		$wpdb->delete( $wpdb->blogs, array( 'blog_id' => $blog_id ) );

    		$uploads = wp_upload_dir();
    		$dir = apply_filters( 'wpmu_delete_blog_upload_dir', $uploads['basedir'], $blog_id );
    		$dir = rtrim( $dir, DIRECTORY_SEPARATOR );
    		$top_dir = $dir;
    		$stack = array($dir);
    		$index = 0;

    		while ( $index < count( $stack ) ) {
    			# Get indexed directory from stack
    			$dir = $stack[$index];

    			$dh = @opendir( $dir );
    			if ( $dh ) {
    				while ( ( $file = @readdir( $dh ) ) !== false ) {
    					if ( $file == '.' || $file == '..' )
    						continue;

    					if ( @is_dir( $dir . DIRECTORY_SEPARATOR . $file ) )
    						$stack[] = $dir . DIRECTORY_SEPARATOR . $file;
    					else if ( @is_file( $dir . DIRECTORY_SEPARATOR . $file ) )
    						@unlink( $dir . DIRECTORY_SEPARATOR . $file );
    				}
    				@closedir( $dh );
    			}
    			$index++;
    		}

    		$stack = array_reverse( $stack ); // Last added dirs are deepest
    		foreach( (array) $stack as $dir ) {
    			if ( $dir != $top_dir)
    				@rmdir( $dir );
    		}

    		clean_blog_cache( $blog );
    	}

    	if ( $switch )
    		restore_current_blog();
    }

    function fbpsych_get_blog_surveys($blogId) {
    	$surveys = fbpsych_get_surveys();
    	$activeSurveys = array();
    	
    	$globalSurveyId = get_blog_option(1, "fbpsych_global_survey_id", 0);
    	$activatedSurveyIds = get_blog_option($blogId, "fbpsych_activated_survey_ids", array());
    	
    	foreach ($surveys as $survey) {
    		if (in_array($survey['surveyId'], $activatedSurveyIds) || $survey['surveyId'] == $globalSurveyId) {
    			$activeSurveys[] = $survey;
    		}
    	}
    	
    	return $activeSurveys;
    }

    function fbpsych_unset_survey_expiration($surveyId)
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once(plugin_dir_path(__FILE__) . "config.php");
    	
    	// create RPC-Client
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);
    	
    	try {
    		// update data in limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		$rpcClient->set_survey_properties($sessionKey, $surveyId, array('expires' => ''));
    		
    		$rpcClient->release_session_key($sessionKey);
    	} catch (Exception $e) {
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    }

    function fbpsych_set_survey_expiration($surveyId, $date = "")
    {
    	if ($date == "") {
    		// use the current time (+ 1 hour for GMT)
    		$date = date('Y-m-d H:i:00', time() + 3600);
    	}
    	
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once(plugin_dir_path(__FILE__) . "config.php");
    	
    	// create RPC-Client
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);
    	
    	try {
    		// update data in limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
    		
    		$rpcClient->set_survey_properties($sessionKey, $surveyId, array('expires' => $date));
    		
    		$rpcClient->release_session_key($sessionKey);
    	} catch (Exception $e) {
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    }

    function fbpsych_ls_has_team_questions($surveyId)
    {
        // include json rpc client
        if (!class_exists('jsonRPCClient')) {
            include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
        }
        
        require_once(plugin_dir_path(__FILE__) . "config.php");

        // create RPC-Client
        $rpcClient = new jsonRPCCLient(LS_RPC_URL);

        try {
            $sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
            
            $rpcTeamQuestionId = $rpcClient->get_team_question_id($sessionKey, $surveyId, "teamWahl");

            return ($rpcTeamQuestionId !== 0);

            $rpcClient->release_session_key($sessionKey);
        } catch ( Exception $e ) {
            echo nl2br($e->getMessage()).'<br />'."\n";
        }

        return false;
    }

    function fbpsych_get_surveys_full($blogId)
    {
        // include json rpc client
        if (!class_exists('jsonRPCClient')) {
            include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
        }
        
        require_once(plugin_dir_path(__FILE__) . "config.php");

        // create RPC-Client
        $rpcClient = new jsonRPCCLient(LS_RPC_URL);

        try {
            // get data from limesurvey
            $sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);

            // get all blog Surveys
            $blogSurveys = get_blog_option($blogId, "fbpsych_project_surveys");

            $return = array();

            foreach ($blogSurveys as $blogSurvey) {
                // use master title if survey does not overwrite it
                if (!isset($blogSurvey['title']) || empty($blogSurvey['title'])) {
                    $masterSurvey = $rpcClient->get_language_properties($sessionKey, $blogSurvey['templateId'], array('surveyls_title'), 'de');
                    $blogSurvey['title'] = $masterSurvey['surveyls_title'];
                }

                // add data from ls
                $lsData = $rpcClient->get_survey_properties($sessionKey, $blogSurvey['surveyId'], array('expires'));
                $blogSurvey['limesurvey'] = $lsData;

                $return[] = $blogSurvey;
            }

            $rpcClient->release_session_key($sessionKey);

            return $return;
        } catch ( Exception $e ) {
            echo nl2br($e->getMessage()).'<br />'."\n";
        }
    }

    function fbpsych_get_surveys()
    {
    	// include json rpc client
    	if (!class_exists('jsonRPCClient')) {
    		include_once(plugin_dir_path(__FILE__) . "lib/jsonrpcphp/jsonRPCClient.php");
    	}
    	
    	require_once(plugin_dir_path(__FILE__) . "config.php");

    	// create RPC-Client
    	$rpcClient = new jsonRPCCLient(LS_RPC_URL);

    	try
    	{
    		// get data from limesurvey
    		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);

    		$surveysFromLS = $rpcClient->list_surveys($sessionKey, null);

    		$rpcClient->release_session_key($sessionKey);

    		// get surveys from main blog
    		switch_to_blog(1);

    		$surveys = get_option("fbpsych_main_surveys", array());

    		$return = array();

    		// determe survey name
    		foreach ( $surveys as $survey )
    		{
    			// if the survey does not overwrite the title
    			if ( !isset($survey["title"]) || empty($survey["title"]) )
    			{
    				// find main template survey from LimeSurvey
    				$surveyMatch = null;
    				foreach ( $surveysFromLS as $surveyFromLS )
    				{
    					if ( $surveyFromLS["sid"] === $survey["surveyId"] )
    					{
    						$surveyMatch = $surveyFromLS;
    						break;
    					}
    				}

    				if ( $surveyMatch )
    				{
    					$survey["title"] = $surveyMatch["surveyls_title"];
    				}
    			}
    				
    			$return[] = $survey;
    		}

    		restore_current_blog();

    		return $return;
    	}
    	catch ( Exception $e )
    	{
    		echo nl2br($e->getMessage()).'<br />'."\n";
    	}
    }

    function fbpsych_get_company_title()
    {
    	return(get_cimyFieldValue(get_current_user_id(), 'COMPANY', ''));
    }

    function fbpsych_get_phone()
    {
    	return(get_cimyFieldValue(get_current_user_id(), 'COMPANY', ''));
    }

    function fbpsych_can_user_edit_question_groups()
    {
    	/* check global permission */
    	$siteOptions = get_site_option("fbpsych_question_groups_options");

    	if ( isset($siteOptions["select"]) && $siteOptions["select"] === "yes" )
    	{
    		return true;
    	}
    	else
    	{
    		// not configured yet or false - check project permissions
    		$blogOptions = get_blog_option($blogId, "fbpsych_question_groups_options");

    		if ( isset($blogOptions["overwrite"]) && $blogOptions["overwrite"] === "yes" && isset($blogOptions["select"]) && $blogOptions["select"] === "yes" )
    		{
    			return true;
    		}

    		return false;
    	}
    }

    function fbpsych_get_pdf_link($surveyId)
    {
    	$mainSurveys = fbpsych_get_main_survey_information();

    	if ( isset($mainSurveys["wp"][$surveyId]) )
    	{
    		$survey = $mainSurveys["wp"][$surveyId];
    		$blogDetails = get_blog_details(1);

    		$response = "";

    		if ( isset($survey["additionalPdfVersions"]) && sizeof($survey["additionalPdfVersions"]) > 0 )
    		{
    			$response .= '
    				<div class="wipeWrapper">
    				<div class="wipeHeader">
    					<a class="wipeSuccessor" href="#">' . __("FBP_PDFS_MORE", "twentytwelve") . '</a>
    				</div>
    				<div class="wipeActor" style="display: none;">
    					<div class="wipeContent">
    						<ul>
    		';
    				
    			foreach ( $survey["additionalPdfVersions"] as $pdfVersion )
    			{
    				$path = $blogDetails->siteurl . '/wp-content/uploads/rscripts/' . $surveyId . '/' . pathinfo($pdfVersion["pdfVersion"], PATHINFO_BASENAME);
    				$description = $pdfVersion["description"];

    				if ( empty($description) )
    				{
    					$description = "PDF";
    				}

    				$response .= '<li><a target="_blank" href="' . $path . '">' . $description . '</a></li>';

    			}
    				
    			$response .= '
    						</ul>
    					</div>
    				</div>
    			</div>
    		';
    		}

    		return $response;
    	}

    	return "";
    }

    function recurse_copy($src, $dst) {
    	if (is_dir($src)) {
    		$dir = opendir($src);
    	    @mkdir($dst);
    	    while(false !== ($file = readdir($dir))) {
    	        if (($file != '.') && ($file != '..')) {
    	            if (is_dir($src . '/' . $file)) {
    	                recurse_copy($src . '/' . $file, $dst . '/' . $file);
    	            } else {
    	                copy($src . '/' . $file, $dst . '/' . $file);
    	            }
    	        }
    	    }
    	    closedir($dir);
    	}
    } 