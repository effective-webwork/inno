<?php
	// include json rpc client
	include("lib/jsonrpcphp/jsonRPCClient.php");
	
	require_once("config.php");

	// get all available surveys
	$rpcClient = new jsonRPCCLient(LS_RPC_URL);
	
	try
	{
		// get data from limesurvey
		$sessionKey = $rpcClient->get_session_key("admin", LS_ADMIN_PW);
	
		$surveys = $rpcClient->list_surveys($sessionKey, null);
		
		// get survey data from wordpress
		$wordpressSurveys = get_option("fbpsych_main_surveys");
		if ( $wordpressSurveys === false )
		{
			$wordpressSurveys = array();
		}
		
		$isEditMode = isset($_GET["edit"]);
		$presets = array();
		
		if ( isset($_POST["form"]["mainSurvey"]) )
		{
			$surveyId = $_POST["form"]["mainSurvey"];
			$presets["title"] = $_POST["form"]["title"];
			$presets["rScriptTitle"] = $_POST["form"]["rScriptTitle"];
			$presets["rScript2Title"] = $_POST["form"]["rScript2Title"];
			$presets["rScript3Title"] = $_POST["form"]["rScript3Title"];
			$presets["surveyId"] = $surveyId;
		}
		else if ( $isEditMode == true )
		{
			$surveyId = $_GET["edit"];
			$presets["surveyId"] = $surveyId;
			
			foreach ( $wordpressSurveys as &$wordpressSurvey )
			{
				if ( $wordpressSurvey["surveyId"] == $_GET["edit"] )
				{
					$title = "";
					if ( isset($wordpressSurvey["title"]) )
					{
						$title = trim($wordpressSurvey["title"]);
					}

					$rScriptTitle = "";
					if (isset($wordpressSurvey["rScriptTitle"])) {
						$rScriptTitle = $wordpressSurvey["rScriptTitle"];
					}

					$rScript2Title = "";
					if (isset($wordpressSurvey["rScript2Title"])) {
						$rScript2Title = $wordpressSurvey["rScript2Title"];
					}

					$rScript3Title = "";
					if (isset($wordpressSurvey["rScript3Title"])) {
						$rScript3Title = $wordpressSurvey["rScript3Title"];
					}
					
					$presets["title"] = $title;
					$presets["rScriptTitle"] = $rScriptTitle;
					$presets["rScript2Title"] = $rScript2Title;
					$presets["rScript3Title"] = $rScript3Title;
					break;
				}
			}
		}
		
		// process _GET
		$getActions = array(
			array("param" => "setInactive", "key" => "active", "value" => false),
			array("param" => "setActive", "key" => "active", "value" => true),
			array("param" => "setEvaluationInactive", "key" => "evaluationActive", "value" => false),
			array("param" => "setEvaluationActive", "key" => "evaluationActive", "value" => true),
			array("param" => "setEvaluation2Inactive", "key" => "evaluation2Active", "value" => false),
			array("param" => "setEvaluation2Active", "key" => "evaluation2Active", "value" => true),
			array("param" => "setEvaluation3Inactive", "key" => "evaluation3Active", "value" => false),
			array("param" => "setEvaluation3Active", "key" => "evaluation3Active", "value" => true),
			array("param" => "setPdfInactive", "key" => "pdfActive", "value" => false),
			array("param" => "setPdfActive", "key" => "pdfActive", "value" => true)
		);
		
		foreach ( $getActions as $getAction )
		{
			if ( isset($_GET[$getAction["param"]]) )
			{
				// find correct survey entry
				foreach ( $wordpressSurveys as &$wordpressSurvey )
				{
					if ( $wordpressSurvey["surveyId"] == $_GET[$getAction["param"]] )
					{
						$wordpressSurvey[$getAction["key"]] = $getAction["value"];
						break;
					}
				}
				
				update_option("fbpsych_main_surveys", $wordpressSurveys);
			}
		}
		
		// process delete action
		if ( isset($_GET["deleteReally"]) )
		{
			$newSurveys = array();
			foreach ( $wordpressSurveys as $wordpressSurvey )
			{
				if ( $wordpressSurvey["surveyId"] != $_GET["deleteReally"])
				{
					$newSurveys[] = $wordpressSurvey;
				}
			}
			$wordpressSurveys = $newSurveys;
			
			// delete disk folder
			$folder = WP_CONTENT_DIR . "/uploads/rscripts/" . $_GET["deleteReally"] . "/";
			if ( is_dir($folder) )
			{
				deleteDirectory($folder);
			}
			
			update_option("fbpsych_main_surveys", $newSurveys);
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
				
			if ( empty($_FILES["form"]["name"]["rScript"]) )
			{
				$errorArray["rscript"] = "missing";
			}

			foreach ( $_FILES["form"]["error"] as $index => $uploadError )
			{
				if ( isset($_FILES["form"]["size"][$index]) && $_FILES["form"]["size"][$index] > 0 && $uploadError !== 0 )
				{
					$errorArray["upload"] = "error";
					break;
				}
			}
			
			// check if survey is already added
			if ( $isEditMode === false && isset($_POST["form"]["mainSurvey"]) )
			{
				foreach ( $wordpressSurveys as $wordpressSurvey )
				{
					if ( $wordpressSurvey["surveyId"] == $_POST["form"]["mainSurvey"] )
					{
						$errorArray["survey"]= "duplicate";
						break;
					}
				}
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
				
		<h1>Fragebögen</h1>
		<form name="projectsettingsform" action="<?php if ( $_POST["step"] == "2" ) : ?>admin.php?page=admin_menu_surveys<?php endif;?>" method="post" enctype="multipart/form-data">
			<p>Hier können Sie neue Umfragen anlegen oder Bestehende verwalten.</p>
			
			<!--  error line -->
			<div class="formErrors">
				<?php if ( isset($errorArray["survey"]) && $errorArray["survey"] === "missing" ) :?>
				<div style="color: red; font-weight: bold;">Sie müssen eine Umfrage auswählen!</div>
				<?php endif; ?>
				<?php if ( isset($errorArray["survey"]) && $errorArray["survey"] === "duplicate" ) :?>
				<div style="color: red; font-weight: bold;">Diese Umfrage wurde bereits angelegt!</div>
				<?php endif; ?>
				<?php if ( isset($errorArray["rscript"]) ) :?>
				<div style="color: red; font-weight: bold;">Sie müssen ein R-Script hochladen!</div>
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
				<h2><?php printf("Fragebögen &rsaquo; %s", ($isEditMode == true) ? "Fragebogen bearbeiten" : "Neuer Fragebogen"); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Umfrage<span style="color: red; font-weight: bold;">*</span></th>
						<td>
							<select id="project_survey" name="form[mainSurvey]">
								<option value="" <?php if (!isset($presets["surveyId"]) ) printf("selected=\"selected\"");?> disabled="disabled">Bitte w&auml;hlen Sie eine Umfrage aus</option>
								<?php foreach ( $surveys as $survey ) { ?>
									<?php
										// skip active surveys
										if ( $survey["active"] === "Y")
										{
											continue;
										}
										
										$isSelected = false;
										if ( isset($presets["surveyId"]) )
										{
											if ( $presets["surveyId"] === $survey["sid"] )
											{
												$isSelected = true;
											}
										}
									?>
									<option value="<?php printf($survey["sid"]); ?>" <?php if (!$survey["active"]) printf("disabled=\"disabled\""); ?> <?php if ($isSelected) printf("selected=\"selected\"");?>><?php printf("%s (%s)", $survey["surveyls_title"], $survey["sid"]); ?></option>
								<?php }?>
							</select>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Titel</th>
						<td>
							<input type="text" size="32" name="form[title]" <?php if(isset($presets["title"])) printf("value=\"%s\"", $presets["title"]); ?>/>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Auswertung - R-Script<span style="color: red; font-weight: bold;">*</span></th>
						<td>
							<input type="text" size="32" name="form[rScriptTitle]" <?php if(isset($presets["rScriptTitle"])) printf("value=\"%s\"", $presets["rScriptTitle"]); ?>/>
							<input type="file" name="form[rScript]"/>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Auswertung2 - R-Script</th>
						<td>
							<input type="text" size="32" name="form[rScript2Title]" <?php if(isset($presets["rScript2Title"])) printf("value=\"%s\"", $presets["rScript2Title"]); ?>/>
							<input type="file" name="form[rScript2]"/>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Auswertung3 - R-Script</th>
						<td>
							<input type="text" size="32" name="form[rScript3Title]" <?php if(isset($presets["rScript3Title"])) printf("value=\"%s\"", $presets["rScript3Title"]); ?>/>
							<input type="file" name="form[rScript3]"/>
						</td>
					</tr>
				</table>
			</div>
			
			<p>
				<input type="hidden" name="step" value="1" />
				<input type="submit" name="submit" value="<?php printf("%s", ($isEditMode == true) ? "Umfrage speichern" : "Umfrage hinzuf&uuml;gen"); ?>" />
			</p>
		</form>
		
		<?php endif; ?>
		
		<?php /* STEP 2 */ ?>
		<?php if( isset($_POST["step"]) && $_POST["step"] === "2" ) : ?>
<?php
			$folder = WP_CONTENT_DIR . "/uploads/rscripts/" . $surveyId . "/";
			
			if ( is_dir($folder) )
			{
				deleteDirectory($folder);
			}
			
			mkdir($folder, 0777, true);

			// get form data
			$rScriptTemp = $_FILES["form"]["tmp_name"]["rScript"];
			$rScriptName = $_FILES["form"]["name"]["rScript"];
			$rScriptTemp2 = $_FILES["form"]["tmp_name"]["rScript2"];
			$rScriptName2 = $_FILES["form"]["name"]["rScript2"];
			$rScriptTemp3 = $_FILES["form"]["tmp_name"]["rScript3"];
			$rScriptName3 = $_FILES["form"]["name"]["rScript3"];
			
			move_uploaded_file($rScriptTemp, $folder . $rScriptName);

			if ($rScriptTemp2) {
				move_uploaded_file($rScriptTemp2, $folder . $rScriptName2);
			}

			if ($rScriptTemp3) {
				move_uploaded_file($rScriptTemp2, $folder . $rScriptName3);
			}
			
			$surveyId = $_POST["form"]["mainSurvey"];
			
			$title = trim($_POST["form"]["title"]);
			$rScriptTitle = trim($_POST["form"]["rScriptTitle"]);
			$rScript2Title = trim($_POST["form"]["rScript2Title"]);
			$rScript3Title = trim($_POST["form"]["rScript3Title"]);
			
			if ( $isEditMode )
			{
				foreach ( $wordpressSurveys as &$wordpressSurvey )
				{
					if ( $wordpressSurvey["surveyId"] == $surveyId )
					{
						$wordpressSurvey["rScript"] = $folder . $rScriptName;

						if ($rScriptName2) {
							$wordpressSurvey["rScript2"] = $folder . $rScriptName2;
						}
						
						if ($rScriptName3) {
							$wordpressSurvey["rScript3"] = $folder . $rScriptName3;
						}
						
						$wordpressSurvey["title"] = $title;
						$wordpressSurvey["rScriptTitle"] = $rScriptTitle;
						$wordpressSurvey["rScript2Title"] = $rScript2Title;
						$wordpressSurvey["rScript3Title"] = $rScript3Title;
						break;
					}
				}
			}
			else
			{
				// store in db
				$newSurvey = array(
					"surveyId"			=> $surveyId,
					"rScript"			=> $folder . $rScriptName,
					"rScriptTitle"		=> $rScriptTitle,
					"rScript2Title"		=> $rScript2Title,
					"rScript3Title"		=> $rScript3Title,
					"title"				=> $title,
					"active"			=> true,
					"evaluationActive"	=> true,
					"evaluation2Active"	=> false,
					"evaluation3Active"	=> false,
					"pdfActive"			=> true
				);

				if ($rScriptName2) {
					$newSurvey["rScript2"] = $folder . $rScriptName2;
				}
				
				if ($rScriptName3) {
					$newSurvey["rScript3"] = $folder . $rScriptName3;
				}

				$wordpressSurveys[] = $newSurvey;
			}
			
			update_option("fbpsych_main_surveys", $wordpressSurveys);
?>
		<?php endif; ?>
		
			<div class="wrap">
				<h2>Fragebögen &rsaquo; Angelegte Fragebögen</h2>
				
				<table>
					<tr>
						<th>Umfrage ID</th>
						<th>Titel Orig.</th>
						<th>Titel</th>
						<th>Auswertung</th>
						<th>Auswertung 2</th>
						<th>Auswertung 3</th>
						<th>Umfrage aktiv</th>
						<th>Auswertung aktiv</th>
						<th>Auswertung 2 aktiv</th>
						<th>Auswertung 3 aktiv</th>
						<th>PDF aktiv</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
					</tr>
					<?php for($i=0; $i < sizeof($wordpressSurveys); $i++) : ?>
					<?php
						$surveyId = $wordpressSurveys[$i]["surveyId"];
						$rScript = pathinfo($wordpressSurveys[$i]["rScript"], PATHINFO_BASENAME);
						$rScript2 = pathinfo($wordpressSurveys[$i]["rScript2"], PATHINFO_BASENAME);
						$rScript3 = pathinfo($wordpressSurveys[$i]["rScript3"], PATHINFO_BASENAME);
						$rScriptTitle = $wordpressSurveys[$i]["rScriptTitle"];
						$rScript2Title = $wordpressSurveys[$i]["rScript2Title"];
						$rScript3Title = $wordpressSurveys[$i]["rScript3Title"];
						$active = $wordpressSurveys[$i]["active"];
						$evaluationActive = $wordpressSurveys[$i]["evaluationActive"];
						$evaluation2Active = $wordpressSurveys[$i]["evaluation2Active"];
						$evaluation3Active = $wordpressSurveys[$i]["evaluation3Active"];
						$pdfActive = $wordpressSurveys[$i]["pdfActive"];
						$title = $wordpressSurveys[$i]["title"];
					?>
						<tr style="background-color: <?php printf("%s", ($i % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
							<td style="text-align: center; padding: 3px;"><?php printf("%s", $surveyId); ?></td>
							<td style="text-align: center; padding: 3px;">
							<?php
								if ( !empty($title) )
								{
									$titleDisplay = $title;
								}
								else
								{
									foreach ( $surveys as $survey )
									{
										if ( $survey["sid"] === $surveyId )
										{
											$titleDisplay = $survey["surveyls_title"];
											break;
										}
									}
								}
								
								printf("%s", $titleDisplay);
							?>
							</td>
							<td style="text-align: center; padding: 3px;">
							<?php
								foreach ( $surveys as $survey )
								{
									if ( $survey["sid"] === $surveyId )
									{
										printf("%s", $survey["surveyls_title"]);
										break;
									}
								}
							?>
							</td>
							<td style="text-align: center; padding: 3px;"><?php printf("%s </br> %s", $rScriptTitle, $rScript); ?></td>
							<td style="text-align: center; padding: 3px;"><?php printf("%s </br> %s", $rScript2Title, $rScript2); ?></td>
							<td style="text-align: center; padding: 3px;"><?php printf("%s </br> %s", $rScript3Title, $rScript3); ?></td>
							<td style="text-align: center; padding: 3px;"><?php if($active === true) : ?><b style="color: green;">Ja</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setActive=%s", $_GET["page"], $surveyId); ?>">Ja</a><?php endif; ?> | <?php if($active === false) : ?><b style="color: red;">Nein</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setInactive=%s", $_GET["page"], $surveyId); ?>">Nein</a><?php endif; ?></td>
							<td style="text-align: center; padding: 3px;"><?php if($evaluationActive === true) : ?><b style="color: green;">Ja</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluationActive=%s", $_GET["page"], $surveyId); ?>">Ja</a><?php endif; ?> | <?php if($evaluationActive === false) : ?><b style="color: red;">Nein</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluationInactive=%s", $_GET["page"], $surveyId); ?>">Nein</a><?php endif; ?></td>
							<td style="text-align: center; padding: 3px;"><?php if($evaluation2Active === true) : ?><b style="color: green;">Ja</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluation2Active=%s", $_GET["page"], $surveyId); ?>">Ja</a><?php endif; ?> | <?php if($evaluation2Active === false) : ?><b style="color: red;">Nein</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluation2Inactive=%s", $_GET["page"], $surveyId); ?>">Nein</a><?php endif; ?></td>
							<td style="text-align: center; padding: 3px;"><?php if($evaluation3Active === true) : ?><b style="color: green;">Ja</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluation3Active=%s", $_GET["page"], $surveyId); ?>">Ja</a><?php endif; ?> | <?php if($evaluation3Active === false) : ?><b style="color: red;">Nein</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setEvaluation3Inactive=%s", $_GET["page"], $surveyId); ?>">Nein</a><?php endif; ?></td>
							<td style="text-align: center; padding: 3px;"><?php if($pdfActive === true) : ?><b style="color: green;">Ja</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setPdfActive=%s", $_GET["page"], $surveyId); ?>">Ja</a><?php endif; ?> | <?php if($pdfActive === false) : ?><b style="color: red;">Nein</b><?php else: ?><a href="<?php printf("admin.php?page=%s&setPdfInactive=%s", $_GET["page"], $surveyId); ?>">Nein</a><?php endif; ?></td>
							<td style="text-align: center; padding: 3px 3px 3px 30px;">
								<a href="<?php printf("admin.php?page=%s&edit=%s", $_GET["page"], $surveyId); ?>">Bearbeiten</a>
							</td>
							<td style="text-align: center; padding: 3px 3px 3px 30px;">
								<?php if ( isset($_GET["delete"]) && $_GET["delete"] === $surveyId ) :?>
									<a style="color: red;" href="<?php printf("admin.php?page=%s&deleteReally=%s", $_GET["page"], $surveyId); ?>">Wirklich l&ouml;schen</a>
								<?php else: ?>
									<a style="color: red;" href="<?php printf("admin.php?page=%s&delete=%s", $_GET["page"], $surveyId); ?>">L&ouml;schen</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endfor; ?>
				</table>
			</div>
				
			<?php		
			$rpcClient->release_session_key($sessionKey);
		}
		catch ( Exception $e )
		{
			echo nl2br($e->getMessage()).'<br />'."\n";
		}
		
	function maySelectGroups()
	{
		$groupOptionsGlobal = get_site_option("fbpsych_question_groups_options");
		
		// check if project admin is allowed to select groups
		$check = false;
		
		if ( !isset($groupOptionsGlobal["select"]) || $groupOptionsGlobal["select"] === "no" )
		{
			// check project specific settings
			$projectQuestionGroupsOptions = get_option("fbpsych_question_groups_options");
				
			if (	isset($projectQuestionGroupsOptions["overwrite"]) && $projectQuestionGroupsOptions["overwrite"] === "yes" &&
					isset($projectQuestionGroupsOptions["select"]) && $projectQuestionGroupsOptions["select"] === "yes" )
			{
				$check = true;
			}
		}
		elseif ( isset($groupOptionsGlobal["select"]) && $groupOptionsGlobal["select"] === "yes" )
		{
			$check = true;
		}
		
		return $check;
	}
	
	// Delete folder function
	function deleteDirectory($dir) {
		if (!file_exists($dir)) return true;
		if (!is_dir($dir) || is_link($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') continue;
			if (!deleteDirectory($dir . "/" . $item)) {
				chmod($dir . "/" . $item, 0777);
				if (!deleteDirectory($dir . "/" . $item)) return false;
			};
		}
		return rmdir($dir);
	}