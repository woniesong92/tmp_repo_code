/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens and enables tab
 * support for dropdown menus.
 */
( function() {
  if ($(window).width() < 250)
    {
        
    }
} )();


  if ($(window).width() < 900)
    {
        
    }



$("#sugToggle").click(function() {
	$("#sugs").slideToggle();
	if ($('#sugToggle').hasClass('fa-angle-up')) {
		$('#sugToggle').removeClass('fa-angle-up');
		$('#sugToggle').addClass('fa-angle-down');
	} else {
		$('#sugToggle').removeClass('fa-angle-down');
		$('#sugToggle').addClass('fa-angle-up');
	}
});