<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="columnset">
		<div id="content" role="main">
		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php twentytwelve_content_nav( 'nav-below' ); ?>

		<?php else : ?>
		
			<?php if ( is_main_site() ) : ?>
				
				<article id="post-0" class="post no-results not-found">

				<?php if ( current_user_can( 'edit_posts' ) ) :
					// Show a different message to a logged-in user who can add posts.
				?>
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'No posts to display', 'twentytwelve' ); ?></h1>
					</header>
	
					<div class="entry-content">
						<p><?php printf( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'twentytwelve' ), admin_url( 'post-new.php' ) ); ?></p>
					</div><!-- .entry-content -->
	
				<?php else :
					// Show the default message to everyone else.
				?>
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found', 'twentytwelve' ); ?></h1>
					</header>
	
					<div class="entry-content">
						<p><?php _e( 'Apologies, but no results were found. Perhaps searching will help find a related post.', 'twentytwelve' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- .entry-content -->
				<?php endif; // end current_user_can() check ?>
	
				</article><!-- #post-0 -->
				
			<?php else : ?>
				
				<?php printf("%s", fbpsych_get_rotator_image()); ?>
				
				<div id="left_column">
					
					<div id="maincontent">
					
						<?php printf("%s", fbpsych_get_step_image()); ?>
						
						<?php /* STEP 1 */ ?>
						<?php if( !isset($_POST["step"]) ) : ?>
						<h1><?php _e("FBP_PROJECT_TITLE_FIRST", "twentytwelve"); ?></h1>
						
						<div class="entry-content">
							<?php _e("FBP_PROJECT_MAIN_WELCOME", "twentytwelve"); ?>
							
							<div class="create_survey">
								<h2><?php _e("FBP_PROJECT_CREATE_SURVEY", "twentytwelve"); ?></h2>
								<form name="createsurvey" action="" method="post" enctype="multipart/form-data">
									<div class="select">
										<select name="surveyId">
											<?php
												foreach ( fbpsych_get_surveys() as $survey )
												{
													// skip inactive
													if ( $survey["active"] === false)
													{
														continue;
													}
													printf("<option value='%d'>%s</option>", $survey["surveyId"], $survey["title"]);
												}
											?>
										</select>
									</div>
									
									<input type="hidden" name="step" value="2" />
									<input type="submit" id="submit" value="erstellen" />
								</form>
								
								<div class="clear"></div>
							</div>
						</div>
						<?php endif; ?>
						
						<?php /* STEP 2 */ ?>
						<?php
							/* form check */
							if( isset($_POST["step"]) && $_POST["step"] === "2" && isset($_POST["submit"]) )
							{
								$errorArray = array();
								
								// form checks
								$countGf = trim($_POST["count_gf"]);
								if ( $countGf === "" )
								{
									$errorArray["gf"] = "empty";
								}
								else
								{
									if ( !is_numeric($countGf) )
									{
										$errorArray["gf"] = "numeric";
									}
									else
									{
										if ( (int) $countGf < 0 )
										{
											$errorArray["gf"] = "negativ";
										}
									}
								}
								
								$countBr = trim($_POST["count_br"]);
								if ( $countBr === "" )
								{
									$errorArray["br"] = "empty";
								}
								else
								{
									if ( !is_numeric($countBr) )
									{
										$errorArray["br"] = "numeric";
									}
									else
									{
										if ( (int) $countBr < 0 )
										{
											$errorArray["br"] = "negativ";
										}
									}
								}
								
								$teams =& $_POST["team"];
								if ( empty($teams) )
								{
									$errorArray["teams"][] = "empty";
								}
								else
								{
									foreach ( $teams["title"] as $title )
									{
										$title = trim($title);
										if ( $title === "" )
										{
											$errorArray["teams"][] = "empty title";
											break;
										}
									}
									
									$emptyFault = false;
									$numericFault = false;
									$negativFault = false;
									foreach ( $teams["count"] as $count )
									{
										$count = trim($count);
										
										if ( $count === "" && !$emptyFault )
										{
											$errorArray["teams"][] = "empty count";
											$emptyFault = true;
											continue;
										}
										
										if ( !is_numeric($count) && !$numericFault )
										{
											$errorArray["teams"][] = "numeric";
											$numericFault = true;
											continue;
										}
										
										if ( (int) $count < 0 && !$negativFault )
										{
											$errorArray["teams"][] = "negativ";
											$negativFault = true;
											continue;
										}
									}
								}
								
								if ( !empty($errorArray) )
								{
									$_POST["step"] = "2";
								}
								else
								{
									$_POST["step"] = "3";
								}
							}
						?>
						<?php if( isset($_POST["step"]) && $_POST["step"] === "2" ) : ?>
						
						<?php
							if( isset($_POST["delTeam"]) )
							{
								// handle delete row
								array_splice($_POST["team"]["title"], $_POST["delTeam"], 1);
								array_splice($_POST["team"]["count"], $_POST["delTeam"], 1);
							}
						?>
						
						<h1><?php _e("FBP_PROJECT_TITLE_FIRST", "twentytwelve"); ?> &rsaquo; <?php _e("FBP_PROJECT_TITLE_PARTICIPANTS", "twentytwelve"); ?></h1>
						
						<div class="entry-content">
							<?php _e("FBP_PROJECT_COMPANY_DATA", "twentytwelve"); ?>
							
							<div class="create_survey">
								<form name="createsurvey" action="" method="post" enctype="multipart/form-data">
									<input type="hidden" name="step" value="2" />
									<input type="hidden" name="surveyId" value="<?php printf("%s", $_POST["surveyId"]); ?>"/>
									
									<!--  error line -->
									<div class="formErrors">
										<?php if ( isset($errorArray["gf"]) ) : ?>
											<?php if ( $errorArray["gf"] === "empty" ) : ?>
												Sie müssen einen Wert für die Anzahl der Geschäftsführer eingeben!</div>
											<?php endif; ?>
											<?php if ( $errorArray["gf"] === "numeric" ) : ?>
												<div>Sie müssen für die Anzahl der Geschäftsführer eine Zahl eingeben!</div>
											<?php endif; ?>
											<?php if ( $errorArray["gf"] === "negativ" ) : ?>
												<div>Sie dürfen für die Anzahl der Geschäftsführer keinen negativen Wert verwenden!</div>
											<?php endif; ?>
										<?php endif;?>
										<?php if ( isset($errorArray["br"]) ) : ?>
											<?php if ( $errorArray["br"] === "empty" ) : ?>
												<div><div>Sie müssen einen Wert für die Anzahl der Betriebsräte eingeben!</div></div>
											<?php endif; ?>
											<?php if ( $errorArray["br"] === "numeric" ) : ?>
												<div>Sie müssen für die Anzahl der Betriebsräte eine Zahl eingeben!</div>
											<?php endif; ?>
											<?php if ( $errorArray["br"] === "negativ" ) : ?>
												<div>Sie dürfen für die Anzahl der Betriebsräte keinen negativen Wert verwenden!</div>
											<?php endif; ?>
											
										<?php endif;?>
										<?php if ( isset($errorArray["teams"]) ) : ?>
											<?php if ( in_array("empty", $errorArray["teams"]) ) : ?>
												<div><?php _e("FBP_PROJECT_ERROR_NO_TEAMS", "twentytwelve"); ?></div>
											<?php endif; ?>
											<?php if ( in_array("empty title", $errorArray["teams"]) ) : ?>
												<div><?php _e("FBP_PROJECT_ERROR_NO_TEAM_TITLE", "twentytwelve"); ?></div>
											<?php endif; ?>
											<?php if ( in_array("empty count", $errorArray["teams"]) ) : ?>
												<div><?php _e("FBP_PROJECT_ERROR_TEAM_THRESHOLD", "twentytwelve"); ?></div>
											<?php endif; ?>
											<?php if ( in_array("numeric", $errorArray["teams"]) ) : ?>
												<div><?php _e("FBP_PROJECT_ERROR_TEAM_NOT_NUMERIC", "twentytwelve"); ?></div>
											<?php endif; ?>
											<?php if ( in_array("negativ", $errorArray["teams"]) ) : ?>
												<div><?php _e("FBP_PROJECT_ERROR_TEAM_NEGATIV", "twentytwelve"); ?></div>
											<?php endif; ?>
										<?php endif;?>
									</div>
									
									<!-- first line -->
									<div class="organi center">
										<?php printf("%s", fbpsych_get_company_title()); ?>
									</div>
									
									<!-- second line -->
									<div class="organi left">
										Anz. Gesch&auml;ftsf&uuml;hrer
										<input class="count" type="text" name="count_gf" value="<?php if(isset($_POST['count_gf'])) printf('%s', $_POST['count_gf']); ?>" />
									</div>
									
									<div class="organi right">
										Anz. Betriebsrat
										<input class="count" type="text" name="count_br" value="<?php if(isset($_POST['count_br'])) printf('%s', $_POST['count_br']); ?>" />
									</div>
									
									<div id="organi_m" class="organi autowidth">
										Mitarbeiter
										<div class="organi_content">
											<?php _e("FBP_PROJECT_CREATE_TEAM", "twentytwelve"); ?>
											<button class="create_new" type="submit" name="addTeam" value="true">
												<img src="<?php echo get_template_directory_uri(); ?>/img/add.png" /><span><?php _e("FBP_ADD", "twentytwelve"); ?></span>
											</button>
											<br/>
											
											<?php if ( isset($_POST["team"]["title"]) ) : ?>
												<?php for ( $i=0; $i < sizeof($_POST["team"]["title"]); $i++ ) :?>
													<?php
														$title = "";
														$titlePost = trim($_POST["team"]["title"][$i]);
														if ( !empty($titlePost) )
														{
															$title = $titlePost;
														}
														
														$count = "";
														$countPost = trim($_POST["team"]["count"][$i]);
														if ( !empty($countPost) && is_numeric($countPost) )
														{
															$count = $countPost;
														}
													?>
													<div class="organi_team_row">
														<div class="column_100"><?php printf("Team #%d:", $i+1); ?></div>
														<div class="column_250">
															<input class="text" type="text" name="team[title][]" value="<?php printf('%s', $title); ?>" />
														</div>
														<div class="column_150">
															<?php _e("FBP_PROJECT_COUNT_TEAM_MEMBER", "twentytwelve"); ?> <input class="count" type="text" name="team[count][]" value="<?php if(!empty($count)) printf('%d', $count); ?>" />
														</div>
														<div class="column_100">
															<button class="delete" type="submit" name="delTeam" value="<?php printf('%s', $i); ?>">
																<img src="<?php echo get_template_directory_uri(); ?>/img/delete.png" /><span><?php _e("FBP_REMOVE", "twentytwelve"); ?></span>
															</button>
														</div>
														<div class="clear"></div>
													</div>
												<?php endfor; ?>
											
											<?php endif; ?>
											
											<?php if ( isset($_POST["addTeam"]) && $_POST["addTeam"] === "true") :?>
												<div class="organi_team_row">
													<div class="column_100"><?php printf("Team #%d:", $i+1); ?></div>
													<div class="column_250">
														<input class="text" type="text" name="team[title][]" />
													</div>
													<div class="column_150">
														<?php _e("FBP_PROJECT_COUNT_TEAM_MEMBER", "twentytwelve"); ?> <input class="count" type="text" name="team[count][]" />
													</div>
													<div class="column_100">
														<button class="delete" type="submit" name="delTeam" value="<?php printf('%s', $i+1); ?>">
															<img src="<?php echo get_template_directory_uri(); ?>/img/delete.png" /><span><?php _e("FBP_REMOVE", "twentytwelve"); ?></span>
														</button>
													</div>
													<div class="clear"></div>
												</div>
											<?php endif;?>
										</div>
									</div>
									
									<div class="clear"></div>
									
									<input type="submit" id="submit" name="submit" value="weiter" />
								</form>
								
								<div class="clear"></div>
							</div>
						</div>
						<?php endif; ?>
						
						<?php /* STEP 3 */ ?>
						<?php
							if ( isset($_POST["step"]) && $_POST["step"] === "3" )
							{
								if ( !fbpsych_can_user_edit_question_groups() )
								{
									// goto step 4
									$_POST["step"] = "4";
								}
								else
								{
									/* form check */
									if( isset($_POST["submit"]) )
									{
										// TODO:
								
										$_POST["step"] = "4";
									}
								}
							}
						?>
						<?php if( isset($_POST["step"]) && $_POST["step"] === "3" ) : ?>
						<h1><?php _e("FBP_PROJECT_TITLE_FIRST", "twentytwelve"); ?> &rsaquo; <?php _e("FBP_PROJECT_TITLE_QUESTION_GROUPS", "twentytwelve"); ?></h1>
						
						<div class="entry-content">
							
							<?php _e("FBP_PROJECT_COMPANY_STRUCTURE", "twentytwelve"); ?>
							
							<div class="create_survey">
								<form name="createsurvey" action="" method="post" enctype="multipart/form-data">
									<input type="hidden" name="step" value="3" />
									<input type="hidden" name="surveyId" value="<?php printf("%s", $_POST["surveyId"]); ?>"/>
									<input type="hidden" name="count_gf" value="<?php printf("%s", $_POST["count_gf"]); ?>"/>
									<input type="hidden" name="count_br" value="<?php printf("%s", $_POST["count_br"]); ?>"/>
									
									<?php foreach ( $_POST["team"]["title"] as $teamTitle ) : ?>
										<input type="hidden" name="team[title][]" value="<?php printf('%s', $teamTitle); ?>"/>
									<?php endforeach; ?>
									
									<?php foreach ( $_POST["team"]["count"] as $teamCount ) : ?>
										<input type="hidden" name="team[count][]" value="<?php printf('%d', $teamCount); ?>"/>
									<?php endforeach; ?>
								</form>
							</div>
						</div>
						<?php endif; ?>
						
						<?php /* STEP 4 */ ?>
						<?php
							/* form check */
							/*
							if( isset($_POST["submit"]) )
							{
								// TODO:
							
								//$_POST["step"] = "4";
							}
							*/
						?>
						<?php if( isset($_POST["step"]) && $_POST["step"] === "4" ) : ?>
						<?php
							/* prepare data */
							$tokenCount = 1 + $_POST["count_br"] + $_POST["count_gf"] + array_sum($_POST["team"]["count"]);
							$data = array(
								"surveyId"		=> $_POST["surveyId"],
								"tokenCount"	=> $tokenCount,
								"teams"			=> $_POST["team"]["title"]
							);

							$surveyUrl = fbpsych_create_survey($data);
						?>
						<h1><?php _e("FBP_PROJECT_TITLE_FIRST", "twentytwelve"); ?> &rsaquo; <?php _e("FBP_PROJECT_TITLE_FINAL", "twentytwelve"); ?></h1>
						
						<div class="entry-content">
							<?php _e("FBP_PROJECT_CREATE_FINAL", "twentytwelve"); ?>
						
							<div class="create_survey">
								<a href="<?php printf("%s", $surveyUrl); ?>"><?php printf("%s", $surveyUrl); ?></a>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			
			<?php endif; ?>

		<?php endif; // end have_posts() check ?>

		</div><!-- #content -->
	

<?php get_sidebar(); ?><div class="clear"></div></div><!-- #primary -->
<?php get_footer(); ?>