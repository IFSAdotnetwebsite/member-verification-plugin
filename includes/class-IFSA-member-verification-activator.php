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
class IFSA_Member_Verification_Activator {
	
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
		
	//	$tblname        = 'IFSALCMember';
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

        IFSA_Member_Verification_Activator::create_xprofile_fields();

        // Create the member-register and member-renew pages

	}

    /**
     * Create the buddypress xprofile fields needed by the plugin
     * @return void
     */
    static function create_xprofile_fields(){
        //     Type of fields in BP
        //
        //      checkbox
        //       datebox
        //       multiselectbox
        //       number
        //       url
        //       radio
        //       selectbox
        //       textarea
        //       textbox
        //       telephone
        //        wp-biography
        //        wp-textbox
        //        checkbox_acceptance
        //        option
        $fields = array(
                'IFSA Region' => 'textbox', //TODO: make this not editable
                'Local Committee Name' => 'textbox',
                'Gender' => 'selectbox',
                'Nationality' => 'selectbox', // This should be a dropdown
                'University Name' => 'textbox',
                'University Country' => 'selectbox',
                'University Level' => 'selectbox',
                'Course Topic' => 'textbox',
                'Graduation Day' => 'datebox');


        $group = BP_XProfile_Group::get(array('profile_group_id' => 1, 'fetch_fields'=>true, 'fetch_field_data'=>true))[0];
        // Base group
        $existing_fields = array();
        foreach($group->fields as $field){
            $existing_fields[$field->name] = $field->type;
        }

        $field_ids = array();
        foreach ($fields as $field_name => $field_type){
            // Check that the field has not been created yet
            if(!array_key_exists($field_name, $existing_fields)){
                $field_id = xprofile_insert_field(array(
                    'field_group_id' => 1, // Default field group: 'Base'
                    'name' => $field_name,
                    'type' => $field_type
                ));
                if(!$field_id){
                    error_log("[membership verification] Error while creating field". $field_name );
                }
                $field_ids[$field_name] = $field_id;
            }
        }

        // put all countries as an option
        foreach(array('Nationality', 'University Country') as $field){
            IFSA_Member_Verification_Activator::insert_xprofile_options(COUNTRY_LIST, $field_ids[$field] ?? NULL);
        }
        // University Levels
        IFSA_Member_Verification_Activator::insert_xprofile_options(UNI_LEVELS, $field_ids['University Level'] ?? NULL);
        // Gender
        IFSA_Member_Verification_Activator::insert_xprofile_options(GENDERS, $field_ids['Gender'] ?? NULL);

        // Make the IFSA Region and Local Committee readonly
        // Uses http://buddydev.com/plugins/bp-non-editable-profile-fields/ plugin
        if(function_exists('bpne_field_helper')){
            foreach (array('IFSA Region', 'Local Committee Name') as $field){
                if(array_key_exists($field, $field_ids)){
                    bpne_field_helper()->update_field_editing_preference($field_ids[$field], 'no');
                }
            }
        }

    }

    static function insert_xprofile_options($options, $parent_id, $field_group_id=1){
        if(is_null($parent_id)){return;} // If field ID is not set return (as means field was not created now)
        $i = 0;
        foreach($options as $option){
            $res = !xprofile_insert_field(array(
                    'field_group_id' => $field_group_id,
                    'parent_id' => $parent_id,
                    'type' => 'option',
                    'name' => $option,
                    'option_order' => $i++
                )
            );
            if(!$res){
                error_log("[membership verification] Error while creating field with ID:".$parent_id .", Value: ".$option);
            }
        }
    }
	
}
