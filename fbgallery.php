<?php
/*
Plugin Name: FBGallery
Plugin URI: http://www.amkd.com.au/wordpress/fbgallery/70
Description: Imports your Facebook albums directly into WordPress. Updating the original Fotobook plugin by Aaron Harp this version now uses OpenGraph. Albums are now stored as posts rather than pages so that photos can be searched using wordpress search.
Author: Caevan Sachinwalla
Author URI: http://www.amkd.com.au/
Version: 1.5.1
*/

/*
Copyright 2012 Caevan Sachinwalla
Acknowledgement to Asron Harp for the orginal code for this plugin

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.	If not, see <http://www.gnu.org/licenses/>.
*/

global $table_prefix, $wp_version;

// plugin configuration variables
define('FB_ALBUM_TABLE', $table_prefix.'fbg_albums');
define('FB_PHOTO_TABLE', $table_prefix.'fbg_photos');
define('FB_POSTS_TABLE', $table_prefix.'posts');
define('FB_PLUGIN_PATH', WP_PLUGIN_DIR.'/fbgallery/');
define('FB_PLUGIN_URL', plugins_url().'/fbgallery/');
define('FB_STYLE_URL', FB_PLUGIN_URL.'styles/');
define('FB_STYLE_PATH', FB_PLUGIN_PATH.'styles/');
define('FB_MANAGE_URL', (get_bloginfo('version') >= 1.0 ? 'media-new.php' : 'edit.php') .'?page=fbgallery/fbg-settings.php?tab=fbg_manage');
define('FB_OPTIONS_URL', 'options-general.php?page=fbgallery/fbg-settings.php');
define('FB_WEBSITE', 'http://www.amkd.com.au/wordpress/fbgallery/70');
define('FB_VERSION', 1.40);
define('FB_TITLE','FB Gallery');

// facebook configuration variables
define('FB_DEBUG_LEVEL',1);

include_once('fbgphototab.php');
include_once('fbgdisplay.php');
include_once('fbgwidget.php');
include_once('styles/photos-widget-class.php');
include_once('styles/photos-thumb-widget-class.php');
// upgrade if needed
if(fb_needs_upgrade()) {
	fb_initialize();
}

$fb_message = null;

function fbg_admin_scripts() {
	wp_enqueue_style('fotobook-css', FB_PLUGIN_URL.'styles/admin-styles.css');
	wp_enqueue_script('fotobook-js', FB_PLUGIN_URL.'js/admin.js', array('jquery', 'jquery-ui-sortable'), FB_VERSION);
}
add_action('load-settings_page_fbgalleryplugin', 'fbg_admin_scripts');
add_action('admin_init', 'fbg_admin_scripts');


function DoHTMLEncode($theCaption)
{
		$encodedCaption = function_exists('seems_utf8') && seems_utf8($theCaption)
															 ? htmlentities($theCaption, ENT_QUOTES, 'utf-8')
															 : htmlentities($theCaption, ENT_QUOTES);
	  return $encodedCaption;
}




// This is a helper function that logs debug, that I find useful when debugging. It needs the FB_DEBUG_LEVEL constant to be defined with a value greater than 0, or it
// can be truened on via the config settings.
// If activated it will create a debug sub directory under the plugin directory. The date is incorporated into the filename and each loof entry is automatically timestamped
	function fb_logdebug($debugStr)
	{
		$debugLevel = FB_DEBUG_LEVEL;
		$options = get_option('fbgallery_settings_section');
		if($options['fb_debug_on'] == 'debugON')
		{
			$debugLevel = 1;
		}
		if($debugLevel > 0)
		{
				if(!is_dir(FB_PLUGIN_PATH.'debug'))
				{
					mkdir(FB_PLUGIN_PATH.'debug');
				}
				global $wp_query;
		   	$TD_DEBUG_DIR = FB_PLUGIN_PATH.'debug/fbgdebug'.date('dmY').'.log'; 
		
	    	$date = date('d.m.Y H:i:s'); 
    		$log = $date." : [TD] ".$debugStr."\n"; 
    		error_log($log, 3, $TD_DEBUG_DIR); 
		}
	}


//--------------------//
//---FACEBOOK-CLASS---//
//--------------------//

class FacebookAPI {
	var $facebook	 = null;
	var $sessions = array();
	var $token		= null;
	var $error		= false;
	var $msg			= null;
	var $secret	 = null;
	var $progress = 0;
	var $increment = null;
	var $keepAliveTime = 0;
	var $options = null;
	var $phpVersion5_3 = false;

function FacebookAPI() 
{
	if(!class_exists('FB_Facebook'))
		{
		include_once('lib/facebook.php');
		include_once('lib/fbconfig.php');
	}
		
	$facebook	= SetupFBConnection();

	$this->facebook = $facebook;  // facebook variable initialized in fbconfig.php
	global $fb_message;
	$this->msg = &$fb_message;
		

	//If we have authorised facebook details, there should be a authorization token stored in the options
	$this->token = get_option('fbAppAuthToken'); 

	// determine how much to increment the progress bar after each request
	$this->progress  = get_option('fb_update_progress');
	$this->increment = 1; // We will increment the progress bar in 1% increments
	$this->options = get_option('fbgallery_settings_section');
  if (strnatcmp(phpversion(),'5.3.0') >= 0) 
  { 
  	$phpVersion5_3 = true;
		gc_enable();
  } 

} // END Constructor

/*
*	FreeUpMemory()
* Tries to free up memory by calling gc_collect_cycles(), though will only work for php version 5.3 or greater
*/
function FreeUpMemory()
{
  if ($phpVersion5_3) 
  {
		gc_collect_cycles();
  } 
}	
	function link_active() {
		return 1;
//		return count($this->sessions) > 0;
	}




	function remove_user() {
		// remove all of this user's albums and photos and facebook details
		global $wpdb;
		fb_remove_all();
		update_option('fb_fan_page_url','');
		update_option('fb_app_id','');
		update_option('fb_app_secret','');
		update_option('fb_fan_page_id','');
		update_option('fbAppAuthToken','');
		update_option('fbAppAuthUser','');

//		update_option('fb_facebook_session', $this->sessions);
	}

	// Updates the progress bar displayed when albums are retrieved from facebook
	function update_progress($reset = false) {
		if($reset == true) {
			$this->progress = 0;
		}
		else {
			$this->progress = $this->progress + $this->increment;
		}
		if($this->progress > 100) {
			$this->progress = 100;
		}
		update_option('fb_update_progress', $this->progress);
		return $this->progress;
	}
	function clear_progress() {
		update_option('fb_update_progress', -1);
	}

