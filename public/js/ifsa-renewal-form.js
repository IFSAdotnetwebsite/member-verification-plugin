(function( $ ) {
	'use strict';

	$( document ).ready( function() {
		$( 'input[type=radio][name="ifsa_renewal_toggle"]' ).change( function() {

			if ( $( this ).val() == 'Yes' ) {
			jQuery('#ifsa-loading-renew-1').show();
				var data = {
					'action': 'ifsa_renew_request',
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post( ajaxurl, data, function( response ) {
					
					if (response == 1 ) {
					$( '#ifsa_renewal_form-sucess' ).css( { 'display': 'block', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_renewal_form-success_btn' ).css( 'display', 'none' );
					jQuery('#ifsa-loading-renew-1').hide();
					}else if (response == 'already requested') {
					alert('You have already submitted request');
					jQuery('#ifsa-loading-renew-1').hide();
					
					}

				} );
			} else {
				jQuery('#ifsa-loading-renew-1').hide();
				$( '#ifsa_renewal_form-1' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_renewal_form-2' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
			}
		} );

		$( '#ifsa_renewal_form-datepicker' ).datepicker( { dateFormat: 'mm-dd-yy', maxDate: 'getDate', endDate: 'today' } );

		$( '#ifsa_renewal_form-datepicker' ).change(
			function() {
				var date = $( '#ifsa_renewal_form-datepicker' ).datepicker( 'getDate' );
				var date_2 = new Date();
				var t1 = date.getTime();
				var t2 = date_2.getTime();
				var diff = t2 - t1;
				var daydiff = (diff / 31536000000);
				if ( daydiff < 1 ) {
					$( '#ifsa_renewal_form-success_btn' ).css( { 'display': 'block', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_renewal_form-error' ).css( 'display', 'none' );
				} else {
					$( '#ifsa_renewal_form-error' ).css( { 'display': 'block', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_renewal_form-success_btn' ).css( 'display', 'none' );
				}
			}
		);

		$( '#ifsa_renewal_form-success_btn' ).click( function() {
			jQuery('#ifsa-loading-renew-2').show();
			var data = {
				'action': 'ifsa_renew_request',
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function( response ) {
				jQuery('#ifsa-loading-renew-2').hide();
				$( '#ifsa_renewal_form-sucess-2' ).css( { 'display': 'block', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_renewal_form-success_btn' ).css( 'display', 'none' );
			} );
		} );

	} );

})( jQuery );
