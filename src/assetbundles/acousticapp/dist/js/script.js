/**
 * Acoustic App plugin for Craft CMS
 *
 * Favourites Field JS
 *
 * @author    @asclearasmud
 * @copyright Copyright (c) 2018 @asclearasmud
 * @link      http://ournameismud.co.uk/
 * @package   AcousticApp
 * @since     1.0.0
 */

$(document).ready(function() {
	$('.fields-wrapper').addClass('js');
	$('button.toggler').on('click', function(e) {
		e.preventDefault();
		var target = $(this).attr('data-target');
		$( this ).toggleClass('shut');
		$( target ).toggleClass('shut');
	});
});