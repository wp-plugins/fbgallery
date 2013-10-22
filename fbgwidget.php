<?php

//--------------------//
//--WIDGET-FUNCTIONS--//
//--------------------//

function fb_widget_init() {
	if (!function_exists('register_sidebar_widget'))
		return;

  add_action( 'bp_include', array($this,'include_files' ));
  add_action( 'bp_loaded', array($this,'register_widgets' ));

//	register_sidebar_widget(array('FBGallery Photos', 'widgets'), 'fbg_photos_widget');
//	register_widget_control(array('FBGallery Photos', 'widgets'), 'fbg_photos_widget_control', 300, 150);
	register_sidebar_widget(array('FBGallery Albums', 'widgets'), 'fbg_albums_widget');
	register_widget_control(array('FBGallery Albums', 'widgets'), 'fbg_albums_widget_control', 300, 150);

}

function fbg_photos_widget($count = '4', $mode = 'random', $size = '80', $photoWidth = '70') {

	// this is a widget
	if(is_array($count)) {
		extract($count);

		$options = get_option('fbg_photos_widget');
		if(is_array($options)) {
			$title = $options['title'];
			$count = $options['count'];
			$size	= $options['size'];
			$mode	= $options['mode'];
		}

		echo $before_widget . $before_title . $title . $after_title;
	}

	if($mode == 'recent') {
		$photos = fb_get_recent_photos($count);
	} else {
		$photos = fb_get_random_photos($count);
	}

	// if the thumbnail size is set larger than the size of
	// the thumbnail, use the full size photo
	if($size > 130) {
		foreach($photos as $key=>$photo)
			$photos[$key]['src'] = $photos[$key]['src_big'];
	}

	if($photos) {
		include(FB_PLUGIN_PATH.'styles/photos-widget.php');
	} else {
		echo '<p>There are no photos.</p>';
	}

	echo $after_widget;
}
/*
*	fbg_photos_web
* Retrieves $count photos for either the widget slider or the content slider from the database table
* All photos are sourced directly from facebook.
*/
function fbg_photos_web($count = '24', $mode = 'random', $width=300) {

	if($mode == 'recent') {
		$photos = fb_get_recent_photos($count);
	} else {
		$photos = fb_get_random_photos($count);
	}

	// if the thumbnail size is set larger than the size of
	// the thumbnail, use the full size photo
	if($width > 130) {
		foreach($photos as $key=>$photo)
			$photos[$key]['src'] = $photos[$key]['src_big'];
	}
	
	if($photos) {
		
		for($i = 0; $i < count($photos); $i++)
		{
			$locAlbum = fb_get_album($photos[$i]['aid']);
			if(count($locAlbum > 0))
			{
				$photos[$i]['caption'] = htmlspecialchars($locAlbum['name']);
			}
		}
		return $photos;
	} 
}
/*
*	fbg_photos_slider
* Function can be called from a template page to display a slide show of recent images
*/
function fbg_photos_slider( $photoHeight = '200', $photoWidth = '300') {
	// this is a widget
		include(FB_PLUGIN_PATH.'styles/photos-slide.php');

	echo $after_widget;
}

