<?php
//---------------------//
//--DISPLAY-FUNCTIONS--//
//---------------------//

function fb_display($content) {
	global $wpdb;

	// get variables to check if this is part of fotobook
	$post           = $GLOBALS['post'];
	$post_id        = $post->ID;
	$post_parent    = $post->post_parent;
	$options = get_option('fbgallery_settings_section');
	$albums_page_id = $options['fb_albums_page'];

	// don't show password protected pages
	if (!empty($post->post_password) && $_COOKIE['wp-postpass_'. COOKIEHASH] != $post->post_password) {
		return $content;
	}


	
	if($post_id != $albums_page_id && $post_parent != $albums_page_id) {
		return $content;
	}


	// display all albums
	if($post_id == $albums_page_id) {
		return fb_display_main($content);
	}

	// display individual albums
	if($post_parent == $albums_page_id && $post_parent != 0) {
		if(isset($_GET['photo']) && get_option('fb_style') == 'embedded') {
			return fb_display_photo($content, $post_id, $_GET['photo']);
		} else {
			return fb_display_album($content, $post_id);
		}
	}

	return $content;
}

function fb_display_main($content) {
	remove_filter('the_content','wpautop');

	// buffer the output
	ob_start();

	// get albums
	$albums = fb_get_album(null, null, true);
	if(!$albums) {
		echo "<p>There are no albums.</p>";
		return;
	}
	$curMonth = date('n');
	$curYear = date('Y');
	$firstTime = true;
	if(isset($_GET['album_m']))
	{
		$curMonth = $_GET['album_m'];
		$firstTime = false;
	}
	if(isset($_GET['album_y']))
	{
		$curYear = $_GET['album_y'];
	}
	$albums = fb_get_album_by_month($curMonth,$curYear);
	if(!$albums) {
		if (!$firstTime)
		{
			echo "<p>There are no albums.</p>";
			return;
		}
		else
		{
			$latestDateStr =  fb_GetLastAlbumDate();
			$latestDateTime = fb_StrToTime($latestDateStr); 
			$curMonth = date('n',$latestDateTime);
			$curYear = date('Y',$latestDateTime);
			$albums = fb_get_album_by_month($curMonth,$curYear);
		}
	}
	$album_count = sizeof($albums);
	$options = get_option('fbgallery_settings_section');
	$album_link = get_permalink($options['fb_albums_page']);
	array_unshift($albums, ''); // moves all the keys down
	unset($albums[0]);

	// determine pagination
	$albums_per_page = get_option('fb_albums_per_page');
	if($albums_per_page == 0) {
		$albums_per_page = $album_count;
	}
	$page_count = ceil($album_count / $albums_per_page);
	$curr_page = $_GET['album_p'] <= $page_count && $_GET['album_p'] > 0 ? $_GET['album_p'] : 1;
	$first_album = (($curr_page-1) * $albums_per_page) + 1;
	$last_album = $first_album + $albums_per_page - 1;
	$last_album = $last_album > $album_count ? $album_count : $last_album;

	// generate pagination
	if($page_count == 1) {
		$prev_link = ''; $next_link = ''; $pagination = '&nbsp;';
	} else {
		$prev_link = $curr_page > 1 ? $curr_page - 1 : false;
		if($prev_link !== false)
			$prev_link = $album_link.(strstr($album_link, '?') ? '&amp;album_p='.($prev_link) : '?album_p='.($prev_link));
		$next_link = $curr_page + 1 <= $page_count ? $curr_page + 1 : false;
		if($next_link)
			$next_link = $album_link.(strstr($album_link, '?') ? '&amp;album_p='.($next_link) : '?album_p='.($next_link));
		$pagination = '';
		for($i = 1; $i <= $page_count; $i++) {
			if($i == $curr_page)
				$pagination .= '<b>'.$i.'</b>';
			else {
				$link = $album_link.(strstr($album_link, '?') ? '&amp;album_p='.$i : '?album_p='.$i);
				$link.= '&amp;album_m='.$curMonth.'&amp;album_y='.$curYear;
				$pagination .= "<a href='$link'>".($i)."</a>";
			}
		}
	}
				$optionLine = '';
				$selectedYear = $curYear;
			for($i = 1; $i <= 12; $i++)
			{
				if($curMonth == $i)
				{
					$optionLine .=  '<option value="'.$album_link.'?album_m='.$i.'&amp;album_y='.$selectedYear.'" selected="selected">'.GetMonthStr($i).' ('.fb_count_albums_by_month($i,$selectedYear).')</option>';
				}
				else
				{
					$optionLine .= '<option value="'.$album_link.'?album_m='.$i.'&amp;album_y='.$selectedYear.'">'.GetMonthStr($i).' ('.fb_count_albums_by_month($i,$selectedYear).')</option>';
				}
	//			echo $optionLine."\n";
			}
			$firstDateStr = fb_GetFirstAlbumDate();
			$firstDateTime = fb_StrToTime($firstDateStr); 
			$startYear = date('Y',$firstDateTime);
			for($i=$startYear; $i<= date('Y'); $i++)
			{
				if($curYear == $i)
				{
					$yearOptionLine .=  '<option value="'.$album_link.'?album_m=1&amp;album_y='.$i.'" selected="selected">'.$i.' ('.fb_count_albums_by_year($i).')</option>';
				}
				else
				{
					$yearOptionLine .= '<option value="'.$album_link.'?album_m=1&amp;album_y='.$i.'">'.$i.' ('.fb_count_albums_by_year($i).')</option>';
				}
			}

	// now get rid of all albums in the array that aren't displayed on this page
	$albums = array_slice_preserve_keys($albums, $first_album-1, $albums_per_page);
	foreach($albums as $key=>$album) {
		$albums[$key]['link']	= get_permalink($albums[$key]['page_id']);
		$albums[$key]['thumb'] = fb_get_photo($albums[$key]['cover_pid'], 'thumb');
	}
fb_logdebug('fb_display_main : style path : '.FB_STYLE_PATH);
	include(FB_STYLE_PATH.'album-main.php');
	?>
<?php
	// now capture the buffer and add it to $content
	$content .= ob_get_clean();
	return $content;
}

