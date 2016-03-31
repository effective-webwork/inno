<?php
	// get site options
	$options = get_site_option("fbpsych_question_groups_options");
	
	// handle form submit
	if ( isset($_POST["action"]) && $_POST["action"] === "save" )
	{
		if ( isset($_POST["question_group_select"]) )
		{
			$options["select"] = $_POST["question_group_select"];
		}
		
		// store options
		update_site_option("fbpsych_question_groups_options", $options);
	}
	
	// determe default values
	if ( $options === false )
	{
		$select = "no";
	}
	else
	{
		$select = (isset($options["select"]) ? $options["select"] : "no");
	}
?>
	<h1>Fragegruppen</h1>
	<form name="questiongroupsglobalform" action="" method="post">
		<p>Hier können Sie Einstellungen für Fragegruppen vornehmen.</p>
		<div class="wrap">
			<h2>Fragegruppen &rsaquo; Globale Voreinstellungen</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Projektleiter dürfen Fragegruppen selbst auswählen</th>
					<td>
						<input type="radio" id="question_group_free_select" name="question_group_select" value="yes"<?php if($select === "yes") printf(" checked=\"checked\"");?> /> ja<br/>
						<input type="radio" id="question_group_nonfree_select" name="question_group_select" value="no"<?php if($select === "no") printf(" checked=\"checked\"");?> /> nein
					</td>
				</tr>
			</table>
		</div>
		<p>
			<input type="hidden" name="action" value="save" />
			<input type="submit" name="Submit" value="Einstellungen Speichern" />
		</p>
	</form>
	
<?php
	// get all blogs
	global $wpdb;
	
	$blogs = $wpdb->get_results("SELECT * FROM wp_blogs ORDER BY blog_id", "ARRAY_A");
	
	// determ selected blog
	$blogId = "";
	
	if ( isset($_POST["action"]) && $_POST["action"] === "save_specific" )
	{
		if ( isset($_POST["question_group_project"]) )
		{
			$blogId = $_POST["question_group_project"];
		}
		
		$blogOptionsStore = null;
		
		if ( isset($_POST["selectedProject"]) && $_POST["selectedProject"] === $blogId )
		{
			if ( isset($_POST["question_group_project_overwrite"]) )
			{
				$blogOptionsStore["overwrite"] = "yes";
			}
			
			if ( isset($_POST["question_group_project_select"]) )
			{
				$blogOptionsStore["select"] = $_POST["question_group_project_select"];
			}
			
			// store options
			if ( $blogOptionsStore !== null && $blogId !== "" )
			{
				update_blog_option($blogId, "fbpsych_question_groups_options", $blogOptionsStore);
			}
		}
	}
	
	// get blog options
	if ( $blogId !== "" )
	{
		$blogOptions = get_blog_option($blogId, "fbpsych_question_groups_options");
	}
	
	if ( $blogId === "" || $blogOptions === false )
	{
		$blogOptions["overwrite"] = "no";
		$blogOptions["select"] = "no";
	}
?>
	<form name="questiongroupsprojectform" action="" method="post">
		<div class="wrap">
			<h2>Fragegruppen &rsaquo; Projektspezifische Einstellungen</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Wählen Sie ein Projekt</th>
					<td>
						<select id="question_group_project" name="question_group_project" onchange="this.form.submit();">
							<option value="" disabled="disabled"<?php if($blogId === "") printf(" selected=\"selected\"")?>>Nichts ausgewählt</option>
							<?php
								foreach ( $blogs as $index => $blog )
								{
							?>
									<option value="<?php printf($blog["blog_id"])?>"<?php if($blogId === $blog["blog_id"]) printf("selected=\"selected\"")?>><?php printf($blog["path"])?></option>
							<?php
								}
							?>
						</select>
					</td>
				</tr>
				
				<?php
					if ( $blogId !== "" )
					{
				?>
						<tr valign="top">
							<th scope="row">Globale Voreinstellungen überschreiben</th>
							<td>
								<input type="checkbox" id="question_group_project_overwrite" name="question_group_project_overwrite" value="yes"<?php if($blogOptions["overwrite"] === "yes") printf(" checked=\"checked\"");?>  />
								<input type="hidden" name="selectedProject" value="<?php printf($blogId)?>" />
							</td>
						</tr>
						
						<tr valign="top">
							<th scope="row">Projektleiter darf Fragegruppen selbst auswählen</th>
							<td>
								<input type="radio" id="question_group_free_select" name="question_group_project_select" value="yes"<?php if($blogOptions["select"] === "yes") printf(" checked=\"checked\"");?> /> ja<br/>
								<input type="radio" id="question_group_nonfree_select" name="question_group_project_select" value="no"<?php if($blogOptions["select"] === "no") printf(" checked=\"checked\"");?> /> nein
							</td>
						</tr>
				<?php
					}
				?>
			</table>
		</div>
		<p>
			<input type="hidden" name="action" value="save_specific" />
			<input type="submit" name="Submit" value="Einstellungen Speichern" />
		</p>
	</form>
	