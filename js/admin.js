jQuery(document).ready(function() {
	fbgallery.init();
});

var seconds = null;

var fbgallery = {
	interval: undefined,
	tickInterval: undefined,
	init: function() {
		jQuery('#grant-permissions').click(function() {
			window.open(this.href, 'fbgalleryAuth', 'width=500,height=300');
			jQuery(this).css('opacity', 0.5);
			window.onbeforeunload = function(e) {
				return ('If this page is refreshed or changed between granting and applying permissions the activation process will not work.');
			};
			return false;
		});
		jQuery('#request-permissions').click(function() {
			window.open(this.href, 'fotobookAuth', 'width=500,height=300');
			jQuery(this).css('opacity', 0.5);
			return false;
		});
		jQuery('#apply-permissions').submit(function() {
			jQuery(this).find('input[type="submit"]').css('opacity', 0.5);
			window.onbeforeunload = undefined;
		});

		fbgallery.initManageList();

		jQuery('#fb-panel input[type="button"][name="get"]').click(fbgallery.getAlbums);
		jQuery('#fb-panel input[type="button"][name="getall"]').click(fbgallery.getAllAlbums);
		jQuery('#fb-panel input[type="button"][name="order"]').click(fbgallery.resetOrder);
		jQuery('#fb-panel input[type="button"][name="remove"]').click(function() {
			if (confirm('Removing the albums will also remove the pages that contain them and their comments.  Are you sure you want to proceed?')) {
				fbgallery.removeAll();
			}
			return false;
		});

		// initialize the stylesheet switcher
		jQuery('#fb-stylesheets select').change(function() {
			jQuery('#fb-stylesheets div').hide();
			jQuery('#' + jQuery(this).val() + '-stylesheet').show();
		});

		// toggle the debug info
		jQuery('#fb-debug').click(function() {
			jQuery('#fb-debug-info').toggle();
			return false;
		});
	},
	initManageList: function() {
		var $list = jQuery('#fb-manage-list');
		$list.sortable({
			update: function() {
				var ids = $list.sortable('serialize', {
					key: 'order[]'
				});
				jQuery.post('admin-ajax.php', "action=fbgallery&" + ids);
			},
			cursor: 'handle'
		});
		$list.find('.toggle-hidden').click(fbgallery.toggleHidden);
	},
	albumList: function(message) {
		var params = {
			action: 'fbgallery',
			albums_list: 'true'
		};
		if(message != '') {
			jQuery.extend(params, {message: message});
		}
		jQuery('#fb-manage').load('admin-ajax.php', params, function() {
			var message = jQuery('#fb-message');
			if (message.length > 0) {
				message.slideDown();
				setTimeout(function() {message.slideUp();}, 5000);
			}
			fbgallery.initManageList();
		});
	},
	resetOrder: function() {
		jQuery.post('admin-ajax.php', {
			action: 'fbgallery',
			reset_order: 'true'
		}, 
		function(response) {
			fbgallery.albumList(response);
		});
		return false;
	},
	removeAll: function() {
		jQuery.post('admin-ajax.php', "action=fbgallery&remove_all=true", function(response) {
			fbgallery.albumList(response);
		});
		return false;
	},
	toggleHidden: function() {
		var $link = jQuery(this);
		var $li   = jQuery(this).parents('li');

		var aid = $li.attr('id').split('_');
		aid.shift();
		aid = aid.join('_');

		jQuery.post('admin-ajax.php', {action: 'fbgallery', hide: aid});
		
		if($link.text() == 'Hide') {
			$link.text('Show');
			$li.addClass('disabled');
		}
		else {
			$link.text('Hide');
			$li.removeClass('disabled');
		}
		return false;
	},
	getAlbums: function() {
		fbgallery.setProgress(0);
		jQuery('#fb-progress').fadeIn();
		jQuery('#fb-manage-list').addClass('disabled');
		seconds = 0;
		fbgallery.tickInterval = setInterval(fbgallery.setTick, 1000);
		fbgallery.interval = setInterval(fbgallery.updateProgressBar, 3000);
		jQuery.post('admin-ajax.php', "action=fbgallery&update=true", function(response) {
			clearInterval(fbgallery.interval);
			fbgallery.setProgress(100);
			fbgallery.albumList(response);
			jQuery('#fbg-new-albums').text('Albums are now up to date');
			jQuery('#fb-progress').fadeOut();
		});
		return false;
	},
	getAllAlbums: function() {
		fbgallery.setProgress(0);
		jQuery('#fb-progress').fadeIn();
		jQuery('#fb-manage-list').addClass('disabled');
		seconds = 0;
		fbgallery.tickInterval = setInterval(fbgallery.setTick, 1000);
		fbgallery.interval = setInterval(fbgallery.updateProgressBar, 5000);
		jQuery.post('admin-ajax.php', "action=fbgallery&all=true", function(response) {
			clearInterval(fbgallery.interval);
			fbgallery.setProgress(100);
			fbgallery.albumList(response);
			jQuery('#fbg-new-albums').text('Albums are now up to date');
			jQuery('#fb-progress').fadeOut();
		});
		return false;
	},
	updateProgressBar: function() {
		jQuery.post('admin-ajax.php', "action=fbgallery&progress=true", function(response) {
			if((response == '-1') || (response == -1))
			{
				clearInterval(fbgallery.interval);
				fbgallery.setProgress(100);
				fbgallery.albumList("Update Completed");
				jQuery('#fb-progress').fadeOut();
			}
			else if((response == '-2') || (response == -2))
			{
				clearInterval(fbgallery.interval);
				fbgallery.setProgress(100);
				fbgallery.albumList("The function appears to have timed out. Please refresh the screen and Click on Get All Albums to retrieve the remaining albums");
			jQuery('#fbg-new-albums').text("The function appears to have timed out. Please refresh the screen and Click on 'Get All Albums' to retrieve the remaining albums");
				jQuery('#fb-progress').fadeOut();
			}
			else
			{
				fbgallery.setProgress(response);
			}
		});
	},
	setProgress: function(percentage) {
		var initial     = -119;
		var imageWidth  = 240;
		var eachPercent = (imageWidth / 2) / 100;
		var percentageWidth = eachPercent * percentage;
		var newProgress = eval(initial)+eval(percentageWidth)+'px';
    var secs = seconds;
    var hrs = Math.floor( secs / 3600 );
    secs %= 3600;
    var mns = Math.floor( secs / 60 );
    secs %= 60;
    var pretty = ( hrs < 10 ? "0" : "" ) + hrs
               + ":" + ( mns < 10 ? "0" : "" ) + mns
               + ":" + ( secs < 10 ? "0" : "" ) + secs;

		jQuery('#fb-progress-indicator').css('backgroundPosition', newProgress+' 0');
		jQuery('#fb-progress-indicatorText').text(percentage + '% elapsed time '+pretty);
	},
	setTick: function(){
		seconds++;
	}
};