function GetMonthStr($monthNum)
{
	switch($monthNum)
	{
		case 1: return "Jan";
						break;
		case 2: return "Feb";
						break;
		case 3: return "Mar";
						break;
		case 4: return "Apr";
						break;
		case 5: return "May";
						break;
		case 6: return "Jun";
						break;
		case 7: return "Jul";
						break;
		case 8: return "Aug";
						break;
		case 9: return "Sep";
						break;
		case 10: return "Oct";
						break;
		case 11: return "Nov";
						break;
		case 12: return "Dec";
						break;
		default : return "Jan";
						break;
	}
}

function fb_display_album($content, $page_id) {
	// turn off content filter so that <p> and <br> tags aren't added
	remove_filter('the_content','wpautop');

	// buffer the output
	ob_start();

	$options = get_option('fbgallery_settings_section');
	$albums_page_link = htmlentities(get_permalink($options['fb_albums_page']));
	$page_link = get_permalink($page_id);
	$album_id = fb_get_album_id($page_id);
	$album = fb_get_album($album_id);
	$photos = fb_get_photos($album_id);
	$photo_count = sizeof($photos);
	if($photo_count == 0) {
		echo '<p>This album is empty.</p>';
		return false;
	}
	array_unshift($photos, ''); // moves all the keys down
	unset($photos[0]);

	// check if page is hidden
	if($album['hidden'] == 1) {
		$message = '<p>This album is not available. <a href="'.get_permalink($options['fb_albums_page']).'">Return to albums</a>.</p>';
		return $message.$content;
	}

	// html encode all captions
	foreach($photos as $key=>$photo) {
		$photos[$key]['caption'] = function_exists('seems_utf8') && seems_utf8($photo['caption'])
															 ? htmlentities($photo['caption'], ENT_QUOTES, 'utf-8')
															 : htmlentities($photo['caption'], ENT_QUOTES);
	}

	$thumb_size = $options['fb_thumb_size'];
	$number_cols = $options['fb_number_cols'];
	$number_rows = $options['fb_number_rows'] == 0 ? ceil($photo_count / $number_cols) : $options['fb_number_rows'];
	$photos_per_page = $number_cols * $number_rows;

	$page_count = ceil($photo_count / $photos_per_page);
	$curr_page = ($_GET['album_p'] <= $page_count) && ($_GET['album_p'] > 0) ? $_GET['album_p'] : 1;
	$first_photo = ($curr_page - 1) * $photos_per_page + 1;
	$last_photo = $first_photo + $photos_per_page - 1;
	$last_photo = $last_photo > $photo_count ? $photo_count : $last_photo;
	$rows_curr_page = ceil(($last_photo - $first_photo + 1) / $number_cols);

	// generate pagination
	if($page_count == 1) {
		$prev_link = ''; $next_link = ''; $pagination = '&nbsp;';
	} else {
		$prev_link = $curr_page > 1 ? $curr_page - 1 : false;
		if($prev_link !== false)
			$prev_link = $page_link.(strstr($page_link, '?') ? '&amp;album_p='.($prev_link) : '?album_p='.($prev_link));
		$next_link = $curr_page < $page_count ? $curr_page + 1 : null;
		if($next_link)
			$next_link = $page_link.(strstr($page_link, '?') ? '&amp;album_p='.($next_link) : '?album_p='.($next_link));
		$pagination = '';
		for($i = 1; $i <= $page_count; $i++) {
			if($i == $curr_page)
				$pagination .= '<b>'.$i.'</b>';
			else {
				$link = $page_link.(strstr($page_link, '?') ? '&amp;album_p='.$i : '?album_p='.$i);
				$pagination .= "<a href='$link'>".($i)."</a>";
			}
		}
	}

	// album info
	$description = $album['description'];
	$location = $album['location'];

	// add hidden links for all images before so that next and previous
	// buttons in lightbox will display these images as well
	$hidden_top = ''; $hidden_bottom = '';
	for($i = 1; $i < $first_photo; $i++) {
		$hidden_top .= "<a href=\"{$photos[$i]['src_big']}\" rel=\"fotobook\" title=\"{$photos[$i]['caption']}\"></a>";
	}
	for($i = $last_photo+1; $i <= $photo_count; $i++) {
		$hidden_bottom .= "<a href=\"{$photos[$i]['src_big']}\" rel=\"fotobook\" title=\"{$photos[$i]['caption']}\"></a>";
	}

	// now get rid of all photos in the array that aren't displayed on this page
	$photos = array_slice_preserve_keys($photos, $first_photo-1, $photos_per_page);

	?>
	<br />
	<p style="display: none"><?php echo $hidden_top ?></p>
	<?php include(FB_STYLE_PATH.'album.php') ?>
	<p style="display: none"><?php echo $hidden_bottom ?></p>
<?php
	$content .= ob_get_clean();
	return $content;
}