	// Updates the keep alive time
	function update_keep_alive($reset = false) {
		if($reset == true) {
			$this->keepAliveTime = 0;
		}
		else {
			$this->keepAliveTime = time();
		}
		update_option('fb_keep_alive_time', $this->keepAliveTime);
		return $this->keepAliveTime;
	}

	function increase_time_limit() {
		// allow the script plenty of time to make requests
		if(!ini_get('safe_mode') && !strstr(ini_get('disabled_functions'), 'set_time_limit'))
			set_time_limit(-1); // this basically gives the script unlimited time to execute
	}

// This is the function that retrieves the albums from facebook using Opengraph	
function TDGetAlbums($nextLink = false)
{
	$first = false;
	if($nextLink === false)
	{
		$first = true;
	}

	if($first)
	{ 
			$options = get_option('fbgallery_plugin_options');

		$fbPgID = $options['fb_fan_page_url'];
    if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);

		$albums = $this->facebook->api("/".$fbPgID."/albums");
		return $albums;
	}
	else
	{
		$faceNeedle = 'graph.facebook.com';
		$facePos = strpos($nextLink,$faceNeedle);
		if($facePos !== false)
		{
			$untilStr = substr($nextLink,$facePos+strlen($faceNeedle),strlen($nextLink)-($facePos+strlen($faceNeedle)));
			$nextLink = $untilStr;
		}
		$albums = $this->facebook->api($nextLink);
		if(!is_array($albums))
		{
			return false;
		}
		else
		{
			return $albums;
		}
	} 
}	

// Function copies the facebook album data into a structure that will be stored in the DB table 
function FillAlbumData($theAlbum)
{																																			 
				$album_data = array(
				'aid' => $theAlbum['id'],
				'page_id'=> "",
				'cover_pid' => $theAlbum['cover_photo'],
				'owner' => $theAlbum['from']['id'],
				'name' => $theAlbum['name'],
				'created' => !empty($theAlbum['created_time']) ?  date('Y-m-d H:i:s', ParseDateTime($theAlbum['created_time'],true)) : '',
				'modified' => !empty($theAlbum['updated_time']) ? date('Y-m-d H:i:s', ParseDateTime($theAlbum['updated_time'],true)) : '',
				'description' => $theAlbum['description'],
				'location' => $theAlbum['location'],
				'link' => $theAlbum['link'],
				'size' => $theAlbum['count'],
				'hidden' => 0,
				'ordinal' => fb_get_next_ordinal()
			);
			return $album_data;
}

// Copy the facebook photo data into a table structure ready to be stored in the database table
function SavePhotoData($photo, $album, $ordinal,$photoCount)
{
		global $wpdb;

	 	$fb_photos = array(
 		'pid'=>$photo['id'],
 		'aid'=>$album['id'],
 		'owner'=>$photo['from']['id'],
 		'src'=>$photo['picture'],
 		'src_big'=>$photo['source'],
 		'src_small'=>$photo['picture'],
 		'link'=>$photo['link'],
 		'caption'=>$photo['name'],
		'created' => date('Y-m-d H:i:s', ParseDateTime($photo['created_time'],true)),
		'ordinal' => $ordinal);
		if($wpdb->insert(FB_PHOTO_TABLE, $fb_photos) == false)
		{
				// Occasionally we get an error on insert and all subsequent inserts fail, the current work around is to re-initialize the wpdb variable
				fb_logdebug("fbphotos:  unable to insert photos : count [".$photoCount."] id [".$photo['id']."]");
				fb_logdebug("fbphotos:  unable to insert photos : SQL error [".$wpdb->print_error()."]");
				fb_logdebug("fbphotos:  unable to insert photos : Last Query [".$wpdb->last_query."]");
				$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
				fb_logdebug("fbphotos:  reinitializing wpdb variable trying again [".DB_USER."] [".DB_NAME."]");
				if($wpdb->insert(FB_PHOTO_TABLE, $fb_photos) == false)
				{
					fb_logdebug("fbphotos:  Second attempt at inserting to database failed : exiting");
					return false;
				}
			}
			unset($fb_photos);
			return true;
}

// Funtion utilizes Facebook FQL to determine how many albums there are belonging to the specific owner
function CountAlbums($uid)
{
	
		$options = get_option('fbgallery_plugin_options');
    $fbPgID = $options['fb_fan_page_url'];
    if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);
		$profile = $this->facebook->api("/".$fbPgID);
		$ownerID = $profile['id'];
		$albumIDs = $this->facebook->api('/fql?q=SELECT+aid+FROM+album+WHERE+owner='.$ownerID);
		return count($albumIDs['data']);
}

// Funtion creates a worpress Post for the specific album, this allows photos to be searched by the description retrieved from facebook.
function CreatePost($theAlbum, $thePhotoPostBodyStr)
{
	$options = get_option('fbgallery_settings_section');
	$fb_albums_category = $options['fb_albums_category'];
	$new_post = array(
		'post_title' => $theAlbum['name'],
		'post_content' => $thePhotoPostBodyStr,
		'post_status' => 'publish',
		'post_date' => date('Y-m-d H:i:s'),
		'post_author' => $user_ID,
		'post_type' => 'post',
		'post_category' => array($fb_albums_category )
	);
	$post_id = wp_insert_post($new_post);
	return $post_id;
}

