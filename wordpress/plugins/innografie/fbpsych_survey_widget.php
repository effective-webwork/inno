<?php
	namespace fbpsych;
	
	class surveyWidget extends \WP_Widget
	{
		function __construct()
		{
			parent::__construct("fbpsych_survey", "Umfrage Widget");
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
			if ( !is_main_site() )
			{
				extract( $args );
				$title = apply_filters( 'widget_title', $instance['title'] );
			
				echo $before_widget;
				if ( ! empty( $title ) )
					echo $before_title . $title . $after_title;
				
				?><a href="<?php printf("%s", get_blog_details(get_current_blog_id())->siteurl); ?>">Eine neue Befragung anlegen</a><br/><br/><?php
				
				$surveys = get_blog_option(get_current_blog_id(), "fbpsych_project_surveys");
				
				$surveyCount = sizeof($surveys);
				if ( !$surveys || $surveyCount === 0)
				{
					?>Bisher wurden keine Befragungen angelegt...<?php
				}
				else
				{
					?>Bisher wurde<?php if ( $surveyCount > 1) printf("%s", "n"); ?> <?php printf("%d", $surveyCount); ?> Befragung<?php if ( $surveyCount > 1) printf("%s", "en"); ?> angelegt<?php
					
					?><ul id="projectSurveys"><?php
					$surveys = array_reverse($surveys);
					foreach ( $surveys as $survey )
					{
						// skip deleted
						if (isset($survey['deleted']) && $survey['deleted'] == true) {
							continue;
						}

						$date = new \DateTime($survey["creationDate"]);
						
						$percentage = fbpsych_get_percentage($survey["surveyId"]);
						$percentageWidth = $percentage * 240 / 100;
						?>
							<li>
							   <?php
							      //print_r($survey);
							      if (empty($survey["surveyName"])) {
   							      $widgetTitle = 'Fragebogen vom '.$date->format("d.m.Y");
							      } else {
   							      $widgetTitle = $survey["surveyName"];
							      }
							   ?>
								<!-- <a href="<?php printf("%s/%s", get_blog_details(get_current_blog_id())->siteurl, $survey["surveyId"]); ?>"><?php printf("%s: %s", $date->format("d.m.Y"), $survey["title"]); ?></a> -->
								<a href="<?php printf("%s/%s", get_blog_details(get_current_blog_id())->siteurl, $survey["surveyId"]); ?>" title="erstellt am <?php echo $date->format("d.m.Y"); ?>"><?php echo $widgetTitle; ?></a>
								<div class="progressBar">
									<div class="progress" style="width: <?php echo $percentageWidth ?>px"></div>
									<div class="progressValue"><?php printf("%d", $percentage); ?>%</div>
								</div>
							</li>
						<?php
					}
					?></ul><?php
				}
				
				echo $after_widget;
			}
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