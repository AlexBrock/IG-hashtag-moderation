$(document).ready(function(){

	var url = window.location.href;

	/**
	 * Event Listeners
	 * Add on the fly event listeners to ajax'd content
	 */
	var addListeners = function(){
		$('.ig__add--item').on('click', function(){
			var igId = $(this).parent('.ig__item').attr('data-ig-id');
			ajaxModifyConfirmed($(this).parent('.ig__item'), 'add=' + igId);
		});
		$('.ig__remove--item').on('click', function(){
			var igId = $(this).parent('.ig__item').attr('data-ig-id');
			ajaxModifyConfirmed($(this).parent('.ig__item'), 'remove=' + igId);
		});
	}

	/**
	 * Page Views
	 * Ajax in different page view based on url passed (derived from data-nav)
	 */
	var ajaxPageLoad = function(url){
		$.ajax({
			url: '/return.php?' + url,
			cache: false,
			beforeSend: function()
			{
				//add url variable of new page
				window.history.pushState('', '', '?'+url);
				//add preloader
				$('#js-main-content').prepend('<div class="pre-loader"><div>Loading...</div></div>');
			},
			success: function(html)
			{
				$('#js-main-content').html(html);
				addListeners();
			}
		});
	};

	/**
	 * Admin Controls
	 * Perform admin actions (reloading data) behind the scenes
	 */
	var ajaxAdmin = function(url){
		$.ajax({
			url: '/return.php?' + url,
			cache: false,
			beforeSend: function()
			{
				// add preloader while script is ran
				$('#js-main-content').prepend('<div class="pre-loader"><div>Reloading...</div></div>');
			},
			success: function(html)
			{
				// remove preloader and remove in progress class so buttons can be used again
				$('#js-main-content .pre-loader').remove();
				$('#js-sidebar-admin li').each(function(){
					$(this).removeClass('in-progress');
				});
				// reload the page
				ajaxPageLoad('all_posts');
			}
		});
	};

	/**
	 * Confirm post
	 * Depending on button pressed (remove or add) modify the confirmed table
	 */
	var ajaxModifyConfirmed = function(clicked, url){
		var $this = clicked;
		var clickedUrl, goTo;

		// check to see which like has been clicked
		if( url.indexOf('add') != -1 )
		{
			clickedUrl = 'add'; 
		}
		else if( url.indexOf('remove') != -1 )
		{
			clickedUrl = 'remove'
		}

		// avoid double clicking to add a post to confirmed
		// only populate goTo url if hasn't already been clicked
		if( $this.find('.ig__null--item').length != 0 )
		{
			goTo = '';
		}
		else
		{
			goTo = '/return.php?' + url;
		}


		$.ajax({			
			url: goTo,
			cache: false,
			beforeSend: function()
			{

			},
			success: function(html)
			{
				// if add url then increase confirmed count, change text of button and disable trigger.
				if(clickedUrl === 'add')
				{
					$this.addClass('in-confirmed');
					$this.find('.ig__add--item').html('added to confirmed').addClass('ig__null--item').removeClass('ig__add--item');
					if(goTo != '')
					{
						var currentConfirmed = parseInt($('li[data-nav="confirmed"] span').text());
						$('li[data-nav="confirmed"] span').text(currentConfirmed + 1);
					}
				}
				// if remove url then decrease confirmed count, remove post from container.
				else if(clickedUrl === 'remove')
				{
					$this.remove();
				
					var currentConfirmed = parseInt($('li[data-nav="confirmed"] span').text());
					$('li[data-nav="confirmed"] span').text(currentConfirmed - 1);
				}				
			}
		});
	};

	/**
	 * Onload
	 * View changing ajax calls depending on page url
	 */
	if(url.indexOf('confirmed') != -1)
	{
		ajaxPageLoad('confirmed');
	}
	else if(url.indexOf('all_posts') != -1)
	{
		ajaxPageLoad('all_posts');
	}
	else
	{
		ajaxPageLoad('all_posts');
	}

	/**
	 * Top nav
	 * View changing ajax calls 
	 */
	$('#js-sidebar-nav li').on('click', function(){
		var clickedLink = $(this).attr('data-nav');
		ajaxPageLoad(clickedLink);
	});

	/**
	 * Bottom nav
	 * Admin functions to repopulate data.
	 */
	$('#js-sidebar-admin li').on('click', function(){
		var clickedLink = $(this).attr('data-nav');
		if(!$(this).hasClass('in-progress'))
		{
			ajaxAdmin(clickedLink);
		}
		$('#js-sidebar-admin li').each(function(){
			$(this).addClass('in-progress');
		});
	});

});