// Function determines whether there are new albums to be retrieved from facebook
function CheckForNewAlbums() {
		global $wpdb;
		
		$totalAlbums = $this->CountAlbums(0);		
		$albums = fb_get_album();
		$totalStoredAlbums = count($albums);
		$albumsToGet = $this->options['fb_max_albums'];

		if($totalStoredAlbums > 0 )
		{
			$latestDateStr =  fb_GetLastAlbumUpDate();
			$latestDateTime = fb_StrToTime($latestDateStr); 
			$options = get_option('fbgallery_plugin_options');
    	$fbPgID = $options['fb_fan_page_url'];
    	if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);
			$profile = $this->facebook->api("/".$fbPgID);
			$ownerID = $profile['id'];
//			$fqlStr = '/fql?q=SELECT modified FROM album WHERE owner='.$ownerID.' AND modified >'.$latestDateTime;
//			fb_logdebug('CheckForNewAlbums: fql : '.$fqlStr);
/*			$albumDates = $this->facebook->api('/fql?q=SELECT+modified+FROM+album+WHERE+owner='.$ownerID.'+AND+modified>'.$latestDateTime);
			if(Count($albumDates) > 0)
			{
			fb_logdebug('CheckForNewAlbums: Count : '.print_r($albumDates,true));
				foreach($albumDates as $albumDate)
				{
					foreach($albumDate as $albumMod)
					{
						fb_logdebug('CheckForNewAlbums: in 2nd for : '.print_r($albumMod,true));
						fb_logdebug('CheckForNewAlbums: fql :'.date('Y-m-d H:i:s', $albumMod['modified']).' : '.$albumMod['modified']);
					}
				}
			}
			else
			{
				fb_logdebug('CheckForNewAlbums: No dates');
			}			*/
			$albumIDs = $this->facebook->api('/fql?q=SELECT+aid+FROM+album+WHERE+owner='.$ownerID.'+AND+modified>'.$latestDateTime);
			$newAlbums = count($albumIDs['data']);
			if($newAlbums > 0)
			{
				//It takes approximately 1 second to retrieve an album so we convert number of albums to seconds
				$hours = floor($newAlbums / 3600);
				$minutes = floor(($newAlbums / 60) % 60);
				$seconds = $newAlbums % 60;
				$timeStr = "It will take approximately ";
				if($hours > 0)
				{
					$timeStr.= $hours."hrs ";
				}
				if($minutes > 0)
				{
					$timeStr.= $minutes."mins ";
				}
				else if($seconds > 0)
				{
					$timeStr.= $seconds."secs ";
				}
				$timeStr.= "to download these albums"; 

				$resultStr = '<p id="fbg-new-albums">You have '.$totalStoredAlbums.' albums. You have '.$newAlbums.' new or updated albums in Facebook. Click the <b>Get Albums</b> button to retrieve these albums.</BR>'.$timeStr;
				if($albumsToGet > 0)
				{
					$resultStr.='</BR><b>NOTE:</b> A maximum of '.$albumsToGet.' albums will be retrieved. This value can be changed in the <b>FB Gallery Settings</b> Tab</p>';
				}
				else
				{
					$resultStr.='</p>';
				}
			}
			else
			{
				$resultStr = '<p id="fbg-new-albums">You have '.$totalStoredAlbums.' albums. You have no new or updated albums on Facebook</p>';
			}
		}
		else
		{
				$hours = floor($totalAlbums / 3600);
				$minutes = floor(($totalAlbums / 60) % 60);
				$seconds = $totalAlbums % 60;
				$timeStr = "It will take approximately ";
				if($hours > 0)
				{
					$timeStr.= $hours."hrs ";
				}
				if($minutes > 0)
				{
					$timeStr.= $minutes."mins ";
				}
				if($seconds > 0)
				{
					$timeStr.= $seconds."secs ";
				}
				$timeStr.= "to download these albums"; 

				$resultStr = '<p id="fbg-new-albums">You have '.$totalAlbums.' albums on Facebook. Click the <b>Get Albums</b> button to retrieve these albums.</BR>'.$timeStr;
				if($albumsToGet > 0)
				{
					$resultStr.='</BR><b>NOTE:</b> A maximum of '.$albumsToGet.' albums will be retrieved. This value can be changed in the <b>FB Gallery Settings</b> Tab</p>';
				}
				else
				{
					$resultStr.='</p>';
				}
		}
		return $resultStr;
}
// Function counts how many new or updaed albums are on facebook/
function CountNewAlbums() {
		global $wpdb;
		
		$totalAlbums = $this->CountAlbums(0);		
		$albums = fb_get_album();
		$totalStoredAlbums = count($albums);
		if($totalStoredAlbums > 0 )
		{
			$latestDateStr =  fb_GetLastAlbumUpDate();
			$latestDateTime = fb_StrToTime($latestDateStr); 
			$options = get_option('fbgallery_plugin_options');
    	$fbPgID = $options['fb_fan_page_url'];
    	if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);
			$profile = $this->facebook->api("/".$fbPgID);
			$ownerID = $profile['id'];

			$albumIDs = $this->facebook->api('/fql?q=SELECT+aid+FROM+album+WHERE+owner='.$ownerID.'+AND+modified>'.$latestDateTime);
			$newAlbums = count($albumIDs['data']);
			return $newAlbums;
		}
		else
		{
			return $totalAlbums;
		}
}

