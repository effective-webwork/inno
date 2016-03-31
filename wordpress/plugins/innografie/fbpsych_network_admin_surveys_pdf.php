<?php
		// get main survey data
		$mainSurveys = fbpsych_get_main_survey_information();
		
		$isEditMode = isset($_GET["edit"]);
		$presets = array();
		
		if ( isset($_POST["form"]["mainSurvey"]) )
		{
			$presets["surveyId"] = $_POST["form"]["mainSurvey"];
			
			$presets["shortDescription"] = $_POST["form"]["shortDescription"];
		}
		else if ( $isEditMode == "true" )
		{
			$presets["surveyId"] = $_GET["surveyId"];
			
			$presets["shortDescription"] = $mainSurveys["wp"][$_GET["surveyId"]]["additionalPdfVersions"][$_GET["fileId"]]["description"];
		}
		
		// process delete action
		if ( isset($_GET["deleteReally"]) )
		{
			$path = $mainSurveys["wp"][$_GET["surveyId"]]["additionalPdfVersions"][$_GET["fileId"]]["pdfVersion"];
			
			array_splice($mainSurveys["wp"][$_GET["surveyId"]]["additionalPdfVersions"], $_GET["fileId"], 1);
			
			// store the wordpress survey
			$wordpressSurveys = get_option("fbpsych_main_surveys");
			if ( $wordpressSurveys === false )
			{
				$wordpressSurveys = array();
			}
			foreach ( $wordpressSurveys as &$wordpressSurvey )
			{
				if ( $wordpressSurvey["surveyId"] == $_GET["surveyId"] )
				{
					$wordpressSurvey = $mainSurveys["wp"][$_GET["surveyId"]];
					break;
				}
			}
				
			update_option("fbpsych_main_surveys", $wordpressSurveys);
			
			// delete the pdf version from disk
			if ( is_file($path) )
			{
				unlink($path);
			}
		}
		
		/* STEP 1 */
		if ( isset($_POST["step"]) && $_POST["step"] === "1" && isset($_POST["submit"]) )
		{
			$errorArray = array();
				
			// form checks
			if ( !isset($_POST["form"]["mainSurvey"]) )
			{
				$errorArray["survey"] = "missing";
			}
				
			if ( empty($_FILES["form"]["name"]["pdfVersion"]) )
			{
				$errorArray["pdf"] = "missing";
			}
			
			if ( $_FILES["form"]["error"]["pdfVersion"] !== 0 )
			{
				$errorArray["upload"] = "error";
			}
			
			if ( empty($errorArray) )
			{
				$_POST["step"] = "2";
				$presets = array();
			}
			else
			{
				$_POST["step"] = "1";
			}
		}
		
		if ( true ) :
