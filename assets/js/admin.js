jQuery(document).ready(function($) {
	$('[data-toggle="tooltip"]').tooltip();
	
	$('#display_admin').on('click', function() {
		if($(this).is(':checked')) {
			$('.display_admin-position').removeClass('hidden');
		} else {
			$('.display_admin-position').addClass('hidden');
		}
	});
	
	$('#display_front').on('click', function() {
		if($(this).is(':checked')) {
			$('.display_front-position').removeClass('hidden');
		} else {
			$('.display_front-position').addClass('hidden');
		}
	});
});