// This is the main function that retrieves the albums and photos from facebook and stores the data in the database and creates posts for each album
// param $recent
// value true : then only retrieve albums since the last update. If there are no albums stored in the database all albms will be retrieved
//       false : All albums are retrieved from facebook if the album already exists in the DB then it will be updated if the modified date is more recent than the exisiting entry.
function update_albums($recent = false) 
{
		global $wpdb;

		$this->increase_time_limit();
	$fb_maxtime=ini_get('max_execution_time');
	ini_get('max_execution_time');
	$this->update_keep_alive();
	$tdfbcount=0;
	set_time_limit ( 0 );
	// Get the configured maximum number of albums to retrieve
	$albumsToGet = $this->options['fb_max_albums'];


	
		// reset album import progress
		$this->update_progress(true);

		$totalStoredAlbums = count(fb_get_album());

		$finished = false;
		$nextPage = false;
	
		// work out how many albums there are and roughly how many times we will have to update the progress counter.	
		$totalAlbums = $this->CountAlbums(0);		
		$albumCount = 0;

		$latestDateTime = 0;
		if($recent)
		{
			if($totalStoredAlbums > 0)
			{
				$latestDateStr =  fb_GetLastAlbumUpDate();
				$latestDateTime = fb_StrToTime($latestDateStr); 
				$totalAlbums =  $this->CountNewAlbums();
			}
		}
		// Check if there is a configured maximum albums to get.
		if(($albumsToGet > 0) && ($albumsToGet < $totalAlbums))
		{
			$totalAlbums = $albumsToGet;
		}
		if($totalAlbums < 100)
		{
			$percentCount = (int)((100/$totalAlbums));
			$this->increment = $percentCount;
			$percentIncrement = $percentCount;
		}
		else
		{
			$percentCount = 1;
			$percentIncrement = 1;
		}
		$this->update_progress(true);
		$updatePoint = (int)(($totalAlbums * $percentCount)/100);
		while(!$finished)
		{
			$this->update_keep_alive();
			$albums = $this->TDGetAlbums($nextPage);
			if(is_array($albums))
			{
				foreach($albums['data'] as $album)
				{
					if( $album['id'] == $lastID)
					{
						$finished = true;
						break;
					}
					if($recent)
					{
						$albumCreatedTime = ParseDateTime($album['created_time'],true);
						$albumUpdatedTime = ParseDateTime($album['updated_time'],true);
						//Need to check update time as facebook returns albums is order of updated time.
						if ($albumUpdatedTime > $latestDateTime)
						{
							$checkAlbum = fb_get_album($album['id']);
							if($checkAlbum)
							{
								if(ParseDateTime($album['updated_time'],true) == fb_StrToTime($checkAlbum['modified']))
								{
								// Album exists but is up to date with the facebook version
									continue;
								}
								else
								{
									// The facebook version of this album has been updated so the just delete the existing album and associated page and photos
									// So that it will be replaced by the newer version
									fb_delete_page($checkAlbum['page_id']);
									$wpdb->query('DELETE FROM `'.FB_PHOTO_TABLE."` WHERE `aid` = '".$checkAlbum['aid']."'");
									$wpdb->query('DELETE FROM `'.FB_ALBUM_TABLE."` WHERE `aid` = '".$checkAlbum['aid']."'");
					  		}
							}
						}
						else
						{
							$finished = true;
							break;
						}
					}
					else
					{
						// Check if the album alread exists in the table
						$checkAlbum = fb_get_album($album['id']);
						if($checkAlbum)
						{
							if(ParseDateTime($album['updated_time'],true) == fb_StrToTime($checkAlbum['modified']))
							{
								// Album exists but is up to date with the facebook version
								continue;
							}
							else
							{
								// The facebook version of this album has been updated so the just delete the existing album and associated page and photos
								// So that it will be replaced by the newer version
								fb_delete_page($checkAlbum['page_id']);
								$wpdb->query('DELETE FROM `'.FB_PHOTO_TABLE."` WHERE `aid` = '".$checkAlbum['aid']."'");
								$wpdb->query('DELETE FROM `'.FB_ALBUM_TABLE."` WHERE `aid` = '".$checkAlbum['aid']."'");
						  }
						}
					}
					$album_data = $this->FillAlbumData($album);
					$photoCount = $album['count'];
					if($photoCount == 0)
					{
						continue;
					}
					
					// Once we have the facebook album id we can retrieve the photos from that album
					$this->update_keep_alive();
        	$photos = $this->facebook->api("/{$album['id']}/photos");
					$ordinal = 1;
					$photoPostStr = "";
					while($ordinal <= $photoCount)
					{
						if(!is_array($photos['data']))
						{
							break;
						}
						$photoCount = 1;
						 $wpdb->show_errors();
		        foreach($photos['data'] as $photo)
	  	      {
	  	      	if(!$this->SavePhotoData($photo, $album, $ordinal,$photoCount))
	  	      	{
	  	      		return;
	  	      	}
							$photoPostStr = $photoPostStr.GeneratePostPhotoEntry($photo); // Append the post entry for this photo
							$ordinal++;
							$photoCount++;
							$this->FreeUpMemory();
 						}
 						// Check of OpenGraph has indicated muliple pages of photo data, if there is we need to retrieve the next page.
			  		$photoPaging = $photos['paging'];
						if(is_array($photoPaging))
						{
								$nextPhotoPage = $photoPaging['next'];
								$idpos = strpos($nextPhotoPage,$album['id']);
								if($idpos === false)
								{
									unset($photos);
									break;
								}
								else
								{
									$idpos--;
									$untilStr = substr($nextPhotoPage,$idpos,strlen($nextPhotoPage)-$idpos);
									unset($photos);
									$this->update_keep_alive();
		        			$photos = $this->facebook->api($untilStr);
								}
						}
						else
						{
							unset($photos);
							break;
						}
						$this->FreeUpMemory();
 					}		
					// We can now generate the post body and create the post.
					$photoPostBodyStr = GeneratePostBody($photoPostStr);
        	$album_data['page_id'] = $this->CreatePost($album,$photoPostBodyStr);
        	
					// Album entry can now be insterted into the album table.
					$wpdb->insert(FB_ALBUM_TABLE, $album_data);
        	$albumCount++;
					unset($album_data);
					unset($photoPostStr);
					unset($photoPostBodyStr);
					if($albumCount >= $updatePoint)
					{ 
						$this->update_progress();
						$percentCount+=$percentIncrement;
						$updatePoint = (int)(($totalAlbums * $percentCount)/100);
					}
					if(($albumsToGet > 0) && ($albumCount >= $albumsToGet))
					{
						$finished = true;
						break;
					}
					$lastID = $album['id'];
					unset($album);
					$this->FreeUpMemory();
        }
  			$index++;
 				
  			$page = $albums['paging'];

				if(is_array($page))
				{
					$nextPage = $page['next'];
					$pos = strpos($nextPage,'after');
					if($pos === false)
					{
						$finished = true;
						break;
					}
				}
				else
				{
					$pos2 = strpos($nextPage,'&',$pos);
					if($pos2 === false)
					{
						break;
					}
				}
			}
			else
			{
				$finished = true;
				break;
			}
			unset($albums);
			$this->FreeUpMemory();
		}
		if(!$this->msg) {
			$this->msg = 'Albums imported successfully.';
		}
		$this->clear_progress();
		// If Caching is enabled now get the latest 24 images and store them locally to speed up the slideshow.
		if($this->options['fb_use_cache'] == 'useCache')
		{
			fb_latest_photos($this->options['fb_num_to_cache']);
		}
	}

} // End of FacebookAPI class

// Helper functions

function GeneratePostBody($theTableBody)
{
$formatStr = '<table id="fotobook-album" style="border-spacing:5px 5px;">'.$theTableBody.'</table>';
  return $formatStr;
}

function GeneratePostPhotoEntry($photo)
{
	$options = get_option('fbgallery_settings_section');

	$thumb_size = $options['fb_thumb_size'];	
	$styleStr = 'style="max-width:'.$thumb_size.'px; max-height: '.$thumb_size.'px"';
	$order   = array("\r\n", "\n", "\r");
	$replace = '';

	$formatStr =  '<tr style="border-bottom:thin solid #C0C0C0; padding-top:5px; padding-bottom:5px; border-spacing: 0px 10px;" >';
   	$formatStr = $formatStr.'<td style="padding-top:5px; padding-bottom:5px;">'.
     '<a href="'.$photo['source'].'" rel="lightbox" title="'.DoHTMLEncode(str_replace($order, $replace,$photo['name'])).'" id="photo'.$photo['ordinal'].'" target="_blank">'.
       '<img src="'.$photo['picture'].'" alt="'.DoHTMLEncode(str_replace($order, $replace,$photo['name'])).'" '.$styleStr.' />'.
     '</a>'.
   	'</td><td style="vertical-align:top">'.$photo['name'].'</td>'.
   	'</tr>';
    
  return $formatStr;
}

