<?php
	$createForm = array();
	$createForm["type"] = false;
	
	// handle form submit
	if ( isset($_POST["action"]) && $_POST["action"] === "create" )
	{
		if ( isset($_POST["create_type"]) )
		{
			$createForm["type"] = $_POST["create_type"];
		}
	}
	
	if ( isset($_POST["Submit"]) )
	{
		
	}
?>
	<h1>Unternehmensformular</h1>
	<form name="companyformconfigform" action="" method="post">
		<p>Hier können Sie das Unternehmensformular konfigurieren.</p>
		<div class="wrap">
			<h2>Unternehmensformular &rsaquo; Neues Feld anlegen</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Feldtyp</th>
					<td>
						<select name="create_type" onchange="this.form.submit();">
							<option value="" disabled="disabled"<?php if($createForm["type"] === false) printf(" selected=\"selected\"")?>>Nichts ausgewählt</option>
							<option value="text"<?php if($createForm["type"] === "text") printf(" selected=\"selected\"")?>>Textfeld</option>
							<option value="numeric"<?php if($createForm["type"] === "numeric") printf(" selected=\"selected\"")?>>Numerisch</option>
						</select>
					</td>
				</tr>
				<?php
					if ( $createForm["type"] !== false )
					{
				?>
						<tr valign="top">
							<th scope="row">Frage</th>
							<td>
								<input name="create_question" type="text" size="60" />
							</td>
						</tr>
				<?php
						switch ( $createForm["type"] )
						{
							case "text":
								?>
									
								<?php
								break;
							
							case "numeric":
								?>
								<!--
									<tr valign="top">
										<th scope="row">Frage</th>
										<td>
											<input type="text" size="60" />
										</td>
									</tr>-->
								<?php
								break;
						}
				?>
						
				<?php
					}
				?>
				<tr valign="top">
					<th scope="row">Pflichtfeld</th>
					<td>
						<input type="radio" value="yes" name="create_mandatory" /> Ja<br/>
						<input type="radio" value="no" name="create_mandatory" checked="checked" /> Nein
					</td>
				</tr>
			</table>
		</div>
		<p>
			<input type="hidden" name="action" value="create" />
			<input type="submit" name="Submit" value="Einstellungen Speichern" />
		</p>
	</form>
	
	<div class="wrap">
		<h2>Unternehmensformular &rsaquo; Vorhandene Felder</h2>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Wählen Sie ein Projekt</th>
				<td>
					
				</td>
			</tr>
		</table>
	</div>
	