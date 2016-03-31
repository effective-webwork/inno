<?php
	// get the global survey id
	$globalSurveyId = get_option("fbpsych_global_survey_id", 0);

	// form processing
	if (isset($_POST['global']) && isset($_POST['globalSurveyId'])) {
		if (is_numeric($_POST['globalSurveyId'])) {
			$globalSurveyId = (int) $_POST['globalSurveyId'];
			
			update_option("fbpsych_global_survey_id", $globalSurveyId);
		}
	}
?>

<div class="wrap">
	<h2>FB <=> Account &rsaquo; Globaler Fragebogen</h2>
	
	<p>Hier haben Sie die Möglichkeit einen Fragebogen zu definieren, die global immer allen Projektleitern zu Verfügung steht</p>
	<p>Folgender Fragebogen ist global für alle Projektleiter verfügbar:</p>
	
	<form name="projectsformglobal" action="admin.php?page=admin_menu_projects" method="post" enctype="multipart/form-data">
		<select name="globalSurveyId">
			<option value="0"<?php if ($globalSurveyId == 0) echo ' selected="selected"'; ?>>Keine Umfrage</option>
			<option value="-1" disabled="disabled">-------------</option>

<?php
	$surveys = fbpsych_get_surveys();
	foreach ($surveys as $survey) :
		if (!$survey['active']) continue;
?>
			<option value="<?php echo $survey['surveyId']; ?>"<?php if ($globalSurveyId == $survey['surveyId']) echo ' selected="selected"'; ?>><?php printf("%s (%s)", $survey['title'], $survey['surveyId']); ?></option>
<?php
	endforeach;
?>

		</select>
		<input type="submit" name="global" value="aktualisieren" />
	</form>
	
	<br/><br/>

    <h2>FB <=> Account &rsaquo; Fragebögen</h2>
    
    <form name="projectsformactivated" action="admin.php?page=admin_menu_projects" method="post" enctype="multipart/form-data">
	    <table>
	        <tr>
	            <th style="text-align: left; padding: 5px;">Projektseite</th>
	            <th style="text-align: left; padding: 5px;">Kennung Projektleiter</th>
	            <th style="text-align: left; padding: 5px;">Freigeschaltete Umfragen</th>
	        </tr>

<?php
    global $wpdb;
	$blogs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_blogs ORDER BY blog_id", array() ) );
?>

<?php
    foreach ($blogs as $i => $blog) :
        $iBlogId = $blog->blog_id;
    
        // skip blog with id 1
        if ($iBlogId == 1) {
            continue;
        }
        
        // get the blog user
        $aBlogUser = get_users(array('blog_id' => $iBlogId));
        $oBlogUser = $aBlogUser[0];
        
        // get the company name
        $company = get_blog_option($iBlogId, "cimy_uef_COMPANY");
        $sCompanyName = '';
        if (isset($company) && !empty($company)) {
            $sCompanyName = $company;
        }
		
		// get currently activated surveys
        $activatedSurveyIds = array();
		if (isset($_POST['activated'])) {
			if (isset($_POST['possibleSurveys'][$iBlogId])) {
				$activatedSurveyIds = $_POST['possibleSurveys'][$iBlogId];
			} else {
				$activatedSurveyIds = array();
			}
			
        } else {
			$activatedSurveyIds = get_blog_option($iBlogId, "fbpsych_activated_survey_ids", array());
		}
		
		// get the list of possible active surveys (remove global)
		$possibleSurveys = array();
		foreach ($surveys as $survey) {
			if (!$survey['active'] || $survey['surveyId'] == $globalSurveyId) {
				continue;
			}
			
			// is the survey currently active for the blog?
			$survey['blogActive'] = in_array($survey['surveyId'], $activatedSurveyIds);
				
			$possibleSurveys[] = $survey;
		}
		
		// form 
		if (isset($_POST['activated'])) {
			update_blog_option($iBlogId, "fbpsych_activated_survey_ids", $activatedSurveyIds);
		}
?>
	        <tr style="background-color: <?php printf("%s", ($i % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
	            <td style="text-align: left; padding: 5px;"><?php echo $sCompanyName; ?>
	            <td style="text-align: left; padding: 5px;"><?php echo $oBlogUser->user_login; ?></td>
	            <td style="text-align: left; padding: 5px;">
<?php
	foreach ($possibleSurveys as $possibleSurvey) :
?>
					<input id="possibleSurvey_<?php echo $possibleSurvey['surveyId']; ?>" type="checkbox" name="possibleSurveys[<?php echo $iBlogId; ?>][]" value="<?php echo $possibleSurvey['surveyId']; ?>"<?php if ($possibleSurvey['blogActive'] == true) echo ' checked="checked"'; ?>/>
					<label for="possibleSurvey_<?php echo $possibleSurvey['surveyId']; ?>"><?php echo $possibleSurvey['title'] . ' (' . $possibleSurvey['surveyId'] . ')'; ?></label>
					<br/>
<?php
	endforeach;
?>
	            </td>
	        </tr>  
<?php
    endforeach;
?>
    	</table>
    	
    	<input type="submit" name="activated" value="aktualisieren" />
    </form>

</div>
