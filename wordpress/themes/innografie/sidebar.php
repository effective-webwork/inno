<?php
/**
 * The sidebar containing the main widget area.
 *
 * If no active widgets in sidebar, let's hide it completely.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<div id="right_column">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	
		<div id="secondary" class="widget-area" role="complementary">
			
		</div><!-- #secondary -->
	<?php endif; ?>