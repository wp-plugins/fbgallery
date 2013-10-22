<?php
/**
 * The FB Gallery Photos Thumbnail Widget Class
 * 
 * 
 */
 
class WP_FBG_Thumbnail_Widget extends WP_Widget {
	function __construct() {
		parent::__construct( 'fbg_thumbnail_widget', $name = __( 'FB Gallery Photos', 'fbg' ) );
	}

	function widget($args, $instance) {
		global $bp;
		extract( $args );
		echo $before_widget;
		echo $before_title
		   . $instance['title'] ;
		echo    $after_title;
		  ?>
		<div id="fbg-photos-thumb-widget">
 	</div>

	<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max_items'] = strip_tags( $new_instance['max_items'] );
 		$instance['height'] = strip_tags( $new_instance['height'] );
 		$instance['width'] = strip_tags( $new_instance['width'] );
		$instance['style'] = strip_tags( $new_instance['style'] );
		$instance['mode'] = strip_tags( $new_instance['mode'] );
                  
		// This is only here so that the options can be easily accessed from the ajax handler
		update_option('fbg_photos_thumb_widget', $instance);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title'=>__('Recent Photos','swa'),'max_items' => 16,'use-cache'=>true , 'height' => 80, 'width'=>70,'mode'=>'recent') );
		$max_items = strip_tags( $instance['max_items'] );
		$title = strip_tags( $instance['title'] );
		$height = strip_tags( $instance['height'] );
		$width = strip_tags( $instance['width'] );
//		$style = strip_tags( $instance['style'] );
		$mode = strip_tags( $instance['mode'] );
                extract($instance);
              
		?>
            <div class="fbg-widget-block">
              <p><label for="fbg-photo-thumb-title"><strong><?php _e('Title:', 'fbg'); ?> </strong><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>
							<p><label for="fbg-photo-thumb-max"><?php _e('Number of photos', 'fbg'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_items' ); ?>" name="<?php echo $this->get_field_name( 'max_items' ); ?>" type="text" value="<?php echo esc_attr( $max_items ); ?>" style="width: 30%" /></label></p>
							<p><label for="fbg-photo-thumb-height"><?php _e('Thumbnail Height:', 'fbg'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>" style="width: 30%" /></label></p>
							<p><label for="fbg-photo-thumb-width"><?php _e('Thumbnail Width:', 'fbg'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>" style="width: 30%" /></label></p>
							<p><?php echo __('Mode:'); ?>
								<label><input type="radio" name="fbg-photos-thumb-mode" value="recent" <?php echo $mode == 'recent' ? 'checked ' : '' ?>/> Recent Photos</label>
								<label><input type="radio" name="fbg-photos-thumb-mode" value="random" <?php echo $mode == 'random' ? 'checked ' : '' ?>/> Random Photos</label>
							</p>
            </div>  
	<?php
	}
}
?>