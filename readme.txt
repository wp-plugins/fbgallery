=== FBGallery ===
Contributors: caevan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YSM3KMT3B5AQE 
Tags: facebook, photos, images, gallery, fbgallery, import, widget, media,graph
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 3.4.2
License: GPLv2 or later

FBGallery allows you to import all of your Facebook photo albums for use in your WordPress installation, while keeping the image storage on Facebook.

== Description ==

FBGallery allows you to import all of your Facebook photo albums for use in your WordPress installation, while keeping the image storage on Facebook.
   
**Features:**

* Able to handle Facebook accouts with a large number of albums (tested with over 1500 albums)
* Posts are created for each album, making the post captions searchable
* Albums are displayed by year an month
* Sidebar widget for displaying a slideshow of recent photos
* Sidebar widget for displaying random or recent photo, thumbnails
* Slideshow of recent photos can be incorporated into a page
* Recent images can be cached for faster load times (Facebook can be a bit slow)
* Widgets use Ajax
* Insert individual photos into posts/pages
* Easy-to-use Ajax album management panel



== Installation ==

1. Download and unzip the most recent version of FBgallery
2. Upload the entire FBgallery folder to /your-wordpress-install-dir/wp-content/plugins/
3. Now login you your wordpress admin and select Plugins, you should see the FBGallery plugin listed amongst your other installed plugins
4. Click on Activate to activate the plugin
5. Once the plugin is activated you should now have FBGallery Settings under the settings menu.
6. Select FBGallery Settings and make sure you are on the FBGallery Settings Tab
7. In order for you to be able to start retrieving your images from your Facebook account, you need to first authorize the plugin. Follow the instructions [here](http://www.amkd.com.au/wordpress/fbgallery-facebook-settings-setup/76) once completed go to the next step.
8. If you have successfully completed the instructions in FBGallery: Facebook settings and setup, there are a couple more configuration items. Make sure you are on the FBGallery Settings Tab
9. Under Display Settings  heading the first option you need to set is, Album page. This is the page used by FBGallery to display the albums. You can either create a custom page or select one you already have.
10. Next Albums per page sets the maximum number of albums FBGallery will display on a single page. Since FBGallery displays albums by the year and month they were created, it will only display the albums for that particular month.
11. As FBGallery retrieves the albums from Facebook, it creates a post with the contents of each album. The Album Category is the category that all album posts will be given.
12. If you have a lot of albums you can set Maximum Albums To Retrieve, this will limit FBGallery to retrieve from Facebook the number of albums specified. If this value is set to 0 or blank all albums will be retrieved.
13. Update Timeout will terminate the function retrieving albums from Facebook if no activity has occurred for more than the seconds specified. While this is not usually necessary we can not control the speed of the connections between your web host and Facebook, we have noticed in some cases this can be quite slow and the connection timeout, rather than leave the application hanging, the timeout facility will allow you to manually resume album retrieval, by clicking on Get All Albums
14. Use Cached Images will store a specified number of photos from Facebook in your wp-content/uploads directory for use by the widgets, this can be used if you notice slow load times which can be caused by a slow connection to Facebook.
15. Number of Images to Cache sets the number of images you would like to cache this should match number you set for your Slideshow and Thumbnail widgets.
16. Turn on Debug this should only be turned on if it advised by support or you want to delve into the code yourself.
17. Save settings and you are now ready to retrieve your albums from Facebook
18. Click on the Manage Albums Tab
19. You should see 4 buttons Get Albums, Get All Albums, Order By Date and Remove Albums, it will also tell you how many albums you have on Facebook and approximately how long it should take to retrieve the albums (based on approximately 1 second per album).
20. Get Albums will retrieve your albums from Facebook. If this is the first time, it will retrieve all your albums unless you have specified a limit to the number of albums. After the first time Get Albums will only retrieve new or updated albums. If an album has been updated on Facebook it will replace the current album information stored in FBGallery. Once Get Albums has been initiated a progress bar and elapsed time counter should appear. On successful completion a thumbnail image of each album along with the album description will appear in the area below.
21. Get All Albums will get all your albums
22. Sort By Date will sort the albums displayed by date
23. Remove albums will remove all albums and their related posts from FBgallery.

To upgrade simply replace the old Fotobook directory with the newest version.  Re-import all of your albums to complete the upgrade.

== Frequently Asked Questions ==

= Will FBGallery coexist with Fotobook =

This has not been tested, we do not recommend having both active, FBGallery does use it's own database tables so it will not overwrite your fotobook installation


To display a slideshow of recent photos in a page template use

fbg_photos_slider(height, width);
Note: Height and width should be no bigger than the images you will be displaying in the slideshow, try to maintain the aspect ration of the images.

*Examples*

<?php fbg_photos_slider(200, 300); ?>

= I would like to view the images with a Lightbox plugin. =
You will need to install [jQuery Lightbox](http://wordpress.org/extend/plugins/jquery-lightbox-balupton-edition/)
FBGallery has been coded to have the appropriate tags to work with jQuery Lightbox. 

== Screenshots ==

1. **FB Gallery Setting 1** - Facebook setting. 
2. **FB Gallery Setting 2** - Display setting. 
3. **Manage Albums** - Manage Albums.
4. **Albums Display** - Albums Display.
5. **Album Contents** - Album Contents.

== Change Log ==
= 1.5 =
* Fixed pagination isssue on album page, when selecting month with no albums.
= 1.4 =
* Removed debug lines from album-main.php

= 1.3 =
* Major bug fix media page not being displayed

= 1.2 =
* Major bug fix release incorrect category being assigned to album posts.

= 1.1 =
* Minor bug release add timeout feature and cached images.

= 1.0 =
* First release

== Upgrade Notice ==
= 1.5 = 
Fixed pagination isssue on album page. Pagination by date is now optional, by default it is off. If you are using it you will have to turn it back on in the settings.
= 1.4 =
Corrects an issue where the album page was not loading
= 1.3 =
Corrects an issue where the media page was not being displayed
= 1.2 =
Upgrade otherwise album post may not display
= 1.1 =
Added timeout feature and cached images.
= 1.0 =
First release

== Donate ==
If you are satisfied with your hosting and want to help out, you can send some cash over [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YSM3KMT3B5AQE).