// Function parses the facebook time returned in OpenGraph to a standard php time value.
function ParseDateTime($datetime, $useTimeZoneOffset = false) {
	$currentTime = time();
 	$pos = strpos($datetime,'+'); 
	if ($pos === false)
	{
   	$pos = strpos($datetime,'-');
		if ($pos === false)
		{
			$dateString = $datetime;
		}
		else
		{
			$dateString = substr($datetime,0,$pos);
			$customOffset = substr($datetime,$pos+1,4);
			$offset = $customOffset*(-60);
		}
  } 
	else
	{
		$dateString = substr($datetime,0,$pos);
		$customOffset = substr($datetime,$pos+1,4);
		$offset = $customOffset*60;
	}
  // Parse the date and time portion of the string.
  $datetimeArray = strptime($dateString, "%Y-%m-%dT%H:%M:%S");

   // Generate the UNIX time. Note that this will be in the wrong timezone.
	$time = mktime($datetimeArray['tm_hour'],
 	$datetimeArray['tm_min'],
 	$datetimeArray['tm_sec'],
  $datetimeArray['tm_mon'] + 1,
	$datetimeArray['tm_mday'] ,
  $datetimeArray['tm_year'] + 1900);

  // Return the calculated UNIX time from above along with the offset
  // necessary to correct for the timezone specified.
  if($useTimeZoneOffset)
  {
  	//Need the wordpress tomezone offset as php timezone may not be set.
  	$gmtOffset=get_option('gmt_offset'); // value returned in hours
		$offset = intval($gmtOffset)*60*60;  // now convert to seconds
	}
  return $time + $offset;
}

//---------------------//
//---SETUP-FUNCTIONS---//
//---------------------//

function fb_initialize() {
	global $wpdb;

	// add default options
	add_option('fbg_version', FB_VERSION);
	add_option('fbAppAuthToken', '');
	add_option('fbAppAuthUser', '');

	$photo_table_query = "CREATE TABLE `".FB_PHOTO_TABLE."` (
	                        `pid` varchar(40),
	                        `aid` varchar(40),
	                        `owner` bigint(20) unsigned,
	                        `src` varchar(255) NOT NULL default '',
	                        `src_big` varchar(255) NOT NULL default '',
	                        `src_small` varchar(255) NOT NULL default '',
	                        `link` varchar(255) NOT NULL default '',
	                        `caption` text,
	                        `created` datetime,
	                        `ordinal` int(11) unsigned NOT NULL default 0,
									KEY `pid` (`pid`)
	                      ) TYPE = MyISAM";

	$album_table_query = "CREATE TABLE `".FB_ALBUM_TABLE."` (
	                        `aid` varchar(40),
	                        `page_id` bigint(20) unsigned,
	                        `cover_pid` varchar(40),
	                        `owner` bigint(20) unsigned,
	                        `name` varchar(255) NOT NULL default '',
	                        `description` text,
	                        `location` varchar(255) NOT NULL default '',
	                        `link` varchar(255) NOT NULL,
	                        `size` int(11) unsigned NOT NULL default 0,
	                        `created` datetime,
	                        `modified` datetime,
	                        `hidden` tinyint(1) unsigned NOT NULL default 0,
	                        `ordinal` int(11) unsigned NOT NULL default 0,
	                        UNIQUE KEY `aid` (`aid`)
	                      ) TYPE = MyISAM";


	if(!fb_table_exists(FB_PHOTO_TABLE)) {
		$wpdb->query($photo_table_query);
	}

	if(!fb_table_exists(FB_ALBUM_TABLE)) {
		$wpdb->query($album_table_query);
	}

	fb_upgrade_tables();

	update_option('fbg_version', FB_VERSION);
}

function fb_table_exists($table_name) {
	global $wpdb;
	foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
		if ($table == $table_name) return true;
	}
	return false;
}

function fb_needs_upgrade() {
	$upgrade = get_option('fbg_version') != FB_VERSION ? true : false;
	if($upgrade)
		$tables = fb_table_exists(FB_ALBUM_TABLE);
	else
		$tables = false;
	return ($upgrade && $tables);
}

function fb_upgrade_tables() {
	global $wpdb;

	$version = get_option('fbg_version');
}


function fb_add_pages() {
		add_media_page('Media &rsaquo; FBGallery', 'FBGallery', 8,'fbg-display-management','fbg_display_management');
	include_once('fbg-settings.php');
	$optionsHook = 	add_options_page(__('FB Gallery Settings', 'fbgalleryplugin'),__('FB Gallery Settings', 'fbgalleryplugin'), 'manage_options',
			'fbgalleryplugin', 'fbGalleryAdminHtmlPage');
			//call register settings function
			add_action( 'admin_init', 'registerFBGSettings' );

}

function fb_action_link($actions) {
	array_unshift($actions, '<a href="' . FB_OPTIONS_URL . '">Settings</a>');
	return $actions;
}
function fbg_display_management() {
	include("fbg-manage.php");
}

//---------------------//
//--WP-PAGE-FUNCTIONS--//
//---------------------//


function fb_delete_page($id) {
	if(fb_page_exists($id)) {

		// disable conflicting Wordbook action
		remove_action('delete_post', 'wordbook_delete_post');

		wp_delete_post($id);
	}
}


function fb_page_exists($id) {
	global $wpdb;
	$page_row = $wpdb->get_row('SELECT * FROM `'.FB_POSTS_TABLE."` WHERE `ID` = '$id'");
	return $page_row ? true : false;
}

//----------------------------//
//--OPTIONS/MANAGE-FUNCTIONS--//
//----------------------------//

function fb_ajax_handler() {
	function elapsedTime($firstTime)
	{
		$lastTime= time();

	// perform subtraction to get the difference (in seconds) between times
		$timeDiff=$lastTime-$firstTime;

	// return the difference
		return $timeDiff;
	}
	$options = get_option('fbgallery_settings_section');

	$timeOut = $options['fb_timeout'];
	static $static_facebook; 
	if(!isset($_POST['action']) || $_POST['action'] != 'fbgallery')
		return false;

	// handle hide/unhide requests
	if(isset($_POST['hide'])) {
		fb_toggle_album_hiding($_POST['hide']);
		echo 'success';
	}

	// handle order change
	elseif(isset($_POST['order'])) {
		fb_update_album_order($_POST['order']);
		echo 'success';
	}

	// handle order reset
	elseif(isset($_POST['reset_order'])) {
		fb_reset_album_order();
		echo 'The albums have been ordered by their modification date.';
	}

	// handle remove all
	elseif(isset($_POST['remove_all'])) {
		fb_remove_all();
		echo 'All albums have been removed.';
	}


	// handle update albums request
	elseif(isset($_POST['update'])) {
		 $static_facebook = new FacebookAPI;
		if($static_facebook->link_active()) {
			$static_facebook->update_albums(true);
			echo $static_facebook->msg;
		} else {
			echo 'There are no accounts linked to FB Gallery.';
		}
	}
	// handle update albums request
	elseif(isset($_POST['all'])) {
		$static_facebook = new FacebookAPI;
		if($static_facebook->link_active()) {
			$static_facebook->update_albums(false);
			echo $static_facebook->msg;
		} else {
			echo 'There are no accounts linked to FB Gallery.';
		}
	}

	// handle albums list request
	elseif(isset($_POST['albums_list'])) {
		fb_display_manage_list($_POST['message']);
	}
	// handle update progress request
	elseif(isset($_POST['progress'])) {
		$lastKeepAliveTime = get_option('fb_keep_alive_time');
			if(elapsedTime($lastKeepAliveTime) > $timeOut)
			{
				echo "-2";
				exit;
			}
		echo round(get_option('fb_update_progress'));
	}

	exit;
}

