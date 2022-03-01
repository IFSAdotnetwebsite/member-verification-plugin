<?php

class IFSALCMember
{

    /**
     * @var Closure|mixed|null
     */
    private $show_messages;

    // This is a flag to avoid logging action more than once.
    private $first_run = true;

    public $lc_member_table;

    /**
     * @var array
     */
    public $email_default_replacements;

    public function __construct($show_messages = false)
    {

        $this->show_messages = $show_messages;
        global $wpdb;
        $this->lc_member_table = $wpdb->prefix . 'ifsa_lc_member';

        $this->setup_emails();
    }

    /**
     * Initialize the email system
     * @return void
     */
    function setup_emails(){
        $days_off = array(
            'date_before_expiration' => get_option('date_before_expiration', 30),
            '1_date_after_expiration' => get_option('1_date_after_expiration', 15),
            '2_date_after_expiration' => get_option('2_date_after_expiration', 22),
            '3_date_after_expiration' => get_option('3_date_after_expiration', 30)
        );

        // Consider to move emails to separate class
        $this->email_default_replacements = array(
            '{user_name}' => "IFSA Member",
            '{lc_admin}' => "IFSA LC",
            '{registration_link_invite}' => home_url()."/member-register",
            '{lc_dashboard_link}' => home_url()."/members/me/memberlist",
            '{reject_reason}' => "",
            '{date_before_expiration}' =>
                $this->get_expiration_date()->modify("-{$days_off['date_before_expiration']} days"),
            '{expiration_date}' =>
                $this->get_expiration_date(),
            '{1_date_after_expiration}' =>
                $this->get_expiration_date()->modify("+{$days_off['1_date_after_expiration']} days"),
            '{2_date_after_expiration}' =>
                $this->get_expiration_date()->modify("+{$days_off['2_date_after_expiration']} days"),
            '{3_date_after_expiration}' =>
                $this->get_expiration_date()->modify("+{$days_off['3_date_after_expiration']} days")
        );

        global $wpdb;
        $this->lc_member_table = $wpdb->prefix."ifsa_lc_member";
    }

    /**
     * Gets the current expiration date
     * corrected for the current year
     * @return DateTime
     */
    function get_expiration_date(): DateTime
    {
        $expiration_date = get_option('expiration_date', new DateTime("20220831"));
        $month = $expiration_date->format('m');
        $day = $expiration_date->format('d');
        $current_year = (new DateTime())->format('Y');

        $expiration_date = (new DateTime())->setDate($current_year, $month, $day);

        // Check if expiration is already over for this year
        // If so add a year
        if ($expiration_date < new DateTime()){
            $expiration_date->modify('+1 year');
        }

        return $expiration_date;
    }

    /**
     * Show message in admin bar
     * If show_messages is true
     * @param string $message The message
     * @param string $notice_type accepts 'notice-error', 'notice-warning', 'notice-success'
     */
    function message(string $message, string $notice_type = 'notice-error')
    {
        if($this->show_messages){
            ifsa_utility()->admin_message($message, $notice_type);
        }

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
                $this->message("Error in LC Id. get in touch with website admin");
            }

            return "";
        }
        return $term_lc->name;
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
            $this->message("IFSA Member verification: Error! No LC admin for {$lc_name}");
            return new WP_Error("No LC Admin");
        }
        if (count($query->get_results()) > 1) {
            $lc_name = $this->get_lc_name($lc_id);
            $this->message("IFSA Member verification: Error! More than 1 LC Admin fo {$lc_name}");
            return new WP_Error("More than 1 LC Admin");
        } else {
            return $query->get_results()[0];
        }
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
            $this->message("Error! An LC can be only in one region. {$lc_id} si in {$n_regions}");
            return new WP_Error();
        }
        return $query->posts[0]->ID;
    }

    function get_region_name($region_id): string
    {
        $args = array(
            'post_type' => 'regions',
            'ID' => $region_id
            );
        $query = new WP_Query($args);

        return $query->posts[0]->post_title;
    }

    /**
     * user is an LC admin
     * @param $user_id
     * @return bool
     */
    function is_lc_admin($user_id){
        return $this->get_ifsa_member_type($user_id) == 'lc_admin';
    }

    /**
     * Check is current user can edit the lc admin page
     * Either super admin of owner of the page
     * @return bool
     */
    function can_edit_lc_admin(){
        return is_super_admin() || bp_is_my_profile();
    }

    /**
     * If is an LC admin page and user can edit it return the LC admin ID
     * otherwise false
     * @return false|int
     */
    function get_settings_lc_admin_id(){
        $user = bp_get_displayed_user();
        if(isset($user)){
            $user_id = $user->id;
            if ($this->can_edit_lc_admin() && $this->is_lc_admin($user_id)) return $user_id;
        } else {
            return false;
        }
    }
    /**
     * Returns the type of users for IFSA member verification
     * this can be 'lc_member', 'lc_admin' or 'none'
     * Priority is given to `lc_admin`
     * @param $user_id
     * @return string
     */
    function get_ifsa_member_type($user_id): string
    {
        $user = get_userdata($user_id);
        if ($user) {
            $roles = $user->roles;
            if (in_array('lc_admin', $roles)) {
                return 'lc_admin';
            } elseif (in_array('lc_member', $roles)) {
                return 'lc_member';
            } else {
                return 'none';
            }
        } else {
            return 'none';
        }
    }
    /**
     * IFSA Log
     * Log a message in IFSA Log table
     * Automatically gets admin user id (if relevant) and IP address
     * @param $log_action string Action type
     * @param $remark string Detail
     * @param $member_id int User affected by the action
     * @return void
     */
    public function log(string $log_action, string $remark, int $member_id)
    {
        global $wpdb;
        $ifsa_log = $wpdb->prefix . 'ifsa_log';
        $admin_id = get_current_user_id(); // This code must be run by an admin
        $action_time = current_time($type = "mysql");

        // Get user IP
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
            $this->message("Unknown error while saving logs, contact web admin");
        }
    }

    public function send_email($to, $subject, $message){
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = wp_mail($to, $subject, $message, $headers);
        if(!$sent){
            $this->message("Failed to send email: $to, $subject");
            error_log("[membership verification] Failed to send email: $to, $subject, $message");
        }
        return $sent;
    }

    /**
     * Sends an IFSA emails
     * @param $email_name string the name of the email (key in IFSA_EMAILS)
     * @param $args
     * @return void | WP_Error
     */
    public function send_ifsa_email($email_name, $args){
        if(!array_key_exists('{user_email}', $args) && !array_key_exists('{lc_admin_email}', $args)){
            return new WP_Error("Invalid arguments", "to email address is a required argument");
        }

        $email_defaults = IFSA_EMAILS[$email_name];
        $to = $this->replace_email($email_defaults['to'], $args);
        $subject = $this->replace_email(get_option($email_name."_subject", $email_defaults['subject']), $args);
        $content = $this->replace_email(get_option($email_name, $email_defaults['content'] ?? $email_defaults['subject']), $args);

        $res = $this->send_email($to, $subject, $content);

        if(!$res) return new WP_Error("Error sending email");
    }

    /**
     * Replaces the in the text using the given replacements and then the defaults replacements
     * @param $text
     * @param $replacements
     * @return string
     */
    function replace_email($text, $replacements): string
    {
        $replacements = array_merge($this->email_default_replacements, $replacements);
        return strtr($text, $replacements);
    }




}