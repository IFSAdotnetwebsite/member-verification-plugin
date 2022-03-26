jQuery( document ).ready( function() {
    // TODO why??
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
            "emptyTable":     "No LC members"
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
        var user_id = jQuery( this ).data( 'user-id' );
        var rowID = jQuery( this ).attr( 'lc-admin-id' );

        jQuery( '#ifsa-loading-' + rowID ).show();
        jQuery( '.cls-action-' + rowID ).html( '' );
        var data = {
            'action': 'ifsa_approve_member',
            '_ajax_nonce': $( '#lc_admin_approval' ).data( 'nonce' ),
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

        var region = jQuery( this ).val();
        var data = {
            'action': 'ifsa_list_region',
            'region': region
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post( ajaxurl, data, function( response ) {
            jQuery( '#lc' ).html( response );
        } );
    } );
} );