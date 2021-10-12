<?php

/**
 * Fired during plugin activation
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/includes
 * @author     Multidots <nishit.langaliya@multidots.com>
 */
class Ifsa_Member_Verification_Activator {
	
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		add_option( 'Activated_Plugin', 'ifsa-member-verification' );
		
		global $table_prefix, $wpdb;
		
	//	$tblname        = 'ifsa_lc_member';
		$ifsa_lc_member = $table_prefix . "ifsa_lc_member";
		$ifsa_log       = $table_prefix . "ifsa_log";
		#Check to see if the table exists already, if not, then create it
		
		if ( $wpdb->get_var( "show tables like '$ifsa_lc_member'" ) != $ifsa_lc_member ) { // WPCS: unprepared SQL ok.
			
			$sql = "CREATE TABLE `" . $ifsa_lc_member . "` ( ";
			$sql .= "  `id`  bigint(20)   NOT NULL auto_increment, ";
			$sql .= "  `user_id`  int(128)   NULL, ";
			$sql .= "  `lc_adminid`  int(128)   NULL, ";
			$sql .= "  `committee_id`  text    NULL, ";
			$sql .= "  `region_id`  text    NULL, ";
			$sql .= "  `action_date`  datetime    NULL, ";
			$sql .= "  `member_status`  int(128)    NULL, ";
			$sql .= "  `source`  text    NULL, ";
			$sql .= "  `reason`  text    NULL, ";
			$sql .= " PRIMARY KEY `member_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // WPCS: unprepared SQL ok.

			
		}
		
		if ( $wpdb->get_var( "show tables like '$ifsa_log'" ) != $ifsa_log ) { // WPCS: unprepared SQL ok.
			
			$sql1 = "CREATE TABLE `" . $ifsa_log . "` ( ";
			$sql1 .= "  `id`  bigint(20)   NOT NULL auto_increment, ";
			$sql1 .= "  `log_action` text   NULL, ";
			$sql1 .= "  `remark`  text   NULL, ";
			$sql1 .= "  `logged_in_user_id`   bigint(20)    NULL, ";
			$sql1 .= "  `member_id`   bigint(20)    NULL, ";
			$sql1 .= "  `action_date`  datetime    NULL, ";
			$sql1 .= "  `user_ip`  text    NULL, ";
			$sql1 .= " PRIMARY KEY `log_id` (`id`) ";
			$sql1 .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
			
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			dbDelta( $sql1 ); // WPCS: unprepared SQL ok.
			
		}
		
	}
	
}
