<?php

/*
Fotobook Management Panel
*/
// get facebook authorization token

$mgFacebook = new FacebookAPI;
?>


<?php if(!$mgFacebook->link_active()): ?>
<div id="message" class="error fade"><p>There is no Facebook account linked to this plugin.  Change that in the <a href="<?php echo FB_OPTIONS_URL ?>">settings panel</a>.</p></div>
<?php endif; ?>
<?php 

if($fb_message): ?>
<div id="message" class="<?php echo $mgFacebook->error ? 'error' : 'updated' ?> fade"><p><?php echo $fb_message ?></p></div>
<?php endif; ?>

<div class="wrap">

	<div id="fb-panel">
		<?php fb_info_box() ?>
		
		<?php if(!fb_albums_page_is_set()): ?>
		<p><?php _e('This is where you can import and manage your Facebook albums.	You can drag the albums to change the order.'); ?></p>
		<p><em>You must select a page for the photo gallery in the <a href="<?php echo FB_OPTIONS_URL ?>">FB Gallery Setting</a> tab before you can import albums.</em></p>
		<?php else: ?>
		<!-- ?php if($mgFacebook->link_active()): ? -->
		<div class="nav">
			<input type="button" class="button-secondary" name="get" value="Get Albums" style="width: 100px" />
			<input type="button" class="button-secondary" name="getall" value="Get All Albums" style="width: 120px" />
			<input type="button" class="button-secondary" name="order" value="Order By Date" />
			<input type="button" class="button-secondary" name="remove" value="Remove All" /> &nbsp;&nbsp;
			<span id="fb-progress" style="display: none">
				<img id="fb-progress-indicator" src="../wp-content/plugins/fotobook/images/percentImage.png" alt="0%" class="percentImage" style="background-position: -500px 0pt;"/>
				<span id="fb-progress-indicatorText">0%</span>
			</span>
		</div>
		<?php

		 echo $mgFacebook->CheckForNewAlbums() ?>		
		<div id="fb-manage">
			<?php fb_display_manage_list() ?>
		</div>
		<!-- ?php endif; ? -->
		
		<?php // condition checking if a gallery page is selected
		 endif; ?> 
	</div>
</div>