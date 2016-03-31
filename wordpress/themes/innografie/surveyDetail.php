<?php
	if (isset($_GET['action'])) {
		if ($_GET['action'] == 'reactivate') {
			fbpsych_unset_survey_expiration($surveyId);
		} else if($_GET['action'] == 'expire') {
			fbpsych_set_survey_expiration($surveyId);
		}
	}
?>

<div id="columnset">
	<div id="content" role="main">
	
		<?php printf("%s", fbpsych_get_rotator_image()); ?>
		
		<?php
		function set_html_content_type()
		{
			return 'text/html';
		}
		?>
	
		<div id="left_column">
			
			<div id="maincontent">
				
				<?php printf("%s", fbpsych_get_step_image()); ?>
				
				<?php
					$surveys = get_blog_option(get_current_blog_id(), "fbpsych_project_surveys");

					$lsSurveyProperties = fbpsych_get_ls_survey_properties($surveyId);

					$isExpired = false;
					if (isset($lsSurveyProperties['expires'])) {
						//$lsSurveyExpire = $lsSurveyProperties['expires'];
						//$compareDate = date('Y-m-d H:i:00', time() + 3600);

						$isExpired = true;
					}
					
					foreach ( $surveys as $survey )
					{
						if ( $survey["surveyId"] == $surveyId )
						{
							$title = $survey["title"];
							$templateId = $survey["templateId"];
							$surveyToken = $survey["surveyToken"];
							$tokenCount = $survey["tokenCount"];
							$completed = fbpsych_get_completed_surveys($survey["surveyId"]);
							$teams = $survey['teams'];

							if (isset($teams['de'])) {
								$teams = $teams['de'];
							}
							
							if (empty($survey["surveyName"])) {
								$date = new \DateTime($survey["creationDate"]);
								$name = 'Fragebogen vom '.$date->format("d.m.Y");
							} else {
								$name = $survey["surveyName"];
							}
							
							break;
						}
					}
				?>
				
				<?php
					$siteSurveys = get_blog_option(1, "fbpsych_main_surveys");
					$isEvaluationActive = false;
					$isEvaluation2Active = false;
					$isEvaluation3Active = false;

					$isPdfActive = false;
					
					foreach ( $siteSurveys as $siteSurvey ) {
						if ( $siteSurvey["surveyId"] == $templateId ) {
							
							if ($siteSurvey["evaluationActive"] === true) {
								$isEvaluationActive = true;
							}

							if ($siteSurvey["evaluation2Active"] === true) {
								$isEvaluation2Active = true;
							}

							if ($siteSurvey["evaluation3Active"] === true) {
								$isEvaluation3Active = true;
							}
							
							if ( $siteSurvey["pdfActive"] === true ) {
								$isPdfActive = true;
							}
							
							break;
						}
					}
				?>
				
				<?php if( !isset($_POST["step"]) ) : ?>
				<h1><?php _e("FBP_PROJECT_TITLE_PAGE", "twentytwelve"); ?> &rsaquo; <?php printf("%s", $title); ?></h1>
				
				<div class="entry-content">
					<?php _e("FBP_PROJECT_SURVEY_INFORMATION", "twentytwelve"); ?>
					<div class="create_survey">
						<?php
							$surveyUrl = get_blog_details(1)->siteurl . '/quick/' . $surveyId . '/' . $surveyToken;
						?>
						<!-- Titel -->
						<h4 style="margin: 0px;"><?php echo $name; ?></h4>
						<div style="font-size: 10px;">
							<?php if ($isExpired) : ?>
								<?php _e("FBP_PROJECT_SURVEY_EXPIRED", "twentytwelve"); ?> ( <a href="?action=reactivate"><?php _e("FBP_PROJECT_SURVEY_REACTIVATE", "twentytwelve"); ?></a> )
							<?php else: ?>
								<a href="?action=expire"><?php _e("FBP_PROJECT_SURVEY_EXPIRE", "twentytwelve"); ?></a>
							<?php endif; ?>
						</div>
						<div class="clear"></div>

						<br>
						<div style="margin-left: 5px;">
							<?php _e("FBP_PROJECT_GOTO_SURVEY", "twentytwelve"); ?> <a href="<?php printf("%s", $surveyUrl); ?>"><?php printf("%s", $surveyUrl); ?></a>
						</div>
						
						<?php if ( $isPdfActive === true ): ?>
							<div><?php printf("%s", fbpsych_get_pdf_link($templateId)); ?></div>
						<?php endif; ?>
						
						<!-- Anzahl der Teilnehmer -->
						<br>
						<div class="column_250">Anzahl der Teilnehmer:</div>
						<div><?php echo $tokenCount; ?></div>
						<div class="clear"></div>
						
						<!-- Vollständig ausgefüllte Fragebögen -->
						<br>
						<div class="column_250">Vollständig ausgefüllte Fragebögen:</div>
						<div><?php echo $completed; ?></div>
						<div class="clear"></div>
						
						<!-- Teams -->
						<br>
						<div class="column_250">Teams:</div>
						<div class="clear"></div>
						<ul>
								<?php
									for ($i = 0; $i < sizeof($teams); $i++) {
										?><li><?php echo $teams[$i]; ?></li><?php
									}
								?>
						</ul>
						
						<!-- Originale Id -->
						<?php
							$current_user = wp_get_current_user();
	                        if (is_super_admin($current_user->id)) {
						?>
								<br>
								<div class="column_250">Id des originalen Fragebogens:</div>
								<div><?php echo $templateId; ?></div>
								<div class="clear"></div>
						<?php
							}
						?>
						
						<div>
				         	<a style="display: none;" id="mail_templates">&nbsp;</a>
							<?php _e("FBP_PROJECT_CREATE_MAIL_INTRODUCTION", "twentytwelve"); ?>
							
							<form name="mail_template_form" action="#mail_templates" method="get" enctype="multipart/form-data">
								Bitte wählen Sie eine Vorlage: <select name="mail_template" onchange="this.form.submit();">
								<?php
									// get all mail templates
									switch_to_blog(1);
									$templates = get_option("fbpsych_mail_templates", array());
									restore_current_blog();
									
									// determe template to show
									$showTemplateIndex = 0;
									if (isset($_GET["mail_template"])) {
										$showTemplateIndex = $_GET["mail_template"];
									}
									
								?>
								<?php if (empty($templates)) : ?>
									<option disabled="disabled">Keine Vorlagen verfügbar</option>
								<?php else: ?>
									<?php foreach ($templates as $index => $template) : ?>
										<option <?php if ($showTemplateIndex == $index) : ?>selected="selected" <?php endif; ?>value="<?php echo $index; ?>"><?php echo $template["title"]; ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
								</select>
								<div class="mail_template">
									<?php
										// prepare and output template content
										$content = $templates[$showTemplateIndex]["content"];
										
										$content = str_replace("%SURVEY_URL%", '<a href="$surveyUrl">' . $surveyUrl . '</a>', $content);
										
										echo "<pre>" . $content . "</pre>";
									?>
								</div>
							</form>
						</div>
				
						<div class="clear"></div>
					</div>
					<br/>

					<?php if ($isEvaluationActive || $isEvaluation2Active || $isEvaluation3Active) : ?>
						<?php _e("FBP_PROJECT_SURVEY_EVALUATION_MAIN", "twentytwelve"); ?>
						<div class="create_survey">
							<h2><?php _e("FBP_PROJECT_SURVEY_EVALUATION_TITLE", "twentytwelve"); ?></h2>
							<form name="evaluatesurvey" action="" method="post" enctype="multipart/form-data">
								<select name="evaOption" style="position: relative; bottom: 2px;">
									<?php if ($isEvaluationActive) : ?>
										<option value="1"><?php echo $siteSurvey["rScriptTitle"]; ?></option>
									<?php endif; ?>
									<?php if ($isEvaluation2Active) : ?>
										<option value="2"><?php echo $siteSurvey["rScript2Title"]; ?></option>
									<?php endif; ?>
									<?php if ($isEvaluation2Active) : ?>
										<option value="3"><?php echo $siteSurvey["rScript3Title"]; ?></option>
									<?php endif; ?>
								</select>


								<button class="chart" type="submit" name="evaluateSurvey" value="true">
									<img src="<?php echo get_template_directory_uri(); ?>/img/chart-search.png" /><span><?php _e("FBP_PROJECT_BUTTON_EVALUATE", "twentytwelve"); ?></span>
								</button>
								
								<input type="hidden" name="step" value="2" />
								
							</form>
							
							<div class="clear"></div>
						</div>

						<br/>
					<?php endif; ?>
					
					<a name="delete"></a>
					
					<form name="deletesurvey" action="#delete" method="get" enctype="multipart/form-data">
						<button class="delete delete_dialog" type="submit" name="deleteSurvey" value="true">
							<img src="<?php echo get_template_directory_uri(); ?>/img/delete.png" /><span><?php _e("FBP_PROJECT_BUTTON_SURVEY_DELETE", "twentytwelve"); ?></span>
						</button>
						
						<input type="hidden" name="deleteSurveyReally" value="true" />
						<input type="hidden" name="step" value="3" />
						
					</form>				</div>
				<?php endif; ?>
				
				<?php
				if ( isset($_POST["step"]) && $_POST["step"] === "2" )
				{
					/* form check */
					if( isset($_POST["submit"]) )
					{
						// TODO:
					
						$_POST["step"] = "2";
					}
				}
				?>
				
				<?php if( isset($_POST["step"]) && $_POST["step"] === "2" ) : ?>
				<?php
					if ($isEvaluationActive || $isEvaluation2Active || $isEvaluation3Active) {
						if (isset($_POST["evaluateSurvey"])) {
							$evaValue = (int) $_POST['evaOption'];

							if ($evaValue == 1) {
								$evaValue = "";
							}
							
							$evaResult = fbpsych_evaluate_survey($surveyId, $templateId, $evaValue);
						}
					}
					
					if ( $evaResult['status'] === 'success' ) :
						$scheme = parse_url(content_url(), PHP_URL_SCHEME);
					    $accessToken = $evaResult['content'];
				?>
					<h1><?php _e("FBP_PROJECT_TITLE_PAGE", "twentytwelve"); ?> &rsaquo; <?php printf("%s", $title); ?></h1>
					
					<div class="entry-content">
						<?php _e("FBP_PROJECT_RESULTS_MAIN", "twentytwelve"); ?>
						
						<div class="create_survey">
							<a href="<?php printf("%s://%s/evaluations/%s/index.php?token=%s", $scheme, $_SERVER['HTTP_HOST'], $surveyId, $accessToken); ?>" target="_blank"><?php _e("FBP_PROJECT_RESULTS", "twentytwelve"); ?></a>
						</div>
					</div>
				<?php elseif ( $evaResult['status'] === 'error' && $evaResult['code'] === 1011 ): ?>
					<h1><?php _e("FBP_PROJECT_TITLE_PAGE", "twentytwelve"); ?> &rsaquo; <?php printf("%s", $title); ?></h1>
					
					<div class="entry-content formErrors">
						<div><?php _e("FBP_PROJECT_EVALUATION_NO_ANSWERS", "twentytwelve"); ?></div>
					</div>
					
				<?php
					else:
				?>
					<h1><?php _e("FBP_PROJECT_TITLE_PAGE", "twentytwelve"); ?> &rsaquo; <?php printf("%s", $title); ?></h1>
					
					<div class="entry-content formErrors">
						<div><?php _e("FBP_PROJECT_EVALUATION_ERROR", "twentytwelve"); ?></div>
					</div>
					
					<?php
					    $errorContent = $evaResult['message'];
					    
						// it the current user is super admin show debug information
						if ( is_super_admin() && is_string($errorContent) ) {
							?>
							<div class="entry-content">
								<div><b>Als Netzwerkadministartor werden Ihnen zusätzliche Informationen zur Fehlerbehebung angezeigt:</b></div>
								<div>
									<?php printf("%s", nl2br($errorContent)); ?>
								</div>
							</div>
							<?php
						} else {
							// otherwise, send mail to super admins
							$admins = get_admin_users_for_domain();
							
							$adminMails = array();
							foreach ($admins as $admin) {
								$adminDetails = get_userdata($admin['ID']);
								$adminMails[] = $adminDetails->get('user_email');
							}
							
							// create and send mail
							$message = '
								<p>Bei der Erzeugung einer Auswertung ist ein Fehler aufgetreten:</p><br/><br/>
								<p>
									Kontext:<br/>
									<pre>' . $_SERVER . '</pre><br/>
               						<br/>
               						Fehler:<br/>
               						<pre>' . $errorContent . '</pre>
								</p>
							';
							
							add_filter( 'wp_mail_content_type', 'set_html_content_type' );
							
							wp_mail($adminMails, '[Innografie Tool] R-Script Auswertung schlug fehl', $message);
							
							// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
							remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
						}
					?>
				<?php
					endif;
				?>
					
				<?php endif; ?>
			</div>
		</div>
	</div>

<?php get_sidebar(); ?><div class="clear"></div></div><!-- #primary -->
<?php get_footer(); ?>