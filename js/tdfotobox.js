	var currentIndex=1;
 	var fadeDuration=3000;
	var slideDuration=8000;

	function FBGetPhotoSlide(theWidth, theHeight)
	{
    jQuery('#fbg-slide').append( '<div id="slider-loadmask"style="text-align: center;"></div>'); 
		jQuery.get(
									FBGAjax.ajaxurl,
									{
										action: 'FBGPhotoSlide',
										width : theWidth,
										height: theHeight,
										source: 'content',
										FBGPhotoSlideNonce: FBGAjax.FBGPhotoSlideNonce
									},
									function(data){
	    	  							jQuery('#slider').remove();
	    	  							var locHeight = jQuery('#fbg-slide').height(); // always crop the height of the image to the height of the div container
											jQuery('#fbg-slide').append('<ul id="slider" class="slideshow"></ul>');
	   	  					jQuery.each(data,function(i,photos){
											jQuery('#slider').append(
    											jQuery('<li>').append(
        											jQuery('<a>').attr('href',photos.link).append(
            										jQuery('<img>').attr({
        																	src : photos.src,
  																				alt: photos.src,
 																	 				title: photos.caption,
 																	 				width: photos.width,
 																	 				height:locHeight}))));												
 										});
 									}, "jsonp")	
 								.success(function() {	
// 									var locli = jQuery("'ul.slideshow li:nth-child("+currentIndex+")'");
 									jQuery('#slider-loadmask').remove();
									jQuery("'ul.slideshow li:nth-child("+currentIndex+")'").addClass('show').animate({opacity: 1.0}, fadeDuration);
									var timer = setInterval('nextSlide()',slideDuration);
					  		})
								.error(function( jqXHR, textStatus, errorThrown) {
       						console.log("error " + textStatus);
        					console.log("errorThrown " + errorThrown);
        					console.log("incoming Text " + jqXHR.responseText);
	       					console.log("contents Text " + jqXHR.contents);
									console.log("get XMLHttpRequest= "+XMLHttpRequest.responseText);
								}).complete(function(){
									jQuery('#fbg-slide').fadeIn('slow'); 
 									jQuery('#fbg-slide').append("<div class='fbg-slide-title'><h2>"+jQuery("'ul.slideshow li:nth-child("+currentIndex+")'").find("img:first").attr("title")+"</h2></div>");
							  });
	}
	function FBGetWidgetPhotoSlide(theWidth, theHeight)
	{
    jQuery('#fbg-widget-slide').append( '<div id="loadmask"style="text-align: center;"></div>'); 
		jQuery.get(
									FBGAjax.ajaxurl,
									{
										action: 'FBGPhotoSlide',
										width : theWidth,
										height: theHeight,
										source:	'widget',
										FBGPhotoSlideNonce: FBGAjax.FBGPhotoSlideNonce
									},
									function(data){
	    	  							jQuery('#widget-slider').remove();
	    	  							var locHeight = jQuery('#fbg-widget-slide').height(); // always crop the height of the image to the height of the div container
											jQuery('#fbg-widget-slide').append('<ul id="widget-slider" class="widget-slideshow"></ul>');
	   	  					jQuery.each(data,function(i,photos){
											jQuery('#widget-slider').append(
    											jQuery('<li>').append(
        											jQuery('<a>').attr('href',photos.link).append(
            										jQuery('<img>').attr({
        																	src : photos.src,
  																				alt: photos.src,
 																	 				title: photos.caption,
 																	 				width: photos.width,
 																	 				height:locHeight}))));
 												jQuery('#widget-slider').css({'height':locHeight});											
 										});
 									}, "jsonp")	
 								.success(function() {	
 									jQuery('#loadmask').remove();
 									jQuery('#fbg-widget-slide').append("<div class='fbg-slideshow-title'><h2>"+jQuery("'ul.widget-slideshow li:nth-child("+currentIndex+")'").find("img:first").attr("title")+"</h2></div>");
									jQuery("'ul.widget-slideshow li:nth-child("+currentIndex+")'").addClass('show').animate({opacity: 1.0}, fadeDuration);
									var timer = setInterval('nextWidgetSlide()',slideDuration);
					  		})
								.error(function( jqXHR, textStatus, errorThrown) {
       						console.log("error " + textStatus);
        					console.log("errorThrown " + errorThrown);
        					console.log("incoming Text " + jqXHR.responseText);
	       					console.log("contents Text " + jqXHR.contents);
									console.log("get XMLHttpRequest= "+XMLHttpRequest.responseText);
								}).complete(function(){
									jQuery('#fbg-widget-slide').fadeIn('slow'); 
							  });
	}
	function FBGetPhotoThumb()
	{
 //   jQuery('#fbg-slide').append( '<div id="slider-loadmask"style="text-align: center;"></div>'); 
		jQuery.get(
									FBGAjax.ajaxurl,
									{
										action: 'FBGPhotoThumb',
										source: 'widget',
										FBGPhotoSlideNonce: FBGAjax.FBGPhotoSlideNonce
									},
									function(data){
										var locHeight = FBGAjax.height;
										var locWidth = FBGAjax.width;
	   	  					jQuery.each(data,function(i,photos){
								  	jQuery('#fbg-photos-thumb-widget').append('<div class="fbg-thumbnail" style="height: '+locHeight+'px; width: '+locWidth+'px"><a href="'+photos.link+'"><img src="'+photos.src+'" alt="'+photos.caption+'" />');
 										});
 									}, "jsonp")	
 								.success(function() {	
					  		})
								.error(function( jqXHR, textStatus, errorThrown) {
       						console.log("error " + textStatus);
        					console.log("errorThrown " + errorThrown);
        					console.log("incoming Text " + jqXHR.responseText);
	       					console.log("contents Text " + jqXHR.contents);
									console.log("get XMLHttpRequest= "+XMLHttpRequest.responseText);
								}).complete(function(){
							  });
	}
