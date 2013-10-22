<div class="fotobook-subheader">
  <table id="fotobook-main">
<?php if($datePagination) : ?>
	 	<tr>
 			<td>
	 			<div style="position:relative">
					<div style="float:left;font-size: 14px; font-weight:bold;">	
						Facebook Albums For <select name="archive-dropdown" onChange='document.location.href=this.options[this.selectedIndex].value;'>
							<option value=""><?php echo attribute_escape(__('Select Month')); ?></option>
							<?php 
								echo $optionLine;
							?>
						</select>
					</div>	
					<div style="float:left">	
						<select name="archive-dropdown" onChange='document.location.href=this.options[this.selectedIndex].value;'>
							<option value=""><?php echo attribute_escape(__('Select Year')); ?></option>
							<?php 
								echo $yearOptionLine;
							?>
						</select>
					</div>	
				</div>
			</td>
		</tr>
<?php endif;  ?>
		<tr>
			<td>
				<div style="position:relative">
	  			<span class='main'>Albums <?php echo $first_album ?> - <?php echo $last_album ?> out of <?php echo $album_count ?></span>
  				<div class='pagination'>
	   	 			<?php if($prev_link): ?><a href='<?php echo $prev_link ?>'>Prev</a><?php endif; ?>
   	 					<?php echo $pagination ?>
  	  			<?php if($next_link): ?><a href='<?php echo $next_link ?>'>Next</a><?php endif; ?>
 	 				</div>
				</div>
			</td>
		</tr>
	</table>
</div>

  <table id="fotobook-main">
 	<tr>
 		<td>
<?php
if(sizeof($albums) > 0):
?>
	<div id="fotobook-preview">
	<div class="clear"></div>
<?php
		$lastAlbumDate = fb_GetLastAlbumDate();

			remove_filter('the_content', 'seo_friendly_images', 100);
			$titleStr = "Album Image";
			$colCount = 0;
			foreach($albums as $album):
//echo "\n<!-- fotobook-main ".$album['link']." --->\n";?>
			    <?php 
			    	$albumImgSrc = fb_get_photo($album['cover_pid'], 'full');
//echo "\n<!-- fotobook-main ".$albumImgSrc." --->\n";
//echo "\n<!-- fotobook-main description : ".$album['description']." --->\n";

			    			if($album['description'] != '')
			    			{
			    	 			$titleStr = $album['description'];
			    	 			$myStr = $album['description'];
//echo "\n<!-- fotobook-main titleStr : ".$titleStr." --->\n";
			    	 		}
			    	 		$photoLabel = "Photo";
			    	 		if ($album['size'] > 1)
			    	 		{
			    	 			$photoLabel = "Photos";
			    	 		} ?>

						<div class="thumbnail" style="height: 180px; width: 180px">
						<table>  
									<tr>
										<td> 
											<?php	$options = get_option('fbgallery_settings_section');
												if ($options['fb_use_album_content_page'] == 'use_album_content_page'):
													$page_id = $options['fb_photo_display'];
													$page_link = get_permalink($page_id);
													$album_link = $page_link.(strstr($page_link, '?') ? '&amp;fb_album='.$album['aid'] : '?fb_album='.$album['aid']);

											?>
			      						<a href="<?php echo $album_link ?>"><img src="<?php echo $album['thumb'] ?>"  alt="<?php echo $titleStr ?>" longDesc="<?php echo $albumImgSrc ?>"  class="popview" /></a>
											<?php else : ?>
			      						<a href="<?php echo $album['link'] ?>"><img src="<?php echo $album['thumb'] ?>"  alt="<?php echo $titleStr ?>" longDesc="<?php echo $albumImgSrc ?>"  class="popview" /></a>
			      					<?php endif; ?>
	      					</td>
	      					</tr> 
	      					<tr> 
	      						<td> 
		    						<h6><?php echo $album['name'];?></h6>
		    						</td>
		    					</tr>  
		    					<tr>
		    						<td>  
   			      				<small><?php echo $album['size']." ".$photoLabel ?></small>
   			      				<div id="fotobox-caption">
   			      					This is a test caption
   			      				</div>
   			      			</td>
   			      		</tr>  
   			      	 
   				  </table> 
		  			</div>
		  					
<?php 
			endforeach; 
?>
	</div>
<?php
endif;
?>
			</td>
		</tr>
</table>
<div class="fotobook-subheader fotobook-subheader-bottom">
  <span class='main'>Albums <?php echo $first_album ?> - <?php echo $last_album ?> out of <?php echo $album_count ?></span>
  <div class='pagination'>
    <?php if($prev_link): ?><a href='<?php echo $prev_link ?>'>Prev</a><?php endif; ?>
    <?php echo $pagination ?>
    <?php if($next_link): ?><a href='<?php echo $next_link ?>'>Next</a><?php endif; ?>
  </div>
</div>