function fb_options_update_albums_page($new_id) {
	global $wpdb;

	$old_id = get_option('fb_albums_page');
	if($old_id == $new_id) {
		return true;
	}

	$albums = fb_get_album();
	if(sizeof($albums) > 0) {
		foreach($albums as $album) {
			$wpdb->update(FB_POSTS_TABLE, array('post_parent' => $new_id), array('ID' => $album['page_id']));
		}
	}

	update_option('fb_albums_page', $new_id);
}

function fb_options_toggle_comments($status = true) {
	global $wpdb;

	if($status) $status = 'open';
	else $status = 'closed';

	$options = get_option('fbgallery_settings_section');
	$fb_albums_page = $options['fb_albums_page'];

	$wpdb->update(FB_POSTS_TABLE, array('comment_status' => $status), array('post_parent' => $fb_albums_page));
}

function fb_albums_page_is_set() {
	global $wpdb;
	$options = get_option('fbgallery_settings_section');
	$fb_albums_page = $options['fb_albums_page'];
	return $wpdb->get_var("SELECT `ID` FROM `$wpdb->posts` WHERE `ID` = '$fb_albums_page'") ? true : false;
}

function fb_get_styles() {
	// get styles
	$styles = array();
	if ($handle = opendir(FB_PLUGIN_PATH.'styles')) {
		while (false !== ($file = readdir($handle))) {
			if(substr($file, 0, 1) != '.' && is_dir(FB_PLUGIN_PATH.'styles/'.$file))
				$styles[] = $file;
		}
		closedir($handle);
	}
	sort($styles);

	return $styles;
}

function fb_parent_dropdown( $default = 0, $parent = 0, $level = 0 ) {
	global $wpdb;

	$options = get_option('fbgallery_settings_section');
	$fb_albums_page = $options['fb_albums_page'];
	$query = "SELECT `ID`, `post_parent`, `post_title` FROM `".$wpdb->posts."` WHERE `post_parent` = '".$parent."' AND `post_type` = 'page' AND `post_parent` != '".$fb_albums_page."' ORDER BY `menu_order`" ;
	if($fb_albums_page)
	{
		$items = $wpdb->get_results( "SELECT `ID`, `post_parent`, `post_title` FROM `$wpdb->posts` WHERE `post_parent` = '$parent' AND `post_type` = 'page' AND `post_parent` != '$fb_albums_page' ORDER BY `menu_order`" );
	}
	else
	{
		$items = $wpdb->get_results( "SELECT `ID`, `post_parent`, `post_title` FROM `$wpdb->posts` WHERE `post_parent` = '$parent' AND `post_type` = 'page' ORDER BY `menu_order`" );
	}

	if ( $items ) {
		foreach ( $items as $item ) {
			$pad = str_repeat( '&nbsp;', $level * 3 );
			if ( $item->ID == $default)
				$current = ' selected="selected"';
			else
				$current = '';

			echo "\n\t<option value='$item->ID'$current>$pad " . wp_specialchars($item->post_title) . "</option>";
			fb_parent_dropdown( $default, $item->ID, $level +1 );
		}
	} else {
		return false;
	}
}
function fb_category_dropdown( $default = 0, $parent = 0, $level = 0 ) {
	global $wpdb;

	$args=array(
  'orderby' => 'name',
  'order' => 'ASC',
  'hide_empty' => 0
  );
	$items=get_categories($args);
	if ( $items ) {
		foreach ( $items as $item ) {
			$pad = str_repeat( '&nbsp;', $level * 3 );
			if ( $item->cat_ID == $default)
				$current = ' selected="selected"';
			else
				$current = '';

			echo "\n\t<option value='$item->cat_ID'$current>$pad " . wp_specialchars($item->name) . "</option>";
		}
	} else {
		return false;
	}
}


function fb_days_used() {
	global $wpdb;
	$status = $wpdb->get_row("SHOW TABLE STATUS FROM ".DB_NAME." WHERE `Name` = '".FB_ALBUM_TABLE."'", ARRAY_A);
	$created = $status['Create_time'];
	$days = ceil((time() - strtotime($created)) / (60 * 60 * 24));
	return $days > 2190 || $days < 0 ? 0 : $days;
}

function fb_cron_url() {
	$secret = get_option('fb_secret');
	if(!$secret) {
		$secret = substr(md5(uniqid(rand(), true)), 0, 12);
		update_option('fb_secret', $secret);
	}
	return FB_PLUGIN_URL.'cron.php?secret='.$secret.'&update';
}

//-------------------------//
//--ALBUM/PHOTO-FUNCTIONS--//
//-------------------------//

function fb_get_album($album_id = 0, $user_id = null, $displayed_only = false) {
	global $wpdb;

	$query = 'SELECT * FROM `'.FB_ALBUM_TABLE.'` ';
	$where = '';

	if($album_id || $user_id || $displayed_only)
		$query .= "WHERE ";

	if($album_id) {
		$query .= "`aid` = '$album_id' ";
		$array = $wpdb->get_results($query, ARRAY_A);
		return $array[0];
	}
	if($user_id) {
		if($where) $where .= "AND ";
		$where .= "`owner` = '$user_id' ";
	}
	if($displayed_only) {
		if($where) $where .= "AND ";
		$where .= "`hidden` = 0 ";
	}

//	$query .= $where."ORDER BY `ordinal` DESC";
//	$query .= $where."ORDER BY `ordinal`";
//	$query .= $where."ORDER BY `modified` DESC";
	$query .= $where."ORDER BY `created` DESC";



	$results = $wpdb->get_results($query, ARRAY_A);

	return $results;
}

