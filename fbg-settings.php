<?php

/*
FB Gallery Settings Page
*/

// get facebook authorization token
include_once('fbgallery.php');
//include_once('lib/facebook.php');

// authorize session
//if(isset($_POST['activate-facebook'])) {
//	$facebook->get_auth_session($_POST['activate-facebook']);
//}

// remove the user
if(isset($_GET['deactivate-facebook']) ) {
	
$facebookAPI = new FacebookAPI;
	$facebookAPI->remove_user();
} 
$settingsOptions = get_option('fbgallery_settings_section');
$options = get_option('fbgallery_plugin_options');

$this_page = $_SERVER['PHP_SELF'].'?page='.$_GET['page'];

// get styles
$styles = fb_get_styles();

// update options if form is submitted


//START CHANGES HERE
  //Adding a Fan Page
  if ( is_numeric($_POST['fb_fan_page_id']) ){
	$fan_page_id=$_POST['fb_fan_page_id'];
	// We're going to add the fan pages by replicating
       // one of the sessions that fotobook already uses
	// but replacing some of the details
  }
	$options = get_option('fbgallery_plugin_options');
  
  function setting_string_fn($args = array()) {
	extract( $args );
	$options = get_option('fbgallery_plugin_options');
	
	if(($fbgid == 'fb_fan_page_url') || ($fbgid == 'fb_app_secret'))
	{
		$fieldSize = 40;
	}
	else
	{
		$fieldSize = 30;
	}
		
  echo "<input id='plugin_text_string' name='fbgallery_plugin_options[".$fbgid."]' size='".$fieldSize."' type='text' value='".esc_textarea($options[$fbgid])."' />";
	echo '<span class="description">'.$fgbOptionHelp.'</span>';
	if($fbgid == 'fb_app_secret' )
	{
		echo("</br><hr/>");
	}
}
function setting_textfn($args = array()) {
	extract( $args );
	$options = get_option('fbgallery_settings_section');
//	fb_logdebug('setting_textfn : args : '.print_r($args,true));
//	fb_logdebug('setting_textfn : options : '.print_r($options,true));
//	fb_logdebug('setting_textfn : fbgid : '.$fbgid);
	if ($fbgid == "fb_albums_category")
	{
				echo "<select class='select' name='fbgallery_settings_section[".$fbgid."]'>\n";
//				if(!fb_albums_page_is_set())
//				{
//						echo "\n".'<option value="0" selected>Please select...</option>'."\n";
//				}
				fb_category_dropdown($options[$fbgid]);
				echo "\n</select>\n";
	}
	else if ($fbgid == "fb_albums_page")
	{
				echo "<select class='select' name='fbgallery_settings_section[".$fbgid."]'>\n";
				if(!fb_albums_page_is_set())
				{
						echo "\n".'<option value="0" selected>Please select...</option>'."\n";
				}
				fb_parent_dropdown($options[$fbgid]);
				echo "\n</select>\n";
	}
	else if ($fbgid == "fb_albums_content_page")
	{
//	fb_logdebug('setting_textfn : Here');
				echo "<select class='select' name='fbgallery_settings_section[".$fbgid."]'>\n";
				if(!fb_album_content_page_is_set())
				{
						echo "\n".'<option value="0" selected>Please select...</option>'."\n";
				}
				fb_parent_dropdown($options[$fbgid]);
				echo "\n</select>\n";
	}
else if ($fbgid == "fb_photo_display")
	{
//	fb_logdebug('setting_textfn : Here');
				echo "<select class='select' name='fbgallery_settings_section[".$fbgid."]'>\n";
				if(!fb_photo_content_page_is_set())
				{
						echo "\n".'<option value="0" selected>Please select...</option>'."\n";
				}
				fb_parent_dropdown($options[$fbgid]);
				echo "\n</select>\n";
	}
/*	else if($fbgid == "fb_style")
	{
			$styles = fb_get_styles();
			echo "<select class='select' name='fbgallery_settings_section[".$fbgid."]'>\n";
			foreach($styles as $style)
			{
				$selected = $style == $options[$fbgid] ? ' selected' : null;							
				echo "\n".'<option value="'.$style.'" '.$selected.">".$style."</option>\n";
			}
			echo "\n</select>\n";
	}*/
	else if($fbgid == "fb_use_cache")
	{
			if((isset($options[$fbgid])) && ($options[$fbgid] == 'useCache'))
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="useCache" checked="checked" />';
			}
			else
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="useCache" />';
			}
	}
	else if($fbgid == "fb_use_album_content_page")
	{
			if((isset($options[$fbgid])) && ($options[$fbgid] == 'use_album_content_page'))
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="use_album_content_page" checked="checked" />';
			}
			else
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="use_album_content_page" />';
			}
	}
	else if($fbgid == "fb_date_pagination_on")
	{
			if((isset($options[$fbgid])) && ($options[$fbgid] == 'datePageOn'))
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="datePageOn" checked="checked" />';
			}
			else
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="datePageOn" />';
			}
	}
	else if($fbgid == "fb_debug_on")
	{
			if((isset($options[$fbgid])) && ($options[$fbgid] == 'debugOn'))
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="debugOn" checked="checked" />';
			}
			else
			{
  			echo '<input id="plugin_text_string" name="fbgallery_settings_section['.$fbgid.']" type="checkbox" value="debugOn" />';
			}
	}
	else
	{
		if(!$options[$fbgid])
		{
			if($fbgid =='fb_albums_per_page')
			{
				$defaultValue = 30;
			}
			if($fbgid =='fb_number_rows')
			{
				$defaultValue = 10;
			}
			if($fbgid =='fb_number_cols')
			{
				$defaultValue = 3;
			}
			if($fbgid =='fb_number_cols')
			{
				$defaultValue = 3;
			}
			if($fbgid =='fb_thumb_size')
			{
				$defaultValue = 200;
			}
			if($fbgid =='fb_timout')
			{
				$defaultValue = 90;
			}
			if($fbgid =='fb_num_to_cache')
			{
				$defaultValue = 25;
			}
			if($fbgid =='fb_max_albums')
			{
				$defaultValue = 0;
			}
		}
		else
		{
			$defaultValue = $options[$fbgid];
		}
  	echo "<input id='plugin_text_string' name='fbgallery_settings_section[".$fbgid."]' size='".$fieldSize."' type='text' value='".esc_textarea($defaultValue)."' />";
	}
		echo '<span class="description">'.$fgbOptionHelp.'</span>';
	echo "</td><td>";
 	echo '<span class="description">'.$rcOptionHelp.'</span>';

}
function plugin_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
//	$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);	
	return $input; // return validated input
}
function  section_access_fn($desc) {  
    echo "<p>Configure Access to Facebook</p>";  
}

