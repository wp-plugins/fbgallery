<?php
/**
 * The FB Gallery Photos Widget Class
 * 
 * 
 */
 
class WP_FBG_Photos_Widget extends WP_Widget {
	function __construct() {
		parent::__construct( 'fbg_photos_widget', $name = __( 'FB Gallery Slideshow', 'fbg' ) );
	}

	function widget($args, $instance) {
		global $bp;
		extract( $args );
		echo $before_widget;
		echo $before_title
		   . $instance['title'] ;
		echo    $after_title;
		  ?>
		<div id="fbg-photos-travel-widget">
		  <div class="fbg-widget-slide" id="fbg-widget-slide" style="height: <?php echo $instance['height'] ?>px; display: block" >

			</div>
		</div>

	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );
 		$instance['height'] = strip_tags( $new_instance['height'] );
                  
		// This is only here so that the options can be easily accessed from the ajax handler
		update_option('fbg_photos_slide_widget', $instance);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title'=>__('Recent Photos','swa'),'max_items' => 24,'use-cache'=>true , 'height' => 200) );
		$max_items = strip_tags( $instance['max_items'] );
		$title = strip_tags( $instance['title'] );
		$height = strip_tags( $instance['height'] );
                extract($instance);
              
		?>
            <div class="fbg-widget-block">
              <p><label for="fbg-photo-title"><strong><?php _e('Title:', 'fbg'); ?> </strong><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>
							<p><label for="fbg-photo-max"><?php _e('Number of photos is slideshow:', 'fbg'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
							<p><label for="fbg-photo-height"><?php _e('Widget height:', 'fbg'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>" style="width: 30%" /></label></p>
            </div>  
	<?php
	}
}
?>