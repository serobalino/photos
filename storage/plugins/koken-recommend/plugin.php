<?php

class KokenRecommend extends KokenPlugin {

	function __construct()
	{
		$this->register_hook('before_closing_head', 'render_head');
		$this->register_hook('before_closing_body', 'render_body');
	}
	
	function render_head()
	{
		$path = $this->get_path();
		
		echo <<<OUT
<script src="{$path}/waypoints.min.js"></script>
<link rel="stylesheet" href="{$path}/plugin.css" type="text/css" />
OUT;
	}
	
	function render_body()
	{
		$path = dirname(dirname(dirname($this->get_path())));
		$essaysPath = $path . '/api.php?/text/type:essay/render:1';
		$pagesPath = $path . '/api.php?/text/type:page/render:1';
		
		$doNotShowOnPages = ($this->data->doNotShowOnPages) ? 1 : 0;
		$doNotShowOnEssays = ($this->data->doNotShowOnEssays) ? 1 : 0;
		$customTitle = addslashes($this->data->customTitle);
		$customDesc = addslashes($this->data->customDescription);
		$offset = (strlen($this->data->offset) > 0) ? $this->data->offset : 100;
		
		echo <<<OUT
<script>
$(function() {
	(function() {
		var isEssay = $('body').hasClass('k-source-essay'),
			isPage = $('body').hasClass('k-source-page'),
			hasData = false,
			canOpen = false,
			offset = parseInt("$offset", 10),
			getRandom = function(max) {
				return Math.floor(Math.random() * max);
			};
		
		if (!isEssay && !isPage) { return; }
		if (isPage && $doNotShowOnPages == 1) { return; }
		if (isEssay && $doNotShowOnEssays == 1) { return; }
			
		$('#plugin_koken_recommend strong').text(("$customTitle" != '') ? "$customTitle" : 'More in ' + ((isEssay) ? 'essays' : 'pages'));
		if ("$customDesc" != '') {
			$('#plugin_koken_recommend a').remove();
			$('#plugin_koken_recommend').append($('<div/>').html("$customDesc").addClass('plugin_koken_recommend_custom_desc'));
		}
		$.ajax({
			url: (isEssay) ? '$essaysPath' : '$pagesPath',
			success: function(data) {
				var index = getRandom(data.total),
					item = data.text[index];
				if (window.location.href == item.url) {
					index = (index+1 >= data.text.length) ? 0 : index+1;
					item = data.text[index];
				}
				if ("$customDesc" == '') {
					$('#plugin_koken_recommend a').text(item.title).attr('href', item.url);
				}			
				hasData = true;
				canOpen == true && $('#plugin_koken_recommend').removeClass('hide');
			}
		});
		if ($.waypoints('viewportHeight') > $('#content').height()) {
			offset = -1;
		}
		if (offset < 0) {
			canOpen = true;
			hasData && $('#plugin_koken_recommend').removeClass('hide');
		} else {
			$('body')
				.waypoint(function(dir) {
					canOpen = (dir === 'up');
					if (hasData) {
						$('#plugin_koken_recommend').toggleClass('hide', canOpen);
					}
				}, {
					offset: function() {
		  				return $.waypoints('viewportHeight') - $(this).height() + ((!isNaN(offset)) ? offset : 100);
		  			}
				})
				.append();
		}
	})();
});
</script>
<div id="plugin_koken_recommend" class="hide"><strong></strong><a></a></div>
OUT;
	}
	
}