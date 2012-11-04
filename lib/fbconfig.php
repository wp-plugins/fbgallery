<?php
function SetupFBConnection()
{
    fb_logdebug("fbconfig : SetupFBConnection : Start");
    $fbconfig['appid' ]  = get_option('fb_app_id'); //$app_id;
    $fbconfig['secret']  = get_option('fb_app_secret'); //$app_secret;

    // Create our Application instance.
    $facebook = new FBG_Facebook(array(
      'appId'  => $fbconfig['appid'],
      'secret' => $fbconfig['secret'],
      'cookie' => true,
    ));
		fb_logdebug("fbconfig : Start : Auth Token : ".get_option('fbAppAuthToken'));
		if(get_option('fbAppAuthToken'))
		{
			fb_logdebug("fbconfig : Start : setting access token");
    	$facebook -> setAccessToken(get_option('fbAppAuthToken'));
  	}

 
    // We may or may not have this data based on a $_GET or $_COOKIE based session.
    // If we get a session here, it means we found a correctly signed session using
    // the Application Secret only Facebook and the Application know. We dont know
    // if it is still valid until we make an API call using the session. A session
    // can become invalid if it has already expired (should not be getting the
    // session back in this case) or if the user logged out of Facebook.
		//    $session = $facebook->getSession();
 
    // Session based graph API call.
    $uid = $facebook->getUser();
    fb_logdebug("fbconfig : $uid : ".$uid);
  	if ($uid) 
  	{
      try 
      {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
//    	 	fb_logdebug("fbconfig : user_profile : ".print_r($user_profile,true));
//        $other_profile = $facebook->api('/traveldaily');
//     		fb_logdebug("fbconfig : other_profile : ".print_r($other_profile,true));
//     		fb_logdebug("fbconfig : SetupFBConnection : Next");
       
      }       
      catch (FacebookApiException $e) {
          d($e);
      }
      fb_logdebug("fbconfig : SetupFBConnection : OK");
  	}
 
    fb_logdebug("fbconfig : SetupFBConnection : Finish");
    return $facebook;
 }
    function d($d)
    {
     	fb_logdebug("fbconfig : $d");
    }
   fb_logdebug("fbconfig : Finish");
//if(is_null($facebook->getUser()))
//{
//	$graphfacebook->getLoginUrl(array('req_perms' => 'user_status,publish_stream,user_photos'));
//        header("Location:{$graphfacebook->getLoginUrl(array('req_perms' => 'user_status,publish_stream,user_photos'))}");
//        exit;
//}
?>