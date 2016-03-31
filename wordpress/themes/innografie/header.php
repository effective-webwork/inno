<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width" />
    <title>
        <?php
        	$request = $wp->request;
        	if ( !is_main_site() && is_numeric($request) )
        	{
        		?>Umfrage<?php
        	}
        	else if ( $request === 'account' )
        	{
        		?>Account<?php
        	}
        	else
        	{
        		wp_title( '|', true, 'right' );
        	}
        ?>
    </title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
    <!--[if lt IE 9]>
    <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
    <![endif]-->
        
    <link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/dojo/1.8.3/dijit/themes/tundra/tundra.css">
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/pure-min.css">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class("tundra"); ?>>
<div id="page" class="hfeed"> <!-- css class removed: site -->
	<div class="wrapper">
        <div id="header">
            
            <?php $header_image = get_header_image();
			if ( ! empty( $header_image ) ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
			<?php endif; ?>
            
            
            <?php
            	/* switch to main blog temporary to display navigation */
            	$mainBlog = is_main_site();
            	switch_to_blog(1);
            	$nav_content = wp_nav_menu( array(	'theme_location'	=> 'primary',
				            						'container'			=> false,
													'menu_class'		=> 'main_navigation',
				            						'menu_id'			=> 'main_navigation',
				            						'echo'				=> false ) );

				if (!$mainBlog) {
					$nav_content = preg_replace('/<\!--:(?!de)-->(.*)<\!--:-->/U', '', $nav_content);
				}				

				echo $nav_content;

				// switch back
				restore_current_blog();
				?>
            
            <div id="language" style="float:right; margin:20px 0px 20px 0px;"><?php if (function_exists('qtrans_generateLanguageSelectCode')) {qtrans_generateLanguageSelectCode('image');} ?></div>
            <div class="clear"></div>
            
            <div class="clear"> </div>
        </div>
    </div>

	<div class="wrapper">