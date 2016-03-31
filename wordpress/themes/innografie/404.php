<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

?>

<?php
	

	$request = $wp->request;
	if ( !is_main_site() && is_numeric($request) )
	{
		$surveyId = $request;
		
		// handle delete
		if ( isset($_GET["deleteSurveyReally"]) )
		{
			fbpsych_delete_project_survey($surveyId);
			
			header("Location: " . get_site_url());
			exit;
		}
		
		get_header();
		
		include("surveyDetail.php");
	}
	else if ( $request === 'account' )
	{
		if ( isset($_GET["account"]) && $_GET["account"] === "deleteReally" )
		{
			require_once(WP_CONTENT_DIR . '/../wp-admin/includes/user.php');
			fbpsych_delete_blog(get_current_blog_id(), true);
			global $wpdb;
			$wpdb->delete( $wpdb->users, array( 'ID' => get_current_user_id() ) );
			$userData = get_userdata(get_current_user_id());
			$wpdb->delete( $wpdb->signups, array( 'user_email' => $userData->user_email ) );
			$wpdb->query('DELETE FROM wp_cimy_uef_data WHERE `USER_ID` = ' . mysql_real_escape_string(get_current_user_id) );
			$wpdb->delete( $wpdb->signups, array( 'user_email' => $userData->user_email ) );
			wp_logout();
			wp_redirect(network_home_url()); exit;
		}
		
		if ( isset($_POST["account"]) && $_POST["account"] === "save" )
		{
			$newCompany = trim($_POST["form"]["company"]);
			update_blog_option(null, "cimy_uef_COMPANY", $newCompany);
		}
		
		if ( isset($_POST["form"]["password"]) && isset($_POST["form"]["password_second"]) )
		{
			$password = trim($_POST["form"]["password"]);
			$passwordSecond = trim($_POST["form"]["password_second"]);
			
			if ( !empty($password) && !empty($passwordSecond) )
			{
				if ( $password === $passwordSecond )
				{
					wp_set_password($password, get_current_user_id());
					wp_logout();
					wp_redirect(network_home_url()); exit;
				}
				else
				{
					$passwordErrors["different"] = true;
				}
			}
		}
		
		get_header();
		
		include("account.php");
	}
	else
	{
			get_header();
		?>
		<div id="primary" class="site-content">
		<div id="content" role="main">

			<article id="post-0" class="post error404 no-results not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'This is somewhat embarrassing, isn&rsquo;t it?', 'twentytwelve' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'twentytwelve' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->
			

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>
<?php } /*}*/?>