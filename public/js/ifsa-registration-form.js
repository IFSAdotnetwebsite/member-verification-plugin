jQuery( document ).ready( function() {
	jQuery('#lc option:first').text('Select Committee');
	jQuery('#ifsa_region option:first').text('Select Region');
	jQuery('#field_4 option').eq(1).remove();  
	jQuery('#field_8 option').eq(1).remove(); 
	jQuery('#field_4 option:first').text('Select Gender');
	jQuery('#field_8 option:first').text('Select Nationality');

	jQuery( '#lc' ).prop( 'disabled', true );
	var modal = document.getElementById( 'myModal' );
	var modal_remove = document.getElementById( 'myModal_remove' );

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName( 'close' )[ 0 ];

	// When the user clicks the button, open the modal 
	jQuery( '.cls-reject' ).click( function() {
		var memID = jQuery( this ).attr( 'data-id' );
		var rowID = jQuery( this ).attr( 'row-id' );
		jQuery( '#final_reject' ).attr( 'data-id', memID );
		jQuery( '#final_reject' ).attr( 'row-id', rowID );
		modal.style.display = 'block';
	} );

	// When the user clicks on <span> (x), close the modal
	jQuery( '.close' ).click( function() {
		modal.style.display = 'none';
	} );
	jQuery( '.close-r' ).click( function() {
		modal_remove.style.display = 'none';
	} );
	// span.onclick = function() {
	//   modal.style.display = "none";
	// }

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function( event ) {
		if ( event.target == modal ) {
			modal.style.display = 'none';
		}

		if ( event.target == modal_remove ) {
			modal_remove.style.display = 'none';
		}
	};

	jQuery( '#table_id' ).dataTable( {
		preDrawCallback: function (settings) {
			var api = new jQuery.fn.dataTable.Api(settings);
			var pagination = jQuery(this)
				.closest('.dataTables_wrapper')
				.find('.dataTables_paginate');
			pagination.toggle(api.page.info().pages > 1);
		},
		"language": {
			"emptyTable":     "No records found"
		}
	} );

	jQuery( '#approval_member_table' ).dataTable( {
		preDrawCallback: function (settings) {
			var api = new jQuery.fn.dataTable.Api(settings);
			var pagination = jQuery(this)
				.closest('.dataTables_wrapper')
				.find('.dataTables_paginate');
			pagination.toggle(api.page.info().pages > 1);
		},
		"language": {
			"emptyTable":     "No records found"
		}
	} );

// When the user clicks the button, open the modal 
//jQuery( '.cls-remove' ).click( function() {
	jQuery(document).on('click', '.cls-remove', function(e) {

	var memID = jQuery( this ).attr( 'data-id' );
	var rowID = jQuery( this ).attr( 'row-id' );
	jQuery( '#final_remove' ).attr( 'data-id', memID );
	jQuery( '#final_remove' ).attr( 'row-id', rowID );
	modal_remove.style.display = 'block';
} );


	jQuery( '.final_remove' ).click( function() {
		var reason = '';
		var memID = jQuery( this ).attr( 'data-id' );
		var rowID = jQuery( this ).attr( 'row-id' );
		 reason = jQuery( 'input[name="reason"]:checked' ).val();
		if (reason == 'other') {
			reason = jQuery('#other_reason').val();
		}
		jQuery( '#ifsa-loading-reject' ).css( 'display', 'inline-block' );
		jQuery( '#ifsa-loading-reject' ).show();

		var data = {
			'action': 'ifsa_remove_member',
			'nonce': ifsa_script_vars.nonce,
			'member_id': memID,
			'rowid': rowID,
			'reason': reason
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post( ajaxurl, data, function( response ) {
			//	alert(response);
			if ( response != 'Something went wrong' ) {
				console.log(response);
				if (response == 'success') {
					jQuery('.ifsa-response-reject').fadeIn();
					jQuery('.ifsa-response-reject').addClass('info');
					
					jQuery('.ifsa-response-reject').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					jQuery('.ifsa_reject_p').html('Member successfully removed!');
					jQuery('.ifsa-response-reject').fadeIn();
			

					

				}else if  (response == 'error') {
					jQuery('.ifsa-response-reject').fadeIn();
					jQuery('.ifsa-response-reject').addClass('error');
					jQuery('.ifsa-response-reject').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					jQuery('.ifsa_reject_p').html('Something went wrong member not removed. Try again later or contact website admin (web@ifsa.net).');
				}else {
					jQuery('.ifsa-response-reject').css(  'display', 'none' );
				}

				jQuery( '.cls-action-remove' + rowID ).html( 'Removed' );
				jQuery( '#ifsa-loading-reject' ).hide();
				modal_remove.style.display = 'none';

				setTimeout(function() {
					jQuery('.ifsa-response-reject').fadeOut("slow");
				}, 2000 );
			}

		} );
	} );

	jQuery( '.cls-approve' ).click( function() {
		var memID = jQuery( this ).attr( 'data-id' );
		var rowID = jQuery( this ).attr( 'row-id' );

		jQuery( '#ifsa-loading-' + rowID ).show();
		jQuery( '.cls-action-' + rowID ).html( '' );
		var data = {
			'action': 'ifsa_approve_member',
			'nonce': ifsa_script_vars.nonce,
			'member_id': memID,
			'rowid': rowID
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post( ajaxurl, data, function( response ) {
			//alert(response);
			console.log(response);
			if (response == 'success') {
				jQuery('.ifsa-response-approve').addClass('info');
				jQuery('.ifsa-response-approve').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				jQuery('.ifsa_approve_p').html('Member successfully approved!');
				jQuery('.ifsa-response-approve').fadeIn("slow");
			}else if  (response == 'error') {
				jQuery('.ifsa-response-approve').addClass('error');

				jQuery('.ifsa-response-approve').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				jQuery('.ifsa_approve_p').html('Something went wrong member not approved Try again later or contact website admin (web@ifsa.net).');
				jQuery('.ifsa-response-approve').fadeIn("slow");
			}else {
				jQuery('.ifsa-response-approve').css(  'display', 'none' );
			}

			jQuery( '#ifsa-loading-' + rowID ).hide();
			setTimeout(function() {
				jQuery('.ifsa-response-approve').fadeOut("slow");
			}, 2000 );

			jQuery( '.cls-action-' + rowID ).html( 'Approved' );

		} );
	} );
	jQuery( 'input[name="reason"]' ).on( 'click', function() {
		if ( jQuery( this ).val() == 'other' ) {
			jQuery( '#other_reason' ).show();
		} else {
			jQuery( '#other_reason' ).val( '' );
			jQuery( '#other_reason' ).hide();
		}
	} );
	jQuery( '.final_reject' ).click( function() {
		var reason = '';
		var memID = jQuery( this ).attr( 'data-id' );
		var rowID = jQuery( this ).attr( 'row-id' );
		 reason = jQuery( 'input[name="reason"]:checked' ).val();
		if (reason == 'other') {
			reason = jQuery('#other_reason').val();
		}
		jQuery( '#ifsa-loading-reject' ).css( 'display', 'inline-block' );
		jQuery( '#ifsa-loading-reject' ).show();

		var data = {
			'action': 'ifsa_reject_member',
			'nonce': ifsa_script_vars.nonce,
			'member_id': memID,
			'rowid': rowID,
			'reason': reason
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post( ajaxurl, data, function( response ) {
			//	alert(response);
			if ( response != 'Something went wrong' ) {
				console.log(response);
				if (response == 'success') {
					jQuery('.ifsa-response-reject').fadeIn();
					jQuery('.ifsa-response-reject').addClass('info');
					
					jQuery('.ifsa-response-reject').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					jQuery('.ifsa_reject_p').html('Member successfully rejected!');
					jQuery('.ifsa-response-reject').fadeIn();
			

					

				}else if  (response == 'error') {
					jQuery('.ifsa-response-reject').fadeIn();
					jQuery('.ifsa-response-reject').addClass('error');
					jQuery('.ifsa-response-reject').css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					jQuery('.ifsa_reject_p').html('Something went wrong member not rejected. Try again later or contact website admin (web@ifsa.net).');
				}else {
					jQuery('.ifsa-response-reject').css(  'display', 'none' );
				}

				jQuery( '.cls-action-' + rowID ).html( 'Rejected' );
				jQuery( '#ifsa-loading-reject' ).hide();
				modal.style.display = 'none';

				setTimeout(function() {
					jQuery('.ifsa-response-reject').fadeOut("slow");
				}, 2000 );
			}

		} );
	} );

	jQuery( document ).on( 'change', '#ifsa_region', function() {
		jQuery( '#lc' ).prop( 'disabled', false );

		var reasonID = jQuery( this ).val();
		var data = {
			'action': 'ifsa_list_region',
			'nonce': ifsa_script_vars.nonce,
			'reasonID': reasonID
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post( ajaxurl, data, function( response ) {
			jQuery( '#lc' ).html( response );
		} );
	} );
} );

(function( $ ) {
	'use strict';

	$( document ).ready( function() {

		$( '#ifsa_multistep_form-loadermask' ).hide();

		//$('body').on('change', 'input[type=radio][name="toggle"]', function() {

		$( 'input[type=radio][name="toggle"]' ).change( function() {
			if ( $( this ).val() == 'Yes' ) {
				$( '#ifsa_form_1' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_2' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
				localStorage.setItem( 'isStudent', $( this ).val() );
			} else {
				$( '#ifsa_form_1' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_6' ).css( {  'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
			}

		} );

		$( 'input[type=radio][name="toggle_6"]' ).change( function() {
			if ( $( this ).val() == 'Yes' ) {
				$( '#ifsa_form_6' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_4' ).css( { 'opacity': '1', 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				localStorage.setItem( 'isStudent', $( this ).val() );
			} else {
				$( '#ifsa_form_6' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_7' ).css( {  'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
			}

		} );

		$( '#ifsa_form_2-btn' ).click( function() {

			if ( $.trim( $( '#ifsa_universityname' ).val() ).length == 0 ) {

				$( '#ifsa_universityname-error' ).html( 'Please add university name');
				//$( '#ifsa_form_2-btn' ).hide();
				$("#ifsa_form_2-btn").prop('disabled', true);
			} else if ( $.trim( $( '#ifsa_country' ).val() ).length == 0 ) {

				$( '#ifsa_country-error' ).html( 'Please select country' );
				//$( '#ifsa_form_2-btn' ).hide();
				$("#ifsa_form_2-btn").prop('disabled', true);
			} else if ( $.trim( $( '#ifsa_universitylevel' ).val() ).length == 0 ) {

				$( '#ifsa_universitylevel-error').html( 'Please select university level' );
				//$( '#ifsa_form_2-btn' ).hide();
				$("#ifsa_form_2-btn").prop('disabled', true);
			}
			
			else {

				$( '#ifsa_form_2' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_3' ).css( {  'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$("#ifsa_form_2-btn").prop('disabled', false);
			}
		} );

		$( '#ifsa_form_2-btn-back' ).click( function() {
			$("#form_1_toggle-yes").prop('checked', false);
			$("#form_1_toggle-no").prop('checked', false);
			$('input[type="radio"]').prop('checked', false);
			$( '#ifsa_form_1' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
			$( '#ifsa_form_2' ).css( { 'opacity': '0', 'visibility': 'hidden' } );

		});

		$( '#ifsa_form_last-btn-back' ).click( function() {
			jQuery("#ifsa_form_3 #toggle-yes").prop('checked', false);

			jQuery("#ifsa_form_3 #toggle-no").prop('checked', false);
			$('input[type="radio"]').prop('checked', false);
			$( '#ifsa_form_3' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
			$( '#ifsa_final_step' ).css( { 'opacity': '0', 'visibility': 'hidden' } );

		});

		$( '#ifsa_form_4-success_btn-back' ).click( function() {
			$("#toggle-yes-6").prop('checked', false);
			$("#toggle-no-6").prop('checked', false);
			$('input[type="radio"]').prop('checked', false);
			$( '#ifsa_form_6' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
			$( '#ifsa_form_4' ).css( { 'opacity': '0', 'visibility': 'hidden' } );

		});

		$( function() {
			$( '#ifsa_universityname' ).keypress( function( e ) {
				var keyCode = e.keyCode || e.which;

				$( '#ifsa_universityname-error' ).html( '' );

				//Regex for Valid Characters i.e. Alphabets and Numbers.
				//var regex = /^[A-Za-z .]+$/;
				var regex = /^([A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\u0041-\u024F\s]*)$/;
				//Validate TextBox value against the Regex.
				var isValid = regex.test( String.fromCharCode( keyCode ) );
				if ( !isValid ) {
					$("#ifsa_form_2-btn").prop('disabled', true);
					$( '#ifsa_universityname-error' ).html( 'Only Alphabets are allowed.' );
				} else {
				//	$( '#ifsa_form_2-btn' ).show();
					$("#ifsa_form_2-btn").prop('disabled', false);
				}

				return isValid;
			} );
		} );

		$( function() {
			$('#ifsa_country').on('change', function(e) {
		//	$( '#ifsa_country' ).on( function( e ) {
				var countryname =  $(this).val();
				//Validate TextBox value against the Regex.
				if (countryname == '' || countryname == null ){
					$( '#ifsa_country-error' ).html( 'Please select country' );
					$("#ifsa_form_2-btn").prop('disabled', true);
				}else {
					$( '#ifsa_country-error' ).html( '' );
					$("#ifsa_form_2-btn").prop('disabled', false);
				}
				
			} );

			$('#ifsa_universitylevel').on('change', function(e) {
				//	$( '#ifsa_country' ).on( function( e ) {
						var ifsa_universitylevel =  $(this).val();
						//Validate TextBox value against the Regex.
						if (ifsa_universitylevel == '' || ifsa_universitylevel == null ){
							$( '#ifsa_universitylevel-error').html( 'Please select university level' );
							$("#ifsa_form_2-btn").prop('disabled', true);
						}else {
							$( '#ifsa_universitylevel-error').html( '' );
							$("#ifsa_form_2-btn").prop('disabled', false);
						}
						
					} );
		} );

		$( function() {
			$( '#ifsa_coursetopic' ).keypress( function( e ) {
				var keyCode = e.keyCode || e.which;

				$( '#ifsa_coursetopic-error' ).html( '' );

				//Regex for Valid Characters i.e. Alphabets and Numbers.
			//	var regex = /^[A-Za-z .]+$/;
				var regex = /^([A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\u0041-\u024F\s]*)$/;
				//Validate TextBox value against the Regex.
				var isValid = regex.test( String.fromCharCode( keyCode ) );
				if ( !isValid ) {
				//	$("#ifsa_form_2-btn").prop('disabled', true);
					$( '#ifsa_coursetopic-error' ).html( 'Only Alphabets are allowed.' );
				} else {
					$("#ifsa_form_2-btn").prop('disabled', false);
				//	$( '#ifsa_form_2-btn' ).show();
				}

				return isValid;
			} );
		} );

		$( function() {
			$( '#ifsa_coursetopic' ).keypress( function( e ) {
				var keyCode = e.keyCode || e.which;

				$( '#ifsa_coursetopic-error' ).html( '' );

				//Regex for Valid Characters i.e. Alphabets and Numbers.
				//var regex = /^[A-Za-z .]+$/;
				var regex = /^([A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\u0041-\u024F\s]*)$/;
				

				//Validate TextBox value against the Regex.
				var isValid = regex.test( String.fromCharCode( keyCode ) );
				if ( !isValid ) {
					//$("#ifsa_form_2-btn").prop('disabled', true);
					$( '#ifsa_coursetopic-error' ).html( 'Only Alphabets are allowed.' );
				} else {
					//$("#ifsa_form_2-btn").prop('disabled', false);
				//	$( '#ifsa_form_2-btn' ).show();
				}

				return isValid;
			} );
		} );

		

		$( '#datepicker' ).datepicker( { dateFormat: 'mm-dd-yy', maxDate: 'getDate', endDate: 'today' } );

		$( '#datepicker' ).change(
			function() {
				var date = $( '#datepicker' ).datepicker( 'getDate' );
				var date_2 = new Date();
				var t1 = date.getTime();
				var t2 = date_2.getTime();
				var diff = t2 - t1;
				var daydiff = (diff / 31536000000);
				if ( daydiff < 1 ) {
					$( '#ifsa_form_4-success_btn' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_form_4-success_btn-back' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_form_4-error' ).css( 'visibility', 'hidden' );
				} else {
					$( '#ifsa_form_4-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
					$( '#ifsa_form_4-success_btn' ).css( 'visibility', 'hidden' );
					$( '#ifsa_form_4-success_btn-back' ).css( 'visibility', 'hidden' );
				}
			}
		);

		$( '#ifsa_form_4-success_btn' ).click( function() {
			$( this ).css( 'visibility', 'hidden' );
			$( '#ifsa_form_4' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
			$( '#ifsa_form_2' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
		} );

		$( 'input[type=radio][name="toggle_3"]' ).change( function() {

			if ( $( this ).val() == 'Yes' ) {
				$('body').addClass('ifsa_final_step_active');
				localStorage.setItem( 'isifsaMember', $( this ).val() );
				$( '#ifsa_final_step' ).css( { 'opacity': '1','visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_form_3' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
			
			} else {
				$( '#ifsa_form_3' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
				$( '#ifsa_form_5' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$('body').removeClass('ifsa_final_step_active');
			}

		} );

		/*$( '#ifsa_form' ).on( 'submit', function() {

			var universityname = $( '#ifsa_universityname' ).val();
			var country = $( '#ifsa_country' ).val();
			var universityLevel = $( '#ifsa_universitylevel' ).val();
			var courseTopic = $( '#ifsa_coursetopic' ).val();
			var isStudent = localStorage.getItem( 'isStudent' );
			var isifsaMember = localStorage.getItem( 'isifsaMember' );
			var graduateday  = $( '#datepicker' ).val();
			localStorage.setItem( 'universityname', universityname );
			localStorage.setItem( 'country', country );
			localStorage.setItem( 'universityLevel', universityLevel );
			localStorage.setItem( 'courseTopic', courseTopic );

			var utm_source = '';
			var url = new URL( window.location.href );
			utm_source = url.searchParams.get( 'utm_source' );

			var stepperForm = {
				'utm_source': utm_source,
				'isStudent': isStudent,
				'isifsaMember': isifsaMember,
				'universityname': universityname,
				'country': country,
				'universityLevel': universityLevel,
				'courseTopic': courseTopic,
				'graduateday': graduateday
			};
			var json_str = JSON.stringify( stepperForm );
			setCookie( 'stepperform', json_str, 1 );

			location.href = window.location.origin + '/register/';

			//Data to be passed in ajax call

		} );*/

		jQuery('#register-button').on('click',function(e){
			
			e.preventDefault();
			var newUserName = jQuery('#txt-username').val();
			var newUserEmail = jQuery('#txt-email').val();
			var newUserfname = jQuery('#txt-name').val();
			var newUserlname = jQuery('#txt-surname').val();
			var newUserPassword = jQuery('#txt-password').val();
			var gender = jQuery('#ddl-gender').val();
			var nationality = jQuery('#ddl-nationality').val();
			
			var isChecked = $('#ddl-terms-0').prop('checked');
				

			var universityname = $( '#ifsa_universityname' ).val();
			var country = $( '#ifsa_country' ).val();
			var universityLevel = $( '#ifsa_universitylevel' ).val();
			var courseTopic = $( '#ifsa_coursetopic' ).val();
			var isStudent = localStorage.getItem( 'isStudent' );
			var isifsaMember = localStorage.getItem( 'isifsaMember' );
			var graduateday  = $( '#datepicker' ).val();

			var ifsa_region = $( '#ifsa_region' ).val();
			var lc = $( '#lc' ).val();

			// Consider remove this
			localStorage.setItem( 'universityname', universityname );
			localStorage.setItem( 'country', country );
			localStorage.setItem( 'universityLevel', universityLevel );
			localStorage.setItem( 'courseTopic', courseTopic );

			var utm_source = '';
			var url = new URL( window.location.href );
			utm_source = url.searchParams.get( 'utm_source' );

			if (newUserName == '' || newUserName == null) {
				$( '#ifsa_username-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_username-error' ).html( 'Please add username' );
				return false;
			}
			if (newUserEmail == '' || newUserEmail == null) {
				$( '#ifsa_email-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_email-error' ).html( 'Please add email address' );
				return false;
			}
			if (newUserPassword == '' || newUserPassword == null) {
				$( '#ifsa_password-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_password-error' ).html( 'Please add password' );
				return false;
			}
			if (newUserfname == '' || newUserfname == null) {
				$( '#ifsa_fname-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_fname-error' ).html( 'Please add name' );
				return false;
			}
			if (newUserlname == '' || newUserlname == null) {
				$( '#ifsa_lname-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_lname-error' ).html( 'Please add surname' );
				return false;
			}

			if (ifsa_region == '' || ifsa_region == null) {
				$( '#ifsa_region-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_region-error' ).html( 'Please select region' );
				return false;
			}
			if (isChecked == false ) {
				$( '#ifsa_terms-error' ).css( { 'visibility': 'visible', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_terms-error' ).html( 'Please accept terms and conditions' );
				return false;
			}


			var stepperForm = {
				'utm_source': utm_source,
				'isStudent': isStudent,
				'isifsaMember': isifsaMember,
				'universityname': universityname,
				'country': country,
				'universityLevel': universityLevel,
				'courseTopic': courseTopic,
				'graduateday': graduateday,
				


			};
			var json_str = JSON.stringify( stepperForm );
			setCookie( 'stepperform', json_str, 1 );

			var data = {
				action: "register_user_front_end",
				'_ajax_nonce': ifsa_vars.nonce,
				user_name : newUserName,
				user_email : newUserEmail,
				user_password : newUserPassword,
				user_first_name: newUserfname,
				user_last_name: newUserlname,
				ifsa_region: ifsa_region,
				lc: lc,
				gender: gender,
				nationality: nationality,
				utm_source: utm_source,
				'universityname': universityname,
				'country': country,
				'universityLevel': universityLevel,
				'courseTopic': courseTopic,
				'graduateday': graduateday,
			};
			jQuery( '#ifsa-loading-register' ).css( 'display', 'inline-block' );
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function( response ) {
				console.log(response);
				if (response == 'success') {
					var res = 'You have successfully created your account!';
					jQuery('.register-success-p').text(res).show();
					jQuery( '.ifsa_multistep_form-heading-h3' ).css( 'display', 'block' );
					
					$( '#ifsa_form' ).css( { 'opacity': '0', 'visibility': 'hidden' } );
					
					
				}else {
					jQuery( '#ifsa_multistep_form-heading-h3' ).css( 'display', 'none' );
					jQuery('.register-message').css( 'color', 'red' );;
					jQuery('.register-message').text(response).show();
					
				}

				jQuery( '#ifsa-loading-register' ).css( 'display', 'none' );
			} );

		});
	} );

	function setCookie( cName, cValue, expDays ) {
		let date = new Date();
		date.setTime( date.getTime() + (expDays * 24 * 60 * 60 * 1000) );
		const expires = 'expires=' + date.toUTCString();
		document.cookie = cName + '=' + cValue + '; ' + expires + '; path=/';
	}

	$( document ).on( 'click', '#bulk_upload', function( e ) {
		e.preventDefault();
		jQuery( '#ifsa-loading-import' ).css( 'display', 'inline-block' );
		jQuery( '#ifsa-loading-import' ).show();

		var file_data = jQuery( '#bulk_upload_file' ).prop( 'files' )[ 0 ];
		var fileInput = $.trim( $( '#bulk_upload_file' ).val() );
		// file_data = $(this).prop('files')[0];
		if ( !fileInput && fileInput == '' ) {
			jQuery( '#ifsa-loading-import' ).hide();
			alert( 'Please select file' );
			return false;
		}

		var fileName = file_data.name;
		var fileval = fileInput;

		var extension = fileval.replace( /^.*\./, '' );
		extension = extension.toLowerCase();

		var str = 'csv';
		var allowedType = new Array();
		// This will return an array with strings "1", "2", etc.
		allowedType = str.split( ',' );
		console.log( allowedType );

		if ( jQuery.inArray( extension, allowedType ) == - 1 ) {
			jQuery( '#ifsa-loading-import' ).hide();
			alert( 'Only csv file format allowed. Please try again.' );
			$.trim( $( '#bulk_upload_file' ).val( '' ) );
			e.preventDefault();
			return false;
		}

		var form_data = new FormData();
		form_data.append( 'file', file_data );
		form_data.append( 'action', 'file_upload' );
		form_data.append( 'nonce', ifsa_script_vars.nonce );

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			contentType: false,
			processData: false,
			data: form_data,
			success: function( response ) {
				console.log( response );
				if ( 0 === response.err ) {
					var session_id = response.session_id;
					var totalRecords = response.total_records;
					var loopCount = Math.ceil( totalRecords / 10 );
					jQuery( '#ifsa-loading-import' ).hide();
					$.trim( $( '#bulk_upload_file' ).val( '' ) );
					jQuery( '.cls-total-record' ).html( 'Total Record imported: ' + totalRecords );
				} else {
					console.log( response.message );
					jQuery( '#ifsa-loading-import' ).hide();
				}
			}
		} );
	} );

	$( document ).on( 'click', '#export_csv', function( e ) {
		e.preventDefault();

		var data = {
			'action': 'download_csv',
		};

		// since 2.8    is always defined in the admin header and points to admin-ajax.php
		jQuery.post( ajaxurl, data, function( response ) {
			jQuery( this ).html( response );
		} );
	} );


		$( document ).on( 'click', '#ifsa-verify_member-btn', function( e ) {
			e.preventDefault();
			
		jQuery('#ifsa-loading-renew-profile').css('display','inline-block');
		
			var data = {
				'action': 'ifsa_renew_request_profile',
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function( response ) {
			if (response == '1') {
				$( '#ifsa_renewal_form-sucess' ).css( { 'display': 'block', 'animation': 'fadein 1s linear' } );
				$( '#ifsa_renewal_form-success_btn' ).css( 'display', 'none' );
				jQuery('#ifsa-loading-renew-profile').hide();
				jQuery('#ifsa_renewal_form-sucess-profile').show();
			}else if (response == 'already requested') {
				alert('You have already submitted request');
			}
			jQuery('#ifsa-loading-renew-profile').hide();
				
			} );
	} );

})( jQuery );