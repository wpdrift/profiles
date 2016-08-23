/* jshint devel: true */
/* global Profiles_Confirm */

jQuery( document ).ready( function() {
	jQuery( 'a.confirm').click( function() {
		if ( confirm( Profiles_Confirm.are_you_sure ) ) {
			return true;
		} else {
			return false;
		}
	});
});
