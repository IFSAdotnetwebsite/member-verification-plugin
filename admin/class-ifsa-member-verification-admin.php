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

require_once plugin_dir_path(__FILE__) . 'class-ifsa-member-verification-logs.php';

/**
 * Main class to handle all the methods of plugin
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 */
class Ifsa_Member_Verification_Admin
{

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
     * Flag to avoid running the user_profile_fields actions more than once
     * @var bool
     */
    private $first_run_user_prof_fields = true;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ifsa-member-verification-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ifsa-member-verification-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-datepicker');
    }



    /* Omit closing PHP tag to avoid "Headers already sent" issues. */

    /**
     * Function to register custom user profile fields for IFSA member
     */
    public function ifsa_custom_user_profile_fields($user)
    {
        //$terms = get_terms( 'regions' );
        $committees_list = get_terms(
            [
                'taxonomy' => 'committee',
                'hide_empty' => false,
            ]
        );

        if ($user == "add-new-user") { // When creating a new user it makes no sense to query the database
            $lc_id = 0;
            $lc_name = "";
            $ifsa_member_type = "none";
            $ifsa_member_status = "not_verified";
        } else {
            $lc_id = intval(get_the_author_meta('ifsa_committee', $user->ID));
            $lc_name = $this->get_lc_name($lc_id);
            $ifsa_member_type = $this->get_ifsa_member_type($user->ID);
            $ifsa_member_status = $this->get_ifsa_member_status($user->ID);
        }


        ?>
        <h2> IFSA Member Verification </h2>
        <p class="description"> Current user status
            is <?php echo "LC Id: {$lc_name}, member type: {$ifsa_member_type}, status: {$ifsa_member_status}" ?></p>
        <table class="form-table">
            <tr>
                <th><label for="ifsa_committee">User Committee</label></th>
                <td>
                    <select name="ifsa_committee" id="ifsa_committee">
                        <option value="none">--No Committee--</option>
                        <?php foreach ($committees_list as $lc) { ?>
                            <option value="<?php echo esc_attr($lc->term_id); ?>" <?php if ($lc->term_id == $lc_id) {
                                echo 'selected';
                            } ?>><?php esc_html_e($lc->name, 'Ifsa_Member_Verification'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="ifsa_member_type">User type</label></th>
                <td>
                    <select name="ifsa_member_type" id="ifsa_member_type">
                        <option value="none" <?php if ($ifsa_member_type === 'none') {
                            echo 'selected';
                        } ?>>--None--
                        </option>
                        <option value="lc_member" <?php if ($ifsa_member_type === 'lc_member') {
                            echo 'selected';
                        } ?>>LC Member
                        </option>
                        <option value="lc_admin" <?php if ($ifsa_member_type === 'lc_admin') {
                            echo 'selected';
                        } ?>>LC Admin
                        </option>
                    </select>
                    <p class="description">Note a member with user type 'none' or no committee is considered to be the
                        same of a non verified member </p>
                </td>
            </tr>
            <tr class="ifsa_admin_member_status">
                <!--                TODO hide this with JS if user is not an lc_member-->
                <th rowspan="2">User status</th>
                <td>
                    <input type="radio" name="ifsa_member_status" value="verified"
                           id="ifsa_member_status_verified" <?php if ($ifsa_member_status == "verified") {
                        echo 'checked';
                    } ?>>
                    <label for="ifsa_member_status_verified">The User is a verified IFSA member</label>

                </td>
            </tr>
            <tr class="ifsa_admin_member_status">
                <td>
                    <input type="radio" name="ifsa_member_status" value="not_verified"
                           id="ifsa_member_status_not_verified" <?php if ($ifsa_member_status == "not_verified") {
                        echo 'checked';
                    } ?>>
                    <label for="ifsa_member_status_not_verified">The User is <strong>NOT</strong> a verified
                        member</label>
                </td>
            </tr>

        </table>
        <?php
    }

    // TODO: move this to IFSALCMember
    /**
     * Returns the type of users for IFSA member verification
     * this can be 'lc_member', 'lc_admin' or 'none'
     * @param $user_id
     * @return string
     */
    function get_ifsa_member_type($user_id): string
    {
        $user = get_userdata($user_id);
        if ($user) {
            $roles = (array)$user->roles;
            //LC Member is first so in case a user is both member and admin (this should not happen!) the type is member
            if (in_array('lc_member', $roles)) {
                return 'lc_member';
            } elseif (in_array('lc_admin', $roles)) {
                return 'lc_admin';
            } else {
                return 'none';
            }
        } else {
            return 'none';
        }
    }

    /**
     * Check if member is verified
     * @param $user_id
     * @return string
     */
    function get_ifsa_member_status($user_id): string
    {
        $user_status = get_user_meta($user_id, 'user_active_status', true);
        if ($user_status == 'true') {
            return 'verified';
        } else {
            return "not_verified";
        }
    }

    /**
     * Save data the IFSA custom user profile fields
     */
    public function ifsa_save_custom_user_profile_fields($user_id)
    {
        //         The function will be called multiple times because the 'profile_update' hook is fired at least twice
        //         The ideal system would be that function runs only once, but since it since some code (probably from buddypress)
        //         reset the new user role is needed to re-run all the code, however to avoid double logging (to admin_notices and log table)
        //         the flag first_run is kept
        if ($this->first_run_user_prof_fields) {
            $this->first_run_user_prof_fields = false;
        }
        else { // Run this only on the first time
            return;
        }

        // again do this only if you can
        if (!current_user_can('manage_options')) {
            return;
        }

        // Take inputs from the form
        $lc_id = $this->get_post_var('ifsa_committee', 0);
        $ifsa_member_type = $this->get_post_var('ifsa_member_type', 0);
        $ifsa_member_status = $this->get_post_var('ifsa_member_status');

        if (!$lc_id || !$ifsa_member_type) {
            // IFSA member verification post variable are no set
            return;
        }
        // Current values in the database
        $old_lc = intval(get_the_author_meta("ifsa_committee", $user_id));
        $old_ifsa_member_type = $this->get_ifsa_member_type($user_id);
        $old_ifsa_member_status = $this->get_ifsa_member_status($user_id);

        // Since the verified status doesn't make sense for LCs, it is artificially added here. Maybe a hack.
        if ($ifsa_member_type == "lc_admin" && $ifsa_member_type == $old_ifsa_member_type) {
            $ifsa_member_status = "verified";
        }


        // Conditions that needs to remove the user
        // Note member_type 'none' or status not verified are assumed to be practically the same
        if (($old_lc != $lc_id ||
                $ifsa_member_type != $old_ifsa_member_type ||
                $ifsa_member_type == 'none' ||
                ($ifsa_member_status == "not_verified" && $ifsa_member_status != $old_ifsa_member_status)) &&
            $old_lc != "" // If the LC was empty means that it was never a member so no need to remove
        ) {
            $res = $this->remove_member($user_id, $old_ifsa_member_type);
            if (is_wp_error($res)) {
                return;
            }
        }

        if (
            $ifsa_member_type != "none" &&
            $ifsa_member_status == "verified" &&
            ($old_lc != $lc_id ||
                $ifsa_member_type != $old_ifsa_member_type ||
                $ifsa_member_status != $old_ifsa_member_status)
        ) {
            $res = $this->make_member($user_id, $ifsa_member_type, $lc_id);
            if (is_wp_error($res)) {
                return;
            }
        }

        // The message is shown also if no action is taken, maybe not the best idea
        $lc_name = $this->get_lc_name($lc_id);
        $this->admin_message("Successfully update IFSA user membership.
         IFSA LC: {$lc_name}, status: {$ifsa_member_status}, type: {$ifsa_member_type}", "notice-success");



    }

    /**
     * Return the LC name give the ID or empty string if it doesn't exist
     * @param $lc_id
     * @return string The LC Name
     */
    function get_lc_name($lc_id): string
    {
        if ($lc_id == 'none') {
            return "";
        }
        $term_lc = get_term_by("ID", $lc_id, 'committee');
        if (!$term_lc) {
            if ($lc_id != 0) { // LC Id can be 0 (eg. when creating a user) otherwise is a error
                error_log("Unknown LC Id");
                $this->admin_message("Error in LC Id. get in touch with website admin");
            }

            return "";
        }
        return $term_lc->name;
    }

    /**
     * Helper function that call the correct make function depending on member type
     * @param $user_id
     * @param $ifsa_member_type
     * @param $lc_id
     * @return bool|WP_Error
     */
    function make_member($user_id, $ifsa_member_type, $lc_id)
    {
        if ($ifsa_member_type == 'lc_member') {
            return $this->make_lc_member($user_id, $lc_id);
        } elseif ($ifsa_member_type == 'lc_admin') {
            return $this->make_lc_admin($user_id, $lc_id);
        } else {
            return true; // Take no action if member type is none
        }
    }

    /**
     * Helper function that call the correct remove function depending on member type
     * @param $user_id
     * @param $ifsa_member_type
     * @return bool|WP_Error
     */
    function remove_member($user_id, $ifsa_member_type)
    {
        if ($ifsa_member_type == 'lc_member') {
            return $this->remove_lc_member($user_id);
        } elseif ($ifsa_member_type == 'lc_admin') {
            return $this->remove_lc_admin($user_id);
        } else {
            return true; // Take no action if member type is none
        }
    }

    /**
     * Returns the id of the admin for the given LC. In case of no or multiple admins shows an error and return WP_Error
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_lc_admin($lc_id)
    {

        $query = new WP_User_Query(
            array(
                "meta_key" => "ifsa_committee",
                "meta_value" => $lc_id,
                "fields" => "ID",
                "role" => "lc_admin"
            )
        );

        if (empty($query->get_results())) {
            $lc_name = $this->get_lc_name($lc_id);
            $this->admin_message("IFSA Member verification: Error! No LC admin for {$lc_name}");
            return new WP_Error("No LC Admin");
        }
        if (count($query->get_results()) > 1) {
            $lc_name = $this->get_lc_name($lc_id);
            $this->admin_message("IFSA Member verification: Error! More than 1 LC Admin fo {$lc_name}");
            return new WP_Error("More than 1 LC Admin");
        } else {
            return $query->get_results()[0];
        }
    }

    // TODO: migrate this to IFSAUtility
    /**
     * Utility function to show a message in the admin page
     * @param string $message The message
     * @param string $notice_type accepts 'notice-error', 'notice-warning', 'notice-success'
     */
    function admin_message(string $message, string $notice_type = 'notice-error')
    {
        // This is the second time the plugin code is run so is assumed that no new logging is needed
        if (!$this->first_run_user_prof_fields) {
            return;
        }

        $notice = "<div class='notice is-dismissible {$notice_type}'>
                    {$message}
            </div>";

//      Technically the transient could expire before is read, but still the plugin work even if the message system breaks
//      by using the transient (instead of options api) an unnecessary write to the database is avoided.
//      The 200 sec is an arbitrary number.
        $notices = get_transient('ifsa_member_verification_admin_notices');
        $notices = $notices ? $notices : array();
        array_push($notices, $notice);
        set_transient('ifsa_member_verification_admin_notices', $notices, 200);
    }

    /**
     * Function called by the 'admin_notices' hook to actually show the notices
     */
    public function ifsa_show_admin_notices()
    {
        $notices = get_transient('ifsa_member_verification_admin_notices');
        $notices = $notices ? $notices : array();
        foreach ($notices as $notice) {
            echo $notice;
        }
        delete_transient('ifsa_member_verification_admin_notices');
    }

    /**
     * Return the region id associated with a given LC
     * @param $lc_id
     * @return int|WP_Error
     */
    function get_region($lc_id)
    {
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
        $query = new WP_Query($args);
        // Safety check that the number of regions is correct
        $n_regions = count($query->posts);
        if ($n_regions != 1) {
            $this->admin_message("Error! An LC can be only in one region. {$lc_id} si in {$n_regions}");
            return new WP_Error();
        }
        return $query->posts[0]->ID;
    }

    /**
     * Makes the user an LC member
     * @param $user_id
     * @param $lc_id
     * @return bool|int|WP_Error
     */
    function make_lc_member($user_id, $lc_id)
    {

        if ($lc_id == "none") {
            return true;
        } // No action to take if the LC is not set
        $lc_admin = $this->get_lc_admin($lc_id);
        if (is_wp_error($lc_admin)) {
            return $lc_admin;
        }

        $region_id = $this->get_region($lc_id);
        if (is_wp_error($region_id)) {
            return $region_id;
        }

        // Update the IFSALCMember table
        global $wpdb;

        $lc_member_data = array(
            "user_id" => $user_id,
            "lc_adminid" => $lc_admin,
            "committee_id" => $lc_id,
            "region_id" => $region_id,
            "action_date" => bp_core_current_time(),
            "member_status" => IFSA_LC_MEMBER_LEVEL,
            "source" => "Super Admin"
        );

        // Check if the user already in the table
        $sql_query = $wpdb->prepare(
            "SELECT user_id from {$wpdb->prefix}IFSALCMember WHERE user_id = %s",
            $user_id
        );
        $lc_members = $wpdb->get_col($sql_query);

        // If the user doesn't exist yet, insert it. Otherwise, updates the row.
        if (empty($lc_members)) {
            $res = $wpdb->insert($wpdb->prefix . 'IFSALCMember', $lc_member_data);
        } else {
            $res = $wpdb->update($wpdb->prefix . 'IFSALCMember', $lc_member_data,
                array(
                    "user_id" => $user_id
                )
            );
        }


        if ($res != 1) {
            $this->admin_message("Unknown error during IFSA member creation");
            return new WP_Error();
        }

        // Update metadata
        update_user_meta($user_id, 'user_active_status', "true");

        // Set the LC
        update_user_meta($user_id, 'ifsa_committee', $lc_id);

        // Update PMPro membership
        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_LC_MEMBER_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta($user_id, 'membership_assigned', IFSA_LC_MEMBER_LEVEL);
            }
        }

        // Add user role
        $user = get_user_by('id', $user_id);
        $user->add_role('lc_member');

        // Log the action
        $this->log("Super Admin approved", "Super Admin made LC Member", $user_id);

        return true;
    }

    /**
     * Removes a user from the LC verified status
     * @param $user_id
     * @return bool|WP_Error
     */
    function remove_lc_member($user_id)
    {

        // Update the IFSALCMember table
        global $wpdb;

        $res = $wpdb->update($wpdb->prefix . 'IFSALCMember',
            array(
                "action_date" => bp_core_current_time(),
                "member_status" => IFSA_MEMBER_REMOVED_LEVEL,
                "source" => "Super Admin"
            ),
            array(
                "user_id" => $user_id
            )
        );

        if (!$res) {
            return new WP_Error("Error LC member remove");
        }

        // Update metadata
        update_user_meta($user_id, 'user_active_status', "no");

        // No need to change the LC

        // Update PMPro membership
        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_NO_MEMBERSHIP_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta($user_id, 'membership_assigned', IFSA_NO_MEMBERSHIP_LEVEL);
            }
        }

        // Remove user role
        $user = get_user_by('id', $user_id);
        $user->remove_role('lc_member');

        // Log the action
        $this->log("Super Admin Removed", "Super Admin removed LC Member", $user_id);

        // Think of sending an email to notify user of the change
        return true;
    }

    /**
     * Make the user an LC admin
     * @param $user_id
     * @param $lc_id
     * @return bool | WP_Error
     */
    function make_lc_admin($user_id, $lc_id)
    {

        // Update metadata
        update_user_meta($user_id, 'user_active_status', "true");

        // Set the LC
        update_user_meta($user_id, 'ifsa_committee', $lc_id);

        // Update PMPro membership
        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_LC_ADMIN_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta($user_id, 'membership_assigned', IFSA_LC_ADMIN_LEVEL);
            }
        }

        // Add user role
        $user = get_user_by('id', $user_id);
        $user->add_role('lc_admin');

        $this->log("Super Admin approved", "made LC Admin", $user_id);

        return true;

    }

    /**
     * Remove the LC admin for a user
     * @param $user_id
     * @return bool|WP_Error
     */
    function remove_lc_admin($user_id)
    {
        // Check if there is any user connected to this LC admin, if so doesn't remove the lc admin account
        global $wpdb;
        $sql_query = $wpdb->prepare(
            "SELECT user_id from {$wpdb->prefix}IFSALCMember WHERE lc_adminid = %i",
            $user_id
        );
        $lc_members = $wpdb->get_col($sql_query);
        if (!empty($lc_members)) {
            $this->admin_message("Error! Cannot remove LC admin as there are still users connected to it");
            return new WP_Error("Cannot remove LC");
        }

        // Update metadata
        update_user_meta($user_id, 'user_active_status', "no");

        // No removal of LC from metadata

        // Update PMPro membership
        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(IFSA_NO_MEMBERSHIP_LEVEL, $user_id);
            if ($memberLevel == true) {
                update_user_meta($user_id, 'membership_assigned', IFSA_NO_MEMBERSHIP_LEVEL);
            }
        }

        // Add user role
        $user = get_user_by('id', $user_id);
        $user->remove_role('lc_admin');

        $this->log("Super Admin removed", "removed LC Admin", $user_id);
        return true;

    }

    /**
     * Return a sanitized post variable or default
     * @param $var
     * @param $default
     * @return mixed
     */
    function get_post_var($var, string $default = "")
    {
        if (isset($_POST[$var]) && !empty($_POST[$var])) {
            return sanitize_text_field($_POST[$var]);
        } else {
            return $default;
        }
    }


    function log($log_action, $remark, $member_id)
    {
        // This is the second time the plugin code is run so is assumed that no new logging is needed
        if (!$this->first_run_user_prof_fields) {
            return;
        }
        global $wpdb;
        $ifsa_log = $wpdb->prefix . 'ifsa_log';
        $admin_id = get_current_user_id(); // This code must be run by an admin
        $action_time = current_time($type = "mysql");

        if (isset($_SERVER['REMOTE_ADDR']) && !empty(sanitize_text_field($_SERVER['REMOTE_ADDR']))) {
            $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        } else {
            $user_ip = "";
        }

        $res = $wpdb->insert($ifsa_log,
            array(
                "log_action" => $log_action,
                "remark" => $remark,
                "logged_in_user_id" => $admin_id,
                "member_id" => $member_id,
                "user_ip" => $user_ip,
                "action_date" => $action_time
            )
        );

        if (!$res) {
            error_log("Error in saving log");
            $this->admin_message("Unknown error while saving logs, contact web admin");
        }
    }


    /**
     * Function to load  Plugin
     */
    public function ifsa_load_plugin()
    {

        /* do stuff once right after activation */
        if (!function_exists('bp_is_active')) {
            //if (is_admin() && get_option('Activated_Plugin') == 'ifsa-member-verification') {
            delete_option('Activated_Plugin');

            add_action('admin_notices', array($this, 'self_deactivate_notice'));

            // Check for proper function.
            if (!function_exists('deactivate_plugins')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            // Deactivate.
            deactivate_plugins(IFSA_MEMBER_VERIFICATION_PATH . 'ifsa-member-verification.php');


            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }

    /**
     * Display an error message when parent plugin is missing
     */
    public function self_deactivate_notice()
    {
        ?>
        <div class="notice notice-error">
            Please install and activate Buddypress plugin before activating this plugin.
        </div>
        <?php
    }

    /**
     * Registers the menu page on admin side
     */
    public function ifsa_member_verification_admin_menu()
    {
        //Adding custom menu page Member Verification Plugin
        add_menu_page(
            __('Member Verification Plugin', 'Ifsa_Member_Verification'),
            __('Member Verification Plugin', 'Ifsa_Member_Verification'),
            'manage_options',
            'ifsa-verification',
            array($this, 'ifsa_member_verification_contents'),
            'dashicons-buddicons-buddypress-logo',
            65
        );

        //Adding sub menu page to the parent Member Verification page
        add_submenu_page('ifsa-verification', 'Add New Region', 'Add New Region', 'manage_options', 'post-new.php?post_type=regions');
        add_submenu_page('ifsa-verification', 'Add New Committee', 'Add New Committee', 'manage_options', 'edit-tags.php?taxonomy=committee&post_type=regions');

        add_submenu_page(
            'ifsa-verification', 'General Settings', 'General Settings', 'manage_options', 'ifsa-settings',
            array($this, 'ifsa_member_verification_contents')
        );
    }

    /**
     * Call back Function for the Menu page
     */
    public function ifsa_member_verification_contents()
    {
        //Admin page markup
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ifsa-member-verification-admin-display.php';
    }

    /**
     * Registering genetal setting section on the admin page
     */
    public function ifsa_member_verification_general_settings()
    {
        //Adding general setting section __('General', 'Ifsa_Member_Verification')
        add_settings_section(
            'ifsa_member_verification_general_settings',
            '',
            array($this, 'ifsa_member_verification_general_setting_callback'),
            'ifsa_member_verification_general_settings'
        );
        register_setting('ifsa_member_verification_general_settings', 'ifsa_general_setting_date_field', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'ifsa_step_1_description', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'ifsa_step_2_description', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'ifsa_step_3_description', array($this, 'sanitize_textarea_field'));

        register_setting('ifsa_member_verification_general_settings', 'after_30', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'after_21', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'after_15', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'before_30', array($this, 'sanitize_textarea_field'));
        register_setting('ifsa_member_verification_general_settings', 'next_yr_valid', array($this, 'sanitize_textarea_field'));
    }

    /**
     * Function to insert the markup for the general setting section
     */
    public function ifsa_member_verification_general_setting_callback()
    {
        //Markup for general page
        include_once plugin_dir_path(__FILE__) . 'partials/ifsa_member_verification_general_settings.php';
    }


    /**
     * Registering the email settings page on the admin side
     */
    public function ifsa_member_verification_email_settings()
    {
        //Adding email setting section __('Email', 'Ifsa_Member_Verification')
        add_settings_section(
            'ifsa_member_verification_email_settings',
            '',
            array($this, 'ifsa_member_verification_email_setting_callback'),
            'ifsa_member_verification_email_settings'
        );

        foreach (IFSA_EMAILS as $email_name => $emails){
            register_setting('ifsa_member_verification_email_settings',$email_name,  array($this, 'sanitize_textarea_field'));
            register_setting('ifsa_member_verification_email_settings',$email_name."_subject",  array($this, 'sanitize_textarea_field'));
        }


    }

    /**
     * Function to insert the markup for the general setting section
     */
    public function ifsa_member_verification_email_setting_callback()
    {
        include_once plugin_dir_path(__FILE__) . 'partials/ifsa_member_verification_email_settings.php';
    }

    /**
     * Markup for the log settings page on the admin general settings page
     */
    public function ifsa_member_verification_log_settings()
    {
        //Adding tab setting section__('Tab-3', 'Ifsa_Member_Verification')
        add_settings_section(
            'ifsa_member_verification_log_settings',
            '',
            array($this, 'ifsa_member_verification_log_setting_callback'),
            'ifsa_member_verification_log_settings'
        );
        //Registering the settings field and its value in the option table
        register_setting('ifsa_member_verification_log_settings', 'ifsa_member_verification_log_settings', array($this, 'sanitize_textarea_field'));
    }

    /**
     * Function to insert the markup for the general setting section
     */
    public function ifsa_member_verification_log_setting_callback()
    {
        //WP List Table for the admin log page
        $ifsa_list_table = new Ifsa_List_Table();


        ?>
        <form id="nds-user-list-form" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

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
    public function ifsa_register_regions()
    {
        $labels = array(
            'menu_name' => esc_html__('Regions', 'ifsa-member-verification'),
            'name_admin_bar' => esc_html__('Region', 'ifsa-member-verification'),
            'add_new' => esc_html__('Add Region', 'ifsa-member-verification'),
            'add_new_item' => esc_html__('Add new Region', 'ifsa-member-verification'),
            'new_item' => esc_html__('New Region', 'ifsa-member-verification'),
            'edit_item' => esc_html__('Edit Region', 'ifsa-member-verification'),
            'view_item' => esc_html__('View Region', 'ifsa-member-verification'),
            'update_item' => esc_html__('View Region', 'ifsa-member-verification'),
            'all_items' => esc_html__('All Regions', 'ifsa-member-verification'),
            'search_items' => esc_html__('Search Regions', 'ifsa-member-verification'),
            'parent_item_colon' => esc_html__('Parent Region', 'ifsa-member-verification'),
            'not_found' => esc_html__('No Regions found', 'ifsa-member-verification'),
            'not_found_in_trash' => esc_html__('No Regions found in Trash', 'ifsa-member-verification'),
            'name' => esc_html__('Regions', 'ifsa-member-verification'),
            'singular_name' => esc_html__('Region', 'ifsa-member-verification'),
        );
        $args = array(
            'label' => __('Region', 'text_domain'),
            'description' => __('Regions', 'text_domain'),
            'labels' => $labels,
            'supports' => array('title'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'ifsa-verification',
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'page',
        );
        register_post_type('Regions', $args);
    }


    /**
     * Funciton to Register Custom Taxonomy
     */
    public function ifsa_region_taxonomy()
    {

        $labels = array(
            'name' => _x('Committees', 'Committee General Name', 'ifsa-member-verification'),
            'singular_name' => _x('Committee', 'Committee Singular Name', 'ifsa-member-verification'),
            'menu_name' => __('Committee', 'ifsa-member-verification'),
            'all_items' => __('All Items', 'ifsa-member-verification'),
            'parent_item' => __('Parent Item', 'ifsa-member-verification'),
            'parent_item_colon' => __('Parent Item:', 'ifsa-member-verification'),
            'new_item_name' => __('New Item Name', 'ifsa-member-verification'),
            'add_new_item' => __('Add New Item', 'ifsa-member-verification'),
            'edit_item' => __('Edit Item', 'ifsa-member-verification'),
            'update_item' => __('Update Item', 'ifsa-member-verification'),
            'view_item' => __('View Item', 'ifsa-member-verification'),
            'separate_items_with_commas' => __('Separate items with commas', 'ifsa-member-verification'),
            'add_or_remove_items' => __('Add or remove items', 'ifsa-member-verification'),
            'choose_from_most_used' => __('Choose from the most used', 'ifsa-member-verification'),
            'popular_items' => __('Popular Items', 'ifsa-member-verification'),
            'search_items' => __('Search Items', 'ifsa-member-verification'),
            'not_found' => __('Not Found', 'ifsa-member-verification'),
            'no_terms' => __('No items', 'ifsa-member-verification'),
            'items_list' => __('Items list', 'ifsa-member-verification'),
            'items_list_navigation' => __('Items list navigation', 'ifsa-member-verification'),
        );
        $args = array(
            'labels' => $labels,

            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'hierarchical' => false,
            'parent_item' => null,
            'parent_item_colon' => null,
        );
        register_taxonomy('committee', array('regions'), $args);
    }

    /**
     * Delete user from member list
     */
    public function ifsa_custom_remove_user($user_id)
    {
        global $wpdb;
        $removefromdb = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}IFSALCMember WHERE user_id = %d", $user_id));

    }

    public function ifsa_hide_dashboard()
    {
        $roles = wp_get_current_user()->roles;
        // Redirects to away from the dashboard only if the current user role is *only* lc_member or lc_admin
        if (count($roles) == 1 && (in_array('lc_member', $roles) || in_array('lc_admin', $roles))
            && (!defined('DOING_AJAX') || !DOING_AJAX)) {
            wp_redirect(home_url());
            exit;
        }
    }


    /**
     * Funciton is use to download csv file
     */
    public function export_csv_callback()
    {

        global $wpdb;
        global $bp;

        $lastupdated = bp_core_current_time();
        if (isset($_GET['action']) && $_GET['action'] == 'export_csv_file' && is_admin()) {

            // Query
            $statement = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifsa_log "));

            // file creation
            $wp_filename = "filename_" . gmdate("d-m-y") . ".csv";

            // Clean object
            ob_end_clean();

            // Open file
            $wp_file = fopen($wp_filename, "w");
            $fields = array('id', 'Actions', 'Remarks', 'LC Admin', 'LC member', 'Date Time', 'IP');

            fputcsv($wp_file, $fields);

            // loop for insert data into CSV file

            foreach ($statement as $ifsa_log_data) {
                $lcadminname = get_user_by('id', $ifsa_log_data->logged_in_user_id);
                $lcmembername = get_user_by('id', $ifsa_log_data->member_id);
                $wp_array = array(
                    'id' => $ifsa_log_data->id,
                    'log_action' => $ifsa_log_data->log_action,
                    'remark' => strip_tags($ifsa_log_data->remark),
                    'logged_in_user_id' => $lcadminname->display_name,
                    'member_id' => $lcmembername->display_name,
                    'action_date' => date('c', strtotime($ifsa_log_data->action_date)),
                    'user_ip' => $ifsa_log_data->user_ip,
                );


                fputcsv($wp_file, $wp_array);
            }


            // Close file
            fclose($wp_file);

            // download csv file
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $wp_filename);
            header("Content-Type: application/csv;");
            readfile($wp_filename);
            exit;
        }
    }


}