/*
*	fb_photos_slide_ajax
* Server end function to handle the ajax call to retrieve image data for either the widget or the content slider
* Images can be retrieved from a cache or directly from facebook.
*/
function fb_photos_slide_ajax()
{

	$nonce = $_GET['FBGPhotoSlideNonce'];
	if(isset($_GET['width']))
	{
		$photoWidth = $_GET['width'];
	}
	else
	{
		$photoWidth = 300;
	}
	if(isset($_GET['height']))
	{
		$photoHeight = $_GET['height'];
	}
	else
	{
		$photoHeight = 200;
	}
	
	$callback = $_GET['callback'];
	$source = $_GET['source'];
		// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
//	if ( !wp_verify_nonce( $nonce, 'FBGPhotoSlide' ) )
//	{
//	fb_logdebug('fb_photos_slide_ajax : nonce not verified  : '.$nonce. ' : wp_nonce : '. wp_create_nonce( 'FBGPhotoSlide' ));
//			return;
//	}
//	else
//	{
		$options = get_option('fbgallery_settings_section');
		if($source == 'widget')
		{
			$widgetOptions = get_option('fbg_photos_slide_widget');
			$maxCount = $widgetOptions['max_items'];
		}
		else
		{
			$maxCount = $options['fb_num_to_cache'];
		}
		if($options['fb_use_cache'] == 'useCache')
		{
			$uploadDir = wp_upload_dir();
			$fbDir = $uploadDir['basedir'].'/fbgallery';
			$fb_recent = file_get_contents($fbDir.'/fb_recent.json', json_encode($photos));
			$imageCount = 0;
			if($fb_recent !== false)
			{
				$photos = json_decode($fb_recent);
			}
			else
			{
				exit();
			}
		}
		else
		{
			$photos = fbg_photos_web($maxCount,'recent',$photoWidth);
			if(!$photos)
			{
				exit();
			}
		}
		$photoCount = Count($photos);

		for($i=0; $i< $photoCount; $i++)
		{
			$photo = $photos[$i];
			
			if($options['fb_use_cache'] == 'useCache')
			{
				list($width, $height, $type, $attr)= getimagesize($photo->srcpath);
			}
			else
			{
				list($width, $height, $type, $attr)= getimagesize($photo['src']);
			}
			// Need to adjust the image width for the window it will be displayed in
			if($width < $photoWidth)
			{
				continue;
			}
			fb_logdebug('fb_photos_slide_ajax : height : '.$height.' t: photoheigh : '.$photoHeight);
			if($height > $width)
			{
				continue;
			}
			$imageCount++;
			
			if($imageCount > $maxCount)
			{
					break;
			}
			if($width>$photoWidth)
			{ 
	    	$diff = $width-$photoWidth; 
	    	$percnt_reduced = (($diff/$width)*100); 
	 	  	$newHeight = round($height-(($percnt_reduced*$height)/100)); 
	 	  	if($newHeight > $photoHeight)
	    	{
	    		$newHeight = $photoHeight;
	 	  	} 
	 	  }
			if($options['fb_use_cache'] == 'useCache')
			{
				$photoArray[] = array('link'=>$photo->link,'src'=>$photo->src,'caption'=>$photo->caption,'width'=>$photoWidth, 'height'=>$newHeight);
			}
			else
			{
				$photoArray[] = array('link'=>$photo['link'],'src'=>$photo['src'],'caption'=>$photo['caption'],'width'=>$photoWidth, 'height'=>$newHeight);
			}
		} 
		echo $callback.'('.json_encode($photoArray).');';
//	}
}
add_action( 'wp_ajax_FBGPhotoSlide', 'fb_photos_slide_ajax' );
add_action('wp_ajax_nopriv_FBGPhotoSlide', 'fb_photos_slide_ajax');