function  section_text_fn($desc) {  
    echo "<p>FB Gallery Display Option</p>";  
}
 

  function registerFBGSettings(){
  	
	register_setting('fbgallery_plugin_options', 'fbgallery_plugin_options', 'plugin_options_validate' );
	add_settings_section('fbgallery_main_section', 'Access Settings', 'section_access_fn','fbgalleryplugin');
	add_settings_field('fbgplugin_fan_page_url', 'Your Facebook URL', 'setting_string_fn', 'fbgalleryplugin', 'fbgallery_main_section', array( 'fbgid' => 'fb_fan_page_url', 'fgbOptionHelp' =>'Could be your Facebook Profile, Facebook Page, Facebook Group' ));
	add_settings_field('fbgplugin_app_id', 'Your Facebook App ID', 'setting_string_fn', 'fbgalleryplugin', 'fbgallery_main_section', array( 'fbgid' => 'fb_app_id', 'fgbOptionHelp' =>'The App ID for the facebook app you created.' ));
	add_settings_field('fbgplugin_app_secret', 'Your Facebook App Secret', 'setting_string_fn', 'fbgalleryplugin', 'fbgallery_main_section', array( 'fbgid' => 'fb_app_secret', 'fgbOptionHelp' =>'The App Secret for the facebook app you created' ));

	register_setting( 'fbgallery_settings_section', 'fbgallery_settings_section', 'plugin_options_validate' );
	add_settings_section('fbgallery_option_section', 'Display Settings', 'section_text_fn','fbgalleryplugin_option');
	add_settings_field('fbgplugin__albums_page', 'Album Page', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_albums_page', 'fgbOptionHelp' =>'Select the page you want to use to display the photo albums' ));
	add_settings_field('fbgplugin_date_pagination', 'Display Albums by Date', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_date_pagination_on', 'fgbOptionHelp' =>' Displaying albums by date will enable pagination through albums by date' ));
	add_settings_field('fbgplugin_albums_per_page', 'Albums Per Page', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_albums_per_page', 'fgbOptionHelp' =>' Number of albums to display on each page of the main gallery. Set to \'0\' to show all.' ));
	add_settings_field('fbgplugin_albums_category', 'Album Category', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_albums_category', 'fgbOptionHelp' =>' Each Album is displayed as a Post. Select the category for all album posts' ));
	add_settings_field('fbgplugin_number_rows', 'Number of Rows', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_number_rows', 'fgbOptionHelp' =>' Set to \'0\' to display all.' ));
	add_settings_field('fbgplugin_number_cols', 'Number of Columns', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_number_cols', 'fgbOptionHelp' =>' The number of columns of images.' ));
//	add_settings_field('fbgplugin_style', 'Select the style', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_style', 'fgbOptionHelp' =>' Select the style you want to use to display the albums.' ));
	add_settings_field('fbgplugin_thumb_size', 'Thumbnail size', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_thumb_size', 'fgbOptionHelp' =>' The maximum size of the thumbnail. The default is 130px.' ));
	add_settings_field('fbgplugin_max_albums', 'Maximium Albums to Retrieve', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_max_albums', 'fgbOptionHelp' =>' If you do not want to retrieve all your facebook albums you can set a maximimum here, leaving the field blank or zero will retrieve all albums' ));
	add_settings_field('fbgplugin_update_timeout', 'Update Timeout', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_timeout', 'fgbOptionHelp' =>' When retrieving albums from Facebook, if no activity is detected for the timeout period (seconds) specified, the function will be terminated' ));
	add_settings_field('fbgplugin_use_cache', 'Use Cached Images', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_use_cache', 'fgbOptionHelp' =>' Use cached copies of images for the widget instead of retrieving them each time from Facebook, which can be a bit slow' ));
	add_settings_field('fbgplugin_num_to_cache', 'Number of Images to Cache', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_num_to_cache', 'fgbOptionHelp' =>' Number of images that will be cached (Max 50)' ));
	add_settings_field('fbgplugin_debug', 'Turn on Debug', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_debug_on', 'fgbOptionHelp' =>' Turning debug on will write debug information to a debug directory under the plugin directory' ));
	add_settings_field('fbgplugin_use_album_content_page', 'Use Album Content Page', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_use_album_content_page', 'fgbOptionHelp' =>'By default fbgallery will display the album content as a post, selecting this option will display the contents dynamically' ));
	add_settings_field('fbgplugin_photos', 'Photos Page', 'setting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_photo_display', 'fgbOptionHelp' =>' Select the page you have configued to display album content. It must contain the shortcode fb_album_content' ));
//fb_logdebug("regusterFBGSettings: B4 Album content");
//	add_settings_field('fbgplugin_album_content_page', 'Album Content Page', 'seting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_albums_content_page', 'fgbOptionHelp' =>'Select the page you have configued to display album content. It must contain the shortcode fb_album_content' ));
//	add_settings_field('fbgplugin_photos_page', 'Album Content Page', 'seting_textfn', 'fbgalleryplugin_option', 'fbgallery_option_section', array( 'fbgid' => 'fb_photos_page', 'fgbOptionHelp' =>'Select the page you have configued to display album content. It must contain the shortcode fb_album_content' ));

}

function SetupFBGSettings()
{
	if ( is_admin() ){ // admin actions

		/* Call the html code */
		add_action('admin_menu', 'fbgAdminMenu');
		function fbgAdminMenu() {
			
			$optionsHook = add_options_page(__('FB Gallery Settings', 'fbgalleryplugin'),__('FB Gallery Settings', 'fbgalleryplugin'), 'manage_options',
			'fbgalleryplugin', 'fbGalleryAdminHtmlPage');
			//call register settings function
			add_action( 'admin_init', 'registerFBGSettings' );

	}
	}
}	
function fbGalleryAdminHtmlPage() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'fbg_options'; ?>

		<h2>FB Gallery</h2>
		<h2 class="nav-tab-wrapper">  
    	<a href="?page=fbgalleryplugin&tab=fbg_options" class="nav-tab <?php echo $active_tab == 'fbg_options' ? 'nav-tab-active' : ''; ?>">FB Gallery Settings</a>  
    	<a href="?page=fbgalleryplugin&tab=fbg_manage" class="nav-tab <?php echo $active_tab == 'fbg_manage' ? 'nav-tab-active' : ''; ?>">Manage Albums</a>  
		</h2>
		<!-- Some optional text here explaining the overall purpose of the options and what they relate to etc. -->
  	<?php if( $active_tab == 'fbg_options' ) {  
			echo '<div class="wrap">'."\n";
				echo '<div id="fb-panel">'."\n";
						fb_info_box();
					echo '<div style="float:left;">';
						echo '<form action="options.php" method="post">'."\n";
	 					settings_fields('fbgallery_plugin_options');
						do_settings_sections('fbgalleryplugin');
						echo '<p class="submit">'."\n";
						echo '<input name="Submit" type="submit" class="button-primary" value="Save Changes"'."\n</p>\n</form>\n";
						// Facebook Authentication Code
					echo '<h3>Facebook Authorization</h3>';
						$options = get_option('fbgallery_plugin_options');
  					$fb_fan_page_url		= $options['fb_fan_page_url'];
						$fb_app_id					= $options['fb_app_id'];
						$fb_app_secret			= $options['fb_app_secret'];
						$fb_fan_page_id			= $options['fb_fan_page_id'];
						$fbAppAuthToken			= get_option('fbAppAuthToken');
						$fbAppAuthUser			= get_option('fbAppAuthUser');
						if($fb_app_secret=='') {
       				echo "\n<p><b>Authorize Your FaceBook Account</b>. Please save your settings and come back here to Authorize your account.</p>";
         		} 
         		else {
        			$facebook = new FBG_Facebook(array( 'appId' => $fb_app_id, 'secret' => $fb_app_secret, 'cookie' => true)); 
         			$user = $facebook->getUser();
         			if($fbAppAuthUser >0)
 							{
            		$targetStr = "\nYour FaceBook Account has been authorized. User ID: ".$fbAppAuthUser."</BR>"
            								."\nIf you are experiencing access issues</BR>"
            								."You can Re-"; 
            	} 
            	else
            	{
            		$targetStr = "\n";
            	}
            	$baseUrl = admin_url().'options-general.php?page=fbgalleryplugin';
            	$loginUrl   = $facebook->getLoginUrl(
            		array(
              		'scope'         => 'user_photos,manage_pages,publish_stream,offline_access,read_stream',
               		'redirect_uri'  => $baseUrl
            		)
    					);
							$targetStr = $targetStr.'<a target="_blank" href="'.$loginUrl.'">Authorize Your FaceBook Account </a>';
						}	
							// Facebook sent as back a code that is all that is required for now, the facebook class will take care of the rest
							// If we have an access token we are authorised from now on, Just need to store the access token for futuure sessions.
	            if ( isset($_GET['code']) && $_GET['code']!='')
	            { 
								$at = $_GET['code'];
//fb_logdebug('SetupFBGSettings :  the $at : '.$at);
//                $response  = wp_remote_get('https://graph.facebook.com/oauth/access_token?client_id='.$fb_app_id.'&redirect_uri='.$baseUrl.'&client_secret='.$fb_app_secret.'&code='.$at); 
//fb_logdebug('SetupFBGSettings : $response : '.print_r($response,true));
//                if ((is_object($response) && isset($response->errors))) { prr($response); die();}
//                parse_str($response['body'], $params); $at = $params['access_token'];
$fbuid = $facebook->getUser();
$at = $facebook->getAccessToken();
              $response  = wp_remote_get('https://graph.facebook.com/oauth/access_token?client_secret='.$fb_app_secret.'&client_id='.$fb_app_id.'&grant_type=fb_exchange_token&fb_exchange_token='.$at); 
// fb_logdebug('SetupFBGSettings : 2nd $response : '.print_r($response,true));
               if ((is_object($response) && isset($response->errors))) { prr($response); die();}
                     parse_str($response['body'], $params); $at = $params['access_token']; $fbAppAuthToken = $at; 
//fb_logdebug('SetupFBGSettings : $fbAppAuthToken : '.$fbAppAuthToken);

                    $facebook->setAccessToken($fbAppAuthToken); 
//fb_logdebug('SetupFBGSettings : After set access token');
										$user = $facebook->getUser();
                    if ($user) {
//fb_logdebug('SetupFBGSettings : user : '.$user);
 								    $fbPgID = $fb_fan_page_url; 
 								    if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);
             
                       try { $page_id = $fbPgID; $page_info = $facebook->api("/$page_id?fields=access_token");
                            if( !empty($page_info['access_token']) ) { 
                            	$fbAppPageAuthToken = $page_info['access_token']; 
                            }
                        } catch (FacebookApiException $e) { $errMsg = $e->getMessage();
//fb_logdebug('SetupFBGSettings : exception : '.$errMsg);
                        	
                          if ( stripos($errMsg, 'Unknown fields: access_token')!==false) $fbAppPageAuthToken = $fbAppAuthToken; else { echo 'Error:',  $errMsg, "\n"; die(); }
                        }
                    }else echo "Please login to Facebook";                
							$accessToken = $facebook->getAccessToken();
//fb_logdebug('SetupFBGSettings : $accessToken : '.$accessToken);
							if($accessToken)
							{
									$locAccessToken = $accessToken;
//fb_logdebug('SetupFBGSettings : $locAccessToken : '.$locAccessToken);
									$locPos = strpos($locAccessToken,'authtoken');
									if($locPos !== false)
									{
										$subAccessToken = substr($locAccessToken,$locPos,strlen('authtoken'));
										$fbAppAuthToken	= $subAccessToken;
									}
									else
									{
										$fbAppAuthToken	= $accessToken;
									}
//fb_logdebug('SetupFBGSettings : update $fbAppAuthToken : '.$fbAppAuthToken);
									update_option('fbAppAuthToken',$fbAppAuthToken);
									$fbAppAuthUser = $user;
									update_option('fbAppAuthUser',$fbAppAuthUser);
									update_option('fbAppPageAuthToken',$fbAppPageAuthToken);
            			echo "Your FaceBook Account has been authorized. User ID: ". $fbAppAuthUser; 
            			if((strlen($targetStr) > 0) && (strpos($targetStr,'Authorize Your') > 0))
            			{
            				echo "\n<p>If you are experiencing access issues</p>";
          				$targetStr = str_replace('Authorize Your','You can Re-Authorize Your',$targetStr);
            				echo "\n<p>".$targetStr."</p>";
            			}
								}
								else
								{
									fb_logdebug("options-fotobook : No accesstoken : ");	
								}
							}
							else
							{
								echo "\n<p>".$targetStr."</p>";
							}
							if($fbAppAuthUser >0) : ?>
							<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="get">
							<input type="hidden" name="deactivate-facebook" value="1">
							<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
							<input type="submit" class="button-secondary" value="Remove Account" onclick="return confirm('Removing an account also removes all of the photos associated with the account.  Would you like to continue?')">
							</form>
						<?php endif; 
					echo("</br><hr/>");
							// Display Settings						 
						echo '<form action="options.php" method="post">'."\n";
							settings_fields('fbgallery_settings_section');
	 					do_settings_sections('fbgalleryplugin_option'); 	 		
						echo '<p class="submit">'."\n";
						echo '<input name="Submit" type="submit" class="button-primary" value="Save Changes"'."\n</p>\n</form>\n";
					echo "</div>\n";
				echo "<table>\n"
							."<tr>\n"
							."<th scope='row'>Cron URL</th>"
							.'<td>To setup automatic updates of your albums, create a cron job that regularly loads the following URL.	If you are unsure how to setup a cron job, <a href="http://www.google.com/search?q=cron">Google</a> is your friend.<br /> <small>'.fb_cron_url().'</small></td>'."\n"
							."</tr>\n"
							."</table>\n";
					echo "</div>\n";
				echo "</div>\n";
 
					}
					else
					{
						include("fbg-manage.php");
					}
						?>
		<!-- ?php do_settings_sections(__FILE__); ? -->
	</div>
<?php
}
?>