//-------------------------//
//--LATEST PHOTO-FUNCTIONS--//
//-------------------------//
// This function gets the latest 'n' photos from facebook and stores them locally so it can be used by the photo slider
function fb_latest_photos($count = 24) {


	$photos = fb_get_recent_photos($count);

	if(count($photos) >= $count)
	{
		$photoIndex = rand(0,$count);
	}
	else
	{
		$photoIndex = 0;
	}
		
		foreach($photos as $key=>$photo)
			$photos[$key]['src'] = $photos[$key]['src_big'];
	
	if($photos) {
	
		for($i = 0; $i < count($photos); $i++)
		{
			$locAlbum = fb_get_album($photos[$i]['aid']);
			if(count($locAlbum > 0))
			{
				$photos[$i]['caption'] = htmlspecialchars($locAlbum['name']);
			}
			$uploadDir = wp_upload_dir();
		$fbDir = $uploadDir['basedir'].'/fbgallery';
		$fbURL = $uploadDir['baseurl'].'/fbgallery';
			if(!is_dir($fbDir))
			{
				mkdir($fbDir,0755);
			} 
			$newSrc = $fbDir.'/'.basename($photos[$i]['src']);  
			$newURL = $fbURL.'/'.basename($photos[$i]['src']);  
			copy($photos[$i]['src'],$newSrc);
			$photos[$i]['src'] = $newURL;
			$photos[$i]['srcpath'] = $newSrc;
			$newSrc = $fbDir.'/'.basename($photos[$i]['src_small']);  
			$newURL = $fbURL.'/'.basename($photos[$i]['src_small']);  
			copy($photos[$i]['src_small'],$newSrc);
			$photos[$i]['src_small'] = $newURL;
			$photos[$i]['srcpath_small'] = $newSrc;

		}
		file_put_contents($fbDir.'/fb_recent.json', json_encode($photos));
	}
}

//-------------------------//
//--ALBUM/PHOTO-FUNCTIONS--//
//-------------------------//

// Gets all albums for a particular month
function fb_get_album_by_month($month = 1,$year=2012) {
	global $wpdb;

	$query = 'SELECT * FROM `'.FB_ALBUM_TABLE.'` ';
	if($month == 12)
	{
		$toMonth = 1;
		$toyear = $year+1;
	}
	else
	{
		$toMonth = $month+1;
		$toYear = $year;
	}
	$fromDateStr = $year."-".$month."-01";
	$toDateStr = $toYear."-".$toMonth."-01";
	$query .= "WHERE ";
	$query .= "`created` >= '$fromDateStr' AND `created` < '$toDateStr'";

	$query .= " ORDER BY `created` DESC";

	$results = $wpdb->get_results($query, ARRAY_A);

	return $results;
}

function fb_get_album_id($page_id) {
	global $wpdb;
	return $wpdb->get_var("SELECT `aid` FROM `".FB_ALBUM_TABLE."` WHERE `page_id` = '$page_id'");
}

function fb_update_album_order($order) {
	global $wpdb;
	$order = array_reverse($order);
	foreach($order as $key=>$value) {
		$wpdb->update(FB_ALBUM_TABLE, array('ordinal' => $key), array('aid' => $value));
	}
}

function fb_reset_album_order() {
	global $wpdb;
	$albums = $wpdb->get_results('SELECT `aid` FROM `'.FB_ALBUM_TABLE.'` ORDER BY `modified` ASC', ARRAY_A);
	if(!$albums)
		return false;
	foreach($albums as $key=>$album) {
		$wpdb->update(FB_ALBUM_TABLE, array('ordinal' => $key), array('aid' => $album['aid']));
	}
	return true;
}
// Counts the albums for a particular month used to navigate through the albums by month
function fb_count_albums_by_month($month = 1,$year=2012) {
	global $wpdb;
	

	$query = 'SELECT COUNT(*) FROM `'.FB_ALBUM_TABLE.'` ';
	$where = '';
	if($month == 12)
	{
		$toMonth = 1;
		$toyear = $year+1;
	}
	else
	{
		$toMonth = $month+1;
		$toYear = $year;
	}
	$fromDateStr = $year."-".$month."-01";
	$toDateStr = $toYear."-".$toMonth."-01";
	$query .= "WHERE ";
	$query .= "`created` >= '$fromDateStr' AND `created` < '$toDateStr'";
	$numAlbums = $wpdb->get_var($query);
	return $numAlbums;
}
// Counts the albums for a particular year this is used for navigating through albums by year
function fb_count_albums_by_year($year=2004) { // why 2004? Thats when facebook started.
	global $wpdb;
	

	$query = 'SELECT COUNT(*) FROM `'.FB_ALBUM_TABLE.'` ';
	$where = '';
	if($year < 2004)
	{
		$startYear = 2004;
	}
	else
	{
		if($startYear > date('Y'))
		{
			$startYear = date('Y');
		}
		else
		{
			$startYear = $year;
		}
	}
	$endYear = $startYear+1;
	$fromDateStr = $startYear."-1-01";
	$toDateStr = $endYear."-1-01";
	
	$query .= "WHERE ";
	$query .= "`created` >= '$fromDateStr' AND `created` < '$toDateStr' ORDER BY `modified` ASC";
	$numAlbums = $wpdb->get_var($query);
	return $numAlbums;
}
// Gets album created dates for a particular year
function fb_get_albums_created_by_year($year=2004) { // why 2004? Thats when facebook started.
	global $wpdb;
	

	$query = 'SELECT `created` FROM `'.FB_ALBUM_TABLE.'` ';
	$where = '';
	if($year < 2004)
	{
		$startYear = 2004;
	}
	else
	{
		if($startYear > date('Y'))
		{
			$startYear = date('Y');
		}
		else
		{
			$startYear = $year;
		}
	}
	$endYear = $startYear+1;
	$fromDateStr = $startYear."-1-01";
	$toDateStr = $endYear."-1-01";
	
	$query .= "WHERE ";
	$query .= "`created` >= '$fromDateStr' AND `created` < '$toDateStr' ORDER BY `modified` ASC";
	$results = $wpdb->get_results($query, ARRAY_A);
	return $results;
}

function fbg_first_album_month($year)
{
	$numAlbums = fb_count_albums_by_year($year);
	if($numAlbums > 0)
	{
		$albums = fb_get_albums_created_by_year($year);
		$firstAlbum = $albums[0];
    $datetimeArray = strptime($firstAlbum['created'], "%Y-%m-%d %H:%M:%S");

 		 return $datetimeArray['tm_mon'] + 1;
	}
	else
	{
		return false;
	}
}

// function removes all posts, and database table entries
function fb_remove_all() {
	global $wpdb;
	$pages = $wpdb->get_results('SELECT `page_id` FROM `'.FB_ALBUM_TABLE.'`', ARRAY_A);
	if($pages) {
		foreach($pages as $page) {
				fb_logdebug("fb_remove_all() : Removing : ".$page['page_id']);
			// I would use the wp_delete_post function here but I'm getting a strange error
			$wpdb->query('DELETE FROM `'.FB_POSTS_TABLE."` WHERE `ID` = '{$page['page_id']}'");
		}
	}
	$wpdb->query('DELETE FROM '.FB_ALBUM_TABLE);
	$wpdb->query('DELETE FROM '.FB_PHOTO_TABLE);
	return;
}

function fb_get_next_ordinal() {
	global $wpdb;
	$highest = $wpdb->get_var('SELECT `ordinal` FROM `'.FB_ALBUM_TABLE.'` ORDER BY `ordinal` DESC LIMIT 1');
	return ($highest + 1);
}

