<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="columnset">
		<?php printf("%s", fbpsych_get_rotator_image()); ?>
	
		<div id="left_column">
		
			<div id="maincontent">
				<?php printf("%s", fbpsych_get_step_image()); ?>
				
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'page' ); ?>
					<?php comments_template( '', true ); ?>
				<?php endwhile; // end of the loop. ?>
			</div>
	
		</div>
		
		<?php get_sidebar(); ?>
	
		<div class="clear"></div>
	</div>
	
	<?php get_footer(); ?>