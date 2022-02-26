<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/admin
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 * @since      1.0.0
 */

require_once plugin_dir_path(__FILE__) . "../common/IFSALCMember.php";
require_once plugin_dir_path(__FILE__) . "../common/IFSAUtility.php";

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ifsa_Member_Verification
 * @subpackage Ifsa_Member_Verification/public
 * @author     Multidots <nishit.langaliya@multidots.com>
 * @license    GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link       #
 * @since      1.0.0
 */
class Ifsa_Member_Verification_Public
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
     * Instance of IFSALCMember class
     * @var IFSALCMember
     */
    private $ifsa;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->ifsa = new IFSALCMember();

        // Short code declaration
        add_shortcode('ifsa-multistep-form', array($this, 'ifsa_multistep_form'));
        add_shortcode('ifsa-renewal-form', array($this, 'ifsa_renewal_form'));
        add_shortcode('trial', array($this, 'table_trial'));

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     * @since 1.0.0
     *
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ifsa_Member_Verification_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ifsa_Member_Verification_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ifsa-member-verification-public.css', array(), $this->version, 'all');
        wp_enqueue_style('renewal_form_style', plugin_dir_url(__FILE__) . 'css/ifsa-renewal-form.css');
        wp_enqueue_style('data-table', '//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @return funciton it returns the enqueued scripts
     * @since 1.0.0
     *
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ifsa_Member_Verification_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ifsa_Member_Verification_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script("ifsa-registration-form", plugin_dir_url(__FILE__) . 'js/ifsa-registration-form.js', array('jquery'), $this->version, false);
        wp_enqueue_script('ifsa-renewal-form', plugin_dir_url(__FILE__) . 'js/ifsa-renewal-form.js', array('jquery'), $this->version, false);



        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $profileURL = get_bloginfo('url') . '/members/me/profile/';

        wp_localize_script(
            'ifsa-renewal-form', 'ifsa_script_vars', array(
                '	' => $profileURL,
                'nonce' => wp_create_nonce('ajax-nonce'),
            )
        );

        // Add nonce to the script for ajax security
        wp_localize_script("ifsa-registration-form", "ifsa_vars",
            array('nonce' => wp_create_nonce('registration-form-ajax'))
        );

        wp_enqueue_script('ifsa-script', '//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-datepicker');
    }

    /**
     * Function is used to create profile tab.
     *
     * @return funciton It registeres the menus in the admin page
     */
    public function ifsa_profile_tab_memberlist()
    {

        global $bp;
        if (is_user_logged_in()) {
            // check if there is a logged in user
            $user = wp_get_current_user(); // getting & setting the current user
            $roles = ( array )$user->roles; // obtaining the role

            if ($roles[0] === 'lc_admin') {

                bp_core_new_nav_item(
                    array(
                        'name' => 'LC admin',
                        'slug' => 'memberlist',
                        'screen_function' => array($this, 'ifsa_memberlist_tab'),
                        'position' => 1,
                        'parent_url' => bp_loggedin_user_domain() . '/memberlist/',
                        'parent_slug' => $bp->profile->slug,
                        'default_subnav_slug' => 'approvalpending',
                    )
                );

                bp_core_new_subnav_item(
                    array(
                        'name' => 'Active Member',
                        'slug' => 'activemember',
                        'parent_url' => bp_loggedin_user_domain() . '/memberlist/',
                        'parent_slug' => 'memberlist',
                        'screen_function' => array($this, 'ifsa_activemember_screen'),
                        'position' => 100,
                        'user_has_access' => bp_is_my_profile(),
                    )
                );

                bp_core_new_subnav_item(
                    array(
                        'name' => 'Approval Pending',
                        'slug' => 'approvalpending',
                        'parent_url' => bp_loggedin_user_domain() . '/memberlist/',
                        'parent_slug' => 'memberlist',
                        'screen_function' => array($this, 'ifsa_approvalpending_screen'),
                        'position' => 40,
                        'user_has_access' => bp_is_my_profile(),
                    )
                );

                bp_core_new_subnav_item(
                    array(
                        'name' => 'Export/Import Members',
                        'slug' => 'importexport',
                        'parent_url' => bp_loggedin_user_domain() . '/memberlist/',
                        'parent_slug' => 'memberlist',
                        'screen_function' => array($this, 'ifsa_importexport_screen'),
                        'position' => 100,
                        'user_has_access' => bp_is_my_profile(),
                    )
                );

            }
        }

    }


    /**
     * Function for the IFSA member list tab
     *
     * @return funciton it fires the actions on the perticular hooks
     */
    public function ifsa_memberlist_tab()
    {
        // Add title and content here - last is to call the members plugin.php template.
        //add_action( 'bp_template_title', array( $this, 'ifsa_memberlis_title' ) );
        add_action('bp_template_content', array($this, 'ifsa_memberlis_content'));
        bp_core_load_template('buddypress/members/single/plugins');
    }


    /**
     * Function for the member list title
     *
     * @return string
     */
    public function ifsa_memberlis_title()
    {
        echo 'Active Member List';

    }

    /**
     * Function for the IFSA member list content
     *
     * @return string
     */
    public function ifsa_memberlis_content()
    { ?>
        <h2>Approval Pending List</h2>
        <aside class="bp-feedback bp-messages ifsa-response" style="visibility: hidden;">
            <span class="bp-icon" aria-hidden="true"></span>
            <p class="ifsa_active_p"></p>
        </aside>

    <?php }

    /**
     * Function for the IFSA active member list tab
     *
     * @return markup
     */
    public function ifsa_activemember_screen()
    {
        add_action('bp_template_content', array($this, 'activemember_screen_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Function for the IFSA approval pending list tab
     *
     * @return markup
     */
    public function ifsa_approvalpending_screen()
    {
        //	add_action( 'bp_template_title', array( $this, 'ifsa_approvalpending_title' ) );
        add_action('bp_template_content', array($this, 'approvalpending_screen_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Function for the member list title
     *
     * @return string
     */
    public function ifsa_approvalpending_title()
    {
        echo 'Approval Pending List';
    }


    /**
     * Function for the IFSA import export tab
     *
     * @return markup
     */
    public function ifsa_importexport_screen()
    {
        add_action('bp_template_content', array($this, 'importexport_screen_content'));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Markup for the activemember screen content
     *
     * @return markup
     */
    public function activemember_screen_content()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}IFSALCMember where lc_adminid = %d and member_status = 1", $user_id));
        //$memberlistresults = $wpdb->get_results( $wpdb->prepare( "SELECT * from FROM {$lcmembertable} WHERE lc_adminid = $user_id" ) );
        echo '<h2>Active Member List</h2>';
        echo '<p>You can see list of all members who are currently active in your Local Committee.</p>';
        if ($res || !is_wp_error($res)) {
            ?>

            <aside class="bp-feedback bp-messages ifsa-response-reject" style="visibility: hidden;">
                <span class="bp-icon" aria-hidden="true"></span>
                <p class="ifsa_reject_p"></p>
            </aside>
            <!-- The Modal -->
            <div id="myModal_remove" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close-r">&times;</span>
                    <div class="ul_reason">
                        <p>Please select reason:</p>
                        <input type="radio" id="membership_expire" name="reason"
                               value="Your LC Membership has expired.">
                        <label for="membership_expire">LC membership expired</label></br>
                        <input type="radio" id="never_member" name="reason" value="You have never been an LC Member">
                        <label for="You have never been an LC Member">The applicant user has never been an LC
                            member</label></br>
                        <input type="radio" id="other" name="reason" value="other">

                        <label for="other">Other - open answer</label><br/>
                        <input type="text" id="other_reason" maxlength="100" hidden="true"/>
                        <a href="javascript:void(0)" row-id="" data-id="" id="final_remove"
                           class="final_remove">Confirm</a>
                        <div id="ifsa-loading-remove" style="display:none;">
                            <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                 title="loading"/>
                        </div>
                    </div>

                </div>

            </div>

            <table id="table_id" class="display">
                <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Member Type</th>
                    <th>Verification date</th>
                    <th>Profile Link</th>
                    <th>Action</th>

                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($res as $memberlistresult) {

                    $the_user = get_user_by('id', $memberlistresult->user_id); // 54 is a user ID
                    $email = $the_user->user_email;
                    $Name = $the_user->first_name;
                    $Surname = $the_user->last_name;
                    $profileURL = get_bloginfo('url') . '/members/' . bp_core_get_username($memberlistresult->user_id) . '/profile/';
                    $renew_request = get_user_meta($memberlistresult->user_id, 'ifsa_renew_request', true);
                    if ($renew_request == '1') {
                        $renew_request = 'Renewal';
                    } else {
                        $renew_request = 'New';
                    }
                    ?>

                    <tr>
                        <td data-title="First Name"><?php echo esc_html_e($Name, 'Ifsa_Member_Verification'); ?></td>
                        <td data-title="Last Name"><?php echo esc_html_e($Surname, 'Ifsa_Member_Verification'); ?></td>
                        <td data-title="Email"><?php echo esc_html_e($email, 'Ifsa_Member_Verification'); ?></td>
                        <td data-title="Source"><?php echo esc_html_e($memberlistresult->source, 'Ifsa_Member_Verification'); ?></td>
                        <td data-title="Status"> Approved</td>
                        <td data-title="Member Type"> <?php echo esc_html_e($renew_request, 'Ifsa_Member_Verification'); ?></td>

                        <td data-title="Verification date"><?php
                            $memberlistresult->action_date = date('F j, Y', strtotime($memberlistresult->action_date));
                            echo esc_html_e(date('F j, Y', strtotime($memberlistresult->action_date)), 'Ifsa_Member_Verification'); ?></td>
                        <td data-title="Profile Link"><a href="<?php echo esc_url($profileURL); ?>">Profile</a></td>
                        <td data-title="Action">
                            <div
                                    class="<?php echo esc_attr('cls-action-remove' . $memberlistresult->id, 'Ifsa_Member_Verification') . ''; ?>">
                                <a href="javascript:void(0)"
                                   row-id="<?php echo esc_html_e($memberlistresult->id, 'Ifsa_Member_Verification'); ?>"
                                   data-id="<?php echo esc_html_e($memberlistresult->user_id, 'Ifsa_Member_Verification'); ?>"
                                   class="cls-remove">Remove </a>

                            </div>
                            <div
                                    id="<?php echo esc_attr('ifsa-loading-' . $memberlistresult->id . '', 'Ifsa_Member_Verification'); ?>"
                                    style="display:none;">
                                <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                     title="loading"/>
                            </div>
                        </td>
                    </tr>

                <?php } ?>

                </tbody>
            </table>
        <?php }
    }

    /**
     * Markup for the pending approval screen content
     *
     * @return markup
     */
    public function approvalpending_screen_content()
    { ?>
        <aside class="bp-feedback bp-messages ifsa-response-approve" style="visibility: hidden;">
            <span class="bp-icon" aria-hidden="true"></span>
            <p class="ifsa_approve_p"></p>
        </aside>
        <aside class="bp-feedback bp-messages ifsa-response-reject" style="visibility: hidden;">
            <span class="bp-icon" aria-hidden="true"></span>
            <p class="ifsa_reject_p"></p>
        </aside>
        <p>The list of all pending requests for the local committee will be listed out here.</p>
        <?php global $wpdb;
        //$lcmembertable = $wpdb->prefix . 'IFSALCMember';
        $user_id = get_current_user_id();
        $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM  wp_ifsa_lc_member where lc_adminid = %d and member_status = 0", $user_id));
        //$memberlistresults = $wpdb->get_results( $wpdb->prepare( "SELECT * from FROM {$lcmembertable} WHERE lc_adminid = $user_id" ) );
        if ($res || !is_wp_error($res)) {
            ?>
            <!-- The Modal -->
            <div id="myModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <div class="ul_reason">
                        <p>Please select reason:</p>
                        <input type="radio" id="membership_expire" name="reason"
                               value="Your LC Membership has expired.">
                        <label for="membership_expire">LC membership expired</label></br>
                        <input type="radio" id="never_member" name="reason" value="You have never been an LC Member">
                        <label for="You have never been an LC Member">The applicant user has never been an LC
                            member</label></br>
                        <input type="radio" id="other" name="reason" value="other">

                        <label for="other">Other - open answer</label><br/>
                        <input type="text" id="other_reason" maxlength="100" hidden="true"/>
                        <a href="javascript:void(0)" row-id="" data-id="" id="final_reject"
                           class="final_reject">Confirm</a>
                        <div id="ifsa-loading-reject" style="display:none;">
                            <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                 title="loading"/>
                        </div>
                    </div>

                </div>

            </div>
            <table id="table_id" class="display">
                <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Source</th>
                    <th>Member Type</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($res as $memberlistresult) {
                    $the_user = get_user_by('id', $memberlistresult->user_id);
                    $email = $the_user->user_email;
                    $Name = $the_user->first_name;
                    $Surname = $the_user->last_name;

                    $renew_request = get_user_meta($memberlistresult->user_id, 'ifsa_renew_request', true);
                    if ($renew_request == '1') {
                        $renew_request = 'Renewal';
                    } else {
                        $renew_request = 'New';
                    }
                    ?>

                    <tr>
                        <td data-title="First Name"><?php echo esc_html_e($Name, 'ifsa-member-verification'); ?></td>
                        <td data-title="Last Name"> <?php echo esc_html_e($Surname, 'ifsa-member-verification'); ?></td>
                        <td data-title="Email"><?php echo esc_html_e($email, 'ifsa-member-verification'); ?></td>
                        <td data-title="Source"><?php echo esc_html_e($memberlistresult->source, 'ifsa-member-verification'); ?></td>
                        <td data-title="Member Type"> <?php echo esc_html_e($renew_request, 'Ifsa_Member_Verification'); ?></td>

                        <?php if ($memberlistresult->member_status == 0 && $memberlistresult->reason == '') { ?>
                            <td data-title="Action">
                                <div class="<?php echo esc_attr_e('cls-action-' . $memberlistresult->id . ''); ?>"><a
                                            href="javascript:void(0)"
                                            row-id="<?php echo esc_attr_e($memberlistresult->id); ?>"
                                            data-id="<?php echo esc_attr_e($memberlistresult->user_id); ?>"
                                            class="cls-reject">Reject </a> <a href="javascript:void(0)"
                                                                              row-id="<?php echo esc_attr_e($memberlistresult->id); ?>"
                                                                              data-id="<?php echo esc_attr_e($memberlistresult->user_id); ?>"
                                                                              class="cls-approve">
                                        Approve</a></div>
                                <div id="<?php echo esc_attr_e('ifsa-loading-' . $memberlistresult->id . ''); ?>"
                                     style="display:none;">
                                    <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                         title="loading"/>
                                </div>
                            </td>
                        <?php } else if ($memberlistresult->member_status == 0 && $memberlistresult->reason != '') {
                            ?>
                            <td data-title="Action"> Rejected</td>
                        <?php } else if ($memberlistresult->member_status == 1 && $memberlistresult->reason == '') {
                            ?>
                            <td data-title="Action"> Approved</td>
                        <?php } ?>
                    </tr>

                <?php } ?>

                </tbody>
            </table>
        <?php }
    }

    /**
     * Import export screen content
     *
     * @return markup
     */
    public function importexport_screen_content()
    {
        ?>
        <div class="cls-seperator">
            <h2>Export & Import Members</h2>
            <div class="cls-seperator_body">
                <div class="cls-seperator_div1">
                    <p> If you want to add bulk member in your assigned community then please upload here.</p>
                    <div class="form-group div_import">
                        <label for="bulk_upload_file">Upload CSV</label>
                        <input type="file" id="bulk_upload_file" name="import_file" require="required"/>
                        <input type="button" id="bulk_upload" class="button" name="butimport" value="Import"/>
                        <div id="ifsa-loading-import" style="display:none;">
                            <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                 title="loading"/>
                        </div>
                        <span class="cls-total-record"></span>
                    </div>
                    <div class="cls-sample-download" style="margin-top:20px;">
                        <p><strong><a
                                        href="<?php echo esc_url(IFSA_MEMBER_VERIFICATION_HOME . '/sample_import.csv'); ?>">Click
                                    here</a> for sample file download.</strong></p>

                    </div>
                </div>
                <div class="cls-seperator_div2">
                    <p>If you want to export active member from your assigned community.</p>
                    <?php $downloadurl = site_url('?action=download_csv_file');
                    $downloadurl = ($downloadurl); ?>
                    <p><a class="cls-seperator_download_user-btn" href="<?php echo esc_url($downloadurl); ?>">Download
                            Users</a></p>
                </div>
            </div>
        </div>
    <?php }


    /**
     * Call back function for the [ifsa-multistep-form] shortcode markup
     *
     * @return markup
     */
    public function ifsa_multistep_form()
    {

        ob_start();

        if (!(is_user_logged_in())) {
            include_once plugin_dir_path(__FILE__) . 'partials/ifsa-registration-form.php';
        } else {
            echo "<script>location.href = window.location.origin</script>";
        }

        return ob_get_clean();

    }

    /**
     * Call back function for the [ifsa-renewal-form] shortcode markup
     *
     * @return markup
     */
    public function ifsa_renewal_form()
    {
        ob_start();

        include_once plugin_dir_path(__FILE__) . 'partials/ifsa-renewal-form.php';

        return ob_get_clean();
    }

    /**
     * Call back funciton for the IFSA member approval
     *
     * @return string it gives the message on successfull approval
     */
    public function ifsa_approve_member_callback()
    {
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


    /**
     * Call back function to list the region
     *
     * @return markup
     */
    public function ifsa_ifsa_list_region_callback()
    {
        //global $wpdb;

        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
                die('Security Check Failed');
            }
        }
        if (isset($_POST['reasonID']) && !empty($_POST['reasonID'])) {
            $reasonID = sanitize_text_field($_POST['reasonID']);
        }

        $committeelist = wp_get_object_terms($reasonID, 'committee');

        // Return null if we found no results
        if (!$committeelist) {
            return;
        }

        // HTML for our select printing post titles as loop
        $output = "";
        foreach ($committeelist as $index) {
            $output .= '<option value="' . $index->term_id . '">' . $index->name . '</option>';
        }

        // get the html
        echo $output;
        wp_die();
    }


    /**
     * Function is used to upload data from csv.
     */
    public function file_upload_callback()
    {

        // global $wpdb;
        // global $bp;

        if (isset($_POST['nonce']) && !empty($_POST['nonce'])) {
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ajax-nonce')) {
                die('Security Check Failed');
            }
        }

        $lastupdated = bp_core_current_time();
        // Table name
        $session_id = uniqid();
        $response = array();
        $totalInserted = 0;
        $memberid = get_current_user_id();

        // File extension
        $extension = pathinfo(sanitize_file_name($_FILES['file']['name']), PATHINFO_EXTENSION);
        // If file extension is 'csv'
        if (!empty($_FILES['file']['name']) && $extension === 'csv') {

            if (isset($_FILES['file']['tmp_name']) && !empty($_FILES['file']['tmp_name'])) {
                // Open file in read mode
                $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            }

            $test = fgetcsv($csvFile); // Skipping header row
            // Read file


            while (($csvData = fgetcsv($csvFile)) !== false) {
                $content_ifsa_member_join_id = get_option('member_bulk_invite_for_join_the_community');
                $message = '';
                $csvData = array_map("utf8_encode", $csvData);
                $tempname = '';
                $_name = '';
                $_lname = '';
                $tempname = '';
                $_name = trim($csvData[0]);
                $_lname = trim($csvData[1]);
                $to = trim($csvData[2]);

                $link = site_url() . '/member-register/?utm_source=invite';

                $fullname = $_name . ' ' . $_lname;
                //user posted variables
                $tempname = $fullname;

                $author_obj = get_user_by('id', $memberid);
                $author_obj->user_nicename = ucfirst($author_obj->user_nicename);
                $content_ifsa_member_join_id = str_replace("{lc_member}", "$tempname", $content_ifsa_member_join_id);
                $content_ifsa_member_join_id = str_replace("{lc_admin}", "$author_obj->user_nicename", $content_ifsa_member_join_id);
                $content_ifsa_member_join_id = str_replace("{lc_link}", "$link", $content_ifsa_member_join_id);

                $message .= $content_ifsa_member_join_id;

                $subject = $author_obj->user_nicename . " has invited you to join IFSA";
                $content_ifsa_member_join_id_subject = !empty(get_option('member_bulk_invite_for_join_the_community_subject')) ? get_option('member_bulk_invite_for_join_the_community_subject') : $subject;
                $content_ifsa_member_join_id_subject = str_replace("{lc_admin}", "$author_obj->user_nicename", $content_ifsa_member_join_id_subject);

                $headers = array('Content-Type: text/html; charset=UTF-8');

                //Here put your Validation and send mail
                $sent = wp_mail($to, "$subject", $message, $headers);
                if ($sent) {
                    $totalInserted++;
                    $err = 0;
                    //message sent!
                } else {
                    $err = 1;
                }//message wasn't sent

            }


            // Check record already exists or not
            if (0 === $err) {
                $response['err'] = 0;
                $response['total_records'] = $totalInserted;
                $response['session_id'] = $session_id;
                $remark = "LC admin has Imported CSV" . '<br/> Total Records: ' . $totalInserted;
                $action = "Import CSV by LC admin";
                $email_key = 'upload csv';

                $this->ifsa_generate_log($memberid, $email_key, $action, $remark, $lastupdated, $reason = '');


            } else {
                $response['err'] = 1;
                $response['message'] = 'There was an error while inserting records!';
                $response['session_id'] = $session_id;
            }
        }

        wp_send_json($response, 200);
        wp_die();
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


    /**
     * Function to generate member logs
     */
    public function ifsa_generate_log($member_id, $email_key, $log_action, $remark, $lastupdated, $reason)
    {
        global $wpdb;
        $ifsa_log = $wpdb->prefix . 'ifsa_log';
        $user_id = get_current_user_id();
        $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP); // WPCS: sanitization ok.
        $userip = !empty ($ip) ? $ip : ''; // WPCS: sanitization ok.
        $memberid = $member_id;
        $getname = $this->ifsa_get_fullname($member_id);
        $fname = $getname['fname'];
        $lname = $getname['lname'];

        $fullname = $fname . ' ' . $lname;
        if ($email_key == 'upload csv') {
            $member_id = 0;
        }
        $query = "INSERT INTO {$ifsa_log} ( log_action,remark, logged_in_user_id ,member_id, action_date, user_ip) VALUES ( %s,%s, %d, %d, %s, %s )";
        $sqllog = $wpdb->prepare($query, "$log_action", "$remark", $user_id, $member_id, $lastupdated, $userip);

        $resultlog = $wpdb->query($sqllog);

        //Check if the resultlogs are not empty and there are no errors
        if (empty($resultlog) || is_wp_error($resultlog)) {
            return false;
        }

        //Checking the status of the email key
        if ($email_key == 'reject_by_lc_admin_email_to_member') {
            $content_ifsa_lc_admin_id = get_option('reject_by_lc_admin_email_to_member');
            $this->ifsa_reject_email_message($fullname, $content_ifsa_lc_admin_id, $reason, $memberid);
        }
        if ($email_key == 'welcome_email_after_verify_member') {
            $welcome_email_after_verify_member = get_option('welcome_email_after_verify_member');
            $this->ifsa_approve_email_message($fullname, $welcome_email_after_verify_member, $memberid, $user_id);
        }
        if ($email_key == 'remove_by_lc_admin_email_to_member') {
            $remove_member_content = get_option('remove_member_content');
            $this->ifsa_remove_email_message($fullname, $remove_member_content, $memberid, $user_id, $reason);
        }


    }

    /**
     * Function for the sending membership rejection mail
     */
    public function ifsa_reject_email_message($fullname, $content_ifsa_lc_admin_id, $reason, $memberid)
    {
        if (isset($content_ifsa_lc_admin_id) && !empty($content_ifsa_lc_admin_id)) {

            $content_ifsa_lc_admin_id = str_replace("{lc_member}", "$fullname", $content_ifsa_lc_admin_id);
            $content_ifsa_lc_admin_id = str_replace("{reject_reason}", "$reason", $content_ifsa_lc_admin_id);
            $message = $content_ifsa_lc_admin_id;
            //	$subject                  = "Reject by LC Admin email to member";
            $subject = !empty(get_option('reject_by_lc_admin_email_to_member_subject')) ? get_option('reject_by_lc_admin_email_to_member_subject') : 'Reject by LC Admin email to member';
            $author_obj = get_user_by('id', $memberid);
            $to = $author_obj->user_email;
            $headers = array('Content-Type: text/html; charset=UTF-8');

            //Here put your Validation and send mail
            $sent = wp_mail($to, "$subject", $message, $headers);
            if ($sent) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Function for the sending membership rejection mail
     */
    public function ifsa_remove_email_message($fullname, $remove_member_content, $memberid, $user_id, $reason)
    {
        if (isset($remove_member_content) && !empty($remove_member_content)) {
            $adminobj = get_user_by('id', $user_id);
            $remove_member_content = str_replace("{lc_member}", "$fullname", $remove_member_content);
            $remove_member_content = str_replace("{lc_admin}", "$adminobj->user_nicename", $remove_member_content);
            $remove_member_content = str_replace("{remove_reason}", "$reason", $remove_member_content);
            $message = $remove_member_content;
            //$subject               = "Removal from IFSA membership";
            $subject = !empty(get_option('remove_member_content_subject')) ? get_option('remove_member_content_subject') : 'Removal from IFSA membership';
            $author_obj = get_user_by('id', $memberid);
            $to = $author_obj->user_email;
            $headers = array('Content-Type: text/html; charset=UTF-8');

            //Here put your Validation and send mail
            $sent = wp_mail($to, "$subject", $message, $headers);
            if ($sent) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Function for the sending membership approval mail
     */
    public function ifsa_approve_email_message($fullname, $welcome_email_after_verify_member, $memberid, $user_id)
    {
        if (isset($welcome_email_after_verify_member) && !empty($welcome_email_after_verify_member)) {
            $adminobj = get_user_by('id', $user_id);
            $adminobj->user_nicename = ucfirst($adminobj->user_nicename);
            $expiry_settings = get_option('ifsa_general_setting_date_field', true);
            if (isset($expiry_settings) && !empty($expiry_settings)) {
                $end = date('Y-m-d h:i:s', strtotime($expiry_settings));
            } else {
                $end = "31st August";
            }
            $welcome_email_after_verify_member = str_replace("{lc_member}", "$fullname", $welcome_email_after_verify_member);
            $welcome_email_after_verify_member = str_replace("{lc_admin}", "$adminobj->user_nicename", $welcome_email_after_verify_member);
            $welcome_email_after_verify_member = str_replace("{renew_date}", "$end", $welcome_email_after_verify_member);

            $message = $welcome_email_after_verify_member;
            //$subject    = "Successfully verified to IFSA";
            $subject = !empty(get_option('welcome_email_after_verify_member_subject')) ? get_option('welcome_email_after_verify_member_subject') : 'Successfully verified to IFSA';
            $author_obj = get_user_by('id', $memberid);
            $to = $author_obj->user_email;
            $headers = array('Content-Type: text/html; charset=UTF-8');

            //Here put your Validation and send mail
            $sent = wp_mail($to, "$subject", $message, $headers);
            if ($sent) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Function tp get the full name of the member
     */
    public function ifsa_get_fullname($member_id)
    {
        $fullname = array();
        $args = array(
            'field' => 'Name',
            'user_id' => $member_id,
        );
        $args1 = array(
            'field' => 'Surname',
            'user_id' => $member_id,
        );

        $fname = bp_get_profile_field_data($args);
        $lname = bp_get_profile_field_data($args1);
        $fullname['fname'] = $fname;
        $fullname['lname'] = $lname;

        return $fullname;
    }


    /**
     * Funciton to notify the expiration date of the user account
     */
    public function ifsa_memebership_expire_callback()
    {
        $memberid = get_current_user_id();
        $end = date('Y-m-d h:i:s', strtotime('+1 years'));

        $expiry_settings = get_option('ifsa_general_setting_date_field', true);
        if (isset($expiry_settings) && !empty($expiry_settings)) {
            $end = date('Y-m-d h:i:s', strtotime($expiry_settings));
        }

        $memberid = get_current_user_id();
        if (is_user_logged_in()) { // check if there is a logged in user
            $user = wp_get_current_user(); // getting & setting the current user
            $roles = ( array )$user->roles; // obtaining the role

            if ($roles[0] === 'lc_member') {

                global $wpdb;
                $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';
                //$user_id       = get_current_user_id();
                //	$dt = '2021-08-01 12:00:00';

                $today = date('Y-m-d 12:00:00');
                //$today = date( 'Y-m-d h:i:s', strtotime( $dt ) );

                $res = $wpdb->get_row("SELECT * FROM  {$lcmembertable} where user_id = $memberid");
                if ($res || !is_wp_error($res)) {

                    $term = get_term_by('id', $res->committee_id, 'committee');


                    ?>
                    <table class="profile-fields bp-tables-user">
                        <tbody>
                        <tr>
                            <td class="label">Region</td>
                            <td class="data"><?php echo get_the_title($res->region_id); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Local Committee</td>
                            <td class="data"><?php echo $term->name; ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php

                }
                $today = date('Y-m-d 12:00:00');
                $beforedate = date('Y-m-d 12:00:00', strtotime($end . ' - 30 days'));

                if ($today <= $end && $today >= $beforedate) {
                    if (isset($_POST['verify_member']) && !empty($_POST['verify_member'])) {

                        $fullname = $this->ifsa_get_fullname($memberid);

                        $fname = $fullname['fname'];
                        $lname = $fullname['lname'];
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        $lcadminid = get_user_meta($memberid, "lc_adminid", true);
                        $lcobj = get_user_by('id', $lcadminid);
                        $to = $lcobj->user_email;
                        $message = '';
                        $message .= $fname . ' ' . $lname . 'with user id :' . $memberid . ' has requested renewal request';

                        wp_mail($to, 'Membership Renewal Request', $message, $headers);
                    }

                    ?>
                    <form action="" method="post">

                        <div class="ifsa_memebr_expiry_wrap">
                            <span><strong>Membership Expiry Date</strong></span>
                            <div style="float:right;">
                                <span><strong><?php echo esc_html_e($end, 'Ifsa_Member_Verification'); ?></strong></span>

                                <button type="button" id="ifsa-verify_member-btn">Verify Membership</button>

                                <div
                                        id="<?php echo esc_attr('ifsa-loading-renew-profile', 'Ifsa_Member_Verification'); ?>"
                                        style="display:none;vertical-align:middle;">
                                    <img src="<?php echo esc_url(site_url() . '/wp-admin/images/loading.gif'); ?>"
                                         title="loading"/>
                                </div>
                            </div>

                            <div id="ifsa_renewal_form-sucess-profile" class="ifsa_renewal_form-sucess-profile"
                                 style="display:none;text-align:center;padding-top:30px !important;">
                                <h4>Your request submitted successfully</h4>

                            </div>
                        </div>

                    </form>
                <?php } else { ?>
                    <div class="ifsa_memebr_expiry_wrap">
                        <span><strong>Membership Expiry Date</strong></span>
                        <div style="float:right;">
                            <span><strong><?php echo esc_html_e($end = date('F j, Y', strtotime($end)), 'Ifsa_Member_Verification'); ?></strong></span>
                            <span disabled="disabled" style="color:#333333;background:#c5c5c5 !important;"
                                  id="ifsa-verify_member-btn">Verify Membership</span>
                        </div>
                    </div>
                <?php }


            }
        }

    }


    /**
     * Funciton for the IFSA corn job . Shows the status of the expiration of the user.
     */
    public function ifsa_cron_job_callback()
    {
        global $wpdb;
        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';
        //$user_id       = get_current_user_id();
        $dt = '2021-08-31 12:00:00';

        $today = date('Y-m-d 12:00:00');
        //$today = '2021-08-31 12:00:00';
        //$today = date( 'Y-m-d 12:00:00', strtotime( $dt ) );

        $res = $wpdb->get_results("SELECT * FROM  {$lcmembertable} where member_status = 1");
        if ($res || !is_wp_error($res)) {
            foreach ($res as $memberlistresult) {
                $memberid = $memberlistresult->user_id;
                $getname = $this->ifsa_get_fullname($memberid);
                $fname = $getname['fname'];
                $lname = $getname['lname'];

                $fullname = $fname . ' ' . $lname;
                $end = get_user_meta($memberid, 'member_expire_date', true);
                $member_expire_after_15 = get_user_meta($memberid, 'member_expire_after_15', true);
                $member_expire_after_22 = get_user_meta($memberid, 'member_expire_after_22', true);
                $member_expire_after_30 = get_user_meta($memberid, 'member_expire_after_30', true);
                $member_expire_before_30 = get_user_meta($memberid, 'member_expire_before_30', true);

                $end = date('Y-m-d h:i:s', strtotime($end));

                $expiry_settings = get_option('ifsa_general_setting_date_field', true);
                if (isset($expiry_settings) && !empty($expiry_settings)) {
                    $end = date('Y-m-d h:i:s', strtotime($expiry_settings));
                }
                $udata = get_userdata($memberid);
                $registered = $udata->user_registered;
                $next_yr_valid = !empty (get_option('next_yr_valid')) ? get_option('next_yr_valid') : '60';
                //$end_date = date($end, strtotime("-'.$next_yr_valid.' days"));
                $end_date = date('Y-m-d h:i:s', strtotime($end . ' - ' . $next_yr_valid . ' days'));

                if ($registered > $end_date) {
                    //wp_mail("test@example.com", $registered, $end_date.'--'.$memberid);
                    continue;
                }


                $member_expire_after_15 = date('Y-m-d h:i:s', strtotime($member_expire_after_15));
                $member_expire_after_22 = date('Y-m-d h:i:s', strtotime($member_expire_after_22));
                $member_expire_after_30 = date('Y-m-d h:i:s', strtotime($member_expire_after_30));
                $member_expire_before_30 = date('Y-m-d h:i:s', strtotime($member_expire_before_30));

                if ($today === $end) {
                    $content_ifsa_reminder_id = get_option('reminder_on_same_date_when_renewed');
                    $response = $this->member_expire_date_email($memberid, $fullname, $content_ifsa_reminder_id, $end);

                    if ($response === true) {
                        update_user_meta($memberid, 'member_expire_date_email_sent', true);
                    } else {
                        update_user_meta($memberid, 'member_expire_date_email_sent', 'no');
                    }
                } else if ($today == $member_expire_after_15) {

                    $fifteen_days_after_expire_date = get_option('fifteen_days_after_expire_date');
                    $response = $this->member_expire_after_15_email($memberid, $fullname, $fifteen_days_after_expire_date, $member_expire_after_15);

                    if ($response === true) {
                        update_user_meta($memberid, 'member_expire_after_15_email_sent', true);
                    } else {
                        update_user_meta($memberid, 'member_expire_after_15_email_sent', 'no');
                    }
                } else if ($today === $member_expire_after_22) {
                    $twentytwo_days_after_expire_date = get_option('twentytwo_days_after_expire_date');
                    $response = $this->member_expire_after_22_email($memberid, $fullname, $twentytwo_days_after_expire_date, $member_expire_after_22);
                    if ($response === true) {
                        update_user_meta($memberid, 'member_expire_after_22_email_sent', true);
                    } else {
                        update_user_meta($memberid, 'member_expire_after_22_email_sent', 'no');
                    }

                } else if ($today === $member_expire_after_30) {
                    $thirty_days_after_expire_date = get_option('thirty_days_after_expire_date');
                    $response = $this->member_expire_after_30_email($memberid, $fullname, $thirty_days_after_expire_date, $member_expire_after_30);
                    if ($response === true) {
                        update_user_meta($memberid, 'member_expire_after_30_email_sent', true);
                    } else {
                        update_user_meta($memberid, 'member_expire_after_30_email_sent', 'no');
                    }
                } else if ($today === $member_expire_before_30) {

                    $thirty_days_before_renewal_date = get_option('thirty_days_before_renewal_date');
                    $response = $this->member_expire_before_30_email($memberid, $fullname, $thirty_days_before_renewal_date, $member_expire_before_30);
                    if ($response === true) {
                        update_user_meta($memberid, 'member_expire_before_30_email_sent', true);
                    } else {
                        update_user_meta($memberid, 'member_expire_before_30_email_sent', 'no');
                    }
                }

            }

        }

    }

    /**
     * Notifies member about the expiration date of the account
     */
    public function member_expire_date_email($memberid, $fullname, $content_ifsa_reminder_id, $end)
    {
        $welcome_email_after_verify_member = str_replace("{lc_member}", "$fullname", $content_ifsa_reminder_id);
        $welcome_email_after_verify_member = str_replace("{renew_date}", "$end", $welcome_email_after_verify_member);
        $message = $welcome_email_after_verify_member;
        //$subject                           = "IFSA account expiration";
        $subject = !empty(get_option('reminder_on_same_date_when_renewed_subject')) ? get_option('reminder_on_same_date_when_renewed_subject') : 'IFSA account expiration';
        $author_obj = get_user_by('id', $memberid);
        $to = $author_obj->user_email;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notifies member about expiration of the account after 15 days
     */
    public function member_expire_after_15_email($memberid, $fullname, $fifteen_days_after_expire_date, $member_expire_after_15)
    {
        $welcome_email_after_verify_member = str_replace("{lc_member}", $fullname, $fifteen_days_after_expire_date);
        $welcome_email_after_verify_member = str_replace("{15_days_after_date_expire}", "$member_expire_after_15", $welcome_email_after_verify_member);

        $message = $welcome_email_after_verify_member;
        //$subject    = "Reminder IFSA account expiration";
        $subject = !empty(get_option('fifteen_days_after_expire_date_subject')) ? get_option('fifteen_days_after_expire_date_subject') : 'Reminder IFSA account expiration';
        $author_obj = get_user_by('id', $memberid);
        $to = $author_obj->user_email;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notifies member about expiration of the account after 22 days
     */
    public function member_expire_after_22_email($memberid, $fullname, $twentytwo_days_after_expire_date, $member_expire_after_22)
    {
        $welcome_email_after_verify_member = str_replace("{lc_member}", "$fullname", $twentytwo_days_after_expire_date);
        $welcome_email_after_verify_member = str_replace("{22_days_after_date_expire}", "$member_expire_after_22", $welcome_email_after_verify_member);
        $message = $welcome_email_after_verify_member;
        //$subject                           = "Reminder IFSA account expiration one week";
        $subject = !empty(get_option('twentytwo_days_after_expire_date_subject')) ? get_option('twentytwo_days_after_expire_date_subject') : 'Reminder IFSA account expiration one week';
        $author_obj = get_user_by('id', $memberid);
        $to = $author_obj->user_email;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Notifies member about expiration of the account after 30 days
     */
    public function member_expire_after_30_email($memberid, $fullname, $thirty_days_after_expire_date, $member_expire_after_30)
    {
        $welcome_email_after_verify_member = str_replace("{lc_member}", "$fullname", $thirty_days_after_expire_date);
        $welcome_email_after_verify_member = str_replace("{30_days_after_date}", "$member_expire_after_30", $welcome_email_after_verify_member);
        $message = $welcome_email_after_verify_member;
        //$subject                           = "IFSA account expiration";
        $subject = !empty(get_option('thirty_days_after_expire_date_subject')) ? get_option('thirty_days_after_expire_date_subject') : 'IFSA account expiration';
        $author_obj = get_user_by('id', $memberid);
        $to = $author_obj->user_email;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            return true;

            if (function_exists('pmpro_changeMembershipLevel')) {
                $memberLevel = pmpro_changeMembershipLevel(3, $memberid);
                if ($memberLevel == true) {
                    update_user_meta($user_id, 'membership_assigned', 3);
                }

            }
        } else {
            return false;
        }
    }

    /**
     * Notifies member about before 30 days of expiration of the account
     */
    public function member_expire_before_30_email($memberid, $fullname, $thirty_days_before_renewal_date, $member_expire_before_30)
    {
        $welcome_email_after_verify_member = str_replace("{lc_member}", "$fullname", $thirty_days_before_renewal_date);
        $welcome_email_after_verify_member = str_replace("{30_days_before_date}", "$member_expire_before_30", $welcome_email_after_verify_member);
        $message = $welcome_email_after_verify_member;
        //$subject                           = "Start of IFSA renewal period";
        $subject = !empty(get_option('thirty_days_before_renewal_date_subject')) ? get_option('thirty_days_before_renewal_date_subject') : 'Start of IFSA renewal period';
        $author_obj = get_user_by('id', $memberid);
        $to = $author_obj->user_email;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Funciton to hide profile field group
     */
    public function ifsa_bpfr_hide_profile_field_group($retval)
    {
        if (!is_super_admin() && !bp_is_register_page()) {
            // exlude groups, separated by comma
            //$retval['exclude_fields'] = '213,209,222';
        }

        return $retval;

    }

    /**
     * Funciton to dissable randam password
     */
    public function bp_page_signup_disable_random_password()
    {
        add_filter('random_password', '__return_empty_string');
    }

    /**
     * Funciton to process the ajax renewal request
     */
    public function ifsa_ifsa_renew_request()
    {

        $memberid = get_current_user_id();

        $message = '';
        $getname = $this->ifsa_get_fullname($memberid);
        $fname = $getname['fname'];
        $lname = $getname['lname'];
        $fullname = $fname . ' ' . $lname;
        $subject = "LC member requested for renewal";
        $lcadminid = get_user_meta($memberid, 'lc_adminid', true);
        $is_done = get_user_meta($memberid, 'ifsa_renew_request', true);
        if (isset($is_done) && $is_done == '1') {
            echo 'already requested';
            wp_die();
        }
        $author_obj = get_user_by('id', $lcadminid);
        $to = $author_obj->user_email;
        $message .= 'Hi,';
        $message .= $fullname . ' has requested renew membership';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            echo 1;
            global $wpdb;
            $lastupdated = bp_core_current_time();

            $result = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}ifsa_lc_member SET member_status = %d, action_date = %s WHERE user_id = %d", 0, $lastupdated, $memberid)); // WPCS: unprepared SQL ok.
            if (!empty($result) || !is_wp_error($result)) {
                update_user_meta($memberid, 'ifsa_renew_request', '1');
            }
            wp_die();
        } else {
            echo 0;
            //	update_user_meta( $memberid, 'ifsa_renew_request', '0' );
            wp_die();
        }

    }


    public function ifsa_change_nouveau_string($array)
    {
        $login = wp_login_url();
        $array['completed-confirmation']['message'] = sprintf('You have successfully created your account! Please <a href="%s">log in</a> in using the username and password you have just created.', $login);

        //$array['request-details']['message'] = '';
        return $array;

    }

    public function weekl_member_list_callback()
    {
        global $wpdb;
        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';
        $user_id = get_current_user_id();
        $args1 = array(
            'role' => 'lc_admin',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        );
        $lcadminids = array();

        $message = '';
        $lcadmins = get_users($args1);

        foreach ($lcadmins as $lcadmin) {
            $lcadminids[] = $lcadmin->ID; //$lcadmin->user_email;
        }
        foreach ($lcadminids as $lcadminid) {
            $message = '';
            $the_user = get_user_by('id', $lcadminid); // 54 is a user ID
            $message = 'Hi ' . $the_user->display_name . ',<br/>';
            $message .= 'List of member approval pending <br/>';
            $res = $wpdb->get_results($wpdb->prepare("SELECT * FROM  {$lcmembertable} where lc_adminid = %d and member_status = 0", $lcadminid));
            //	$res = $wpdb->get_results( $wpdb->prepare( "SELECT * from FROM {$lcmembertable} WHERE lc_adminid = $user_id" ) );
            if ($res || !is_wp_error($res)) {

                $to = $the_user->user_email;
                if (count($res) <= 0) {
                    $message .= 'No pending approvals';
                } else {
                    $message .= '<table style="margin-top:20px;">';
                    $message .= '<tr><td style="border:1px solid #000;"><strong>Member Name<strong> </td></tr>';
                    foreach ($res as $val) {
                        $getname = $this->ifsa_get_fullname($val->user_id);
                        $fname = $getname['fname'];
                        $lname = $getname['lname'];
                        $fullname = $fname . ' ' . $lname;
                        $message .= '<tr><td style="border:1px solid #000;">' . $fullname . ' </td></tr>';
                    }
                    $message .= '</table>';
                }
                $headers = array('Content-Type: text/html; charset=UTF-8');
                //Here put your Validation and send mail
                $sent = wp_mail($to, "Weekly pending member list", $message, $headers);
                if ($sent) {

                } else {

                }

            }
        }
    }


    public function ifsa_membership_start()
    {
        global $wpdb;
        $lcmembertable = $wpdb->prefix . 'ifsa_lc_member';
        $user_id = get_current_user_id();
        $args1 = array(
            'role' => 'lc_admin',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        );
        $lcadminids = array();

        $message = '';
        $lcadmins = get_users($args1);

        $expiry_settings = get_option('ifsa_general_setting_date_field', true);
        if (isset($expiry_settings) && !empty($expiry_settings)) {
            $end = date('Y-m-d h:i:s', strtotime($expiry_settings));
        }
        $before_30 = !empty (get_option('before_30')) ? get_option('before_30') : '30';
        $today = date('Y-m-d 12:00:00');

        $member_expire_before_30 = date('Y-m-d h:i:s', strtotime($end . ' - ' . $before_30 . ' days'));

        if ($today == $member_expire_before_30) {

            foreach ($lcadmins as $lcadmin) {
                $lcadminids[] = $lcadmin->ID; //$lcadmin->user_email;
            }
            foreach ($lcadminids as $lcadminid) {
                $message = '';
                $the_user = get_user_by('id', $lcadminid); // 54 is a user ID
                $message = 'Hi ' . $the_user->display_name . ',<br/>';
                $message .= 'Membership renewal starts from today. <br/>';
                $message .= 'Thanks <br/>';
                //$res     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$lcmembertable} where lc_adminid = %d and member_status = 0", $lcadminid ) );
                //	$res = $wpdb->get_results( $wpdb->prepare( "SELECT * from FROM {$lcmembertable} WHERE lc_adminid = $user_id" ) );
                //	if ( $res || ! is_wp_error( $res ) ) {

                $to = $the_user->user_email;
                $headers = array('Content-Type: text/html; charset=UTF-8');
                //Here put your Validation and send mail
                $sent = wp_mail($to, "Membership renewal starts", $message, $headers);
                if ($sent) {

                } else {

                }

                //}
            }
        }
    }


    public function ifsa_redirect_to_profile($redirect_to_calculated, $redirect_url_specified, $user)
    {

        if (!$user || is_wp_error($user)) {
            return $redirect_to_calculated;
        }

        // If the redirect is not specified, assume it to be dashboard.
        if (empty($redirect_to_calculated)) {
            $redirect_to_calculated = admin_url();
        }

        // if the user is not site admin, redirect to his/her profile.
        if (function_exists('bp_core_get_user_domain') && !is_super_admin($user->ID)) {
            return bp_core_get_user_domain($user->ID) . '/profile';
        }

        // if site admin or not logged in, do not do anything much.
        return $redirect_to_calculated;
    }


    public function ifsa_remove_admin_bar()
    {
        if (!current_user_can('manage_options') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    public function ifsa_add_to_registration()
    {
        global $bp;
        ?>
        <div class=”clear”></div>
        <div class=”register-section” id=”terms-section”>
            <?php do_action('bp_accept_tos_errors') ?>

            <label for=”accept_tos”>I accept the <a href='../terms'>terms & conditions</a> (required)</label>

        </div>
        <?php
    }

    /**
     * Register a user from the frontend
     * This is an ajax callback
     * @return void
     */
    public function ifsa_register_user_front_end()
    {
        check_ajax_referer("registration-form-ajax");

        // Maps the ajax fields into Wordpress user fields and sanatize them
        $user_data = array(
            'user_login' => sanitize_text_field($_POST['user_name']),
            'user_email' => sanitize_email($_POST['user_email']),
            'user_pass' => sanitize_text_field($_POST['user_password']),
            'first_name' => sanitize_text_field($_POST['user_first_name']),
            'last_name' => sanitize_text_field($_POST['user_last_name']),
        );
        $user_data['display_name'] = $user_data['first_name']. " " . $user_data['last_name'];

        // Add user in database
        $user_id = wp_insert_user($user_data);

        // Check that the user creation was successful
        if (is_wp_error($user_id)) {
            if (isset($user_id->errors['empty_user_login'])) {
                $notice_key = 'User Name and Email are mandatory';
                echo $notice_key;
            } elseif (isset($user_id->errors['existing_user_login'])) {
                echo 'User name already exist.';
            } else {
                echo 'Error Occurred during sign up. Error:' . $user_id->get_error_message() . "If problem persists contact site admin";
            }
            wp_die();
        }

        /** Basic user creation completed. Deal with IFSA specific part */
        
        $lc_id = IFSAUtility::get_post_var('lc');
        
        // Map Xprofile_name => POST_name
        
        $user_add_info = array(
                'IFSA Region' => 'ifsa_region',
                'Gender' => 'gender',
                'Nationality' => 'nationality',
                'University Name' => 'universityname',
                'University Country' => 'country',
                'University Level' => 'universityLevel',
                'Course Topic' => 'courseTopic',
                'Graduation Day' => 'graduateday',
            );
        // uses get_post_var to sanitize the input
        foreach ($user_add_info as $key => $ajax_value){
            $user_add_info[$key] = IFSAUtility::get_post_var($ajax_value);
        }

        // Check Local Committee
        $lc_admin = $this->ifsa->get_lc_admin($lc_id);
        if(is_wp_error($lc_admin)){
            echo "Invalid LC. try again or contact website admin. Error: ".$lc_admin->get_error_code();
            wp_die();
        }

        // Add LC id to user meta. This is used in the admin interface
        update_user_meta($user_id, 'ifsa_committee', $lc_id);

        // Update custom table for member verification
        // differentiate email invites
        $source = IFSAUtility::get_post_var('utm_source') == 'invite' ? "Invite" : "Website"; // default source is website

        global $wpdb;
        $last_updated = bp_core_current_time();

        $query = "INSERT INTO {$this->ifsa->lc_member_table} ( user_id,lc_adminid, committee_id ,region_id, action_date, member_status,source) VALUES ( %d,%d, %s, %s, %s, %d,%s )";
        $sql = $wpdb->prepare($query, $user_id, $lc_admin, $lc_id, $user_add_info['IFSA Region'], $last_updated, 0, $source);

        $result = $wpdb->query($sql);

        if (empty($result) || is_wp_error($result)) {
            echo "Error in updating user into database. Please contact website admin.";
            wp_die();
        }


        // Add addition fields to profile

        $user_add_info['Local Committee Name'] = $this->ifsa->get_lc_name($lc_id);

        foreach ($user_add_info as $field_name => $field_value){
            $return = xprofile_set_field_data($field_name, $user_id, $field_value);
            if(!$return){
                echo "Error in creting xprofile fields";
                wp_die();
            }
        }

        // Email

        $subject = "Successfully Registered";

        $author_obj = get_user_by('id', $user_id);
        $to = $author_obj->user_email;

        $message = 'You have successfully registered and should wait for the LC admin to verify';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        //Here put your Validation and send mail
        $sent = wp_mail($to, "$subject", $message, $headers);
        if ($sent) {
            update_user_meta($user_id, 'ifsa_registration_email', '1');
        } else {
            update_user_meta($user_id, 'ifsa_registration_email', '0');
        }

        echo 'success';

        // need to log stuff

        // refactor email send

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

}