?>
			
	<h1>PDF-Versionen</h1>
	<form name="projectsettingsform" action="<?php if ( $_POST["step"] == "2" ) : ?>admin.php?page=admin_menu_surveys_pdf<?php endif;?>" method="post" enctype="multipart/form-data">
		<p>Hier können Sie für eine Umfrage zusätzliche PDF-Versionen verwalten.</p>
		
		<!--  error line -->
		<div class="formErrors">
			<?php if ( isset($errorArray["survey"]) && $errorArray["survey"] === "missing" ) :?>
			<div style="color: red; font-weight: bold;">Sie müssen eine Umfrage auswählen!</div>
			<?php endif; ?>
			<?php if ( isset($errorArray["pdf"]) ) :?>
			<div style="color: red; font-weight: bold;">Sie müssen eine PDF-Version der Umfrage hochladen!</div>
			<?php endif; ?>
			<?php if ( isset($errorArray["upload"]) ) :?>
				<?php
					$max_upload = (int)(ini_get('upload_max_filesize'));
					$max_post = (int)(ini_get('post_max_size'));
					$memory_limit = (int)(ini_get('memory_limit'));
					$upload_mb = min($max_upload, $max_post, $memory_limit);
				?>
			<div style="color: red; font-weight: bold;">
				Es ist ein Problem beim Upload der Dateien aufgetreten! Bitte beachten Sie das eingestellte Uploadlimit: <?php printf("%d Mb", $upload_mb); ?>
			</div>
			<?php endif; ?>
		</div>
		
		<div class="wrap">
			<h2><?php printf("PDF-Versionen &rsaquo; %s", ($isEditMode == true) ? "PDF-Version bearbeiten" : "Neue PDF-Version"); ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Umfrage<span style="color: red; font-weight: bold;">*</span></th>
					<td>
						<select id="project_survey" name="form[mainSurvey]">
							<option value="" <?php if (!isset($presets["surveyId"]) ) printf("selected=\"selected\"");?> disabled="disabled">Bitte w&auml;hlen Sie eine Umfrage aus</option>
							<?php
								foreach ( $mainSurveys["wp"] as $wpSurveyId => $wpSurvey )
								{
									$isSelected = false;
									if ( isset($presets["surveyId"]) )
									{
										if ( $presets["surveyId"] == $wpSurveyId )
										{
											$isSelected = true;
										}
									}
									
									$title = !empty($wpSurvey["title"]) ? $wpSurvey["title"] : $mainSurveys["ls"][$wpSurveyId]["surveyls_title"];
							?>
									<option value="<?php printf($wpSurveyId); ?>" <?php if ($isSelected) printf("selected=\"selected\"");?>><?php printf("%s (%s)", $title, $wpSurveyId); ?></option>
							<?php		
								}
							?>
						</select>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">PDF-Version<span style="color: red; font-weight: bold;">*</span></th>
					<td>
						<input type="file" name="form[pdfVersion]"/>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">Kurzbeschreibung</th>
					<td>
						<input type="text" size="32" name="form[shortDescription]" <?php if(isset($presets["shortDescription"])) printf("value=\"%s\"", $presets["shortDescription"]); ?>/>
					</td>
				</tr>
			</table>
		</div>
		
		<p>
			<input type="hidden" name="step" value="1" />
			<input type="submit" name="submit" value="<?php printf("%s", ($isEditMode == true) ? "PDF-Version speichern" : "PDF-Version hinzuf&uuml;gen"); ?>" />
		</p>
	</form>
	
	<?php endif; ?>
	
	<?php /* STEP 2 */ ?>
	<?php if( isset($_POST["step"]) && $_POST["step"] === "2" ) : ?>
<?php
			$folder = WP_CONTENT_DIR . "/uploads/rscripts/" . $_POST["form"]["mainSurvey"] . "/";

			// get form data
			$pdfVersionTemp = $_FILES["form"]["tmp_name"]["pdfVersion"];
			$pdfVersionName = $_FILES["form"]["name"]["pdfVersion"];
			
			move_uploaded_file($pdfVersionTemp, $folder . $pdfVersionName);
			
			if ( $isEditMode )
			{
				// get the wordpress entry
				$wordpressSurvey = $mainSurveys["wp"][$_POST["form"]["mainSurvey"]];
				
				// overwrite the additional pdfs section for the given fileId
				$currentAdditionalPdfVersion = $wordpressSurvey["additionalPdfVersions"][$_GET["fileId"]];
				$currentAdditionalPdfVersions = array(
						"pdfVersion"	=> $folder . $pdfVersionName,
						"description"	=> $_POST["form"]["shortDescription"]
				);
				$mainSurveys["wp"][$_POST["form"]["mainSurvey"]]["additionalPdfVersions"][$_GET["fileId"]] = $currentAdditionalPdfVersions;
			}
			else
			{
				// get the wordpress entry
				$wordpressSurvey = $mainSurveys["wp"][$_POST["form"]["mainSurvey"]];
				
				// add the pdf to the additional pdfs section
				$currentAdditionalPdfVersions = isset($wordpressSurvey["additionalPdfVersions"]) ? $wordpressSurvey["additionalPdfVersions"] : array();
				$currentAdditionalPdfVersions[] = array(
					"pdfVersion"	=> $folder . $pdfVersionName,
					"description"	=> $_POST["form"]["shortDescription"]
				);
				$mainSurveys["wp"][$_POST["form"]["mainSurvey"]]["additionalPdfVersions"] = $currentAdditionalPdfVersions;
			}
			
			// store the wordpress survey
			$wordpressSurveys = get_option("fbpsych_main_surveys");
			if ( $wordpressSurveys === false )
			{
				$wordpressSurveys = array();
			}
			foreach ( $wordpressSurveys as &$wordpressSurvey )
			{
				if ( $wordpressSurvey["surveyId"] == $_POST["form"]["mainSurvey"] )
				{
					$wordpressSurvey = $mainSurveys["wp"][$_POST["form"]["mainSurvey"]];
					break;
				}
			}
			
			update_option("fbpsych_main_surveys", $wordpressSurveys);