function fb_display_photo($content, $page_id, $photo) {
	// turn off content filter so that <p> and <br> tags aren't added
	remove_filter('the_content','wpautop');

	// buffer the output
	ob_start();

	// get photos
	$photos = fb_get_photos(fb_get_album_id($page_id));
	$photo_count = sizeof($photos);
	array_unshift($photos, ''); // moves all the keys down
	unset($photos[0]);

	// pagination
	$page_link = get_permalink($page_id);
	$curr = ($photo <= $photo_count && $photo > 0) ? $photo : 1;
	$next = ($curr + 1 <= $photo_count) ? $curr + 1 : false;
	$prev = ($curr != 1) ? $curr - 1 : false;
	if($next)
		$next_link = $page_link.(strstr($page_link, '?') ? '&amp;photo='.($next) : '?photo='.($next));
	if($prev)
		$prev_link = $page_link.(strstr($page_link, '?') ? '&amp;photo='.($prev) : '?photo='.($prev));
	$photo = $photos[$curr];

	// html encode caption
	$photo['caption'] = function_exists('seems_utf8') && seems_utf8($photo['caption'])
											? htmlentities($photo['caption'], ENT_QUOTES, 'utf-8')
											: htmlentities($photo['caption'], ENT_QUOTES);

	// get max width
	$options = get_option('fbgallery_settings_section');
	$width = $options['fb_embedded_width'];

	include(FB_STYLE_PATH.'photo.php');

	$content .= ob_get_clean();
	return $content;
}