function fb_toggle_album_hiding($id) {
	global $wpdb;
	$old = $wpdb->get_row("SELECT `hidden` FROM `".FB_ALBUM_TABLE."` WHERE `aid` = '$id'");
	$new = ($old->hidden == 1) ? 0 : 1;
	$wpdb->update(FB_ALBUM_TABLE, array('hidden' => $new), array('aid' => $id));
	return true;
}

function fb_get_photos($album_id = 0) {
	global $wpdb;

	$query = 'SELECT * FROM `'.FB_PHOTO_TABLE.'` ';
	if($album_id != 0) $query .= "WHERE `aid` = '$album_id' ";
	$query .= "ORDER BY `ordinal` ASC";
	$photos = $wpdb->get_results($query, ARRAY_A);

	return $photos;
}

function fb_get_photo($id, $size = null) {
	global $wpdb;
	$query = 'SELECT * FROM `'.FB_PHOTO_TABLE."` WHERE `pid` = '$id'";
	$photo = $wpdb->get_row($query, ARRAY_A);
	switch ($size) {
		case 'small':
			return $photo['src_small'];
			break;
		case 'thumb':
			return $photo['src'];
			break;
		case 'full':
			return $photo['src_big'];
			break;
		default:
			return $photo;
			break;
	}
}

function fb_get_random_photos($count) {
	global $wpdb;
	$query = "SELECT `".FB_PHOTO_TABLE."`.`link`, `pid`, `src`, `src_big`, `src_small`, `caption`
	          FROM `".FB_PHOTO_TABLE."`, `".FB_ALBUM_TABLE."`
	          WHERE `".FB_PHOTO_TABLE."`.`aid` = `".FB_ALBUM_TABLE."`.`aid` AND `".FB_ALBUM_TABLE."`.`hidden` = 0
	          ORDER BY rand() LIMIT ".$count;
	$photos = $wpdb->get_results($query, ARRAY_A);
	for($i = 0; $i < count($photos); $i++) {
		$photos[$i]['link'] = fb_get_photo_link($photos[$i]['pid']);
	}
	return $photos;
}

function fb_get_recent_photos($count) {
	global $wpdb;
	$query = "SELECT `".FB_PHOTO_TABLE."`.`link`, `pid`, `src`, `src_big`, `src_small`, `caption`,`".FB_PHOTO_TABLE."`.`aid` 
	          FROM `".FB_PHOTO_TABLE."`, `".FB_ALBUM_TABLE."`
	          WHERE `".FB_PHOTO_TABLE."`.`aid` = `".FB_ALBUM_TABLE."`.`aid` AND `".FB_ALBUM_TABLE."`.`hidden` = 0
	          ORDER BY `".FB_PHOTO_TABLE."`.`created` DESC LIMIT ".$count;

	$photos = $wpdb->get_results($query, ARRAY_A);
	for($i = 0; $i < count($photos); $i++) {
		$photos[$i]['link'] = fb_get_photo_link($photos[$i]['pid']);
	}
		foreach($photos as $key=>$photo)
			$photos[$key]['src'] = $photos[$key]['src_big'];
	return $photos;
}


function fb_get_photo_link($photo)	{ // accepts either photo id or array of photo
	if(!is_array($photo)) {
		$photo = fb_get_photo($photo);
	}
	$album = fb_get_album($photo['aid']);
	$page_id = $album['page_id'];
	$page_link = get_permalink($page_id);
	$options = get_option('fbgallery_settings_section');
	$number_cols = $options['fb_number_cols'];
	$number_rows = $options['fb_number_rows'];
	if($number_rows == 0)
		$number_rows = ceil($photo_count / $number_cols);
	$photos_per_page = $number_cols * $number_rows;
	$album_p = $photos_per_page == 0 ? 1 : ceil($photo['ordinal'] / $photos_per_page);
	switch (get_option('fb_style')) {
		case 'lightbox':
		case 'colorbox':
			$page_link .= strstr($page_link, '?') ? '&' : '?';
			$page_link .= 'album_p='.$album_p;
			$page_link .= '#photo'.($photo['ordinal']);
			break;
		case 'embedded':
			$page_link .= strstr($page_link, '?') ? '&' : '?';
			$page_link .= 'photo='.($photo['ordinal']);
			break;
	}
	return htmlentities($page_link);
}

function fb_hidden_pages($array = array()) {
	global $wpdb;

	if(get_option('fb_hide_pages') == 1) {
		$query = 'SELECT `page_id` FROM `'.FB_ALBUM_TABLE.'`';
	} else {
		$query = 'SELECT `page_id` FROM `'.FB_ALBUM_TABLE.'` WHERE `hidden` = 1';
	}

	$results = $wpdb->get_results($query, ARRAY_A);
	if(!$results) return $array;

	foreach($results as $result) {
		$array[] = $result['page_id'];
	}
	return $array;
}



//------------------------//
//--INTEGRATE-IT-INTO-WP--//
//------------------------//

add_filter('wp_list_pages_excludes', 'fb_hidden_pages');
add_action('activate_fbgallery/fbgallery.php', 'fb_initialize');
add_action('plugin_action_links_fbgallery/fbgallery.php', 'fb_action_link');
add_action('admin_menu', 'fb_add_pages');
add_filter('the_content', 'fb_display');
add_action('widgets_init', 'fb_widget_init');
add_action('template_redirect', 'fb_display_scripts');
add_action('wp_print_styles', 'fb_display_styles');
add_action('wp_ajax_fbgallery', 'fb_ajax_handler');
add_action('widgets_init', create_function('', 'return register_widget("WP_FBG_Photos_Widget");') );
add_action('widgets_init', create_function('', 'return register_widget("WP_FBG_Thumbnail_Widget");') );


//---------------------//
//--GENERAL-FUNCTIONS--//
//---------------------//

function array_slice_preserve_keys($array, $offset, $length = null) {
	// PHP >= 5.0.2 is able to do this itself
	//if((int)str_replace('.', '', phpversion()) >= 502)
		//return(array_slice($array, $offset, $length, true));

	// prepare input variables
	$result = array();
	$i = 0;
	if($offset < 0)
		$offset = count($array) + $offset;
	if($length > 0)
		$endOffset = $offset + $length;
	else if($length < 0)
		$endOffset = count($array) + $length;
	else
		$endOffset = count($array);

	// collect elements
	foreach($array as $key=>$value)
	{
		if($i >= $offset && $i < $endOffset)
			$result[$key] = $value;
		$i++;
	}

	// return
	return($result);
}

if(!function_exists('file_put_contents')) {
	function file_put_contents($n,$d) {
		$f=@fopen($n,"w");
		if (!$f) {
		 return false;
		} else {
		 fwrite($f,$d);
		 fclose($f);
		 return true;
		}
	}
}
?>