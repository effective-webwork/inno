<h1>Mailvorlagen</h1>

<?php
	// get current templates
	switch_to_blog(1);
	$templates = get_option("fbpsych_mail_templates", array());
	restore_current_blog();
	
	// check delete
	if (isset($_POST["deleteReally"])) {
		foreach ($templates as $index => $template) {
			if ($template["title"] == $_POST["title"]) {
				array_splice($templates, $index, 1);
				break;
			}
		}
		
		switch_to_blog(1);
		update_option("fbpsych_mail_templates", $templates);
		restore_current_blog();
		
		unset($_POST);
	}

	// check save
	if (isset($_POST["save"])) {
		$title = $_POST["title"];
		$content = $_POST["mailtemplateeditor"];
		
		if (empty($title) || empty($content)) { ?>
			<div>
				<div style="color: red; font-weight: bold; text-align: center;">Titel und Inhalt dürfen nicht leer sein!</div>
			</div> <?php
		} else {
			// check if title is unique
			$validTitle = true;
			
			foreach ($templates as $template) {
				if ($template["title"] == $title && $_POST["option"] != $title) {
					$validTitle = false;
					break;
				}
			}
			
			if ($validTitle) {
				// store new template or update old one
				
				if ($_POST["option"] == "create") {
					$templates[] = array("title" => $title, "content" => $content);
				} else {
					// if title has not been changed
					$done = false;
					foreach ($templates as $index => $template) {
						if ($template["title"] == $title) {
							$templates[$index] = array("title" => $title, "content" => $content);
							$isEditOperation = true;
							$done = true;
							break;
						}
					}
					
					// if title has been changed
					if (!$done) {
						foreach ($templates as $index => $template) {
							if ($template["title"] == $_POST["option"]) {
								array_splice($templates, $index, 1);
								$templates[] = array("title" => $title, "content" => $content);
								break;
							}
						}
					}
				}
				
				switch_to_blog(1);
				update_option("fbpsych_mail_templates", $templates);
				restore_current_blog();
				
				unset($_POST);
				?>
				<div>
					<div style="color: green; font-weight: bold; text-align: center;">Vorlage gespeichert!</div>
				</div>
				<?php
			} else { ?>
				<div>
					<div style="color: red; font-weight: bold; text-align: center;">Dieser Titel wird bereits verwendet!</div>
				</div> <?php
			}
		}
	}
?>

<?php
	// determine the selected form option
	$selected = "choose";
	if (isset($_POST["option"])) {
		$selected = $_POST["option"];
	}
	
	$updateEntry = null;
	if ($selected != "choose" && $selected != "create") {
		foreach ($templates as $template) {
			if ($selected == $template["title"]) {
				$updateEntry = $template;
				break;
			}
		}
	}
?>

<form action="admin.php?page=admin_menu_mail_templates" method="post" enctype="multipart/form-data">
	<div>Bitte wählen Sie die Aktion "Neue Vorlage erstellen" oder eine bereits vorhandene Vorlage, um diese zu bearbeiten.</div>
	<select name="option" onchange="this.form.submit();">
		<option <?php if ($selected == "choose") : ?>selected="selected" <?php endif; ?>disabled="disabled">Bitte auswählen</option>
		<option disabled="disabled">----------------</option>
		<option <?php if ($selected == "create") : ?>selected="selected" <?php endif; ?>value="create">Neue Vorlage erstellen</option>
		<option disabled="disabled">----------------</option>
		<?php foreach ($templates as $template) : ?>
			<option <?php if ($updateEntry["title"] == $template["title"]) : ?>selected="selected" <?php endif; ?>value="<?php echo $template["title"]; ?>"><?php echo $template["title"]; ?></option>
		<?php endforeach; ?>
	</select>
	
	<?php if ($selected != "choose") : ?>
		<?php
			$titleDefault = "";
			if ($updateEntry) {
				$titleDefault = $updateEntry["title"];
			}
		?>
		<div id="poststuff">
			<div>
				<b>Info: Bei der Benutzung des Editors können sie folgende Platzhalter verwenden:</b>
				<ul>
					<li>%SURVEY_URL% - Gibt die Adresse der Umfrage aus</li>
				</ul> 
			</div>
			<div>
				Titel der Vorlage: <input type="text" name="title" value="<?php echo $titleDefault; ?>"/>
			</div>
			
			<?php
				$content = "";
				if ($selected == "created" && isset($_POST["mailtemplateeditor"])) {
					$content = $_POST["mailtemplateeditor"];
				} else if ($updateEntry) {
					$content = $updateEntry["content"];
				}
				
				wp_editor($content, "mailtemplateeditor", array(
					"media_buttons" => false
				));
			?>
		</div>
		<div style="padding-top: 10px">
			<input type="submit" name="save" value="Speichern">
			<?php if (isset($updateEntry)) : ?>
				<?php
					$deleteValue = "Löschen";
					$deleteName = "delete";
					if (isset($_POST["delete"])) {
						$deleteValue = "Wirklich löschen!";
						$deleteName = "deleteReally";
					}
				?>
				<input type="submit" name="<?php echo $deleteName; ?>" value="<?php echo $deleteValue; ?>" style="border: 1px solid red; margin-left: 40px; background-color: rosybrown;">
			<?php endif; ?>
		</div>
	<?php endif; ?>
</form>