?>
	<?php endif; ?>
	
	<div class="wrap">
		<h2>PDF-Versionen &rsaquo; Angelegte PDF-Versionen</h2>
		
		<table>
			<tr>
				<th>Umfrage ID</th>
				<th>Titel</th>
				<th>Zusätzliche PDF-Versionen</th>
			</tr>
			<?php
				foreach ( $mainSurveys["wp"] as $wpSurveyId => $wpSurvey )
				{
					$surveyId = $wpSurveyId;
					$title = !empty($wpSurvey["title"]) ? $wpSurvey["title"] : $mainSurveys["ls"][$wpSurveyId]["surveyls_title"];
					$additionalPdfVersions = isset($wpSurvey["additionalPdfVersions"]) ? $wpSurvey["additionalPdfVersions"] : array();
			?>		
					<tr style="background-color: <?php printf("%s", ($i % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
						<td style="text-align: center; padding: 3px;"><?php printf("%s", $surveyId); ?></td>
						<td style="text-align: center; padding: 3px;"><?php printf("%s", $title);?></td>
						<td style="text-align: center; padding: 3px 3px 3px 3px;">
							<?php if ( empty($additionalPdfVersions) ) : ?>
								Keine zusätzlichen Versionen vorhanden
							<?php else: ?>
								<table>
									<tr>
										<th>PDF</th>
										<th>Beschreibung</th>
										<th>&nbsp;</th>
										<th>&nbsp;</th>
									</tr>
								<?php
									foreach ( $wpSurvey["additionalPdfVersions"] as $key => $pdfVersion )
									{
										$file = $pdfVersion["pdfVersion"];
										$description = $pdfVersion["description"];
								?>
										<tr>
											<td style="text-align: center; padding: 3px;"><?php printf("%s", pathinfo($file, PATHINFO_BASENAME)); ?></td>
											<td style="text-align: center; padding: 3px;"><?php printf("%s", $description); ?></td>
											<td style="text-align: center; padding: 3px 3px 3px 30px;">
												<a href="<?php printf("admin.php?page=%s&edit=true&surveyId=%s&fileId=%s", $_GET["page"], $surveyId, $key); ?>">Bearbeiten</a>
											</td>
											<td style="text-align: center; padding: 3px 3px 3px 30px;">
												<?php if ( isset($_GET["delete"]) && $_GET["delete"] === "true" && $_GET["surveyId"] == $wpSurveyId && $_GET["fileId"] == $key ) :?>
													<a style="color: red;" href="<?php printf("admin.php?page=%s&deleteReally=true&surveyId=%s&fileId=%s", $_GET["page"], $surveyId, $key); ?>">Wirklich l&ouml;schen</a>
												<?php else: ?>
													<a style="color: red;" href="<?php printf("admin.php?page=%s&delete=true&surveyId=%s&fileId=%s", $_GET["page"], $surveyId, $key); ?>">L&ouml;schen</a>
												<?php endif; ?>
											</td>
										</tr>
								<?php
									}
								?>
								</table>
							<?php endif; ?>
						</td>
					</tr>
			<?php
				}
			?>
		</table>
	</div>