/*
	Image preview function using jQuery
	Used to popup a full size image with a description when the mouse hovers over the thumbnail
	The <img> atributes alt and longDesc are used by the function as follows
	alt : holds the description of the image
	longDesc : holds the URL of the larger image. 
*/
this.imagePreview = function(){	
	/* CONFIG */
		
		xOffset = 10;
		yOffset = 30;
		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	jQuery("img.popview").hover(function(e){
		this.t = this.alt; //this.title;
		this.title = "";	
		var c = (this.t != "") ?  this.t : "";
		var imgWidth = 0;
		var imgHeight = 0;
    var locCaption = jQuery("#fotobox-caption").val;
		// This defines the body of our popup window
		jQuery("body").append('<div id="fotobox-preview"><div id="fotobox-imageContainer"><img id="fotobox-image" /></div><div id="fotobox-infoBox"><div id="fotobox-infoContainer"><span id="fotobox-caption-description"></span></div></div></div>');								 
					// the image is preloaded here to determine it's width and height so we can correctly size the container
					var preloader = new Image();
						// Set callback
						preloader.onload = function()
						{	// We have preloaded the image
							// Update image with our new info
							imgWidth  = preloader.width;
							imgHeight = preloader.height;
							// Kill preloader
							preloader.onload = null;
							preloader = null;
						};
						// Start preload
						preloader.src = this.longDesc; //this.alt;

		jQuery('#fotobox-image')
    .attr('src', this.longDesc)
//    .attr('src', this.alt)
    .load(function(){
        // Adjust the image container width
     		jQuery("#fotobox-imageContainer").css({'width':imgWidth});
     		jQuery("#fotobox-imageContainer").css({'z-index':1000});
     		// Add the description to the caption container
     		jQuery("#fotobox-caption-description").html(c || '&nbsp;');
     		jQuery("#fotobox-caption-description").css({'width':imgWidth});
     		// determine the left and right padding of the infoContainer so we can size it correctly
     		padLeft = parseInt(jQuery("#fotobox-infoContainer").css('padding-left'));
     		padRight = parseInt(jQuery("#fotobox-infoContainer").css('padding-right'));
     		// Set the width of the infoContainer to the image width minus any padding
     		txtWidth = imgWidth - padLeft - padRight;
     		jQuery("#fotobox-infoContainer").css({'width':txtWidth});
        		jQuery("#fotobox-preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
    });						
    },
	function(){
		this.title = this.t;	
		jQuery("#fotobox-preview").remove();
    });	
	jQuery("a.fotobox-preview").mousemove(function(e){
		jQuery("#fotobox-preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});			
};
	function nextSlide(){
			var nextIndex =currentIndex+1;
			if(nextIndex > jQuery('ul.slideshow li').length)
			{
				nextIndex = 1;
			}
			jQuery(".fbg-slide-title h2").html(jQuery("'ul.slideshow li:nth-child("+nextIndex+")'").find("img:first").attr("title"));
 			jQuery("'ul.slideshow li:nth-child("+nextIndex+")'").addClass('show').animate({opacity: 1.0}, fadeDuration);
			jQuery("'ul.slideshow li:nth-child("+currentIndex+")'").animate({opacity: 0.0}, fadeDuration).removeClass('show');
			currentIndex = nextIndex;
	}
	function nextWidgetSlide(){
			var nextIndex =currentIndex+1;
			if(nextIndex > jQuery('ul.widget-slideshow li').length)
			{
				nextIndex = 1;
			}
			jQuery(".fbg-slideshow-title h2").html(jQuery("'ul.widget-slideshow li:nth-child("+nextIndex+")'").find("img:first").attr("title"));
	 		jQuery("'ul.widget-slideshow li:nth-child("+nextIndex+")'").addClass('show').animate({opacity: 1.0}, fadeDuration);
			jQuery("'ul.widget-slideshow li:nth-child("+currentIndex+")'").animate({opacity: 0.0}, fadeDuration).removeClass('show');
			currentIndex = nextIndex;
	}
function centerSquareThumbs(container, size) {
  var widget = document.getElementById(container);
  var images = widget.getElementsByTagName('img');
  for(var i=0; i < images.length; i++) {
    var left = ((images[i].width - size) / 2) * -1;
    var top = ((images[i].height - size) / 2) * -1;
    images[i].style.marginLeft = left+'px';
    images[i].style.marginTop  = top+'px';
  }
}
// starting the script on page load
jQuery(document).ready(function(){
	if(jQuery('#fbg-slide').length)
	{
		FBGetPhotoSlide(jQuery('#fbg-slide').width(),jQuery('#fbg-slide').height());
	}
	if(jQuery('#fbg-widget-slide').length)
	{
		FBGetWidgetPhotoSlide(jQuery('#fbg-widget-slide').width()-2,jQuery('#fbg-widget-slide').height());
	}
	if(jQuery('#fbg-photos-thumb-widget').length)
	{
		FBGetPhotoThumb();
	}
	imagePreview();
});
window.onload = function() {
	var locSize = jQuery('.fbg-thumbnail').height();
	centerSquareThumbs('fbg-photos-thumb-widget',locSize);
}