/*
*	fb_photos_thumb_ajax
* Server end function to handle the ajax call to retrieve image data for either the photo widget
* Images can be retrieved from a cache or directly from facebook.
*/
function fb_photos_thumb_ajax()
{

	$nonce = $_GET['FBGPhotoThumbNonce'];
	if(isset($_GET['width']))
	{
		$photoWidth = $_GET['width'];
	}
	else
	{
		$photoWidth = 80;
	}
	if(isset($_GET['height']))
	{
		$photoHeight = $_GET['height'];
	}
	else
	{
		$photoHeight = 80;
	}
	
	$callback = $_GET['callback'];
	$source = $_GET['source'];
		// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
//	if ( !wp_verify_nonce( $nonce, 'FBGPhotoSlide' ) )
//	{
//	fb_logdebug('fb_photos_slide_ajax : nonce not verified  : '.$nonce. ' : wp_nonce : '. wp_create_nonce( 'FBGPhotoSlide' ));
//			return;
//	}
//	else
//	{
		$options = get_option('fbgallery_settings_section');
		$widgetOptions = get_option('fbg_photos_thumb_widget');
		$maxCount = $widgetOptions['max_items'];
		$mode = $widgetOptions['mode'];
		$width = $widgetOptions['width'];
		if($options['fb_use_cache'] == 'useCache')
		{
			$uploadDir = wp_upload_dir();
			$fbDir = $uploadDir['basedir'].'/fbgallery';
			$fb_recent = file_get_contents($fbDir.'/fb_recent.json', json_encode($photos));
			$imageCount = 0;
			if($fb_recent !== false)
			{
				$photos = json_decode($fb_recent);
				if($width < 130)
				{
					foreach( $photos as $photo)
					{
						$photo->src = $photo->src_small;
						$photo->srcpath = $photo->srcpath_small;
					}
				}
			}
			else
			{
				exit();
			}
		}
		else
		{
			$photos = fbg_photos_web($maxCount,$mode,$width);
			if(!$photos)
			{
				exit();
			}
		}
		$photoCount = Count($photos);

		for($i=0; $i< $photoCount; $i++)
		{
			$photo = $photos[$i];
			
			if($options['fb_use_cache'] == 'useCache')
			{				
				list($width, $height, $type, $attr)= getimagesize($photo->srcpath);
			}
			else
			{
				list($width, $height, $type, $attr)= getimagesize($photo['src']);
			}
			$imageCount++;
			
			if($imageCount > $maxCount)
			{
					break;
			}
			if($options['fb_use_cache'] == 'useCache')
			{
				$photoArray[] = array('link'=>$photo->link,'src'=>$photo->src,'caption'=>$photo->caption,'width'=>$width, 'height'=>$height);
			}
			else
			{
				$photoArray[] = array('link'=>$photo['link'],'src'=>$photo['src'],'caption'=>$photo['caption'],'width'=>$width, 'height'=>$height);
			}
		} 
		echo $callback.'('.json_encode($photoArray).');';
//	}
}
add_action( 'wp_ajax_FBGPhotoThumb', 'fb_photos_thumb_ajax' );
add_action('wp_ajax_nopriv_FBGPhotoThumb', 'fb_photos_thumb_ajax');


function fbg_photos_widget_control() {
	$options = get_option('fbg_photos_widget');
	if (!is_array($options) )
		$options = array('title'=>'Random Photos', 'count'=>'4', 'style'=>'list','size'=>'80','mode'=>'random');
	if ( $_POST['fb-photos-submit'] ) {
		$options['title'] = strip_tags(stripslashes($_POST['fb-photos-title']));
		$options['count'] = is_numeric($_POST['fb-photos-count']) ? $_POST['fb-photos-count'] : 4;
		$options['style'] = $_POST['fb-photos-style'];
		$options['mode'] = $_POST['fb-photos-mode'];
		$options['size']	= is_numeric($_POST['fb-photos-size']) ? $_POST['fb-photos-size'] : 60;
		update_option('fbg_photos_widget', $options);
	}
	$options['title'] = htmlspecialchars($options['title'], ENT_QUOTES);

	?>
	<p><label for="fb-title"><?php echo __('Title:'); ?>
		<input style="width: 200px;" id="fb-photos-title" name="fb-photos-title" type="text" value="<?php echo $options['title'] ?>" />
	</label></p>
	<p><label for="fb-count"><?php echo __('Number of Pictures:'); ?>
		<input style="width: 80px;" id="fb-photos-count" name="fb-photos-count" type="text" value="<?php echo $options['count'] ?>" />
	</label></p>
	<p><label for="fb-size"><?php echo __('Thumbnail Size:'); ?>
		<input style="width: 80px;" id="fb-photos-size" name="fb-photos-size" type="text" value="<?php echo $options['size'] ?>" />
	</label></p>
	<p><?php echo __('Mode:'); ?>
		<label><input type="radio" name="fb-photos-mode" value="recent" <?php echo $options['mode'] == 'recent' ? 'checked ' : '' ?>/> Recent Photos</label>
		<label><input type="radio" name="fb-photos-mode" value="random" <?php echo $options['mode'] == 'random' ? 'checked ' : '' ?>/> Random Photos</label>
	</p>
	<input type="hidden" name="fb-photos-submit" value="1" />
	<?php
}

