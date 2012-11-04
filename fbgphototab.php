<?php
//------------------------//
//--PHOTOS-TAB-FUNCTIONS--//
//------------------------//

function fb_add_upload_tab($tabs) {
	// 0 => tab display name, 1 => required cap, 2 => function that produces tab content, 3 => total number objects OR array(total, objects per page), 4 => add_query_args
	$tab = array('fbgallery' => array('FBGallery', 'upload_files', 'fb_upload_tab', 0));
	return array_merge($tabs, $tab);
}

function fb_upload_tab() {
	// generate link without aid variable
	$vars = explode('&', $_SERVER['QUERY_STRING']);
	if(stristr($vars[count($vars)-1], 'aid')) {
		unset($vars[count($vars)-1]);
	}
	$link = 'upload.php?'.implode('&', $vars);
	echo '<br />';
	fb_photos_tab($link);
}

function fb_add_media_tab($tabs) {
	if(isset($_GET['type']) && $_GET['type'] == 'image')
		$tabs['fbgallery'] = 'FBGallery';
	return $tabs;
}

function media_upload_fbgallery() {
	global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types;
	wp_enqueue_script('admin-gallery');
	wp_enqueue_script('media-upload');
	return wp_iframe( 'media_upload_fbgallery_tab', $errors );
}

function media_upload_fbgallery_tab($errors) {
	// generate link without aid variable
	$vars = explode('&', $_SERVER['QUERY_STRING']);
	if(stristr($vars[count($vars)-1], 'aid')) {
		unset($vars[count($vars)-1]);
	}
	$link = 'media-upload.php?'.implode('&', $vars);
	media_upload_header();
	fb_photos_tab($link);
}

function fb_photos_tab($link) { ?>
	<style type="text/css">
	<?php include(FB_PLUGIN_PATH.'styles/admin-styles.css'); ?>
	</style>
	<form id="image-form">
	<?php
	if(isset($_GET['aid'])):
	$album = fb_get_album($_GET['aid']);
	$photos = fb_get_photos($_GET['aid']);
	?>
	<script language="javascript">
	var fbThumb; var fbFull; var fbLink; var fbCaption;
	function findPos(obj) {
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}
		return [curleft,curtop];
	}
	function insertPopup(obj, thumb, full, link, caption) {
		fbThumb = thumb;	fbFull		= full;
		fbLink	= link;	 fbCaption = caption;
		var popup = document.getElementById('fb-insert-popup');
		popup.style.display = 'block';
		popup.style.left		= findPos(obj)[0]+'px';
		popup.style.top		 = findPos(obj)[1]+'px';
	}
	function insertPhoto(size) {
		var src;
		if (size == 'thumb')
			src = fbThumb;
		else
			src = fbFull;
		var html =
			'<a href="'+fbLink+'" class="fb-photo">' +
			'<img src="'+src+'" alt="'+fbCaption+'" />' +
			'</a> ';
		wpgallery.getWin().send_to_editor(html);
	}
	</script>
	<h3><?php echo $album['name'] ?> <a href="<?php echo $link ?>" style="font-size: 11px">&laquo; Back to Albums</a></small></h2>

	<div id="fb-insert-popup">
		Insert...<br />
		&nbsp; <a href="#" onclick="insertPhoto('thumb'); return false;">Thumbnail</a><br />
		&nbsp; <a href="#" onclick="insertPhoto('full'); return false;">Full</a><br />
		<br /><a href="#" onclick="this.parentNode.style.display = 'none'; return false;">[close]</a>
	</div>

	<ul id="fb-photos-tab">
		<?php foreach($photos as $photo): ?>
		<li>
			<a href="#" onclick="insertPopup(this.parentNode, '<?php echo $photo['src'] ?>','<?php echo $photo['src_big'] ?>','<?php echo fb_get_photo_link($photo['pid']) ?>', '<?php echo addslashes($photo['caption']) ?>'); return false;">
				<img src="<?php echo $photo['src']; ?>" />
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php
	else:
	$albums = fb_get_album();
	?>
	<h3>Select an Album</h3>
	<ul id="fb-manage-list">
		<?php
		foreach($albums as $album):
		$thumb = fb_get_photo($album['cover_pid'], 'small');
		?>
		<li id="album_<?php echo $album['aid'] ?>" style="cursor: default">
			<div class="thumb" style="background-image:url(<?php echo $thumb ?>);"></div>
			<div>
				<h3><a href="<?php echo $link ?>&amp;aid=<?php echo $album['aid'] ?>"><?php echo $album['name'] ?></a></h3>
				<div class="description">
					<small style="font-weight: normal"><?php echo $album['size'] ?> Photos</small><br />
				</div>
			</div>
			<div style="clear: both"></div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	</form>
	<?php
}

// This function was removed from WP 2.5.1 to 2.6
if (!function_exists('media_admin_css')) {
	function media_admin_css() {
		wp_admin_css('css/media');
	}
}

if(get_bloginfo('version') >= 2.5) {
	add_filter('media_upload_tabs', 'fb_add_media_tab');
	add_filter('media_upload_fbgallery', 'media_upload_fbgallery');
	add_action('admin_head_media_upload_fbgallery_tab', 'media_admin_css');
} else {
	add_filter('wp_upload_tabs', 'fb_add_upload_tab');
}
?>