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

require_once plugin_dir_path(__FILE__) . "../includes/IFSALCMember.php";
require_once plugin_dir_path(__FILE__) . "../includes/IFSAUtility.php";

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
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Short code declaration
        add_shortcode('ifsa-multistep-form', array($this, 'ifsa_multistep_form'));
        add_shortcode('ifsa-renewal-form', array($this, 'ifsa_renewal_form'));

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


        wp_enqueue_script('ifsa-script', '//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery-ui-datepicker');
    }



    /**
     * Call back function for the [ifsa-multistep-form] shortcode markup
     *
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
     */
    public function ifsa_renewal_form()
    {
        ob_start();

        include_once plugin_dir_path(__FILE__) . 'partials/ifsa-renewal-form.php';

        return ob_get_clean();
    }




    /**
     * Ajax callback to get LC list for a region
     *
     * Note is not checking nonce as there is no action done on the server
     */
    public function ifsa_ifsa_list_region_callback()
    {
        if (isset($_POST['region']) && !empty($_POST['region'])) {
            $region = sanitize_text_field($_POST['region']);
        }

        $committee_list = wp_get_object_terms($region, 'committee');

        // Return null if we found no results
        if (!$committee_list) {
            return;
        }

        // HTML for our select printing post titles as loop
        $output = "";
        foreach ($committee_list as $index) {
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
            return wp_mail($to, "$subject", $message, $headers);
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
        // Explain why the nonce is not checked?
        // is there a good reason?
        //check_ajax_referer("registration-form-ajax");

        // Maps the ajax fields into Wordpress user fields and sanitize them
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
                echo 'Error Occurred during sign up. Message: ' . $user_id->get_error_message() . ". If problem persists contact site admin";
            }
            wp_die();
        }

        /** Basic user creation completed. Deal with IFSA specific part */
        
        $lc_id = ifsa_utility()->get_post_var('lc');
        

        /**  Check Local Committee */
        $lc_admin = ifsa_lc()->get_lc_admin($lc_id);
        if(is_wp_error($lc_admin)){
            echo "Invalid LC. try again or contact website admin. Error: ".$lc_admin->get_error_code();
            wp_die();
        }

        // Add LC id to user meta. This is used in the admin interface
        update_user_meta($user_id, 'ifsa_committee', $lc_id);

        /** Buddypress profile */

        $region_id = ifsa_utility()->get_post_var('ifsa_region');
        // Map Xprofile_name => POST_name
        $user_add_info = array(
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
            $user_add_info[$key] = ifsa_utility()->get_post_var($ajax_value);
        }
        // Add addition fields to profile

        $user_add_info['Local Committee Name'] = ifsa_lc()->get_lc_name($lc_id);
        $user_add_info['IFSA Region'] = ifsa_lc()->get_region_name($region_id);

        foreach ($user_add_info as $field_name => $field_value){
            $return = xprofile_set_field_data($field_name, $user_id, $field_value);
            if(!$return){
                error_log("Error in creating xprofile fields. Field name: $field_name, User id: $user_id, Field value: $field_value");
            }
        }
        // Update custom table for member verification
        // differentiate email invites
        // Consider update this to use Wp_Query
        $source = ifsa_utility()->get_post_var('utm_source') == 'invite' ? "Invite" : "Website"; // default source is website

        global $wpdb;
        $last_updated = bp_core_current_time();

        $query = "INSERT INTO {ifsa_lc()->lc_member_table} ( user_id,lc_adminid, committee_id ,region_id, action_date, member_status,source) VALUES ( %d,%d, %s, %s, %s, %d,%s )";
        $sql = $wpdb->prepare($query, $user_id, $lc_admin, $lc_id, $region_id, $last_updated, 0, $source);

        $result = $wpdb->query($sql);

        if (empty($result) || is_wp_error($result)) {
            echo "Error in updating user into database. Please contact website admin.";
            wp_die();
        }


        /** Email */

        // To member
        $res1 = ifsa_lc()->send_ifsa_email('register_email_user', array(
            '{user_email}' => $user_data['user_email'],
            '{user_name}' => $user_data['first_name'],
            '{user_fullname}' => $user_data['display_name'],
            '{lc_name}' => $user_add_info['Local Committee Name']
        ));
        // To LC admin
        $res2 = ifsa_lc()->send_ifsa_email('register_email_lc_admin', array(
            '{lc_admin_email}' => get_userdata($lc_admin)->user_email,
            '{user_name}' => $user_data['first_name'],
            '{user_fullname}' => $user_data['display_name'],
            '{lc_name}' => $user_add_info['Local Committee Name']
        ));

        if(is_wp_error($res1) || is_wp_error($res2)){
            echo "Error in sending email. Contact website administrator. {$res1->get_error_code()} {$res2->get_error_code()}";
            wp_die();
        }

        /** Log */

        ifsa_lc()->log("user registered",
            "Source: $source, LC: {$user_add_info['Local Committee Name']}", $user_id);

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

}
