(function( $ ) {
	'use strict';
	$(document).ready(function(){
		//Date picker initialization and settings
		$("#ifsa_general_setting_date_field").datepicker({dateFormat: "dd-mm-yy"});
		$("#ifsa_member_type").change(function (){
			var member_type = $("#ifsa_member_type").val();
			if (member_type == "lc_member"){
				$(".ifsa_admin_member_status").show();
			}
			else {
				$(".ifsa_admin_member_status").hide();
			}
		});
	});
})( jQuery );

