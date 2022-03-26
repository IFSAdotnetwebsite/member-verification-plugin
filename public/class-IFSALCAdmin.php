<?php

/**
 * LC Admin Dashboard and action taken by LC Admin
 */
class IFSALCAdmin
{
    /**
     * The version of this plugin.
     *
     * @access private
     * @var    string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     * @param string $version The version of this plugin.
     */
    public function __construct(string $version)
    {
        $this->version = $version;
    }
    /**
     * Register the stylesheets
     * @return void
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('member-verification-lc-admin', plugin_dir_url(__FILE__) . 'css/ifsa-member-verification-public.css', array(), $this->version);
        wp_enqueue_style('data-table', '//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), $this->version);

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script("ifsa-registration-form", plugin_dir_url(__FILE__) . 'js/ifsa-registration-form.js', array('jquery'), $this->version, false);
        wp_enqueue_script('ifsa-script', '//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
    }

    /**
     * Function is used to create profile tab.
     *
     * @return void It registeres the menus in the admin page
     */
    public function ifsa_profile_tab_member_list()
    {
        $lc_admin_id = ifsa_lc()->get_settings_lc_admin_id();
        // add settings only to LC admins
        if ($lc_admin_id ) {
            global $bp;
            bp_core_new_nav_item(
                array(
                    'name' => 'LC admin',
                    'slug' => 'memberlist',
                    'screen_function' => array($this, 'ifsa_member_list_tab'),
                    'position' => 1,
                    'parent_url' => bp_core_get_user_domain($lc_admin_id)  . '/memberlist/',
                    'parent_slug' => $bp->profile->slug,
                    'default_subnav_slug' => 'approvalpending',
                )
            );

            bp_core_new_subnav_item(
                array(
                    'name' => 'Active Member',
                    'slug' => 'activemember',
                    'parent_url' => bp_core_get_user_domain($lc_admin_id) . '/memberlist/',
                    'parent_slug' => 'memberlist',
                    'screen_function' => array($this, 'active_member_loader'),
                    'position' => 100
                )
            );

            bp_core_new_subnav_item(
                array(
                    'name' => 'Approval Pending',
                    'slug' => 'approvalpending',
                    'parent_url' => bp_core_get_user_domain($lc_admin_id) . '/memberlist/',
                    'parent_slug' => 'memberlist',
                    'screen_function' => array($this, 'approval_pending_loader'),
                    'position' => 40
                )
            );

            bp_core_new_subnav_item(
                array(
                    'name' => 'Export/Import Members',
                    'slug' => 'importexport',
                    'parent_url' => bp_core_get_user_domain($lc_admin_id) . '/memberlist/',
                    'parent_slug' => 'memberlist',
                    'screen_function' => array($this, 'import_export_loader'),
                    'position' => 100
                )
            );

        }
    }

    /**
     * Function for the IFSA member list tab
     */
    public function ifsa_member_list_tab()
    {
        // Add title and content here - last is to call the members plugin.php template.
        //add_action( 'bp_template_title', array( $this, 'ifsa_memberlis_title' ) );
        add_action('bp_template_content', array($this, 'ifsa_member_list_content'));
        bp_core_load_template(array('buddypress/members/single/plugins'));
    }



    /**
     * Function for the IFSA member list content
     *
     */
    public function ifsa_member_list_content()
    { ?>
        <h2>Approval Pending List</h2>
        <aside class="bp-feedback bp-messages ifsa-response" style="visibility: hidden;">
            <span class="bp-icon" aria-hidden="true"></span>
            <p class="ifsa_active_p"></p>
        </aside>

    <?php }

