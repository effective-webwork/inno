<div id="columnset">
	<div id="content" role="main">
	
		<?php printf("%s", fbpsych_get_rotator_image()); ?>
	
		<div id="left_column">
			
			<div id="maincontent">
				
				<?php printf("%s", fbpsych_get_step_image()); ?>
				
				<h1><?php _e("FBP_PROJECT_ACCOUNT", "twentytwelve"); ?></h1>
				
				<div class="entry-content">
					<?php _e("FBP_PROJECT_ACCOUNT_INFORMATION", "twentytwelve"); ?>
					<?php
						$company = get_blog_option(null, "cimy_uef_COMPANY");
					?>
					<div class="create_survey">
						<form class="pure-form pure-form-aligned" name="evaluatesurvey" action="" method="post" enctype="multipart/form-data">
							<!--  error line -->
							<div class="formErrors">
								<?php if ( isset($passwordErrors["different"]) ) : ?>
									<div><?php _e("FBP_PROJECT_ERROR_PASSWORDS_MATCH", "twentytwelve"); ?></div>
								<?php endif;?>
							</div>

							<fieldset>
								<div class="pure-control-group">
									<label for="companyName">Name des Unternehmens:</label>
									<input id="companyName" type="text" name="form[company]" size="40" value="<?php echo $company; ?>"/>
								</div>

								<div class="pure-control-group">
									<label for="password">Passwort:</label>
									<input id="password" type="password" name="form[password]" size="32" value=""/>
								</div>

								<div class="pure-control-group">
									<label for="password2">Passwort Wiederholung:</label>
									<input id="password2" type="password" name="form[password_second]" size="32" value=""/>

									<input type="hidden" name="account" value="save" />
									<input class="pure-button" type="submit" id="submit" name="submit" value="speichern" />
							</fieldset>
						</form>
						
						<div class="clear"></div>
					</div>
					<br/>
					
					<?php if ( !( isset($_GET["account"]) && $_GET["account"] === "delete" ) ) : ?>
						<form name="deleteaccount" action="" method="get" enctype="multipart/form-data">
							<button class="delete" type="submit" name="deleteAccount" value="true">
								<img src="<?php echo get_template_directory_uri(); ?>/img/delete.png" /><span><?php _e("FBP_PROJECT_ACCOUNT_DELETE", "twentytwelve"); ?></span>
							</button>
							
							<input type="hidden" name="account" value="delete" />
							
						</form>
					<?php else: ?>
						<form name="deleteaccount" action="" method="get" enctype="multipart/form-data">
							<button class="delete" type="submit" name="deleteAccount" value="true">
								<img src="<?php echo get_template_directory_uri(); ?>/img/delete.png" /><span><?php _e("FBP_PROJECT_ACCOUNT_DELETE_REALLY", "twentytwelve"); ?></span>
							</button>
							
							<input type="hidden" name="account" value="deleteReally" />
							
						</form>
					<?php endif; ?>
					
				</div>
			</div>
		</div>
	</div>

<?php get_sidebar(); ?><div class="clear"></div></div><!-- #primary -->
<?php get_footer(); ?>