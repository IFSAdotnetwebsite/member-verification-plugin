<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 * @since      1.0.0
 */

require_once plugin_dir_path( __FILE__ ) . 'class-ifsa-member-verification-logs.php';

/**
 * Main class to handle all the methods of plugin
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 */
class Ifsa_Member_Verification_Admin {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;
	
	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}
	
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ifsa-member-verification-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
	}
	
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ifsa-member-verification-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
	
	
	
	/* Omit closing PHP tag to avoid "Headers already sent" issues. */
	
	/**
	 * Function to register custom user profile fields for IFSA member
	 */
	public function ifsa_custom_user_profile_fields( $user ) {
		//$terms = get_terms( 'regions' );
		$committees_list = get_terms(
			[
				'taxonomy'   => 'committee',
				'hide_empty' => false,
			]
		);

        $user_committee   = get_the_author_meta( 'ifsa_committee', $user->ID );
        $ifsa_member_type = $this->get_ifsa_member_type($user->ID);
        $ifsa_member_status = $this->get_ifsa_member_status($user->ID );
		
		?>

		<table class="form-table">
			<tr>
				<th><label for="ifsa_committee">User Committee</label></th>
				<td>
					<select name="ifsa_committee" id="ifsa_committee">
						<option value="">--Select Committee--</option>
						<?php foreach ( $committees_list as $lc ) { ?>
							<option value="<?php echo esc_attr( $lc->term_id ); ?>" <?php if ( $lc->term_id == $user_committee ) {
								echo 'selected';
							} ?>><?php esc_html_e( $lc->name, 'Ifsa_Member_Verification' ); ?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="ifsa_member_type">User type</label></th>
				<td>
					<select name="ifsa_member_type" id="ifsa_member_type">
						<option value="none">None</option>
						<option value="lc_member" <?php if ( $ifsa_member_type === 'lc_member' ) {
							echo 'selected';
						} ?>>LC Member
						</option>
						<option value="lc_admin"  <?php echo 'selected';  if ( $ifsa_member_type === 'lc_admin' ) {
							echo 'selected';
						} ?>>LC Admin
						</option>
					</select>
				</td>
			</tr>
            <tr class="ifsa_admin_member_status">
<!--                TODO hide this with JS if user is not an lc_member-->
                <th>User status</th>
                <td>
                    <input type="radio" name="ifsa_member_status" value="verified" id="ifsa_member_status_approved" <?php if($ifsa_member_status=="approved"){echo 'checked';}?>>
                    <label for="ifsa_member_status_verified">The User is a verified IFSA member</label>
                </td>

                <td>
                    <input type="radio" name="ifsa_member_status" value="not_verified" id="ifsa_member_status_rejected" <?php if($ifsa_member_status!="approved"){echo 'checked';}?>>
                    <label for="ifsa_member_status_rejected">The User is <strong>NOT</strong> a verified approved member</label>
                </td>
            </tr>
<!--            <tr>-->
<!--                <th></th>-->
<!--                <td>-->
<!--                    <input type="checkbox" id="ifsa_update_paidmembershippro" name="ifsa_update_paidmembershippro" checked>-->
<!--                    <label for="ifsa_update_paidmembershippro">Sync paid PaidmembershipPro level with the member verification (default yes)</label>-->
<!--                </td>-->
<!--            </tr>-->
		</table>
		<?php
	}

    /**
     * Returns the type of users for IFSA member verification
     * this can be 'lc_member', 'lc_admin' or 'none'
     * @param $user_id
     * @return string
     */
    function get_ifsa_member_type($user_id): string
    {
        $user = get_userdata($user_id);
        if ($user){
            $roles = (array) $user->roles;
            //LC Member is first so in case a user is both member and admin (this should not happen!) the type is member
            if (in_array('lc_member', $roles)){
                return 'lc_member';
            }
            elseif (in_array('lc_admin', $roles)){
                return 'lc_admin';
            }
            else{
                return 'none';
            }
        }
        else{
            return 'none';
        }
    }

    /**
     * Check if member is verified
     * @param $user_id
     * @return string
     */
    function get_ifsa_member_status($user_id): string{
        $user_status = get_user_meta($user_id, 'ifsa_active_status', true);
        if ($user_status=='true'){
            return 'verified';
        }
        else{
            return "not_verified";
        }
    }
	
	/**
	 * Save data the IFSA custom user profile fields
	 */
	public function ifsa_save_custom_user_profile_fields( $user_id ) {
		// again do this only if you can
		if ( ! current_user_can( 'manage_options' ) ) {
			return ;
		}

        // Take inputs from the form
        $lc = $this->get_post_var('ifsa_committee', false);
        $ifsa_member_type = $this->get_post_var('ifsa_member_type');
        $ifsa_member_status = $this->get_post_var('ifsa_member_status');
        // Current values in the database
        $old_lc = get_user_meta($user_id, "ifsa_commmittee", true);
        $old_ifsa_member_type = $this->get_ifsa_member_type($user_id);
        $old_ifsa_member_status = $this->get_ifsa_member_status($user_id);

        // Since the verified status doesn't make sense for LCs, it is artificially added here. Maybe a hack.
        if ($ifsa_member_type == "lc_admin" && $ifsa_member_type == $old_ifsa_member_type){
            $ifsa_member_status = "verified";
        }


        // Conditions that needs to remove the user
        // Note member_type 'none' or status not verified are assumed to be practically the same
        if ($old_lc != $lc ||
            $ifsa_member_type != $old_ifsa_member_type ||
            $ifsa_member_type == 'none' ||
            ($ifsa_member_status == "not_verified" && $ifsa_member_status != $old_ifsa_member_status)
        ){
            $this->remove_member($user_id, $ifsa_member_type);
        }

        if (
            $ifsa_member_type !="none" &&
            $ifsa_member_status == "verified" &&
            ($old_lc != $lc  ||
            $ifsa_member_type != $old_ifsa_member_type ||
            $ifsa_member_status != $old_ifsa_member_status)
        )
        {
            $this->make_member($user_id, $ifsa_member_type, $lc);
        }

	}

    /**
     * Helper function that call the correct make function depending on member type
     * @param $user_id
     * @param $ifsa_member_type
     * @param $lc_id
     */
    function make_member($user_id, $ifsa_member_type, $lc_id){
        if ($ifsa_member_type=='lc_member'){
            $this->make_lc_member($user_id, $lc_id);
        }
        elseif ($ifsa_member_type == 'lc_admin'){
            $this->make_lc_admin($user_id, $lc_id);
        }
    }
    /**
     * Helper function that call the correct remove function depending on member type
     * @param $user_id
     * @param $ifsa_member_type
     */
    function remove_member($user_id, $ifsa_member_type){
        if ($ifsa_member_type=='lc_member'){
            $this->remove_lc_admin($user_id);
        }
        elseif ($ifsa_member_type == 'lc_admin'){
            $this->remove_lc_admin($user_id);
        }
    }

    /**
     * Returns the id of the admin for the given LC. In case of no or multiple admins shows an error and return WP_Error
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_lc_admin($lc_id){

        $query = new WP_User_Query(
                array(
                    "meta_key" => "ifsa_committee",
                    "meta_value" => $lc_id,
                    "fields" => "ID"
                )
        );
//        global $wpdb;
//        $sql = $wpdb->prepare(
//                "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = ifsa_committee AND meta_value = %s", $lc_id);
//
//        $res = $wpdb->get_col($sql);

        if (empty($query->get_results())){
            $this->admin_message("Error! No LC admin for {$lc_id}");
            return new WP_Error("No LC Admin");
        }
        if (count($query->get_results()) > 1){
            $this->admin_message("Error! More than 1 LC Admin fo {$lc_id}");
            return new WP_Error("More than 1 LC Admin");
        }
        else{
            return $query->get_results()[0];
        }


    }

    /**
     * Utility function to show a message in the admin page
     * @param string $message The message
     * @param string $notice_type accepts 'notice-error', 'notice-warning', 'notice-success'
     */
    function admin_message($message, string $notice_type = 'notice-error'){
        $func = function () use ($message, $notice_type) {
            echo "<div class='notice is-dismissible {$notice_type}'>
                    {$message}
            </div>";
        };
        add_action('admin_notices', $func);
    }

    /**
     * Return the region id associated with a given LC
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_region($lc_id){
        $args = array(
            'post_type' => 'regions',
            'tax_query' => array(
                array(
                    'taxonomy' => 'committee',
                    'field' => 'term_id',
                    'terms' => $lc_id
                )
            )
        );
        $query = new WP_Query( $args );
        // Safety check that the number of regions is correct
        $n_regions = count($query->posts);
        if ( $n_regions != 1){
            $this->admin_message("Error! An LC can be only in one region. {$lc_id} si in {$n_regions}");
            return new WP_Error();
        }
        return $query->posts[0]->ID;
    }
    /**
     * Makes the user an LC member
     * @param $user_id
     * @param $lc_id
     */
    function make_lc_member($user_id, $lc_id){

        $lc_admin = $this->get_lc_admin($lc_id);
        if (is_wp_error($lc_admin)){return;}

        $region_id = $this->get_region($lc_id);
        if (is_wp_error($region_id)){return;}

        // Update the ifsa_lc_member table
        global $wpdb;

        $wpdb->insert($wpdb->prefix . 'ifsa_lc_member',
            array(
                "user_id" => $user_id,
                "lc_adminid" => $lc_admin,
                "committee_id" => $lc_id,
                "region_id" => $region_id,
                "action_date" => bp_core_current_time(),
                "member_status" => IFSA_LC_MEMBER_LEVEL,
                "source" => "Super Admin"
            )
        );

        // Update metadata
        update_user_meta( $user_id, 'user_active_status', "true" );

        // Set the LC
        update_user_meta( $user_id, 'ifsa_committee', $lc_id );

        // Update PMPro membership
        if(function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_LC_MEMBER_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta( $user_id, 'membership_assigned', IFSA_LC_MEMBER_LEVEL );
            }
        }

        // Add user role
        $user = get_user_by( 'id', $user_id );
        $user->add_role( 'lc_member' );

        // Log the action
        $this->log("Super Admin approved", "Super Admin made LC Member", $user_id);
    }

    /**
     * Removes a user from the LC verified status
     * @param $user_id
     */
    function remove_lc_member($user_id){

        // Update the ifsa_lc_member table
        global $wpdb;

        $wpdb->update($wpdb->prefix . 'ifsa_lc_member',
            array(
                "action_date" => bp_core_current_time(),
                "member_status" => IFSA_MEMBER_REMOVED_LEVEL,
                "source" => "Super Admin"
            ),
            array(
                "user_id" => $user_id
            )
        );

        // Update metadata
        update_user_meta( $user_id, 'user_active_status', "no" );

        // No need to change the LC

        // Update PMPro membership
        if(function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_NO_MEMBERSHIP_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta( $user_id, 'membership_assigned', IFSA_NO_MEMBERSHIP_LEVEL );
            }
        }

        // Add user role
        $user = get_user_by( 'id', $user_id );
        $user->remove_role( 'lc_member');

        // Log the action
        $this->log("Super Admin Removed", "Super Admin removed LC Member", $user_id);

        // Think of sending an email to notify user of the change

    }

    /**
     * Make the user an LC admin
     * @param $user_id
     * @param $lc_id
     */
    function make_lc_admin($user_id, $lc_id){

        // Update metadata
        update_user_meta( $user_id, 'user_active_status', "true" );

        // Set the LC
        update_user_meta( $user_id, 'ifsa_committee', $lc_id );

        // Update PMPro membership
        if(function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_LC_ADMIN_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta( $user_id, 'membership_assigned', IFSA_LC_ADMIN_LEVEL );
            }
        }

        // Add user role
        $user = get_user_by( 'id', $user_id );
        $user->add_role( 'lc_admin' );

        $this->log("Super Admin approved", "made LC Admin", $user_id);

    }

    /**
     * Remove the LC admin for a user
     * @param $user_id
     */
    function remove_lc_admin($user_id){
        // Check if there is any user connected to this LC admin, if so doesn't remove the lc admin account
        global $wpdb;
        $sql_query = $wpdb->prepare(
              "SELECT lc_admindid from {$wpdb->prefix}ifsa_lc_member WHERE lc_admindid = %s",
            $user_id
        );
        $lc_members = $wpdb->get_col($sql_query);
        if (!empty($lc_members)){
            $this->admin_message("Error! Cannot remove LC admin as there are still users connected to it");
            return ;
        }

        // Update metadata
        update_user_meta( $user_id, 'user_active_status', "no" );

        // No removal of LC from metadata

        // Update PMPro membership
        if(function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_NO_MEMBERSHIP_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta( $user_id, 'membership_assigned', IFSA_NO_MEMBERSHIP_LEVEL );
            }
        }

        // Add user role
        $user = get_user_by( 'id', $user_id );
        $user->remove_role('lc_admin' );

        $this->log("Super Admin removed", "removed LC Admin", $user_id);

    }

    /**
     * Small utility function to return a post variable or default
     * @param $var
     * @param $default
     * @return mixed
     */
    function get_post_var($var, $default=""){
        if (isset( $_POST[$var] ) && ! empty( $_POST[$var])){
            return sanitize_text_field($_POST[$var]);
        }
        else{
            return $default;
        }
    }


    function log($log_action, $remark,$member_id){
        global $wpdb;
        $ifsa_log = $wpdb->prefix . 'ifsa_log';
        $admin_id = get_current_user_id(); // This code must be run by an admin
        $action_time = current_time($type="mysql");

        if ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( sanitize_text_field($_SERVER['REMOTE_ADDR']) ) ) {
            $user_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
        }
        else{
            $user_ip = "";
        }

        $query  = "INSERT INTO {$ifsa_log} ( log_action,remark, logged_in_user_id ,member_id, action_time, user_ip) VALUES ( %s,%s, %d, %d, %s, %s )";

        $sql_log = $wpdb->prepare( $query, "$log_action", "$remark", $admin_id, $member_id, $action_time, $user_ip );

        $result_log = $wpdb->query( $sql_log );

        // TODO remove?
        if ( empty( $resultlog ) || is_wp_error( $result_log ) ) {

        }
    }


	/**
	 * Funcition to load  Plugin
	 */
	public function ifsa_load_plugin() {
		
		/* do stuff once right after activation */
		if ( ! function_exists( 'bp_is_active' ) ) {
			//if (is_admin() && get_option('Activated_Plugin') == 'ifsa-member-verification') {
			delete_option( 'Activated_Plugin' );
			
			add_action( 'admin_notices', array( $this, 'self_deactivate_notice' ) );
			
			// Check for proper function.
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			
			// Deactivate.
			deactivate_plugins( IFSA_MEMBER_VERIFICATION_PATH . 'ifsa-member-verification.php' );
			
			
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			//    }
		}
	}
	
	/**
	 * Display an error message when parent plugin is missing
	 */
	public function self_deactivate_notice() {
		?>
		<div class="notice notice-error">
			Please install and activate Buddypress plugin before activating this plugin.
		</div>
		<?php
	}
	
	/**
	 * Registers the menu page on admin side
	 */
	public function ifsa_member_verification_admin_menu() {
		//Adding custom menu page Member Verification Plugin
		add_menu_page(
			__( 'Member Verification Plugin', 'Ifsa_Member_Verification' ),
			__( 'Member Verification Plugin', 'Ifsa_Member_Verification' ),
			'manage_options',
			'ifsa-verification',
			array( $this, 'ifsa_member_verification_contents' ),
			'dashicons-buddicons-buddypress-logo',
			65
		);
		
		//Adding sub menu page to the parent Member Verification page
		add_submenu_page( 'ifsa-verification', 'Add New Region', 'Add New Region', 'manage_options', 'post-new.php?post_type=regions' );
		add_submenu_page( 'ifsa-verification', 'Add New Committee', 'Add New Committee', 'manage_options', 'edit-tags.php?taxonomy=committee&post_type=regions' );
		
		add_submenu_page(
			'ifsa-verification', 'General Settings', 'General Settings', 'manage_options', 'ifsa-settings',
			array( $this, 'ifsa_member_verification_contents' )
		);
	}
	
	/**
	 * Call back Function for the Menu page
	 */
	public function ifsa_member_verification_contents() {
		//Admin page markup
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/ifsa-member-verification-admin-display.php';
	}
	
	/**
	 * Registering genetal setting section on the admin page
	 */
	public function ifsa_member_verification_general_settings() {
		//Adding general setting section __('General', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_general_settings',
			'',
			array( $this, 'ifsa_member_verification_general_setting_callback' ),
			'ifsa_member_verification_general_settings'
		);
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_general_setting_date_field', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_1_description', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_2_description', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'ifsa_step_3_description', array( $this, 'sanitize_textarea_field' ) );

		register_setting( 'ifsa_member_verification_general_settings', 'after_30', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'after_21', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'after_15', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'before_30', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_general_settings', 'next_yr_valid', array( $this, 'sanitize_textarea_field' ) );
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_general_setting_callback() {
		//Markup for general page
		include_once plugin_dir_path( __FILE__ ) . 'partials/ifsa_member_verification_general_settings.php';
	}
	
	
	/**
	 * Registering the email settings page on the admin side
	 */
	public function ifsa_member_verification_email_settings() {
		//Adding email setting section __('Email', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_email_settings',
			'',
			array( $this, 'ifsa_member_verification_email_setting_callback' ),
			'ifsa_member_verification_email_settings'
		);
		
		//Registering the settings field and its value in the option table
		register_setting( 'ifsa_member_verification_email_settings', 'welcome_email_after_verify_member', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reminder_on_same_date_when_renewed', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'member_bulk_invite_for_join_the_community', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'fifteen_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reject_by_lc_admin_email_to_member', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'twentytwo_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_before_renewal_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_after_expire_date', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'remove_member_content', array( $this, 'sanitize_textarea_field' ) );
	
		
		register_setting( 'ifsa_member_verification_email_settings', 'welcome_email_after_verify_member_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reminder_on_same_date_when_renewed_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'member_bulk_invite_for_join_the_community_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'fifteen_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'reject_by_lc_admin_email_to_member_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'twentytwo_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_before_renewal_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'thirty_days_after_expire_date_subject', array( $this, 'sanitize_textarea_field' ) );
		register_setting( 'ifsa_member_verification_email_settings', 'remove_member_content_subject', array( $this, 'sanitize_textarea_field' ) );
	
	
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_email_setting_callback() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/ifsa_member_verification_email_settings.php';
	}
	
	/**
	 * Markup for the log settings page on the admin general settings page
	 */
	public function ifsa_member_verification_log_settings() {
		//Adding tab setting section__('Tab-3', 'Ifsa_Member_Verification')
		add_settings_section(
			'ifsa_member_verification_log_settings',
			'',
			array( $this, 'ifsa_member_verification_log_setting_callback' ),
			'ifsa_member_verification_log_settings'
		);
		//Registering the settings field and its value in the option table
		register_setting( 'ifsa_member_verification_log_settings', 'ifsa_member_verification_log_settings', array( $this, 'sanitize_textarea_field' ) );
	}
	
	/**
	 * Function to insert the markup for the general setting section
	 */
	public function ifsa_member_verification_log_setting_callback() {
		//WP List Table for the admin log page
		$ifsa_list_table = new Ifsa_List_Table();
		
	
		?>
				<form id="nds-user-list-form" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

				<?php
		$ifsa_list_table->prepare_items();
		$ifsa_list_table->search_box('Search', 'search');
		$ifsa_list_table->display();
		?>
		</form>
	<?php }
	
	/**
	 * Funciotn to register Region custom  post type
	 */
	public function ifsa_register_regions() {
		$labels = array(
			'menu_name'          => esc_html__( 'Regions', 'ifsa-member-verification' ),
			'name_admin_bar'     => esc_html__( 'Region', 'ifsa-member-verification' ),
			'add_new'            => esc_html__( 'Add Region', 'ifsa-member-verification' ),
			'add_new_item'       => esc_html__( 'Add new Region', 'ifsa-member-verification' ),
			'new_item'           => esc_html__( 'New Region', 'ifsa-member-verification' ),
			'edit_item'          => esc_html__( 'Edit Region', 'ifsa-member-verification' ),
			'view_item'          => esc_html__( 'View Region', 'ifsa-member-verification' ),
			'update_item'        => esc_html__( 'View Region', 'ifsa-member-verification' ),
			'all_items'          => esc_html__( 'All Regions', 'ifsa-member-verification' ),
			'search_items'       => esc_html__( 'Search Regions', 'ifsa-member-verification' ),
			'parent_item_colon'  => esc_html__( 'Parent Region', 'ifsa-member-verification' ),
			'not_found'          => esc_html__( 'No Regions found', 'ifsa-member-verification' ),
			'not_found_in_trash' => esc_html__( 'No Regions found in Trash', 'ifsa-member-verification' ),
			'name'               => esc_html__( 'Regions', 'ifsa-member-verification' ),
			'singular_name'      => esc_html__( 'Region', 'ifsa-member-verification' ),
		);
		$args   = array(
			'label'               => __( 'Region', 'text_domain' ),
			'description'         => __( 'Regions', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'ifsa-verification',
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);
		register_post_type( 'Regions', $args );
	}
	
	
	/**
	 * Funciton to Register Custom Taxonomy
	 */
	public function ifsa_region_taxonomy() {
		
		$labels = array(
			'name'                       => _x( 'Committees', 'Committee General Name', 'ifsa-member-verification' ),
			'singular_name'              => _x( 'Committee', 'Committee Singular Name', 'ifsa-member-verification' ),
			'menu_name'                  => __( 'Committee', 'ifsa-member-verification' ),
			'all_items'                  => __( 'All Items', 'ifsa-member-verification' ),
			'parent_item'                => __( 'Parent Item', 'ifsa-member-verification' ),
			'parent_item_colon'          => __( 'Parent Item:', 'ifsa-member-verification' ),
			'new_item_name'              => __( 'New Item Name', 'ifsa-member-verification' ),
			'add_new_item'               => __( 'Add New Item', 'ifsa-member-verification' ),
			'edit_item'                  => __( 'Edit Item', 'ifsa-member-verification' ),
			'update_item'                => __( 'Update Item', 'ifsa-member-verification' ),
			'view_item'                  => __( 'View Item', 'ifsa-member-verification' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'ifsa-member-verification' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'ifsa-member-verification' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'ifsa-member-verification' ),
			'popular_items'              => __( 'Popular Items', 'ifsa-member-verification' ),
			'search_items'               => __( 'Search Items', 'ifsa-member-verification' ),
			'not_found'                  => __( 'Not Found', 'ifsa-member-verification' ),
			'no_terms'                   => __( 'No items', 'ifsa-member-verification' ),
			'items_list'                 => __( 'Items list', 'ifsa-member-verification' ),
			'items_list_navigation'      => __( 'Items list navigation', 'ifsa-member-verification' ),
		);
		$args   = array(
			'labels'            => $labels,
			
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'hierarchical' => false,
'parent_item'  => null,
'parent_item_colon' => null,
		);
		register_taxonomy( 'committee', array( 'regions' ), $args );
	}
	
	/**
	 * Delete user from member list
	 */
	public function ifsa_custom_remove_user($user_id) {
		global $wpdb;
		$removefromdb = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}ifsa_lc_member WHERE user_id = %d", $user_id));
		
	}

	public function ifsa_hide_dashboard() {
		if ( ! current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			 wp_redirect(home_url()); exit;
		 }
	   }

	
	   /**
	 * Funciton is use to download csv file
	 */
	public function export_csv_callback() {
		
		global $wpdb;
		global $bp;
		
		$lastupdated = bp_core_current_time();
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'export_csv_file' && is_admin() ) {
			
			// Query
			$statement = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ifsa_log " ));
			
			// file creation
			$wp_filename = "filename_" . gmdate( "d-m-y" ) . ".csv";
			
			// Clean object
			ob_end_clean();
			
			// Open file
			$wp_file = fopen( $wp_filename, "w" );
			$fields  = array( 'id', 'Actions', 'Remarks', 'LC Admin', 'LC member', 'Date Time','IP' );
			
			fputcsv( $wp_file, $fields );
			
			// loop for insert data into CSV file
			
				foreach ( $statement as $ifsa_log_data ) {
					$lcadminname  = get_user_by( 'id', $ifsa_log_data->logged_in_user_id );
					$lcmembername = get_user_by( 'id', $ifsa_log_data->member_id );
					$wp_array = array(
						'id'                => $ifsa_log_data->id,
						'log_action'        => $ifsa_log_data->log_action,
						'remark'            => strip_tags($ifsa_log_data->remark),
						'logged_in_user_id' => $lcadminname->display_name,
						'member_id'         => $lcmembername->display_name,
						'action_date'       =>  date( 'c', strtotime( $ifsa_log_data->action_date )) ,
						'user_ip'           => $ifsa_log_data->user_ip,
					);
				
				
				fputcsv( $wp_file, $wp_array );
			}
			
			
			// Close file
			fclose( $wp_file );
			
			// download csv file
			header( "Content-Description: File Transfer" );
			header( "Content-Disposition: attachment; filename=" . $wp_filename );
			header( "Content-Type: application/csv;" );
			readfile( $wp_filename );
			exit;
		} else {
		
		}
	}

	
	   
}
