<?php
	namespace fbpsych;
	
	class loginWidget extends \WP_Widget
	{
		function __construct()
		{
			parent::__construct("fbpsych_login", "Login Widget");
		}
		
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
		
			echo $before_widget;
			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;
			//echo __( 'Hello, World!', 'text_domain' );
			
			?>
			<?php if ( is_user_logged_in() ) :?>
				
				<?php if ( current_user_can("manage_network") ) :?>
					<a href="wp-admin/">Administration</a><br/>
				<?php else: ?>
					<?php
						$userBlogs = get_blogs_of_user(get_current_user_id());
						if ( sizeof($userBlogs) === 1) :
							$userBlog = reset($userBlogs);
					?>
						<div id="contact_portlet">
							Willkommen im Umfrage-Tool<br/>
								<?php $current_user = wp_get_current_user(); ?>
								<a href="<?php echo $userBlog->path . "account/"; ?>"><?php echo $current_user->display_name; ?></a>
							
							<?php
										$company = get_blog_option($userBlog->userblog_id, "cimy_uef_COMPANY");
										if ( isset($company) && !empty($company) ) :
									?>
										<?php echo("(" . $company . ")"); ?>
									<?php endif; ?>
					<?php
						if ( get_current_blog_id() !== $userBlog->userblog_id ) :
					?>
								
									
									<br/><br/>
									<a href="<?php echo $userBlog->path; ?>"><h2>Tool starten</h2></a>
								
							<?php endif; ?>
						<?php endif; ?>
						</div>
						<br/>
				<?php endif; ?>
				
				<a href="<?php echo wp_logout_url(home_url()); ?>" title="Logout"><?php _e("FBP_LOGOUT", "twentytwelve"); ?></a>
				
			<?php else: ?>
				<div id="contact_portlet">
					<?php _e("FBP_REGISTER_MAIN", "twentytwelve"); ?>
					<div id="widget_register"><a href="wp-login.php?action=register"><?php _e("FBP_REGISTER", "twentytwelve"); ?></a></div>
					<br/>
					<?php _e("FBP_LOGIN_MAIN", "twentytwelve"); ?>
					<div id="widget_login"><a href="wp-login.php"><?php _e("FBP_LOGIN", "twentytwelve"); ?></a></div>
			<?php endif; ?>
			
			<?php
			
			echo $after_widget;
		}
		
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance )
		{
			if ( isset( $instance[ 'title' ] ) ) {
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'New title', 'text_domain' );
			}
			?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php 
		}
	}