function fbg_albums_widget($count = '4', $mode = 'recent') {
	global $wpdb;

	if(is_array($count)) {
		extract($count);

		$options = get_option('fbg_albums_widget');
		if(is_array($options)) {
			$title = $options['title'];
			$mode	= $options['mode'];
			$count = $options['count'];
		}

		echo $before_widget . $before_title . $title . $after_title;
	}

	$count = (int) $count;

	if($mode == 'recent') {
		$albums = $wpdb->get_results('SELECT `name`, `aid`, `page_id` FROM `'.FB_ALBUM_TABLE.'` WHERE `hidden` = 0 ORDER BY `modified` DESC LIMIT '.$count, ARRAY_A);
	} else {
		$albums = $wpdb->get_results('SELECT `name`, `aid`, `page_id` FROM `'.FB_ALBUM_TABLE.'` WHERE `hidden` = 0 ORDER BY rand() LIMIT '.$count, ARRAY_A);
	}

	if($albums) {
		include(FB_PLUGIN_PATH.'styles/albums-widget.php');
	} else {
		echo '<p>There are no albums.</p>';
	}

	echo $after_widget;
}

function fbg_albums_widget_control() {
	$options = get_option('fbg_albums_widget');
	if (!is_array($options) )
		$options = array('title'=>'Recent Albums', 'mode'=>'recent', 'count'=>'4');
	if ( $_POST['fb-albums-submit'] ) {
		$options['title'] = strip_tags(stripslashes($_POST['fb-albums-title']));
		$options['count'] = is_numeric($_POST['fb-albums-count']) ? $_POST['fb-albums-count'] : 4;
		$options['mode'] = $_POST['fb-albums-mode'];
		update_option('fbg_albums_widget', $options);
	}
	$options['title'] = htmlspecialchars($options['title'], ENT_QUOTES);

	?>
	<p><label for="fb-title"><?php echo __('Title:'); ?>
		<input style="width: 200px;" id="fb-albums-title" name="fb-albums-title" type="text" value="<?php echo $options['title'] ?>" />
	</label></p>
	<p><label for="fb-count"><?php echo __('Number of Albums:'); ?>
		<input style="width: 80px;" id="fb-albums-count" name="fb-albums-count" type="text" value="<?php echo $options['count'] ?>" />
	</label></p>
	<p><?php echo __('Mode:'); ?>
		<label><input type="radio" name="fb-albums-mode" value="recent" <?php echo $options['mode'] == 'recent' ? 'checked ' : '' ?>/> Recent Albums</label>
		<label><input type="radio" name="fb-albums-mode" value="random" <?php echo $options['mode'] == 'random' ? 'checked ' : '' ?>/> Random Albums</label>
	</p>
	<input type="hidden" name="fb-albums-submit" value="1" />
	<?php
}
function fb_GetLastAlbumUpDate() {
	global $wpdb;
	$highest = $wpdb->get_var('SELECT MAX(`modified`) FROM `'.FB_ALBUM_TABLE.'`');
	return ($highest);
}
function fb_GetLastAlbumDate() {
	global $wpdb;
	$highest = $wpdb->get_var('SELECT MAX(`created`) FROM `'.FB_ALBUM_TABLE.'`');
	return ($highest);
}
function fb_GetFirstAlbumDate() {
	global $wpdb;
	$first = $wpdb->get_var('SELECT MIN(`created`) FROM `'.FB_ALBUM_TABLE.'`');
	return ($first);
}

function fb_StrToTime($dateTimeStr)
{
    $datetimeArray = strptime($dateTimeStr, "%Y-%m-%d %H:%M:%S");
			fb_logdebug('fbphotos:  fb_StrToTime : '.print_r($datetimeArray,true));

     // Generate the UNIX time. Note that this will be in the wrong timezone.
    $time = mktime($datetimeArray['tm_hour'],
 		$datetimeArray['tm_min'],
 		$datetimeArray['tm_sec'],
 		$datetimeArray['tm_mon'] + 1,
		$datetimeArray['tm_mday'] ,
 		$datetimeArray['tm_year'] + 1900);
		return $time;
}
?>