function fb_display_manage_list($message = '') {
	$albums = fb_get_album();

	if($message != ''): ?>
	<div id="fb-message" class="updated fade" style="display: none"><p><?php echo $message ?></p></div>
	<?php endif; ?>

	<?php if($albums) { ?>
	<ul id="fb-manage-list">
		<?php
		for($i = 0; $i < count($albums); $i++):
		$album = $albums[$i];
		$thumb = fb_get_photo($album['cover_pid'], 'small');
		$class = ($album['hidden'] == 1) ? 'disabled' : '';
		?>
		<li class="<?php echo $class ?>" id="album_<?php echo $album['aid'] ?>">
			<div class="thumb" style="background-image:url(<?php echo $thumb ?>);"></div>
			<div>
				<h3><?php echo $album['name'] ?><small style="font-weight: normal"></h3>
				<div class="description">
					<?php echo $album['size'] ?> Photos</small><br />
					Created: <?php echo mysql2date('m-d-Y', $album['created']) ?>, Modified: <?php echo mysql2date('m-d-Y', $album['modified']) ?><br />
					<span>
						<a href="<?php echo get_option('siteurl').'?page_id='.$album['page_id'] ?>" target="_blank">View</a>
						<a href="#" class="toggle-hidden"><?php echo $album['hidden'] ? 'Show' : 'Hide' ?></a>
					</span>
				</div>
			</div>
			<div style="clear: left"></div>
		</li>
		<?php endfor; ?>
	</ul>
	<?php } else { ?>
	<p>There are currently no albums in FB Gallery.</p>
	<?php
	}
}

function fb_info_box() {
?>
	<div id="fb-info">
		<h3>Info</h3>
		<ul>
			<li><a href="<?php echo FB_WEBSITE?>">FB Gallery Home</a></li>
			<!--  li><a href="http://wordpress.org/tags/fotobook?forum_id=10">Support Forum</a></li -->
			<li><a href="http://www.fatcow.com/join/index.bml?AffID=642780">Host your Web site with FatCow!</a></li>
			<li><a href="http://www.amkd.com.au/">Need someone to build your web site?</a></li>
		</ul>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="N2B6H6ZJZ9C9J">
			<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_AU/i/scr/pixel.gif" width="1" height="1">
		</form>


	</div>
<?php
}

function fb_display_scripts() {
	$post = $GLOBALS['post'];
	$options = get_option('fbgallery_settings_section');
	$albums_page = $options['fb_albums_page'];
	if ($post->ID == $albums_page || $post->post_parent == $albums_page) {
		if ($options['fb_style'] == 'colorbox') {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-colorbox', FB_STYLE_URL . 'js/colorbox.js', array('jquery'));
		}
		if (file_exists(FB_STYLE_PATH . 'js/init.js')) {
			wp_enqueue_script('fotobook-style', FB_STYLE_URL . 'js/init.js', array('jquery'));
		}
	}
	if ((is_active_widget('fbg_thumbnail_widget')) || (is_active_widget('fbg_photos_widget'))) {
		wp_enqueue_script('fotobook-widget', FB_PLUGIN_URL.'js/widget.js');
	}
	wp_enqueue_script('tdfotobox', FB_PLUGIN_URL.'js/tdfotobox.js');
	$widgetOptions = get_option('fbg_photos_thumb_widget');

	wp_localize_script( 'tdfotobox', 'FBGAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 
																								'height'=>$widgetOptions['height'],  
																								'width'=>$widgetOptions['width'],  
																							 'FBGPhotoSlideNonce' => wp_create_nonce( 'FBGPhotoSlide' )) );		
}

function fb_display_styles() {
	$post = $GLOBALS['post'];
	$options = get_option('fbgallery_settings_section');
	$albums_page = $options['fb_albums_page'];
	if ($post->ID == $albums_page || $post->post_parent == $albums_page) {
		if ($options['fb_style'] == 'colorbox') {
			wp_enqueue_style('fotobook-colorbox', FB_STYLE_URL.'colorbox.css');
		}
		wp_enqueue_style('fotobook-style', FB_STYLE_URL.'style.css');
	}
	if ((is_active_widget(false, false,'fbg_thumbnail_widget')) || (is_active_widget(false, false,'fbg_photos_widget'))) {
		wp_enqueue_style('fbgallery-widget', FB_PLUGIN_URL . 'styles/sidebar-style.css');
	}
}
?>