    /**
     * Register hook to show Active member tab
     */
    public function active_member_loader()
    {
        add_action('bp_template_content', array($this, 'active_member_tab_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Register hook to show Approval pending tab
     */
    public function approval_pending_loader()
    {
        //	add_action( 'bp_template_title', array( $this, 'ifsa_approvalpending_title' ) );
        add_action('bp_template_content', array($this, 'approvalpending_screen_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Function for the member list title
     *
     */
    public function ifsa_approvalpending_title()
    {
        echo 'Approval Pending List';
    }


    /**
     * Register hook for Import Export tab
     */
    public function import_export_loader()
    {
        add_action('bp_template_content', array($this, 'importexport_screen_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Markup for the activemember screen content
     *
     * @return void
     */
    public function active_member_tab_content()
    {
        include_once plugin_dir_path(__FILE__) . 'partials/ifsa-active-members.php';
    }

    /**
     * Markup for the pending approval screen content
     *
     */
    public function approvalpending_screen_content()
    {
        include_once plugin_dir_path(__FILE__) . 'partials/ifsa-approval-pending.php';
    }


    /**
     * Ajax Call back function for the IFSA member approval
     *
     */
    public function ifsa_approve_member_callback()
    {

        check_ajax_referer("lc_admin_approval");

        // Check capabilities

        $lc_admin_id = get_current_user_id();

        ifsa_lc()->is_lc_admin($lc_admin_id);

        ifsa_lc()->get_lc_admin();

        $lc_id = ifsa_lc()->get_settings_lc_admin_id();

        if(!ifsa_lc()->get_settings_lc_admin_id()){

        }

        global $wpdb;
        // global $bp;

        $lastupdated = bp_core_current_time();

        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';

        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ajax-nonce')) {
                die('Security Check Failed');
            }
        }

        if (isset($_POST['rowid']) && !empty($_POST['rowid'])) {
            $rowid = sanitize_text_field($_POST['rowid']);
        }
        if (isset($_POST['member_id']) && !empty($_POST['member_id'])) {
            $memberid = sanitize_text_field($_POST['member_id']);
        }

        $result = $wpdb->query($wpdb->prepare("UPDATE {$lcmembertable} SET member_status = '1', action_date = %s WHERE id = %d AND user_id = %d", $lastupdated, $rowid, $memberid)); // WPCS: unprepared SQL ok.

        $getname = $this->ifsa_get_fullname($memberid);
        $fname = $getname['fname'];
        $lname = $getname['lname'];

        if (empty($result) || is_wp_error($result)) {
            echo 'error';
            wp_die();
        }

        $remark = "LC admin has Approved LC Member - " . $fname . ' ' . $lname;
        $log_action = "Approve member";
        $email_key = 'welcome_email_after_verify_member';

        $this->ifsa_generate_log($memberid, $email_key, $log_action, $remark, $lastupdated, $reason = '');

        //$wpdb->query( $wpdb->prepare( "UPDATE {$lcmembertable} member_status = 1, WHERE user_id = %d AND ", $user_id ) );
        $user = get_user_by('id', $memberid);
        // Add role
        $user->add_role('lc_member');
        update_user_meta($memberid, 'user_active_status', "true");
        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(1, $memberid);
            if ($memberLevel == true) {
                update_user_meta($memberid, 'membership_assigned', 1);
            }

        }
        $lcadminid = get_current_user_id();
        update_user_meta($memberid, 'lc_adminid', $lcadminid);
        update_user_meta($memberid, 'approved_date', $lastupdated);


        $end = date('Y-m-d h:i:s', strtotime('+1 years'));

        $expiry_settings = get_option('ifsa_general_setting_date_field', true);
        if (isset($expiry_settings) && !empty($expiry_settings)) {
            $end = date('Y-m-d h:i:s', strtotime($expiry_settings));
        }
        $after_30 = !empty (get_option('after_30')) ? get_option('after_30') : '30';
        $after_21 = !empty (get_option('after_21')) ? get_option('after_21') : '21';
        $after_15 = !empty (get_option('after_15')) ? get_option('after_15') : '15';
        $before_30 = !empty (get_option('before_30')) ? get_option('before_30') : '30';
        $next_yr_valid = !empty (get_option('next_yr_valid')) ? get_option('next_yr_valid') : '60';


        $member_expire_after_15 = date('Y-m-d h:i:s', strtotime($end . ' + ' . $after_15 . ' days'));
        $member_expire_after_22 = date('Y-m-d h:i:s', strtotime($end . ' + ' . $after_21 . ' days'));
        $member_expire_after_30 = date('Y-m-d h:i:s', strtotime($end . ' + ' . $after_30 . ' days'));
        $member_expire_before_30 = date('Y-m-d h:i:s', strtotime($end . ' - ' . $before_30 . ' days'));


        update_user_meta($memberid, 'member_expire_date', $end);
        update_user_meta($memberid, 'member_expire_after_15', $member_expire_after_15);
        update_user_meta($memberid, 'member_expire_after_22', $member_expire_after_22);
        update_user_meta($memberid, 'member_expire_after_30', $member_expire_after_30);
        update_user_meta($memberid, 'member_expire_before_30', $member_expire_before_30);

        echo 'success';
        wp_die();
    }

    /**
     * Call back function for member rejection
     *
     * @return string Gives message of successfull regection
     */
    public function ifsa_reject_member_callback()
    {
        global $wpdb;
        //global $bp;
        $lastupdated = bp_core_current_time();
        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';

        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
                die('Security Check Failed');
            }
        }

        if (isset($_POST['member_id']) && !empty($_POST['member_id'])) {
            $memberid = sanitize_text_field($_POST['member_id']);
        }

        if (isset($_POST['rowid']) && !empty($_POST['rowid'])) {
            $rowid = sanitize_text_field($_POST['rowid']);
        }

        if (isset($_POST['reason']) && !empty($_POST['reason'])) {
            $reason = sanitize_text_field($_POST['reason']);
        }


        $result = $wpdb->query($wpdb->prepare("UPDATE {$lcmembertable} SET member_status = '4', action_date = %s, reason = %s WHERE id = %d AND user_id = %d", $lastupdated, $reason, $rowid, $memberid));

        if (empty($result) || is_wp_error($result)) {
            echo 'error';
            wp_die();
        }

        $user = new WP_User($memberid);
        // Remove all user roles after registration
        foreach ($user->roles as $role) {
            //$user->remove_role( $role );
        }


        if (function_exists('pmpro_changeMembershipLevel')) {
            $memberLevel = pmpro_changeMembershipLevel(1, $memberid);
            if ($memberLevel == true) {
                update_user_meta($memberid, 'membership_assigned', 0);
            }

        }

        $getname = $this->ifsa_get_fullname($memberid);
        $fname = $getname['fname'];
        $lname = $getname['lname'];

        $remark = "LC admin has rejected LC Member - " . $fname . ' ' . $lname . '<br/>' . 'Reason: ' . $reason;
        $action = "Reject Member";
        $email_key = 'reject_by_lc_admin_email_to_member';

        $this->ifsa_generate_log($memberid, $email_key, $action, $remark, $lastupdated, $reason);

        echo 'success';
        wp_die();
    }

    /**
     * Function to remove the member and update the memeber status
     */
    public function ifsa_remove_member_callback()
    {
        $lastupdated = bp_core_current_time();
        global $wpdb;
        //global $bp;
        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';

        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
                die('Security Check Failed');
            }
        }

        if (isset($_POST['member_id']) && !empty($_POST['member_id'])) {
            $memberid = sanitize_text_field($_POST['member_id']);
        }

        if (isset($_POST['rowid']) && !empty($_POST['rowid'])) {
            $rowid = sanitize_text_field($_POST['rowid']);
        }

        if (isset($_POST['reason']) && !empty($_POST['reason'])) {
            $reason = sanitize_text_field($_POST['reason']);
        }

        $result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ifsa_lc_member SET member_status = '3', action_date = %s, reason = %s WHERE id = %d AND user_id = %d", $lastupdated, $reason, $rowid, $memberid));

        if (empty($result) || is_wp_error($result)) {
            echo 'error';
            wp_die();
        }

        $user = new WP_User($memberid);

        // Remove all user roles after registration
        foreach ($user->roles as $role) {
            $user->remove_role($role);
        }

        $getname = $this->ifsa_get_fullname($memberid);
        $fname = $getname['fname'];
        $lname = $getname['lname'];

        $remark = "LC admin has removed LC Member - " . $fname . ' ' . $lname . '<br/>' . 'Reason: ' . $reason;;
        $action = "Remove Member";
        $email_key = 'remove_by_lc_admin_email_to_member';

        $this->ifsa_generate_log($memberid, $email_key, $action, $remark, $lastupdated, $reason);
        update_user_meta($memberid, 'user_active_status', "no");
        update_user_meta($memberid, 'user_state', 3);

        echo 'success';
        wp_die();
    }

    public function ifsa_lcadmin_banner()
    {

        $user = wp_get_current_user(); // getting & setting the current user
        $roles = ( array )$user->roles; // obtaining the role
        $profileid = bp_displayed_user_id();
        $user_id = get_current_user_id();
        if ($roles[0] === 'lc_admin' && $profileid == $user_id) { ?>
            <div class="cls-lcbanner"><p>

                    This is an LC admin account use it only for admin purposes not for personal stuff. Click <a
                            id="logout-submit" href="<?php echo wp_logout_url(); ?>">here</a> to logout and login with
                    your personal account
                </p></div>
        <?php }

    }

    /**
     * Funciton is use to download csv file
     */
    public function download_csv_callback()
    {

        global $wpdb;
        global $bp;

        $lastupdated = bp_core_current_time();
        if (isset($_GET['action']) && $_GET['action'] == 'download_csv_file' && is_user_logged_in()) {
            $user_id = get_current_user_id();

            // Query
            $statement = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifsa_lc_member where lc_adminid = %d and member_status = 1 ORDER BY 'id' DESC", $user_id));

            // file creation
            $wp_filename = "filename_" . gmdate("d-m-y") . ".csv";

            // Clean object
            ob_end_clean();

            // Open file
            $wp_file = fopen($wp_filename, "w");
            $fields = array('id', 'First Name', 'Last Name', 'Email', 'Last Updated');

            fputcsv($wp_file, $fields);

            // loop for insert data into CSV file
            foreach ($statement as $statementFet) {
                $getname = array();
                $getname = $this->ifsa_get_fullname($statementFet->user_id);
                $fname = $getname['fname'];
                $lname = $getname['lname'];
                $user = get_user_by('ID', $statementFet->user_id);

                $wp_array = array(
                    "id" => $statementFet->id,
                    "fname" => $fname,
                    "lname" => $lname,
                    "email" => $user->user_email,
                    "last_updated" => $statementFet->action_date,
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

            $remark = "LC admin has Exported CSV";
            $action = "Export CSV by LC admin";
            $email_key = 'export csv';
            $this->ifsa_generate_log($memberid = '', $email_key, $action, $remark, $lastupdated, $reason = '');

            exit;
        } else {

        }
    }
}